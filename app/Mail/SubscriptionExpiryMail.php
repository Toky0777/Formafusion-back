<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $daysRemaining;

    public function __construct($daysRemaining)
    {
        $this->daysRemaining = $daysRemaining;
    }

    public function build()
    {
        return $this->subject('Votre abonnement sur FormaFusion expire bientôt')
            ->view('emails.subscription_expiry')
            ->with([
                'daysRemaining' => $this->daysRemaining,
            ]);
    }
}
