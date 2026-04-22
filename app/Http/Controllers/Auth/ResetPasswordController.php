<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    protected function resetPassword($user, $password): void
    {
        $user->password = Hash::make($password);
        $user->setRememberToken(Str::random(60));

        if ($user instanceof User) {
            $user->save();
            $user->markCrmOnboardingRequired();
        } else {
            $user->save();
        }

        event(new PasswordReset($user));

        $this->guard()->login($user);
    }

    protected function redirectTo(): string
    {
        $user = $this->guard()->user();

        if ($user instanceof User && $user->requiresCrmOnboarding()) {
            return route($user->crmOnboardingRouteName());
        }

        return RouteServiceProvider::HOME;
    }
}
