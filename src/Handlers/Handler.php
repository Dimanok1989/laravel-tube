<?php

namespace Kolgaev\Tube\Handlers;

use Kolgaev\Tube\Interfaces\HandlerInterface;
use Kolgaev\Tube\TubeService;

class Handler implements HandlerInterface
{
    /**
     * Инициализация обработчика
     * 
     * @param \Kolgaev\Tube\TubeService $service
     * @return void
     */
    public function __construct(
        protected TubeService $service
    ) {
        //
    }

    /**
     * Обработка процесса
     * 
     * @return void
     */
    public function handle(): void
    {
        //
    }
}
