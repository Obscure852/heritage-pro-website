<?php

namespace App\Services\Crm;

use App\Models\CrmCommercialDocumentArtifact;
use App\Models\CrmInvoice;
use App\Models\CrmQuote;
use App\Models\DiscussionMessageAttachment;
use App\Models\DiscussionThread;
use App\Models\DiscussionThreadParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CommercialDocumentShareService
{
    public function __construct(
        private readonly CommercialDocumentPdfService $pdfService
    ) {
    }

    public function shareQuote(CrmQuote $quote, User $sender, array $payload): DiscussionThread
    {
        $quote->forceFill([
            'status' => $quote->status === 'draft' ? 'sent' : $quote->status,
            'shared_at' => now(),
        ])->save();

        $artifact = $this->pdfService->ensureQuoteArtifact($quote, $sender);
        $thread = $this->createThread(
            'Quote',
            $quote->quote_number,
            $payload,
            $sender,
            $artifact
        );

        return $thread;
    }

    public function shareInvoice(CrmInvoice $invoice, User $sender, array $payload): DiscussionThread
    {
        $invoice->forceFill([
            'status' => $invoice->status === 'issued' ? 'sent' : $invoice->status,
            'shared_at' => now(),
        ])->save();

        $artifact = $this->pdfService->ensureInvoiceArtifact($invoice, $sender);
        $thread = $this->createThread(
            'Invoice',
            $invoice->invoice_number,
            $payload,
            $sender,
            $artifact
        );

        return $thread;
    }

    private function createThread(
        string $documentLabel,
        string $documentNumber,
        array $payload,
        User $sender,
        CrmCommercialDocumentArtifact $artifact
    ): DiscussionThread {
        $deliveryStatus = $this->resolveDeliveryStatus($payload['channel'], $payload['integration_id'] ?? null);

        $thread = DiscussionThread::query()->create([
            'owner_id' => $sender->id,
            'initiated_by_id' => $sender->id,
            'recipient_user_id' => $payload['recipient_user_id'] ?? null,
            'direct_participant_key' => $payload['channel'] === 'app' && filled($payload['recipient_user_id'])
                ? collect([(int) $sender->id, (int) $payload['recipient_user_id']])->sort()->implode(':')
                : null,
            'integration_id' => $payload['integration_id'] ?? null,
            'subject' => $payload['subject'],
            'channel' => $payload['channel'],
            'kind' => $payload['channel'] === 'app' ? 'direct' : 'external_direct',
            'recipient_email' => $payload['recipient_email'] ?? null,
            'recipient_phone' => $payload['recipient_phone'] ?? null,
            'delivery_status' => $deliveryStatus,
            'status' => $payload['channel'] === 'app'
                ? 'sent'
                : ($deliveryStatus === 'failed' ? 'failed' : ($deliveryStatus === 'sent' ? 'sent' : 'queued')),
            'last_message_at' => now(),
            'notes' => trim(implode("\n\n", array_filter([
                $payload['notes'] ?? null,
                $documentLabel . ' ' . $documentNumber,
            ]))) ?: null,
            'source_type' => strtolower($documentLabel),
            'source_id' => $documentLabel === 'Quote' ? $artifact->quote_id : $artifact->invoice_id,
            'target_type' => filled($payload['recipient_user_id']) ? 'user' : 'manual',
            'target_id' => $payload['recipient_user_id'] ?? null,
            'metadata_updated_at' => now(),
            'edited_by_id' => $sender->id,
        ]);

        $message = $thread->messages()->create([
            'user_id' => $sender->id,
            'direction' => 'outbound',
            'channel' => $thread->channel,
            'body' => $payload['body'],
            'delivery_status' => $deliveryStatus,
            'sent_at' => now(),
        ]);

        DiscussionMessageAttachment::query()->firstOrCreate(
            [
                'message_id' => $message->id,
                'path' => $artifact->path,
            ],
            [
                'uploaded_by_id' => $sender->id,
                'disk' => $artifact->disk ?: 'documents',
                'original_name' => $artifact->original_name,
                'mime_type' => $artifact->mime_type,
                'extension' => $artifact->extension,
                'size' => (int) $artifact->size,
            ]
        );

        if ($thread->channel === 'app') {
            foreach (collect([$sender->id, $thread->recipient_user_id])->filter()->unique()->values() as $userId) {
                DiscussionThreadParticipant::query()->updateOrCreate(
                    [
                        'thread_id' => $thread->id,
                        'user_id' => (int) $userId,
                    ],
                    [
                        'role' => (int) $userId === (int) $sender->id ? 'owner' : 'member',
                        'last_read_at' => (int) $userId === (int) $sender->id ? now() : null,
                    ]
                );
            }
        }

        $dispatchedStatus = $this->dispatchExternalMessage($thread, $message, $artifact);

        $thread->forceFill([
            'delivery_status' => $dispatchedStatus,
            'last_message_at' => $message->sent_at,
            'status' => $thread->channel === 'app'
                ? 'sent'
                : $this->resolveStatusFromDelivery($dispatchedStatus),
        ])->save();

        $artifact->forceFill([
            'shared_discussion_thread_id' => $thread->id,
        ])->save();

        return $thread->fresh(['messages.user', 'initiatedBy', 'recipientUser', 'integration']);
    }

    private function resolveDeliveryStatus(string $channel, ?int $integrationId): string
    {
        if ($channel === 'app') {
            return 'sent';
        }

        if ($channel === 'email') {
            return 'queued';
        }

        return $integrationId ? 'queued' : 'pending_integration';
    }

    private function dispatchExternalMessage($thread, $message, CrmCommercialDocumentArtifact $artifact): string
    {
        if ($thread->channel === 'app') {
            return 'sent';
        }

        if ($thread->channel === 'email' && filled($thread->recipient_email)) {
            try {
                Mail::raw($message->body, function ($mail) use ($thread, $artifact) {
                    $mail->to($thread->recipient_email)
                        ->subject($thread->subject)
                        ->attach(
                            Storage::disk($artifact->disk ?: 'documents')->path($artifact->path),
                            [
                                'as' => $artifact->original_name,
                                'mime' => $artifact->mime_type ?: 'application/pdf',
                            ]
                        );
                });

                return 'sent';
            } catch (Throwable) {
                return 'failed';
            }
        }

        return $thread->integration_id ? 'queued' : 'pending_integration';
    }

    private function resolveStatusFromDelivery(string $deliveryStatus): string
    {
        return match ($deliveryStatus) {
            'failed' => 'failed',
            'queued', 'pending_integration' => 'queued',
            default => 'sent',
        };
    }
}
