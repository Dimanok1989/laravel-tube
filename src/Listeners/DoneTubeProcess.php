<?php

namespace Kolgaev\Tube\Listeners;

use Kolgaev\Tube\Events\TubeDoneEvent;
use Kolgaev\Tube\Models\TubeProcess;

class DoneTubeProcess
{
    /**
     * Обработка события завершения скачивания
     * 
     * @param \Kolgaev\Tube\Events\TubeDoneEvent $event
     * @return void
     */
    public function handle(TubeDoneEvent $event): void
    {
        TubeProcess::whereUuid($event->uuid)->update([
            'status' => TubeProcess::STATUS_DONE
        ]);
    }
}