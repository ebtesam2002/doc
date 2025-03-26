<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends Notification
{
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->line('Thank you for registering. Please click the button below to verify your email address.')
            ->action('Verify Email', url('/api/email/verify/' . $notifiable->id . '/' . sha1($notifiable->email)))
            ->line('If you did not create an account, no further action is required.');
    }
}
