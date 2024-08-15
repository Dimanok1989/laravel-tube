<?php

namespace Kolgaev\Tube\Http\Controllers;

use Illuminate\Http\Request;
use Kolgaev\Tube\Models\TubeProcess;
use Kolgaev\Tube\TubeService;

class TubeController extends Controller
{
    /**
     * Модель процесса загрузки
     * 
     * @var \Kolgaev\Tube\Models\TubeProcess
     */
    protected TubeProcess $process;

    /**
     * Инициализация контроллера
     * 
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->process = TubeProcess::whereUuid($request->process)->firstOrFail();
    }

    /**
     * Начало загрузки
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function download()
    {
        $service = new TubeService($this->process->video_url);
        $stream = $service->getDonwnloadStream();

        return response()->json([
            'message' => "Загрузка началась",
        ]);
    }

    /**
     * Обработка webhook
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function webhook()
    {
        return response()->json([
            'message' => "Запрос обработан",
        ]);
    }
}
