<?php

namespace Kolgaev\Tube\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Kolgaev\Tube\Traits\HasDebug;

/**
 * Событие прогресса скачивания видео
 */
class TubeDownloadProgressVideoEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels, HasDebug;

    /**
     * Create a new event instance.
     * 
     * @param null|string $uuid
     * @param float $percent
     * @return void
     */
    public function __construct(
        public ?string $uuid,
        public float $percent
    ) {

        $this->toDubugLog();
    }
}
