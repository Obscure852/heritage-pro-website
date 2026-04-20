<?php

namespace App\Http\Controllers\StudentAuth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class StudentResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo = '/student/dashboard';

    public function __construct()
    {
        $this->middleware('guest:student');
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.student.passwords.reset')->with([
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function broker()
    {
        return Password::broker('students');
    }

    protected function guard()
    {
        return Auth::guard('student');
    }
}
