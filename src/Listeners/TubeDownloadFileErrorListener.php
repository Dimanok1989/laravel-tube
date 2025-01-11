<?php

namespace Kolgaev\Tube\Listeners;

use Kolgaev\Tube\Enums\DownloadStatuses;
use Kolgaev\Tube\Events\TubeDownloadFileErrorEvent;

class TubeDownloadFileErrorListener
{
    /**
     * Обработка события
     * 
     * @param \Kolgaev\Tube\Events\TubeDownloadFileErrorEvent $event
     * @return void
     */
    public function handle(TubeDownloadFileErrorEvent $event): void
    {
        $event->tube->update([
            'status' => DownloadStatuses::download_file_error,
        ]);

        $event->tube->logs()->create([
            'status' => DownloadStatuses::download_file_error,
            'message' => $event->message,
            'data' => [
                'video' => $event->video,
                'audio' => $event->audio,
            ],
        ]);
    }
}
