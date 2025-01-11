<?php

namespace Kolgaev\Tube\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Kolgaev\Tube\Models\Tube;

class TubeDownloadFileErrorEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     * 
     * @param \Kolgaev\Tube\Models\Tube $tube
     * @param null|string $message
     * @param null|string|int $video
     * @param null|string|int $audio
     * @return void
     */
    public function __construct(
        public Tube $tube,
        public ?string $message,
        public $video,
        public $audio
    ) {
        //
    }
}
