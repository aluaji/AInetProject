<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecoverPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return array(
            "driver" => "smtp",
            "host" => "smtp.mailtrap.io",
            "port" => 2525,
            "from" => array(
                "address" => "from@example.com",
                "name" => "Example"
            ),
            "username" => "e8e0a8cb4a7c1c",
            "password" => "ffbf8bda443a0f",
            "sendmail" => "/usr/sbin/sendmail -bs",
            "pretend" => false
        );
    }
}
