<?php

namespace App\Services\Crm;

use App\Models\User;
use Illuminate\Http\Request;

class CrmUserLoginEventService
{
    public function record(User $user, string $eventType, ?Request $request = null): void
    {
        if (! in_array($user->role, array_keys(config('heritage_crm.roles', [])), true)) {
            return;
        }

        $user->loginEvents()->create([
            'event_type' => $eventType,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'occurred_at' => now(),
        ]);
    }
}
