<?php
// app/Console/Kernel.php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Register your custom commands here
        \App\Console\Commands\GenerateStockAlerts::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Generate stock alerts daily at 8 AM (adjust timezone as needed)
        $schedule->command('alerts:generate')
            ->dailyAt('08:00')
            ->timezone('Africa/Lagos') // Changed to a more common timezone
            ->description('Generate daily stock alerts')
            ->withoutOverlapping() // Prevents overlapping runs
            ->appendOutputTo(storage_path('logs/alerts.log')); // Logs output

        // Optional: Clean up old alerts weekly (resolved alerts older than 30 days)
        // First, make sure you have a Prunable trait in your Alert model
        $schedule->command('model:prune', [
            '--model' => [\App\Models\Alert::class],
            '--except' => [\App\Models\User::class], // Exclude users from pruning
        ])->weekly()->sundays()->at('02:00');

        // Optional: Backup database daily (requires spatie/laravel-backup package)
        // $schedule->command('backup:run')->daily()->at('01:00');

        // Optional: Clear application cache weekly
        $schedule->command('cache:clear')->weekly()->sundays()->at('03:00');

        // Optional: Queue worker restart (if using queue workers)
        // $schedule->command('queue:restart')->hourly();

        // For development/testing - run alerts every hour
        if (app()->environment('local')) {
            $schedule->command('alerts:generate')
                ->hourly()
                ->description('Hourly alerts (dev only)');
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        // Load all commands from the Commands directory
        $this->load(__DIR__.'/Commands');

        // Load command routes
        require base_path('routes/console.php');

        // Manually register commands if auto-discovery doesn't work
        if (class_exists(\App\Console\Commands\GenerateStockAlerts::class)) {
            $this->app->singleton(\App\Console\Commands\GenerateStockAlerts::class);
        }
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     *
     * @return string
     */
    protected function scheduleTimezone(): string
    {
        // Set default timezone for all scheduled tasks
        return 'Africa/Lagos'; // Or your local timezone: 'America/New_York', 'Europe/London', etc.
    }
}