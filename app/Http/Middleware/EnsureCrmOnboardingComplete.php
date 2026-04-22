<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnsureCrmOnboardingComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->requiresCrmOnboarding()) {
            return $next($request);
        }

        if ($request->isMethod('GET') && ! $request->expectsJson()) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return $this->redirectToOnboarding($user);
    }

    private function redirectToOnboarding(User $user): RedirectResponse
    {
        return redirect()->route($user->crmOnboardingRouteName());
    }
}
