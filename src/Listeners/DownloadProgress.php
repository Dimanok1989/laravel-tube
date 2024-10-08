<?php

namespace Kolgaev\Tube\Listeners;

use Illuminate\Support\Facades\Http;
use Kolgaev\Tube\Events\TubeDownloadProgressAudioEvent;
use Kolgaev\Tube\Events\TubeDownloadProgressVideoEvent;
use Kolgaev\Tube\Models\TubeProcess;
use Kolgaev\Tube\TubeService;

/**
 * Слушатель обрабатывает событие процесса загрузки
 * В случае загрузки через http сервер, отпраавляется обратный
 * вебхук с процентом заавершения загрузки файла
 */
class DownloadProgress
{
    /**
     * Обработка события завершения скачивания
     * 
     * @param TubeDownloadProgressVideoEvent|TubeDownloadProgressAudioEvent $event
     * @return void
     */
    public function handle(
        TubeDownloadProgressVideoEvent|TubeDownloadProgressAudioEvent $event
    ): void {

        $process = TubeProcess::whereUuid($event->uuid)->first();

        $status = match ($event::class) {
            TubeDownloadProgressVideoEvent::class => TubeProcess::STATUS_VIDEO_DOWNLOADED,
            TubeDownloadProgressAudioEvent::class => TubeProcess::STATUS_AUDIO_DOWNLOADED,
            default => null,
        };

        $percent = $event->percent ?? 0;

        if ($percent >= 100 && $status) {
            $process->update([
                'status' => $status,
            ]);
        }

        if ($process->callback_url ?? null) {
            Http::baseUrl($process->callback_url)
                ->withHeader('X-Sing', (new TubeService($process))->sing())
                ->post(route('kolgaev.tube.webhook', $process->uuid, false), [
                    'type' => $event::class,
                    'percent' => $event->percent ?? null,
                ]);
        }
    }
}
