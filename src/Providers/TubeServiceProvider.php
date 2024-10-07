<?php

namespace Kolgaev\Tube\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Kolgaev\Tube\Console\Commands\DownloadCommand;
use Kolgaev\Tube\Events\TubeDoneEvent;
use Kolgaev\Tube\Events\TubeDownloadedEvent;
use Kolgaev\Tube\Events\TubeDownloadProgressAudioEvent;
use Kolgaev\Tube\Events\TubeDownloadProgressVideoEvent;
use Kolgaev\Tube\Events\TubeFailEvent;
use Kolgaev\Tube\Events\WebhookEvent;
use Kolgaev\Tube\Listeners\DownloadDoneProcess;
use Kolgaev\Tube\Listeners\DownloadedFiles;
use Kolgaev\Tube\Listeners\DownloadProgress;
use Kolgaev\Tube\Listeners\Webhook;

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

        // Процесс скачивания файлов
        Event::listen(TubeDownloadProgressVideoEvent::class, DownloadProgress::class);
        Event::listen(TubeDownloadProgressAudioEvent::class, DownloadProgress::class);
        
        // События сохранения файлов
        Event::listen(TubeDownloadStartEvent::class, DownloadedFiles::class);
        Event::listen(TubeDownloadedEvent::class, DownloadedFiles::class);

        // Обработка webhook
        Event::listen(WebhookEvent::class, Webhook::class);

        // Завершение всего процесса
        Event::listen(TubeDoneEvent::class, DownloadDoneProcess::class);
        Event::listen(TubeFailEvent::class, DownloadDoneProcess::class);
    }
}
