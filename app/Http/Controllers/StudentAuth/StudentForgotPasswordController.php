<?php

namespace App\Http\Controllers\StudentAuth;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;

class StudentForgotPasswordController extends Controller{
    use SendsPasswordResetEmails;

    public function showLinkRequestForm(){
        if (Auth::guard('student')->check()) {
            return redirect()->route('student.dashboard');
        }
        return view('auth.student.passwords.email');
    }

    public function broker(){
        return Password::broker('students');
    }
}
