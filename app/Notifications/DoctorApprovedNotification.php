<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DoctorApprovedNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Account Approved')
            ->greeting('Hello Dr. ' . $notifiable->username)
            ->line('Congratulations! Your account has been approved by the admin.')
            ->line('You can now log in and start using the application.')
            ->action('Go to App', url('/'))
            ->line('Thank you for being part of our platform.');
    }
}
