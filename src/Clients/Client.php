<?php

namespace Kolgaev\Tube\Clients;

use Kolgaev\Tube\TubeService;

class Client
{
    /**
     * Клиент загрузчика
     * 
     * @var \Kolgaev\Tube\Interfaces\ClientIterface
     */
    private $client;

    /**
     * Инициализация клиента
     * 
     * @param \Kolgaev\Tube\TubeService $service
     * @return void
     */
    public function __construct(protected TubeService $service) {

        $this->setClient();
    }

    /**
     * Возвращает инициализированный клиент загрузчика
     * 
     * @return \Kolgaev\Tube\Interfaces\ClientIterface
     */
    public function __invoke()
    {
        return $this->getClient();
    }

    /**
     * Возвращает инициализированный клиент загрузчика
     * 
     * @return \Kolgaev\Tube\Interfaces\ClientIterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Устаналивает клиент загрузчика
     * 
     * @return \Kolgaev\Tube\Clients\YtDlp
     */
    private function setClient()
    {
        return $this->client = new YtDlp($this->service);
    }
}
