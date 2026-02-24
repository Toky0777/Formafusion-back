<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AddApprenant extends Mailable
{
    public $cfp_name;
    public $emp_email;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($cfp_name, $emp_email)
    {
        $this->cfp_name = $cfp_name;
        $this->emp_email = $emp_email;
    }

    public function build()
    {
        return $this->from('formation@numerika.top')
            ->subject('INVITATION')
            ->view('emails.apprenant');
    }
}
