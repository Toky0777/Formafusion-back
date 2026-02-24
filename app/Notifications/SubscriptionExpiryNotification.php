<?php

namespace App\Notifications;

use App\Mail\SubscriptionExpiryMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SubscriptionExpiryNotification extends Notification
{
    use Queueable;

    protected $daysRemaining;

    public function __construct($daysRemaining)
    {
        $this->daysRemaining = $daysRemaining;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Votre abonnement expire dans ' . $this->daysRemaining . ' jours.',
            'days_remaining' => $this->daysRemaining,
        ];
    }


    public function toMail($notifiable)
    {
        return (new SubscriptionExpiryMail($this->daysRemaining))->to($notifiable->customerEmail);
    }
}
