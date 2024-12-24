<?php

namespace Kolgaev\Tube\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Kolgaev\Tube\Traits\HasDebug;

class TubeRenderStartEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels, HasDebug;

    /**
     * Create a new event instance.
     * 
     * @param null|string $uuid
     * @param null|string $path
     * @return void
     */
    public function __construct(
        public ?string $uuid,
        public ?string $path
    ) {

        $this->toDubugLog();
    }
}
