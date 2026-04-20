<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\TermHelper;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Services\Auth\IdleSessionService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;

class LoginController extends Controller{
    use AuthenticatesUsers;
    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct(){
       $this->middleware('guest')->except('logout');
    }

    public function showLoginForm(Request $request){
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
            return redirect()->route('dashboard');
        }
        
        if (Auth::guard('sponsor')->check()) {
            return redirect()->route('sponsor.dashboard');
        }
        
        if (Auth::guard('student')->check()) {
            return redirect()->route('student.dashboard');
        }
        
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login', [
            'activeTab' => $activeTab
        ]);
    }

    protected function maxAttempts(){
        return 3;
    }

    protected function decayMinutes(){
        return 10;
    }

    public function username(){
        return 'email';
    }

    public function login(Request $request){
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

    protected function sendLockoutResponse(Request $request){
        $seconds = $this->decayMinutes() * 60;
        $message = 'Too many login attempts. Please try again in ' . $seconds . ' seconds.';

        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors([$this->username() => $message]);
    }

    protected function authenticated(Request $request, $user){
        app(IdleSessionService::class)->touch($request->session(), 'web');

        $currentTerm = TermHelper::getCurrentTerm();
        if ($currentTerm && $user->can('shouldTakeActionForTermEnd', $currentTerm)) {
            session()->put('term_end_modal_shown', false);
        }

        return redirect()->intended($this->redirectPath());
    }


    protected function credentials(Request $request){
        return array_merge($request->only($this->username(), 'password'), ['active' => true]);
    }

    protected function sendFailedLoginResponse(Request $request){
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

    protected function loggedOut(Request $request){
        return redirect()->route('login');
    }
}
