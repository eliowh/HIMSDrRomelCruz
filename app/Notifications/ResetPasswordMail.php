<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Notification
{
    use Queueable, SerializesModels;

    public $user;
    public $token;

    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Reset Password')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We received a request to reset your password. If you did not make this request, please ignore this email.')
            ->action('Reset Password', url('/reset-password/' . $this->token))
            ->line('Best regards, Dr. Romel Cruz Hospital');
    }
}


?>