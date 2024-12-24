<?php

namespace Kolgaev\Tube\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Kolgaev\Tube\Traits\HasDebug;

class TubeFailEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels, HasDebug;

    /**
     * Create a new event instance.
     * 
     * @param null|string $uuid
     * @param null|string $errorMessage
     * @param null|int $failId
     * @return void
     */
    public function __construct(
        public ?string $uuid,
        public ?string $errorMessage,
        public ?int $errorCode = null
    ) {

        $this->toDubugLog();
    }
}
