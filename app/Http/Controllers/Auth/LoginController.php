<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\Crm\CrmUserLoginEventService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct(
        private readonly CrmUserLoginEventService $loginEventService
    ) {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm(Request $request)
    {
        $activeTab = $request->input('activeTab', 'user');

        if (!in_array($activeTab, ['user', 'sponsor', 'student'])) {
            $activeTab = 'user';
        }

        if ($activeTab === 'student' && Auth::guard('student')->check()) {
            return redirect()->route('student.dashboard');
        }
        
        if ($activeTab === 'sponsor' && Auth::guard('sponsor')->check()) {
            return redirect()->route('sponsor.dashboard');
        }
        
        if ($activeTab === 'user' && Auth::check()) {
            return $this->redirectAuthenticatedUser();
        }
        
        if (Auth::guard('sponsor')->check()) {
            return redirect()->route('sponsor.dashboard');
        }
        
        if (Auth::guard('student')->check()) {
            return redirect()->route('student.dashboard');
        }
        
        if (Auth::check()) {
            return $this->redirectAuthenticatedUser();
        }
        
        return view('auth.login', [
            'activeTab' => $activeTab,
        ]);
    }

    protected function maxAttempts()
    {
        return 3;
    }

    protected function decayMinutes()
    {
        return 10;
    }

    public function username()
    {
        return 'email';
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->decayMinutes() * 60;
        $message = 'Too many login attempts. Please try again in ' . $seconds . ' seconds.';

        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors([$this->username() => $message]);
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user instanceof User) {
            $this->loginEventService->record($user, 'login', $request);

            if ($user->requiresCrmOnboarding()) {
                return redirect()->route($user->crmOnboardingRouteName());
            }
        }

        return null;
    }

    protected function credentials(Request $request)
    {
        return array_merge($request->only($this->username(), 'password'), ['active' => true]);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $user = \App\Models\User::where($this->username(), $request->{$this->username()})->first();

        if ($user && !$user->active) {
            $errors = [$this->username() => 'Your account is disabled.'];
        } else {
            $errors = [$this->username() => trans('auth.failed')];
        }

        if ($request->expectsJson()) {
            return response()->json($errors, 422);
        }

        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors($errors);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user instanceof User) {
            $this->loginEventService->record($user, 'logout', $request);
        }

        return redirect()->route('login');
    }

    private function redirectAuthenticatedUser(): RedirectResponse
    {
        $user = Auth::user();

        if ($user instanceof User && $user->requiresCrmOnboarding()) {
            return redirect()->route($user->crmOnboardingRouteName());
        }

        return redirect()->route('dashboard');
    }
}
