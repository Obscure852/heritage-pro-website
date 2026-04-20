<?php

namespace App\Http\Controllers\StudentAuth;

use App\Http\Controllers\Controller;
use App\Services\Auth\IdleSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentLoginController extends Controller{

    public function showLoginForm(){
        return view('auth.login', [
            'activeTab' => 'student'
        ]);
    }

    public function studentLogin(Request $request){
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        if (Auth::guard('student')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            app(IdleSessionService::class)->touch($request->session(), 'student');
            return redirect()->intended(route('student.dashboard'))->with('status', 'You are now logged in as a Student!');
        }

        return back()->withErrors([
            'email' => 'Invalid student credentials provided.',
        ])->onlyInput('email');
    }

    public function logout(Request $request){
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}
