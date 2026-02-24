<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CfpInviteFormCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $mail;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($d, $m)
    {
        $this->data = $d;
        $this->mail = $m;
    }

    public function build(){
        return $this->from('formation@numerika.top')
            ->subject('INVITATION')
            ->view('emails.formateur_created');
    }
}
