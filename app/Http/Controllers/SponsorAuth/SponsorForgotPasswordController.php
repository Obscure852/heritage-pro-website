<?php

namespace App\Http\Controllers\SponsorAuth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class SponsorForgotPasswordController extends Controller{
    use SendsPasswordResetEmails;

    public function showLinkRequestForm(){
        if (Auth::guard('sponsor')->check()) {
            return redirect()->route('sponsor.dashboard');
        }
        return view('auth.sponsor.passwords.email');
    }


    public function broker(){
        return Password::broker('sponsors');
    }
}
