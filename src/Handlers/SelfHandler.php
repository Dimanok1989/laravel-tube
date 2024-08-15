<?php

namespace Kolgaev\Tube\Handlers;

use Kolgaev\Tube\Events\TubeDoneEvent;
use Kolgaev\Tube\Exceptions\StreamNotFound;
use Kolgaev\Tube\Models\TubeProcess;

class SelfHandler extends Handler
{
    /**
     * Обработка процесса
     * 
     * @return void
     */
    public function handle(): void
    {
        $stream = $this->service->getDonwnloadStream();

        if (empty($stream['itag'])) {
            throw new StreamNotFound("Данные о потоке видео не найдены");
        }

        $this->service->download($stream['itag']);

        $this->service->process()->update([
            'status' => TubeProcess::STATUS_UPLOADED,
        ]);

        TubeDoneEvent::dispatch($this->service->process()->uuid);
    }
}
