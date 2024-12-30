<?php

namespace Kolgaev\Tube\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Kolgaev\Tube\Models\Tube;
use Kolgaev\Tube\Resources\MetaResource;

class TubeReceivedMetaEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     * 
     * @param \Kolgaev\Tube\Models\Tube $tube
     * @param \Kolgaev\Tube\Resources\MetaResource $meta
     * @return void
     */
    public function __construct(
        public Tube $tube,
        public MetaResource $meta
    ) {
        //
    }
}
