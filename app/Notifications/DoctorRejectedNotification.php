<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DoctorRejectedNotification extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Account Rejected')
            ->greeting('Hello Dr. ' . $notifiable->username)
            ->line('We regret to inform you that your account has been rejected by the admin.')
            ->line('If you believe this is a mistake, please contact support.')
            ->line('Thank you for your interest.');
    }
}
