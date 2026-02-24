<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ParticulierMail extends Mailable
{
    public $customer_name;
    public $pPass;
    public $pEmail;
    public function __construct($cfpName, $pPass, $pEmail)
    {
        $this->customer_name = $cfpName;
        $this->pPass = $pPass;
        $this->pEmail = $pEmail;
    }

    public function build(){
        return $this->from('formation@numerika.top')
            ->subject('INVITATION')
            ->view('emails.particulier');
    }
}
