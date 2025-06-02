<?php

namespace App\Console\Commands;


use App\FileManager;
use App\Globals;
use App\Helper\EmailsHelper;
use App\Models\_AutoMobile\UsersDevice;
use App\Models\comptabilite\Classeur;
use App\Models\comptabilite\Ocr;
use App\Models\Reminder;
use App\OcrUtils;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use File;
use Illuminate\Support\Facades\Storage;

class SendNotif extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notif:verif';
    private $emailHelper = null;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifier s il ya des notifs';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $today = Carbon::today();

        // Récupérer les rappels dans 5 jours
        $remindersIn5Days = Reminder::whereDate('reminder_date', $today->addDays(5))->get();
        $this->sendNotification($remindersIn5Days, 5);

        // Récupérer les rappels dans 2 jours
        $remindersIn2Days = Reminder::whereDate('reminder_date', $today->addDays(2))->get();
        $this->sendNotification($remindersIn2Days, 2);

        // Récupérer les rappels du jour même
        $todayReminders = Reminder::whereDate('reminder_date', $today)->get();
        $this->sendNotification($todayReminders, 0);

    }

    private function sendNotification($reminders, $daysBefore)
    {
        foreach ($reminders as $reminder) {
            $user = $reminder->user; // Supposons que chaque rappel est lié à un utilisateur
            $title = "Rappel de votre événement !";

            if ($daysBefore === 5) {
                $message = "Votre rappel est prévu dans 5 jours.";
            } elseif ($daysBefore === 2) {
                $message = "Votre rappel est prévu dans 2 jours.";
            } else {
                $message = "Aujourd'hui est le jour de votre rappel.";
            }

            // Envoyer la notification (ex : email, notification push, etc.)
            if ($user && $user->player_id){
               $user->notify(new \ReminderNotification($title, $message));
            }
            // Vous pouvez aussi ajouter un log pour vérifier
            Log::info("Notification envoyée à l'utilisateur {$user->name} pour un rappel dans {$daysBefore} jours.");
        }
    }


}
