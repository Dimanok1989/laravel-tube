<?php

namespace Kolgaev\Tube\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Kolgaev\Tube\Traits\HasDebug;

class TubeUploadStartEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels, HasDebug;

    /**
     * Create a new event instance.
     * 
     * @param null|string $uuid
     * @return void
     */
    public function __construct(
        public ?string $uuid
    ) {

        $this->toDubugLog();
    }
}
