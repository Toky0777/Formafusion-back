<?php

namespace App\Console\Commands;

use App\Notifications\SubscriptionExpiryNotification;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Laravelcm\Subscriptions\Models\Subscription;

class CheckExpiringSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expiring';
    protected $description = 'Check for subscriptions expiring within 5 days and notify users.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $expiringSubscriptions = Subscription::where('ends_at', '>=', Carbon::now())
            ->where('ends_at', '<=', Carbon::now()->addDays(6))
            ->get();

        foreach ($expiringSubscriptions as $subscription) {
            $daysRemaining = Carbon::now()->diffInDays($subscription->ends_at);
            if ($subscription->customer) {
                $subscription->customer->notify(new SubscriptionExpiryNotification($daysRemaining));
            }
        }

        $this->info('Notifications envoyées (email via SubscriptionExpiryMail + base de données).');
    }
}
