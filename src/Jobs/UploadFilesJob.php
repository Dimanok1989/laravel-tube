<?php

namespace Kolgaev\Tube\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Kolgaev\Tube\Models\TubeFile;
use Kolgaev\Tube\Models\TubeProcess;
use Kolgaev\Tube\TubeService;

class UploadFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Количество попыток выполнения задания
     * 
     * @var int
     */
    public $tries = 1;

    /**
     * Таймаут
     * 
     * @var int
     */
    public $timeout = 3600;

    /**
     * Подпись запроса
     * 
     * @var null|string
     */
    private $xSing = null;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected TubeProcess $process
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->process->files()->each(function ($file) {
                $this->uploadFile($file);
            });
        } catch (Exception $e) {
            //
        }

        if (config('tube.upload_delete_after')) {
            Http::baseUrl(config('tube.base_url'))
                ->withHeader('X-Sing', $this->xSing($this->process))
                ->delete(route(
                    'kolgaev.tube.file.delete',
                    ['process' => $this->process->uuid],
                    false
                ));
        }

        $this->process->update([
            'status' => TubeProcess::STATUS_UPLOADED,
        ]);

        TubeRenderJob::dispatch(
            $this->process->refresh()
        );
    }

    /**
     * Загрузка файла
     * 
     * @param \Kolgaev\Tube\Models\TubeFile $file
     * @return void
     */
    public function uploadFile(TubeFile $file)
    {
        $storage = Storage::disk('local');

        if ($storage->exists($file->path)) {
            return;
        }

        $storage->makeDirectory(
            pathinfo($file->path, PATHINFO_DIRNAME)
        );

        $params = [
            'process' => $this->process->uuid,
            'file' => $file->basename,
        ];

        $data = Http::baseUrl(config('tube.base_url'))
            ->withHeader('X-Sing', $this->xSing($this->process))
            ->withOptions(['stream' => true])
            ->timeout(3600)
            ->get(route('kolgaev.tube.download.file', $params, false));

        throw_if($data->failed(), Exception::class, "Исходный файл не доступен");

        $storage->put($file->path, $data->getBody());
    }

    /**
     * Подпись запроса
     * 
     * @return string
     */
    private function xSing()
    {
        return $this->xSing
            ?: ($this->xSing = (new TubeService($this->process))->sing());
    }
}
