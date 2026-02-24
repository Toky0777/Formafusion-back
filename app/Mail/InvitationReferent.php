<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationReferent extends Mailable
{
    public $data;
    public $mail;

    use Queueable, SerializesModels;

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

    public function build()
    {
        return $this->from('formation@numerika.top')
            ->subject('INVITATION')
            ->view('emails.referent');
    }
}
