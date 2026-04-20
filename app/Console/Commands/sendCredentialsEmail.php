<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\User; 
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class SendCredentialsEmail extends Command{
    protected $signature = 'email:send-credentials {userId} {password}';
    protected $description = 'Send credentials email to a new user';

    public function handle(){
        $userId = $this->argument('userId');
        $password = $this->argument('password');

        $user = User::findOrFail($userId);

        $email = $user->email;
        $token = Password::createToken($user);

        $resetLink = url('/password/reset/' . $token);

        $emailContent = "Welcome to Our Application!\n\n"
                      . "Your account has been created. Here are your credentials:\n\n"
                      . "Email: " . $email . "\n"
                      . "Password: " . $password . "\n\n"
                      . "Please change your password after logging in for the first time."
                      ."Here is the reset link"
                      .$resetLink;

        try {
            Mail::raw($emailContent, function ($message) use ($user) {
                $message->to($user->email)->subject('Set Your Password');
            });
            $this->info('Credentials email sent to user ID: ' . $userId);
        } catch (\Exception $e) {
            // Handle the exception
            Log::error('Failed to send user credentials: ' . $e->getMessage());
            $this->error('An error occurred while sending user credentials.');
        }
        
    }


    public function resetPassword($id){
        $user = User::findOrFail($id);
        $email = $user->email;
        $token = Password::createToken($user);

        $resetLink = url('/password/reset/' . $token);
        $emailContent = "Welcome back to Heritage School Management System"
                        ."Click the link below to reset your password"
                        .$resetLink;

        Mail::raw($emailContent, function ($message) use ($email) {
            $message->to($email)->subject('Your New Account Credentials');
        });

    }
}
