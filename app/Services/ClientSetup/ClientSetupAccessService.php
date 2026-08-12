<?php

namespace App\Services\ClientSetup;

use App\Models\CrmClientSetupInvitation;

class ClientSetupAccessService
{
    public function markVerified(CrmClientSetupInvitation $invitation): void
    {
        session()->regenerate();
        session()->put(config('client_setup.session_verified_invitation_key'), $invitation->uuid);
    }

    public function isVerified(CrmClientSetupInvitation $invitation): bool
    {
        return $invitation->isUsable()
            && session(config('client_setup.session_verified_invitation_key')) === $invitation->uuid;
    }

    public function forget(): void
    {
        session()->forget(config('client_setup.session_verified_invitation_key'));
    }
}
