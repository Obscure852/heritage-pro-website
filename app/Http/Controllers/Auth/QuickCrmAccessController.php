<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class QuickCrmAccessController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('crm.dashboard');
        }

        if (! $this->quickAccessEnabled()) {
            return redirect()->route('login');
        }

        $user = User::query()
            ->where('role', 'admin')
            ->orderBy('id')
            ->first();

        if ($user === null) {
            $user = User::query()->create([
                'name' => 'Heritage CRM Admin',
                'email' => $this->availableEmail(),
                'password' => Hash::make(Str::random(48)),
                'role' => 'admin',
                'active' => true,
            ]);
        } elseif (! $user->active) {
            $user->forceFill(['active' => true])->save();
        }

        Auth::login($user, true);

        return redirect()
            ->route('crm.dashboard')
            ->with('crm_success', 'Quick local CRM access is active. Standard password login remains available outside local/debug mode.');
    }

    private function quickAccessEnabled(): bool
    {
        return app()->environment('local') || (bool) config('app.debug');
    }

    private function availableEmail(): string
    {
        $baseEmail = 'crm-admin@heritagepro.local';

        if (! User::withTrashed()->where('email', $baseEmail)->exists()) {
            return $baseEmail;
        }

        return 'crm-admin+' . Str::lower(Str::random(6)) . '@heritagepro.local';
    }
}
