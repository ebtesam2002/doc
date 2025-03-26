<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Notifications\VerifyEmailNotification;

class SendUserVerificationEmail
{
    public function handle(UserRegistered $event)
    {
        $event->user->notify(new VerifyEmailNotification());
    }
}
