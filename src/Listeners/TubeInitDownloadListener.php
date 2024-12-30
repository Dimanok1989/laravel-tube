<?php

namespace Kolgaev\Tube\Listeners;

use Kolgaev\Tube\Enums\DownloadStatuses;
use Kolgaev\Tube\Events\TubeInitDownloadEvent;

class TubeInitDownloadListener
{
    /**
     * Обработка события
     * 
     * @param \Kolgaev\Tube\Events\TubeInitDownloadEvent $event
     * @return void
     */
    public function handle(TubeInitDownloadEvent $event): void
    {
        $event->tube->logs()->create([
            'status' => DownloadStatuses::init_download,
        ]);
    }
}
