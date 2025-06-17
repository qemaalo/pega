<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     */
    protected $commands = [
        Commands\EnviarEmailCumpleanos::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Enviar emails de cumpleaños todos los días a las 8:00 AM
        $schedule->command('cumpleanos:enviar-emails')
                ->dailyAt('08:00')
                ->timezone('America/Santiago') // 👈 Cambia por tu zona horaria
                ->withoutOverlapping() // Evita ejecuciones simultáneas
                ->runInBackground(); // Ejecuta en segundo plano
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
