<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class DoctorRegistered
{
    use Dispatchable;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
