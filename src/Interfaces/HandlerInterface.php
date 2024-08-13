<?php

namespace Kolgaev\Tube\Interfaces;

use Kolgaev\Tube\TubeService;

interface HandlerInterface
{
    /**
     * Инициализация обработчика
     * 
     * @param \Kolgaev\Tube\TubeService $service
     * @return void
     */
    public function __construct(TubeService $service);

    /**
     * Обработка процесса
     * 
     * @return void
     */
    public function handle(): void;
}