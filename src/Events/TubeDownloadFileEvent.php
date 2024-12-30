<?php

namespace Kolgaev\Tube\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Kolgaev\Tube\Models\Tube;

class TubeDownloadFileEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     * 
     * @return void
     */
    public function __construct(
        public Tube $tube,
        public $video,
        public $audio
    ) {
        //
    }
}
