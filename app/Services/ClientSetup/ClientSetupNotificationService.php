<?php

namespace App\Services\ClientSetup;

use App\Mail\ClientSetupInvitationMail;
use App\Mail\ClientSetupNotificationMail;
use App\Models\CrmClientSetupInvitation;
use App\Models\CrmClientSetupNotification;
use App\Models\CrmClientSetupSubmission;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class ClientSetupNotificationService
{
    public function sendInvitation(CrmClientSetupInvitation $invitation, string $setupUrl): CrmClientSetupNotification
    {
        $notification = $this->notificationForRecipient(
            $invitation->submission,
            $invitation,
            'client',
            null,
            $invitation->email,
            $invitation->contact_name,
            'invitation_sent',
            [
                'subject' => 'Heritage Pro client setup invitation',
                'heading' => 'Your Heritage Pro setup is ready',
                'message' => 'Use the secure link below to start your client setup. Your progress will be saved as you work.',
            ],
            'invitation:' . hash('sha256', $setupUrl)
        );

        $this->deliver($notification, function () use ($invitation, $setupUrl): void {
            Mail::to($invitation->email)->send(new ClientSetupInvitationMail($invitation, $setupUrl));
        });

        return $notification->fresh();
    }

    /**
     * Send a workflow notification to the client, the assigned CRM owner, or both.
     * The context key makes retries idempotent without suppressing later events of the same type.
     */
    public function notifySubmission(
        CrmClientSetupSubmission $submission,
        string $eventKey,
        array $options = []
    ): array {
        if (! config('client_setup.notifications.enabled', true)) {
            return [];
        }

        $submission->loadMissing(['invitations', 'assignedTo']);
        $audiences = $options['audiences'] ?? ['client', 'crm'];
        $copy = $this->copy($submission, $eventKey, $options);
        $contextKey = (string) ($options['context_key'] ?? 'default');
        $sent = [];

        foreach ($audiences as $audience) {
            foreach ($this->recipients($submission, $audience) as $recipient) {
                $notification = $this->notificationForRecipient(
                    $submission,
                    $recipient['invitation'],
                    $audience,
                    $recipient['user'],
                    $recipient['email'],
                    $recipient['name'],
                    $eventKey,
                    $copy[$audience],
                    "submission:{$submission->id}:{$eventKey}:{$audience}:" . Str::lower($recipient['email']) . ":{$contextKey}"
                );

                $this->deliver($notification);
                $sent[] = $notification->fresh();
            }
        }

        return $sent;
    }

    /**
     * Retry failed or delayed deliveries without throwing an email provider error into a web request.
     */
    public function retryDue(?Carbon $now = null): array
    {
        $now ??= now();
        $maxAttempts = max(1, (int) config('client_setup.notifications.mail_retry_attempts', 3));
        $summary = ['attempted' => 0, 'sent' => 0, 'failed' => 0];

        CrmClientSetupNotification::query()
            ->whereIn('status', ['pending', 'failed'])
            ->where('attempts', '<', $maxAttempts)
            ->where(function ($query) use ($now): void {
                $query->whereNull('available_at')->orWhere('available_at', '<=', $now);
            })
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->each(function (CrmClientSetupNotification $notification) use (&$summary): void {
                $summary['attempted']++;
                $this->deliver($notification);
                $notification->refresh();
                $summary[$notification->status === 'sent' ? 'sent' : 'failed']++;
            });

        return $summary;
    }

    public function sendOperationalReminders(?Carbon $now = null): array
    {
        $now ??= now();
        $settings = config('client_setup.notifications.reminders', []);
        $summary = ['draft_reminders' => 0, 'overdue_reviews' => 0, 'expiry_warnings' => 0];

        if (! ($settings['enabled'] ?? true)) {
            return $summary;
        }

        $repeatHours = max(1, (int) ($settings['repeat_after_hours'] ?? 72));
        $bucket = Carbon::createFromTimestamp(
            intdiv($now->getTimestamp(), $repeatHours * 3600) * $repeatHours * 3600,
            $now->getTimezone()
        )->format('YmdHis');

        $draftCutoff = $now->copy()->subHours(max(1, (int) ($settings['draft_after_hours'] ?? 72)));
        CrmClientSetupSubmission::query()
            ->with(['invitations', 'assignedTo'])
            ->where('status', 'draft')
            ->whereNotNull('last_activity_at')
            ->where('last_activity_at', '<=', $draftCutoff)
            ->orderBy('id')
            ->limit(250)
            ->get()
            ->each(function (CrmClientSetupSubmission $submission) use (&$summary, $bucket): void {
                $notifications = $this->notifySubmission($submission, 'draft_reminder', [
                    'context_key' => "reminder:{$bucket}",
                    'audiences' => ['client', 'crm'],
                ]);
                $summary['draft_reminders'] += count($notifications);
            });

        $reviewCutoff = $now->copy()->subHours(max(1, (int) ($settings['submitted_after_hours'] ?? 48)));
        CrmClientSetupSubmission::query()
            ->with(['invitations', 'assignedTo'])
            ->whereIn('status', ['academic_submitted', 'supplemental_in_progress', 'complete_submission', 'changes_requested'])
            ->whereNotNull('last_activity_at')
            ->where('last_activity_at', '<=', $reviewCutoff)
            ->orderBy('id')
            ->limit(250)
            ->get()
            ->each(function (CrmClientSetupSubmission $submission) use (&$summary, $bucket): void {
                $notifications = $this->notifySubmission($submission, 'submission_overdue', [
                    'context_key' => "reminder:{$bucket}",
                    'audiences' => ['crm'],
                ]);
                $summary['overdue_reviews'] += count($notifications);
            });

        $expiryCutoff = $now->copy()->addHours(max(1, (int) ($settings['expiry_warning_hours'] ?? 72)));
        CrmClientSetupInvitation::query()
            ->with(['submission.invitations', 'submission.assignedTo'])
            ->where('status', 'active')
            ->whereBetween('expires_at', [$now, $expiryCutoff])
            ->orderBy('id')
            ->limit(250)
            ->get()
            ->each(function (CrmClientSetupInvitation $invitation) use (&$summary, $bucket): void {
                $notifications = $this->notifySubmission($invitation->submission, 'invitation_expiry_warning', [
                    'context_key' => "expiry:{$bucket}",
                    'audiences' => ['client', 'crm'],
                ]);
                $summary['expiry_warnings'] += count($notifications);
            });

        return $summary;
    }

    private function notificationForRecipient(
        ?CrmClientSetupSubmission $submission,
        ?CrmClientSetupInvitation $invitation,
        string $audience,
        ?User $user,
        string $email,
        ?string $name,
        string $eventKey,
        array $payload,
        string $idempotencyKey
    ): CrmClientSetupNotification {
        $payload['heading'] ??= $payload['subject'] ?? ucfirst(str_replace('_', ' ', $eventKey));
        $payload['message'] ??= 'There is an update to your Heritage Pro client setup.';
        $subject = (string) ($payload['subject'] ?? $payload['heading']);

        $notification = CrmClientSetupNotification::query()->firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'submission_id' => $submission?->id,
                'invitation_id' => $invitation?->id,
                'recipient_user_id' => $user?->id,
                'audience' => $audience,
                'event_key' => $eventKey,
                'channel' => 'email',
                'recipient_email' => Str::lower(trim($email)),
                'recipient_name' => $name ? trim($name) : null,
                'subject' => $subject,
                'payload' => $this->storedPayload($payload, $audience),
                'status' => 'pending',
                'available_at' => now(),
            ]
        );

        // A client action URL can contain the one-time raw invitation token.
        // It may be used for this immediate send, but must never be persisted.
        $notification->setAttribute('payload', $payload);

        return $notification;
    }

    private function deliver(CrmClientSetupNotification $notification, ?callable $sender = null): void
    {
        if ($notification->status === 'sent') {
            return;
        }

        $maxAttempts = max(1, (int) config('client_setup.notifications.mail_retry_attempts', 3));

        if ($notification->attempts >= $maxAttempts) {
            return;
        }

        $notification->forceFill([
            'status' => 'sending',
            'attempts' => $notification->attempts + 1,
            'last_attempt_at' => now(),
            'failure_message' => null,
        ])->save();

        try {
            $sender ??= function () use ($notification): void {
                Mail::to($notification->recipient_email, $notification->recipient_name)
                    ->send(new ClientSetupNotificationMail($notification));
            };
            $sender();
            $notification->forceFill([
                'status' => 'sent',
                'sent_at' => now(),
                'available_at' => null,
                'failed_at' => null,
            ])->save();
        } catch (Throwable $exception) {
            $notification->forceFill([
                'status' => 'failed',
                'failed_at' => now(),
                'available_at' => now()->addMinutes(15 * max(1, $notification->attempts)),
                'failure_message' => Str::limit($exception->getMessage(), 2000),
            ])->save();
            Log::error('Client setup notification delivery failed.', [
                'notification_uuid' => $notification->uuid,
                'submission_id' => $notification->submission_id,
                'event_key' => $notification->event_key,
                'recipient' => $notification->recipient_email,
                'attempt' => $notification->attempts,
                'reason' => $exception->getMessage(),
            ]);
        }
    }

    private function recipients(CrmClientSetupSubmission $submission, string $audience): array
    {
        if ($audience === 'client') {
            $invitation = $submission->invitations->sortByDesc('id')->first();

            return $invitation && filter_var($invitation->email, FILTER_VALIDATE_EMAIL)
                ? [[
                    'email' => $invitation->email,
                    'name' => $invitation->contact_name,
                    'invitation' => $invitation,
                    'user' => null,
                ]]
                : [];
        }

        if ($audience !== 'crm') {
            return [];
        }

        if ($submission->assignedTo && $submission->assignedTo->active && filter_var($submission->assignedTo->email, FILTER_VALIDATE_EMAIL)) {
            return [[
                'email' => $submission->assignedTo->email,
                'name' => $submission->assignedTo->name,
                'invitation' => null,
                'user' => $submission->assignedTo,
            ]];
        }

        return User::query()
            ->where('active', true)
            ->whereIn('role', config('client_setup.notifications.crm_fallback_roles', ['admin', 'manager']))
            ->whereNotNull('email')
            ->get()
            ->filter(fn (User $user): bool => filter_var($user->email, FILTER_VALIDATE_EMAIL))
            ->map(fn (User $user): array => [
                'email' => $user->email,
                'name' => $user->name,
                'invitation' => null,
                'user' => $user,
            ])
            ->values()
            ->all();
    }

    private function copy(CrmClientSetupSubmission $submission, string $eventKey, array $options): array
    {
        $institution = data_get($submission->payloadArray(), 'scope.institution_legal_name') ?: 'your institution';
        $subject = $options['subject'] ?? null;
        $heading = $options['heading'] ?? null;
        $message = $options['message'] ?? null;
        $details = $options['details'] ?? [];
        $actionUrl = $options['action_url'] ?? null;
        $actionLabel = $options['action_label'] ?? null;

        $labels = [
            'draft_created' => ['New client setup draft', "A new client setup draft for {$institution} is ready for implementation follow-up."],
            'academic_submitted' => ['Academic setup received', "The academic configuration for {$institution} has been submitted for review."],
            'supplemental_received' => ['Supplemental setup received', "New supplemental setup information for {$institution} is ready for review."],
            'final_submission_received' => ['Client setup complete', "The client setup submission for {$institution} is ready for implementation review."],
            'changes_requested' => ['Changes requested on client setup', "The implementation team has requested changes to the {$institution} setup."],
            'approved' => ['Client setup approved', "The {$institution} client setup has been approved by the implementation team."],
            'draft_reminder' => ['Client setup needs attention', "The {$institution} setup has been waiting for an update."],
            'submission_overdue' => ['Client setup review overdue', "The {$institution} setup has been waiting for implementation review."],
            'migration_validation_failed' => ['Migration workbook needs attention', "A migration workbook for {$institution} contains validation errors."],
            'client_change_response' => ['Client responded to a change request', "The client has responded to a change request for {$institution}."],
            'invitation_expiry_warning' => ['Client setup link expiring soon', "The setup invitation for {$institution} will expire soon."],
        ];
        [$defaultHeading, $defaultMessage] = $labels[$eventKey] ?? ['Client setup update', "There is an update to the {$institution} client setup."];

        $base = [
            'subject' => $subject ?: $defaultHeading,
            'heading' => $heading ?: $defaultHeading,
            'message' => $message ?: $defaultMessage,
            'details' => $details,
            'action_url' => $actionUrl,
            'action_label' => $actionLabel,
        ];

        return [
            'client' => $base,
            'crm' => array_merge($base, [
                'action_url' => $options['crm_action_url'] ?? (route('crm.client-setup.show', $submission)),
                'action_label' => $options['crm_action_label'] ?? 'Open client setup review',
            ]),
        ];
    }

    private function storedPayload(array $payload, string $audience): array
    {
        if ($audience === 'client') {
            unset($payload['action_url']);
        }

        return $payload;
    }
}
