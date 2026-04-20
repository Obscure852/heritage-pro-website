<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SponsorResetPasswordNotification extends Notification{

    public $token;

    public function __construct($token){
        $this->token = $token;
    }

    public function via($notifiable){
        return ['mail'];
    }

    public function toMail($notifiable){
        $resetUrl = url(route('sponsor.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ], false));

        return (new MailMessage)
            ->subject('Sponsor Password Reset')
            ->greeting('Hello!')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $resetUrl)
            ->line('If you did not request a password reset, no further action is required.');
    }
}
