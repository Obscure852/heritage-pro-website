<?php

namespace App\Services\Documents;

use App\Mail\Documents\DocumentExpiringMail;
use App\Models\Document;
use App\Models\DocumentAudit;
use App\Models\DocumentNotification;
use App\Models\DocumentRetentionPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RetentionService {
    protected DocumentSettingService $settingService;

    public function __construct(
        protected NotificationService $notificationService,
        DocumentSettingService $settingService,
    ) {
        $this->settingService = $settingService;
    }

    /**
     * Process document expirations: 7-day warnings, grace period notices, and auto-archival.
     *
     * @return array{warnings: int, grace_notices: int, archived: int, skipped_legal_hold: int}
     */
    public function processExpirations(): array {
        $results = ['warnings' => 0, 'grace_notices' => 0, 'archived' => 0, 'skipped_legal_hold' => 0];
        $gracePeriodDays = $this->settingService->get('retention.grace_period_days', 30);

        // 1. 7-day pre-expiry warnings (RET-02)
        $expiringDocuments = Document::whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(7))
            ->where('status', '!=', Document::STATUS_ARCHIVED)
            ->where('legal_hold', false)
            ->whereNull('expiry_warning_sent_at')
            ->with('owner:id,firstname,lastname,email')
            ->get();

        foreach ($expiringDocuments as $document) {
            if (!$document->owner) {
                continue;
            }

            DocumentNotification::create([
                'user_id' => $document->owner_id,
                'type' => 'document_expiring',
                'title' => 'Document Expiring Soon',
                'message' => "'{$document->title}' expires on {$document->expiry_date->format('d M Y')}.",
                'url' => route('documents.show', $document),
                'data' => [
                    'document_id' => $document->id,
                    'expiry_date' => $document->expiry_date->toDateString(),
                    'icon' => 'bx-time-five',
                    'color' => 'warning',
                ],
            ]);

            try {
                Mail::to($document->owner)->queue(new DocumentExpiringMail($document));
            } catch (\Throwable $e) {
                Log::error('Failed to send document expiring email', [
                    'document_id' => $document->id,
                    'owner_id' => $document->owner_id,
                    'error' => $e->getMessage(),
                ]);
            }

            $document->update(['expiry_warning_sent_at' => now()]);
            $results['warnings']++;
        }

        // 2. Grace period entry notification
        $graceDocuments = Document::whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->where('expiry_date', '>', now()->subDays($gracePeriodDays))
            ->where('status', '!=', Document::STATUS_ARCHIVED)
            ->whereNull('grace_period_notification_sent_at')
            ->with('owner:id,firstname,lastname,email')
            ->get();

        foreach ($graceDocuments as $document) {
            if (!$document->owner) {
                continue;
            }

            $remainingDays = (int) now()->diffInDays($document->expiry_date->addDays($gracePeriodDays), false);

            DocumentNotification::create([
                'user_id' => $document->owner_id,
                'type' => 'document_grace_period',
                'title' => 'Document Will Be Archived',
                'message' => "'{$document->title}' has expired and will be archived in {$remainingDays} days. You can extend or renew the expiry date to prevent archival.",
                'url' => route('documents.show', $document),
                'data' => [
                    'document_id' => $document->id,
                    'remaining_days' => $remainingDays,
                    'icon' => 'bx-archive',
                    'color' => 'danger',
                ],
            ]);

            $document->update(['grace_period_notification_sent_at' => now()]);
            $results['grace_notices']++;
        }

        // 3. Grace period auto-archive (RET-03)
        $expiredDocuments = Document::whereNotNull('expiry_date')
            ->where('expiry_date', '<', now()->subDays($gracePeriodDays))
            ->where('status', '!=', Document::STATUS_ARCHIVED)
            ->get();

        foreach ($expiredDocuments as $document) {
            if ($document->legal_hold) {
                $results['skipped_legal_hold']++;
                continue;
            }

            DB::transaction(function () use ($document) {
                $document->update([
                    'status' => Document::STATUS_ARCHIVED,
                    'archived_at' => now(),
                ]);

                DocumentAudit::create([
                    'document_id' => $document->id,
                    'action' => DocumentAudit::ACTION_ARCHIVED,
                    'metadata' => ['reason' => 'auto_archived_grace_period_expired'],
                ]);
            });

            $results['archived']++;
        }

        return $results;
    }

    /**
     * Execute active retention policies against matching documents.
     *
     * @return array{policies_run: int, documents_affected: int}
     */
    public function processRetentionPolicies(): array {
        $results = ['policies_run' => 0, 'documents_affected' => 0];

        $policies = DocumentRetentionPolicy::where('is_active', true)->get();

        foreach ($policies as $policy) {
            $query = Document::where('status', '!=', Document::STATUS_ARCHIVED)
                ->where('legal_hold', false)
                ->where('created_at', '<', now()->subDays($policy->retention_days));

            // Apply category condition if set
            $conditions = $policy->conditions ?? [];
            if (!empty($conditions['category_id'])) {
                $query->where('category_id', $conditions['category_id']);
            }

            $documents = $query->get();
            $affected = 0;

            foreach ($documents as $document) {
                switch ($policy->action) {
                    case DocumentRetentionPolicy::ACTION_ARCHIVE:
                        DB::transaction(function () use ($document) {
                            $document->update([
                                'status' => Document::STATUS_ARCHIVED,
                                'archived_at' => now(),
                            ]);
                            DocumentAudit::create([
                                'document_id' => $document->id,
                                'action' => DocumentAudit::ACTION_ARCHIVED,
                                'metadata' => ['reason' => 'retention_policy'],
                            ]);
                        });
                        $affected++;
                        break;

                    case DocumentRetentionPolicy::ACTION_DELETE:
                        DB::transaction(function () use ($document) {
                            DocumentAudit::create([
                                'document_id' => $document->id,
                                'action' => DocumentAudit::ACTION_TRASHED,
                                'metadata' => ['reason' => 'retention_policy'],
                            ]);
                            $document->delete(); // soft delete
                        });
                        $affected++;
                        break;

                    case DocumentRetentionPolicy::ACTION_NOTIFY:
                        if ($document->owner) {
                            DocumentNotification::create([
                                'user_id' => $document->owner_id,
                                'type' => 'retention_policy_notice',
                                'title' => 'Retention Policy Notice',
                                'message' => "'{$document->title}' has exceeded the retention period of {$policy->retention_days} days.",
                                'url' => route('documents.show', $document),
                                'data' => [
                                    'document_id' => $document->id,
                                    'policy_id' => $policy->id,
                                    'icon' => 'bx-info-circle',
                                    'color' => 'info',
                                ],
                            ]);
                        }
                        $affected++;
                        break;
                }
            }

            $policy->update([
                'last_run_at' => now(),
                'next_run_at' => now()->addDay(),
            ]);

            $results['policies_run']++;
            $results['documents_affected'] += $affected;
        }

        return $results;
    }

    /**
     * Renew/extend a document's expiry date.
     */
    public function renewExpiry(Document $document, string $newExpiryDate): void {
        DB::transaction(function () use ($document, $newExpiryDate) {
            $oldDate = $document->expiry_date?->toDateString();

            $document->update([
                'expiry_date' => $newExpiryDate,
                'expiry_warning_sent_at' => null,
                'grace_period_notification_sent_at' => null,
            ]);

            DocumentAudit::create([
                'document_id' => $document->id,
                'user_id' => auth()->id(),
                'action' => DocumentAudit::ACTION_UPDATED,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => [
                    'field' => 'expiry_date',
                    'old_value' => $oldDate,
                    'new_value' => $newExpiryDate,
                    'reason' => 'expiry_renewed',
                ],
            ]);
        });
    }
}
