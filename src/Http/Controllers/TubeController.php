<?php

namespace Kolgaev\Tube\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Kolgaev\Tube\Events\WebhookEvent;
use Kolgaev\Tube\Jobs\DeleteFilesJob;
use Kolgaev\Tube\Jobs\DownloadFilesJob;
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

        $sing = $request->header('x-sing');
        $string = (new TubeService($this->process))->singString();

        abort_if(!Hash::check($string, $sing), 403);
    }

    /**
     * Начало загрузки
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function download()
    {
        DownloadFilesJob::dispatch(
            $this->process->id
        );

        return response()->json([
            'message' => "OK",
            'success' => true,
        ]);
    }

    /**
     * Обработка webhook
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function webhook(Request $request)
    {
        \Log::info('webhook', $request->all());

        WebhookEvent::dispatch(
            $this->process,
            $request->all()
        );

        return response()->json([
            'message' => "OK",
            'success' => true,
        ]);
    }

    /**
     * Выдача файла
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function file(Request $request)
    {
        $file = $this->process
            ->files()
            ->where('filename', pathinfo($request->file ?: "", PATHINFO_FILENAME))
            ->where('extension', pathinfo($request->file ?: "", PATHINFO_EXTENSION))
            ->first();

        abort_if(!$file, 404);

        $storage = Storage::disk('local');
        abort_if(!$storage->exists($file->path), 404);

        return response()->file($file->full_path);
    }

    /**
     * Удаление файлов после скачивания
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete()
    {
        DeleteFilesJob::dispatch($this->process->id ?? null);

        return response()->json([
            'message' => "OK",
            'success' => true,
        ]);
    }
}
