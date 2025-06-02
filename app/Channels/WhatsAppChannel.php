<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    public function send($notifiable, Notification $notification)
    {
        $to = $notifiable->routeNotificationForSms($notification);

        $message = $notification->toWhatsApp($notifiable);

        // Implémentez ici votre logique pour envoyer le SMS
        // Exemple avec une API personnalisée :
       // $this->sendSms($to, $message);
    }

    protected function sendSms($to, $message)
    {
        logger("Passing whatApp to {$to} with message: {$message}");
    }
}
