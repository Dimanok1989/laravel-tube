<?php

namespace Kolgaev\Tube\Providers;

use Illuminate\Support\ServiceProvider;
use Kolgaev\Tube\Console\Commands\DownloadCommand;

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

        if ($this->app->runningInConsole()) {
            $this->commands([
                DownloadCommand::class,
            ]);
        }
    }
}
