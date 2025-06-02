<?php

namespace App\Listeners;

use App\Events\CompanyRegisterEvent;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Notifications\CompanyRegisterNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompanyRegisterListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(CompanyRegisterEvent $event)
    {
        $admin = User::role(RoleEnum::ADMIN)->first();
        if (isset($admin)) {
            $admin->notify(new CompanyRegisterNotification($event->company));
        }
    }
}
