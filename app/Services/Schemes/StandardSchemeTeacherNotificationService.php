<?php

namespace App\Services\Schemes;

use App\Mail\Schemes\StandardSchemeDistributedMail;
use App\Models\Schemes\StandardScheme;
use App\Models\User;
use App\Services\Messaging\StaffMessagingFeatureService;
use App\Services\Messaging\StaffMessagingService;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StandardSchemeTeacherNotificationService
{
    public function __construct(
        protected StaffMessagingService $staffMessagingService,
        protected StaffMessagingFeatureService $staffMessagingFeatureService,
        protected SettingsService $settingsService,
    ) {
    }

    /**
     * @param array<int, array{teacher_id:int, scheme_ids:array<int>, items:array<int, array{scheme_id:int, label:string}>}> $manifest
     */
    public function notifyTeachers(StandardScheme $standardScheme, User $actor, array $manifest): void
    {
        if (empty($manifest)) {
            return;
        }

        $teacherIds = collect($manifest)
            ->pluck('teacher_id')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($teacherIds)) {
            return;
        }

        $teachers = User::query()
            ->whereIn('id', $teacherIds)
            ->where('active', true)
            ->get()
            ->keyBy(fn (User $teacher): int => (int) $teacher->id);

        $emailEnabled = (bool) $this->settingsService->get('features.email_enabled', true);
        $directMessagesEnabled = $this->staffMessagingFeatureService->directMessagesEnabled();

        $emailQueuedCount = 0;
        $emailSkippedCount = 0;
        $directMessageSentCount = 0;
        $directMessageSkippedCount = 0;

        foreach ($manifest as $teacherId => $entry) {
            $teacher = $teachers->get((int) $teacherId);
            $schemeItems = $this->normalizeSchemeItems($entry['items'] ?? []);

            if (!$teacher || empty($schemeItems)) {
                Log::warning('Skipping standard scheme teacher notification because the recipient or payload is missing.', [
                    'standard_scheme_id' => $standardScheme->id,
                    'actor_id' => $actor->id,
                    'teacher_id' => (int) $teacherId,
                ]);
                continue;
            }

            if ($emailEnabled && $teacher->hasValidEmail()) {
                try {
                    Mail::to($teacher)->queue(
                        new StandardSchemeDistributedMail($standardScheme, $actor, $teacher, $schemeItems)
                    );
                    $emailQueuedCount++;
                } catch (\Throwable $exception) {
                    Log::error('Failed to queue standard scheme distribution email.', [
                        'standard_scheme_id' => $standardScheme->id,
                        'actor_id' => $actor->id,
                        'teacher_id' => $teacher->id,
                        'channel' => 'email',
                        'error' => $exception->getMessage(),
                    ]);
                }
            } else {
                $emailSkippedCount++;
            }

            if (!$directMessagesEnabled || (int) $teacher->id === (int) $actor->id) {
                $directMessageSkippedCount++;
                continue;
            }

            try {
                $this->staffMessagingService->startConversation(
                    $actor,
                    $teacher,
                    $this->buildDirectMessageBody($standardScheme, $actor, $schemeItems)
                );
                $directMessageSentCount++;
            } catch (\Throwable $exception) {
                Log::error('Failed to send standard scheme distribution direct message.', [
                    'standard_scheme_id' => $standardScheme->id,
                    'actor_id' => $actor->id,
                    'teacher_id' => $teacher->id,
                    'channel' => 'direct_message',
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        Log::info('Processed standard scheme teacher notifications.', [
            'standard_scheme_id' => $standardScheme->id,
            'actor_id' => $actor->id,
            'teacher_count' => count($teacherIds),
            'email_enabled' => $emailEnabled,
            'direct_messages_enabled' => $directMessagesEnabled,
            'email_queued_count' => $emailQueuedCount,
            'email_skipped_count' => $emailSkippedCount,
            'direct_message_sent_count' => $directMessageSentCount,
            'direct_message_skipped_count' => $directMessageSkippedCount,
        ]);
    }

    /**
     * @param array<int, array{scheme_id:int, label:string}> $items
     * @return array<int, array{scheme_id:int, label:string, url:string}>
     */
    private function normalizeSchemeItems(array $items): array
    {
        return collect($items)
            ->filter(fn ($item): bool => !empty($item['scheme_id']))
            ->unique(fn ($item): int => (int) $item['scheme_id'])
            ->map(function ($item): array {
                $schemeId = (int) $item['scheme_id'];
                $label = trim((string) ($item['label'] ?? 'Teaching assignment'));

                return [
                    'scheme_id' => $schemeId,
                    'label' => $label !== '' ? $label : 'Teaching assignment',
                    'url' => route('schemes.show', $schemeId),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param array<int, array{scheme_id:int, label:string, url:string}> $schemeItems
     */
    private function buildDirectMessageBody(StandardScheme $standardScheme, User $actor, array $schemeItems): string
    {
        $lines = [
            sprintf(
                '%s shared the standard scheme for %s, %s, Term %s %s and new schemes have been created for you.',
                $actor->full_name ?: 'A staff member',
                $standardScheme->subject?->name ?? 'this subject',
                $standardScheme->grade?->name ?? 'this grade',
                $standardScheme->term?->term ?? '—',
                $standardScheme->term?->year ?? '—'
            ),
            '',
            'New schemes:',
        ];

        foreach ($schemeItems as $item) {
            $lines[] = '- ' . $item['label'];
        }

        $lines[] = '';
        $lines[] = 'Open in the app:';

        foreach ($schemeItems as $item) {
            $lines[] = $item['url'];
        }

        return implode("\n", $lines);
    }
}
