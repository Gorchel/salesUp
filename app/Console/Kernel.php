<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\UpdateTables::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command("update_tables:init", [
            'type' => 'orders',
            'dayUpdated' => 2
        ])->everyMinute();
        $schedule->command("update_tables:init", [
            'type' => 'property',
            'dayUpdated' => 2
        ])->everyMinute();
        $schedule->command("update_tables:init", [
            'type' => 'company',
            'dayUpdated' => 2
        ])->everyMinute();
    }
}
