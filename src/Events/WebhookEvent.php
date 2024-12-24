<?php

namespace Kolgaev\Tube\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Kolgaev\Tube\Models\TubeProcess;
use Kolgaev\Tube\Traits\HasDebug;

class WebhookEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels, HasDebug;

    /**
     * Create a new event instance.
     * 
     * @param \Kolgaev\Tube\Models\TubeProcess $process
     * @param array $data
     * @return void
     */
    public function __construct(
        public TubeProcess $process,
        public array $data
    ) {

        $this->toDubugLog();
    }
}
