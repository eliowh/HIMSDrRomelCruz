<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewUserCredentials extends Notification
{
    use Queueable;

    protected $password;
    protected $role;

    public function __construct($password, $role)
    {
        $this->password = $password;
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
        return (new MailMessage)
            ->subject('Welcome to Dr. Romel Cruz Hospital')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your account has been created with the following credentials:')
            ->line('Email: ' . $notifiable->email)
            ->line('Temporary Password: ' . $this->password)
            ->line('Role: ' . $this->formatRole($this->role))
            ->line('Please change your password after logging in for the first time.')
            ->action('Login Now', url('/login'))
            ->line('Thank you for joining us!')
            ->line('Best regards,')
            ->line('Dr. Romel Cruz Hospital');
    }
}
