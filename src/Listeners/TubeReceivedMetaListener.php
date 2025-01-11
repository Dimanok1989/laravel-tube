<?php

namespace Kolgaev\Tube\Listeners;

use Kolgaev\Tube\Enums\DownloadStatuses;
use Kolgaev\Tube\Events\TubeReceivedMetaEvent;

class TubeReceivedMetaListener
{
    /**
     * Обработка события
     * 
     * @param \Kolgaev\Tube\Events\TubeReceivedMetaEvent $event
     * @return void
     */
    public function handle(TubeReceivedMetaEvent $event): void
    {
        $event->tube->update([
            'title' => $event->meta->fulltitle,
            'description' => $event->meta->description,
            'duration' => $event->meta->duration,
            'status' => DownloadStatuses::received_meta,
            'thumbnail' => $event->meta->thumbnail,
            'channel' => $event->meta->channel,
            'publish_date' => $event->meta->publishDate,
            'data' => $event->meta->toArray(),
        ]);

        $event->tube->logs()->create([
            'status' => DownloadStatuses::received_meta,
        ]);
    }
}
