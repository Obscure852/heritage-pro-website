<?php

namespace App\Http\Controllers\SponsorAuth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Password;

class SponsorResetPasswordController extends Controller{

  use ResetsPasswords;

  protected $redirectTo = '/sponsor/dashboard';

  public function __construct(){
      $this->middleware('guest:sponsor');
  }

  public function showResetForm(Request $request, $token = null){
      return view('auth.sponsor.passwords.reset')->with([
          'token' => $token,
          'email' => $request->email,
      ]);
  }

  public function broker(){
      return Password::broker('sponsors');
  }

  protected function guard(){
      return Auth::guard('sponsor');
  }

}
