<?php

namespace Kolgaev\Tube\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Kolgaev\Tube\Console\Commands\DownloadCommand;
use Kolgaev\Tube\Events\TubeDoneEvent;
use Kolgaev\Tube\Listeners\DoneTubeProcess;

class TubeServiceProvider extends ServiceProvider
{
    /**
     * Регистрация любых служб пакета.
     * 
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/tube.php', 'tube');
    }

    /**
     * Загрузка любых служб пакета.
     * 
     * @return void
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->loadRoutesFrom(__DIR__ . '/../../routes/tube.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DownloadCommand::class,
            ]);
        }

        Event::listen(TubeDoneEvent::class, DoneTubeProcess::class);
    }
}
