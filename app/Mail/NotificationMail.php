<?php

namespace App\Mail;

use App\Helpers\Helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $title;
    public $message;
    public $setting;
    public $logo;

    /**
     * Create a new message instance.
     */
    public function __construct($title, $message)
    {
        $this->title = $title;
        $this->message = $message;
        $this->setting = Helpers::getSettings();
        $this->logo = $this->setting['general']['light_logo_image']->original_url;
    }

    /**
     * Build the message.
     */
    public function build()
    {

        return $this->subject((string) $this->title)
            ->markdown('emails.generic-mail-two');
    }
}
