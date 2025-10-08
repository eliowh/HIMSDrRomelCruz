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
        $mail = (new MailMessage)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Reset Password')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We received a request to reset your password. If you did not make this request, please ignore this email.')
            ->action('Reset Password', secure_url('/reset-password/'.$this->token))
            ->line('Best regards, ' . config('app.name'));
        
        // Add admin test email as BCC for testing purposes
        if (env('ADMIN_TEST_EMAIL')) {
            $mail->bcc(env('ADMIN_TEST_EMAIL'));
        }
        
        return $mail;
    }
}


?>