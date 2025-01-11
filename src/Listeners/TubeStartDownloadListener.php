<?php

namespace Kolgaev\Tube\Listeners;

use Kolgaev\Tube\Events\TubeStartDownloadEvent;

class TubeStartDownloadListener
{
    /**
     * Обработка события
     * 
     * @param \Kolgaev\Tube\Events\TubeStartDownloadEvent $event
     * @return void
     */
    public function handle(TubeStartDownloadEvent $event): void
    {
        //
    }
}
