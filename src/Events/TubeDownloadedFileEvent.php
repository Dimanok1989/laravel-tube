<?php

namespace Kolgaev\Tube\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Kolgaev\Tube\Models\Tube;
use Kolgaev\Tube\Resources\MetaResource;

class TubeDownloadedFileEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     * 
     * @param \Kolgaev\Tube\Models\Tube $tube
     * @param array $path
     * @param \Kolgaev\Tube\Resources\MetaResource $meta
     * @param null|string|int $video
     * @param null|string|int $audio
     * @return void
     */
    public function __construct(
        public Tube $tube,
        public array $path,
        public MetaResource $meta,
        public $video,
        public $audio,
    ) {
        //
    }
}
