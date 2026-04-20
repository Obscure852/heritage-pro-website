<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class ResetUserPassword extends Command{
    protected $signature = 'email:reset-credentials {userId}';
    protected $description = 'Send a password reset link to a user';

    public function handle(){
        $userId = $this->argument('userId');
        $user = User::find($userId);

        if (!$user) {
            $this->error('User not found with ID: ' . $userId);
            return 1; 
        }

        $token = Password::createToken($user);

        try {
            $user->sendPasswordResetNotification($token);
            $this->info('Password reset link sent to user ID: ' . $userId);
            return 0;
        } catch (\Exception $e) {
            Log::error('Failed to send password reset link to user ID ' . $userId . ': ' . $e->getMessage());
            $this->error('An error occurred while sending the password reset link.');
            return 1;
        }
    }
}
