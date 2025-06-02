<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;

class SmsChannel
{
    public function send($notifiable, Notification $notification)
    {
        $to = $notifiable->routeNotificationForSms($notification);

        $message = $notification->toSms($notifiable);

        // Implémentez ici votre logique pour envoyer le SMS
        // Exemple avec une API personnalisée :
       // $this->sendSms($to, $message);
    }

    protected function sendSms($to, $message)
    {
        logger("Passing sms to {$to} with message: {$message}");
    }
}
