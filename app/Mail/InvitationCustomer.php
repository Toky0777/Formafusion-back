<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationCustomer extends Mailable
{
    public $cfp;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($cfpName)
    {
        $this->cfp = $cfpName;
    }

    public function build(){
        return $this->from('formation@numerika.top')
            ->subject('INVITATION')
            ->view('emails.customer');
    }

}
