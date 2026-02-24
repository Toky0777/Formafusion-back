<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BadgeAttribueMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Les données pour l'email
     *
     * @var array
     */
    public $data;

    /**
     * Create a new message instance.
     *
     * @param array $data
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Félicitations ! Un nouveau badge vous a été attribué')
            ->view('emails.attributionMail')
            ->with(['data' => $this->data]);
    }
}
