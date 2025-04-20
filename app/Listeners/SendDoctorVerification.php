<?php

namespace App\Listeners;

use App\Events\DoctorRegistered;
use App\Notifications\DoctorVerificationNotification;

class SendDoctorVerification
{
    public function handle(DoctorRegistered $event)
    {
        $code = rand(100000, 999999);
        $event->user->update(['verification_code' => $code]);
        $event->user->notify(new DoctorVerificationNotification($code));
    }
}
