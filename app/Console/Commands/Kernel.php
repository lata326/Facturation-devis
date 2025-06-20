<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
         Commands\CheckNotifications::class,
        // Ici tu peux enregistrer tes commandes personnalisées, par exemple :
        // \App\Console\Commands\CheckNotifications::class,
    ];

        
           
        

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('notifications:check')
            ->hourly()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        // Optionnel : charger les commandes définies dans routes/console.php
        require base_path('routes/console.php');
    }
}
