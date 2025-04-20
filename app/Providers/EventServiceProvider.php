<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use App\Events\DoctorRegistered;
use App\Listeners\SendDoctorVerification;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        DoctorRegistered::class => [
            SendDoctorVerification::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
