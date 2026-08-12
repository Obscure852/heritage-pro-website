<?php

namespace App\Services\ClientSetup;

use App\Models\CrmClientSetupEvent;
use App\Models\CrmClientSetupInvitation;
use App\Models\CrmClientSetupSubmission;
use App\Models\User;

class ClientSetupAuditService
{
    public function record(
        CrmClientSetupSubmission $submission,
        string $eventType,
        array $attributes = []
    ): CrmClientSetupEvent {
        $request = app()->bound('request') ? request() : null;
        $user = $attributes['user'] ?? null;
        $invitation = $attributes['invitation'] ?? null;

        return $submission->events()->create([
            'invitation_id' => $invitation instanceof CrmClientSetupInvitation
                ? $invitation->id
                : null,
            'user_id' => $user instanceof User
                ? $user->id
                : null,
            'actor_type' => $attributes['actor_type'] ?? ($user instanceof User ? 'crm_user' : 'client'),
            'event_type' => $eventType,
            'stage_key' => $attributes['stage_key'] ?? null,
            'metadata' => $attributes['metadata'] ?? null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'occurred_at' => now(),
        ]);
    }
}
