<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CheckExpiringSubscriptions::class,
        \App\Console\Commands\AI\TrainRentabilityModels::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('subscriptions:check-expiring')->daily();

        // Optionnel: planifier l'entraînement automatique
        $schedule->command('ai:train-rentability --all')
            ->weekly()
            ->mondays()
            ->at('02:00')
            ->withoutOverlapping()
            ->sendOutputTo(storage_path('logs/ai-training.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
