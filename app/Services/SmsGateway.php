<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsGateway
{
    public function sendViaVonage($to, $message)
    {
        // Exemple d'utilisation de Vonage
        return Http::post('https://rest.nexmo.com/sms/json', [
            'api_key' => config('services.nexmo.key'),
            'api_secret' => config('services.nexmo.secret'),
            'to' => $to,
            'from' => config('services.nexmo.sms_from'),
            'text' => $message,
        ]);
    }

    public function sendViaTwilio($to, $message)
    {
        // Exemple d'utilisation de Twilio
        $url = 'https://api.twilio.com/2010-04-01/Accounts/' . config('services.twilio.sid') . '/Messages.json';

        return Http::withBasicAuth(config('services.twilio.sid'), config('services.twilio.auth_token'))
            ->post($url, [
                'To' => $to,
                'From' => config('services.twilio.from'),
                'Body' => $message,
            ]);
    }
}
