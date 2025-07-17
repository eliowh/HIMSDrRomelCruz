<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RoleAssigned extends Notification
{
    use Queueable;

    protected $role;

    /**
     * Create a new notification instance.
     */
    public function __construct($role)
    {
        $this->role = $role;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Account Has Been Approved')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your account has been approved and your role is now: ' . ucfirst($this->role) . '.')
            ->action('Login Now', url('/login'))
            ->line('Thank you for registering!');
    }
}
