<?php

namespace App\Http\Controllers\SponsorAuth;

use App\Http\Controllers\Controller;
use App\Services\Auth\IdleSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;

class SponsorLoginController extends Controller{

    public function showLoginForm(){
        return view('auth.login', [
            'activeTab' => 'sponsor'
        ]);
    }

    public function sponsorLogin(Request $request){
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        if (Auth::guard('sponsor')->attempt($credentials)) {
            $request->session()->regenerate();
            app(IdleSessionService::class)->touch($request->session(), 'sponsor');
            return redirect()->intended(route('sponsor.dashboard'))->with('status', 'You are now logged in as a Sponsor!');
        }

        return back()->withErrors([
            'email' => 'Invalid sponsor credentials provided.',
        ])->onlyInput('email');
    }

    public function logout(Request $request){
        Auth::guard('sponsor')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
    
}
