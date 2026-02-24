<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationFormateur extends Mailable
{
    public $data;
    public $mail;
    public $password;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($d, $m, $p)
    {
        $this->data = $d;
        $this->mail = $m;
        $this->password = $p;
    }

    public function build(){
        return $this->from('formation@numerika.top')
            ->subject('INVITATION')
            ->view('emails.formateur');
    }
    
}