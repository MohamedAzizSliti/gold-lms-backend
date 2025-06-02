<?php

namespace App\Mail;

use App\Helpers\Helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $message;
    public $setting;
    public $logo;
    public $user;
    public $clearedPassword;

    /**
     * Create a new message instance.
     */
    public function __construct($user,$clearedPassword)
    {
        $this->title = "Bienvenue chez Gold LMS - Creation compte";
        $this->setting = Helpers::getSettings();
        $this->logo = $this->setting['general']['light_logo_image']->original_url;
        $this->user = $user;
        $this->clearedPassword = $clearedPassword;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject((string) $this->title)
            ->markdown('emails.welcome-mail');
    }
}
