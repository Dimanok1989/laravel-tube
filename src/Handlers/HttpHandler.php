<?php

namespace Kolgaev\Tube\Handlers;

use Illuminate\Support\Facades\Http;
use Kolgaev\Tube\TubeService;

class HttpHandler extends Handler
{
    /**
     * HTTP клиент
     * 
     * @var \Illuminate\Http\Client\PendingRequest
     */
    protected $client;

    /**
     * Инициализация обработчика
     * 
     * @param \Kolgaev\Tube\TubeService $service
     * @return void
     */
    public function __construct(TubeService $service)
    {
        parent::__construct($service);

        $this->client = Http::baseUrl(config('tube.base_url'))
            ->withHeader('X-Sing', $this->service->sing());
    }

    /**
     * Обработка процесса
     * 
     * @return void
     */
    public function handle(): void
    {
        $this->client->post(
            route('kolgaev.tube.download', $this->service->process()->uuid, false)
        );
    }
}
