<?php

namespace Kolgaev\Tube\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Kolgaev\Tube\Console\DownloadCommand;
use Kolgaev\Tube\Events\TubeDownloadDoneEvent;
use Kolgaev\Tube\Events\TubeDownloadedFileEvent;
use Kolgaev\Tube\Events\TubeDownloadFileErrorEvent;
use Kolgaev\Tube\Events\TubeDownloadFileEvent;
use Kolgaev\Tube\Events\TubeInitDownloadEvent;
use Kolgaev\Tube\Events\TubeReceivedMetaEvent;
use Kolgaev\Tube\Events\TubeStartDownloadEvent;
use Kolgaev\Tube\Listeners\TubeDownloadDoneListener;
use Kolgaev\Tube\Listeners\TubeDownloadedFileListener;
use Kolgaev\Tube\Listeners\TubeDownloadFileErrorListener;
use Kolgaev\Tube\Listeners\TubeDownloadFileListener;
use Kolgaev\Tube\Listeners\TubeInitDownloadListener;
use Kolgaev\Tube\Listeners\TubeReceivedMetaListener;
use Kolgaev\Tube\Listeners\TubeStartDownloadListener;

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

        Event::listen(TubeInitDownloadEvent::class, TubeInitDownloadListener::class);
        Event::listen(TubeReceivedMetaEvent::class, TubeReceivedMetaListener::class);
        Event::listen(TubeStartDownloadEvent::class, TubeStartDownloadListener::class);
        Event::listen(TubeDownloadFileEvent::class, TubeDownloadFileListener::class);
        Event::listen(TubeDownloadFileErrorEvent::class, TubeDownloadFileErrorListener::class);
        Event::listen(TubeDownloadedFileEvent::class, TubeDownloadedFileListener::class);
        Event::listen(TubeDownloadDoneEvent::class, TubeDownloadDoneListener::class);
    }
}
