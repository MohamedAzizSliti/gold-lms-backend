<?php
namespace App\Notifications;
use App\Channels\SmsChannel;
use App\Channels\WhatsAppChannel;
use App\Services\XSenderService;
use Illuminate\Support\Facades\Http;
use App\Channels\CallChannel;
use App\Services\TrackCarService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\OneSignal\OneSignalMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class ReminderNotification extends Notification{
    use Queueable;
    private $prams = null;
    private $gateway = null;

    /**
     * Create a new notification instance.
     */
    public function __construct($params,$gateway = "vonage")
    {
        $this->prams = $params;
        $this->gateway = $gateway;
    }

    public function via($notifiable)
    {
        $methods = ['database'];

        // Appel
        if (strpos($this->prams['methods'],'4') !==  false  ){
            array_push($methods,CallChannel::class);
        }

        // SMS
        if (strpos($this->prams['methods'],'1') !==  false ){
          array_push($methods,SmsChannel::class);
        }

        // mail
        if (strpos($this->prams['methods'],'2') !==  false  ){
            // todo uncomment this line
             array_push($methods,'mail');
        }

        // notification : Push Notification Mobile
        if (strpos($this->prams['methods'],'3') !==  false  ){
            array_push($methods,OneSignalChannel::class);
        }

        // Via WhatsApp
        if (strpos($this->prams['methods'],'5') !==  false  ){
            // todo uncomment this line
            array_push($methods,WhatsAppChannel::class);
        }

        // 'mail',
        // 'database',
        return $methods;
    }

    public function toOnesignal($notifiable)
    {

//        $response = Http::withHeaders([
//            'Authorization' => 'Basic '.env('ONESIGNAL_REST_API_KEY'),
//            'Content-Type' => 'application/json'
//        ])->post('https://onesignal.com/api/v1/notifications', [
//            'app_id' => env('ONESIGNAL_APP_ID'),
//            'contents' => ['en' => 'Test message'],
//            'include_player_ids' => ['f59dffd3-7367-4c58-935c-beff0b6fc03c']
//        ]);
//
//        dd($response->json());

        return OneSignalMessage::create()
            ->subject($this->prams['title'])
            ->setData('custom_key', 'custom_value') // Optional: Custom data for the notification
            ->body($this->prams['body']); // Optional

        return $response; // Optional
    }

    public function toMail(){
        try {
             $token = $this->prams['body'];
             return (new MailMessage)
                ->subject($this->prams['title'])
                ->view('emails.test', ['token' => $token]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
             return null; // Retourner null pour éviter de planter le processus de notification
        }
    }

    public function toCall($notifiable)
    {
//        $to = $notifiable->routeNotificationForCall($this);
//        $twilio = new Client(config('services.twilio.sid'), config('services.twilio.auth_token'));
//
//        $twilio->calls->create(
//            $to, // Numéro à appeler
//            config('services.twilio.from'), // Numéro d'expéditeur
//            [
//                'twiml' => '<Response><Say>' . $this->message . '</Say></Response>',
//            ]
//        );

        // Pass a Call with the GPS device
        if(false){
            $traccarService = new TrackCarService();
            $deviceId = 3; // ID de l'appareil dans Traccar
            $commandType = 'custom_commande'; // Type de commande
            $command = 'CALL,+21655899308'; // Commande pour initier l'appel

            $response = $traccarService->sendCommand($deviceId, $commandType, $command);

            if ($response['success'] ?? false) {
                echo "L'appel a été initié avec succès.";
                Log::info("L'appel a été initié avec succès.");
            } else {
                echo "Échec de l'envoi de la commande.";
                Log::info("Échec de l'envoi de la commande.");
                // Faire l'appel avec Twilio

            }
        }
        // Or With Twilio Account
        else{
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_AUTH_TOKEN');
            $twilioNumber = env('TWILIO_PHONE_NUMBER');
            $client = new Client($sid, $token);
            // Twilio Call URL with Event Name
            $callUrl = url('/api/twilio-voice?event=' . urlencode($this->prams['title']));
            $call = $client->calls->create(
                $notifiable->routeNotificationForSms(),  // Number to call
                $twilioNumber,       // Your Twilio number
                ['url' => $callUrl]  // URL to fetch TwiML instructions
            );
            return $call->sid;
        }
        return null;
    }

    public function toSms($notifiable)
    {
//        $to = '+2161124423';
//        $smsGateway = new SmsGateway();
//
//        switch ($this->gateway) {
//            case 'twilio':
//                return $smsGateway->sendViaTwilio($to, 'message');
//            case 'vonage':
//            default:
//              //  return $smsGateway->sendViaVonage($to,'message');
//        }


       $response =  XSenderService::sendSms($notifiable->routeNotificationForSms(),$this->prams['title'].': '.$this->prams['body']);

       Log::info('Sending SMS ...');
       Log::info($response);
    }


    public function toWhatsApp($notifiable)
    {
//        $to = '+2161124423';
//        $smsGateway = new SmsGateway();
//
//        switch ($this->gateway) {
//            case 'twilio':
//                return $smsGateway->sendViaTwilio($to, 'message');
//            case 'vonage':
//            default:
//              //  return $smsGateway->sendViaVonage($to,'message');
//        }

        $response =  XSenderService::sendWhatsApp($notifiable->routeNotificationForSms(),$this->prams['title'].': '.$this->prams['body']);
        Log::info('Sending WhatsApp ...');
        Log::info($response);
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        //for admin
       // $consumer = User::where('id', $this->refund->consumer_id)->pluck('name')->first();
        return [
            'title' => $this->prams['title'],
            'message' =>  $this->prams['body'],
            'type' => 'traccar_event',
        ];
    }

}

