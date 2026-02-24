<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RequestCustomer extends Mailable
{
    public $customer_name;

    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($cfpName)
    {
        $this->customer_name = $cfpName;
    }

    public function build(){
        return $this->from('formation@numerika.top')
            ->subject('INVITATION')
            ->view('emails.customerRequest');
    }
}
