<?php

namespace Kolgaev\Tube\Listeners;

use Kolgaev\Tube\Enums\DownloadStatuses;
use Kolgaev\Tube\Events\TubeDownloadFileEvent;

class TubeDownloadFileListener
{
    /**
     * Обработка события
     * 
     * @param \Kolgaev\Tube\Events\TubeDownloadFileEvent $event
     * @return void
     */
    public function handle(TubeDownloadFileEvent $event): void
    {
        $event->tube->update([
            'status' => DownloadStatuses::download_file,
        ]);

        $event->tube->logs()->create([
            'status' => DownloadStatuses::download_file,
            'message' => "[{$event->video}]"
        ]);
    }
}
