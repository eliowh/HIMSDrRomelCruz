<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewUserCredentials extends Notification
{
    use Queueable;

    protected $token;
    protected $role;

    public function __construct($token, $role)
    {
        $this->token = $token;
        $this->role = $role;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    protected function formatRole($role)
    {
        $roleMap = [
            'lab_technician' => 'Lab Technician',
            'admin' => 'Admin',
            'doctor' => 'Doctor',
            'nurse' => 'Nurse',
            'cashier' => 'Cashier'
        ];

        return $roleMap[$role] ?? ucfirst($role);
    }

    public function toMail($notifiable)
    {
        // Use just the token in the URL
        $resetUrl = url('/reset-password/'.$this->token);

        $mail = (new MailMessage)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Welcome to ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('An account has been created for you with the role of ' . $this->formatRole($this->role) . '.')
            ->line('Before you can access your account, please set your password by clicking the button below.')
            ->line('This link will expire in 24 hours.')
            ->action('Set Your Password', $resetUrl)
            ->line('Thank you for joining us!');

        // Add BCC to admin email for testing (only if configured)
        if (env('ADMIN_TEST_EMAIL')) {
            $mail->bcc(env('ADMIN_TEST_EMAIL'));
        }

        return $mail;
    }
}
