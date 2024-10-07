<?php

namespace Kolgaev\Tube\Listeners;

use Illuminate\Support\Facades\Http;
use Kolgaev\Tube\Events\TubeDownloadedEvent;
use Kolgaev\Tube\Models\TubeProcess;
use Kolgaev\Tube\TubeService;

/**
 * Слушатель обрабатывает событие завершения скачивания файлов
 */
class DownloadedFiles
{
    /**
     * Обработка события завершения скачивания
     * 
     * @param \Kolgaev\Tube\Events\TubeDownloadStartEvent|\Kolgaev\Tube\Events\TubeDownloadedEvent $event
     * @return void
     */
    public function handle(object $event): void
    {
        if (!$process = TubeProcess::whereUuid($event->uuid)->first()) {
            return;
        }

        if ($event::class == TubeDownloadedEvent::class) {
            $process->update([
                'status' => TubeProcess::STATUS_DOWNLOADED,
            ]);
        }

        if ($process->callback_url ?? null) {
            Http::baseUrl($process->callback_url)
                ->withHeader('X-Sing', (new TubeService($process))->sing())
                ->post(route('kolgaev.tube.webhook', $process->uuid, false), [
                    'type' => $event::class,
                ]);
        }
    }
}
