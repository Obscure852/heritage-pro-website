<?php

namespace App\Services\ClientSetup;

use App\Mail\ClientSetupVerificationCodeMail;
use App\Models\CrmClientSetupInvitation;
use App\Models\CrmClientSetupSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

class ClientSetupInvitationService
{
    public function __construct(
        private readonly ClientSetupAuditService $auditService,
        private readonly ClientSetupNotificationService $notificationService
    ) {
    }

    public function create(array $attributes, ?User $createdBy = null): array
    {
        $email = strtolower(trim((string) ($attributes['email'] ?? '')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('A valid client email address is required.');
        }

        $rawToken = bin2hex(random_bytes(32));
        $expiresAt = $attributes['expires_at'] ?? now()->addDays(config('client_setup.invitation_expires_days', 30));

        $result = DB::transaction(function () use ($attributes, $createdBy, $email, $expiresAt, $rawToken) {
            $submission = CrmClientSetupSubmission::query()->create([
                'lead_id' => $attributes['lead_id'] ?? null,
                'customer_id' => $attributes['customer_id'] ?? null,
                'primary_contact_id' => $attributes['primary_contact_id'] ?? null,
                'assigned_to_id' => $attributes['assigned_to_id'] ?? null,
                'schema_version' => config('client_setup.schema_version', '1.0'),
                'status' => 'draft',
                'academic_status' => 'not_started',
                'payload' => [],
                'completed_stages' => [],
                'last_activity_at' => now(),
            ]);

            $invitation = $submission->invitations()->create([
                'created_by_id' => $createdBy?->id,
                'email' => $email,
                'contact_name' => $attributes['contact_name'] ?? null,
                'token_hash' => $this->hashToken($rawToken),
                'status' => 'active',
                'expires_at' => $expiresAt,
            ]);

            $this->auditService->record($submission, 'invitation_created', [
                'invitation' => $invitation,
                'user' => $createdBy,
                'actor_type' => $createdBy ? 'crm_user' : 'system',
                'metadata' => [
                    'expires_at' => $expiresAt instanceof \DateTimeInterface ? $expiresAt->toIso8601String() : (string) $expiresAt,
                ],
            ]);

            return [
                'submission' => $submission->fresh(),
                'invitation' => $invitation->fresh(),
            ];
        });

        if ($createdBy) {
            $this->notificationService->notifySubmission(
                $result['submission']->load(['invitations', 'assignedTo']),
                'draft_created',
                [
                    'audiences' => ['crm'],
                    'context_key' => 'draft-created:' . $result['submission']->uuid,
                ]
            );
        }

        return array_merge($result, [
            'raw_token' => $rawToken,
            'url' => route(config('client_setup.invitation_url_route', 'client-setup.entry'), ['token' => $rawToken]),
        ]);
    }

    public function sendInvitation(CrmClientSetupInvitation $invitation, string $rawToken): void
    {
        if (! $invitation->isUsable()) {
            throw new RuntimeException('This setup invitation is no longer active.');
        }

        $this->notificationService->sendInvitation(
            $invitation,
            route(config('client_setup.invitation_url_route', 'client-setup.entry'), ['token' => $rawToken])
        );
    }

    public function resend(CrmClientSetupInvitation $invitation, ?User $actor = null): array
    {
        $rawToken = bin2hex(random_bytes(32));

        $updated = DB::transaction(function () use ($invitation, $rawToken, $actor): CrmClientSetupInvitation {
            $locked = CrmClientSetupInvitation::query()
                ->with('submission')
                ->lockForUpdate()
                ->findOrFail($invitation->id);

            $locked->forceFill([
                'token_hash' => $this->hashToken($rawToken),
                'status' => 'active',
                'expires_at' => now()->addDays(config('client_setup.invitation_expires_days', 30)),
                'revoked_at' => null,
                'verified_at' => null,
                'verification_code_hash' => null,
                'verification_code_expires_at' => null,
                'verification_attempts' => 0,
            ])->save();

            $this->auditService->record($locked->submission, 'invitation_resent', [
                'invitation' => $locked,
                'user' => $actor,
                'actor_type' => $actor ? 'crm_user' : 'client',
            ]);

            return $locked->fresh();
        });

        $url = route(config('client_setup.invitation_url_route', 'client-setup.entry'), ['token' => $rawToken]);
        $this->sendInvitation($updated, $rawToken);

        return ['invitation' => $updated, 'raw_token' => $rawToken, 'url' => $url];
    }

    public function resendForEmail(string $email): bool
    {
        $invitation = CrmClientSetupInvitation::query()
            ->where('email', strtolower(trim($email)))
            ->where('status', 'active')
            ->latest('id')
            ->first();

        if (! $invitation || ! $invitation->isUsable()) {
            return false;
        }

        $result = $this->resend($invitation);

        $this->auditService->record($result['invitation']->submission, 'resume_link_requested', [
            'invitation' => $result['invitation'],
            'actor_type' => 'client',
        ]);

        return true;
    }

    public function findByToken(string $rawToken): ?CrmClientSetupInvitation
    {
        if ($rawToken === '' || strlen($rawToken) !== 64 || ! ctype_xdigit($rawToken)) {
            return null;
        }

        return CrmClientSetupInvitation::query()
            ->with('submission')
            ->where('token_hash', $this->hashToken($rawToken))
            ->first();
    }

    public function markAccessed(CrmClientSetupInvitation $invitation): void
    {
        DB::transaction(function () use ($invitation): void {
            $invitation = CrmClientSetupInvitation::query()->lockForUpdate()->findOrFail($invitation->id);
            $invitation->forceFill(['last_accessed_at' => now()])->save();
            $this->auditService->record($invitation->submission, 'invitation_accessed', [
                'invitation' => $invitation,
            ]);
        });
    }

    public function requestVerificationCode(CrmClientSetupInvitation $invitation): void
    {
        $code = DB::transaction(function () use ($invitation): string {
            $lockedInvitation = CrmClientSetupInvitation::query()
                ->with('submission')
                ->lockForUpdate()
                ->findOrFail($invitation->id);

            if (! $lockedInvitation->isUsable()) {
                throw new RuntimeException('This setup invitation is no longer active.');
            }

            if ($lockedInvitation->verification_sent_at
                && $lockedInvitation->verification_sent_at->gt(now()->subSeconds(config('client_setup.verification_code_resend_cooldown_seconds', 60)))) {
                throw new RuntimeException('A verification code was sent recently. Please wait before requesting another code.');
            }

            $code = (string) random_int(100000, 999999);
            $lockedInvitation->forceFill([
                'verification_code_hash' => Hash::make($code),
                'verification_code_expires_at' => now()->addMinutes(config('client_setup.verification_code_expires_minutes', 10)),
                'verification_attempts' => 0,
                'verification_sent_at' => now(),
            ])->save();

            $this->auditService->record($lockedInvitation->submission, 'verification_code_requested', [
                'invitation' => $lockedInvitation,
            ]);

            return $code;
        });

        try {
            Mail::to($invitation->email)->send(new ClientSetupVerificationCodeMail(
                $invitation,
                $code,
                (int) config('client_setup.verification_code_expires_minutes', 10)
            ));
        } catch (\Throwable $exception) {
            Log::error('Client setup verification code email failed.', [
                'invitation_uuid' => $invitation->uuid,
                'reason' => $exception->getMessage(),
            ]);
        }
    }

    public function verifyCode(CrmClientSetupInvitation $invitation, string $code): bool
    {
        return DB::transaction(function () use ($invitation, $code): bool {
            $lockedInvitation = CrmClientSetupInvitation::query()
                ->with('submission')
                ->lockForUpdate()
                ->findOrFail($invitation->id);

            if (! $lockedInvitation->isUsable()
                || ! $lockedInvitation->verification_code_hash
                || ! $lockedInvitation->verification_code_expires_at
                || $lockedInvitation->verification_code_expires_at->isPast()
                || $lockedInvitation->verification_attempts >= config('client_setup.verification_max_attempts', 5)) {
                return false;
            }

            if (! Hash::check($code, $lockedInvitation->verification_code_hash)) {
                $lockedInvitation->increment('verification_attempts');
                $this->auditService->record($lockedInvitation->submission, 'verification_code_failed', [
                    'invitation' => $lockedInvitation,
                    'metadata' => ['attempt' => $lockedInvitation->verification_attempts],
                ]);

                return false;
            }

            $lockedInvitation->forceFill([
                'verified_at' => now(),
                'verification_code_hash' => null,
                'verification_code_expires_at' => null,
                'verification_attempts' => 0,
            ])->save();

            $this->auditService->record($lockedInvitation->submission, 'invitation_verified', [
                'invitation' => $lockedInvitation,
            ]);

            return true;
        });
    }

    public function revoke(CrmClientSetupInvitation $invitation): void
    {
        DB::transaction(function () use ($invitation): void {
            $lockedInvitation = CrmClientSetupInvitation::query()
                ->with('submission')
                ->lockForUpdate()
                ->findOrFail($invitation->id);
            $lockedInvitation->revoke();
            $this->auditService->record($lockedInvitation->submission, 'invitation_revoked', [
                'invitation' => $lockedInvitation,
            ]);
        });
    }

    private function hashToken(string $rawToken): string
    {
        return hash('sha256', $rawToken);
    }
}
