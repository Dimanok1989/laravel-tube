<?php

namespace Kolgaev\Tube\Listeners;

use Kolgaev\Tube\Enums\DownloadStatuses;
use Kolgaev\Tube\Events\TubeDownloadDoneEvent;

class TubeDownloadDoneListener
{
    /**
     * Обработка события
     * 
     * @param \Kolgaev\Tube\Events\TubeDownloadDoneEvent $event
     * @return void
     */
    public function handle(TubeDownloadDoneEvent $event): void
    {
        $event->tube->update([
            'status' => DownloadStatuses::download_done,
        ]);

        $event->tube->logs()->create([
            'status' => DownloadStatuses::download_done,
        ]);
    }
}
