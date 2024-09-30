<?php

namespace Kolgaev\Tube\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kolgaev\Tube\Events\TubeFailEvent;
use Kolgaev\Tube\Models\TubeProcess;
use Kolgaev\Tube\TubeService;

class DownloadFilesJob implements ShouldQueue
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
     * Create a new job instance.
     */
    public function __construct(
        protected int $processId
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $process = TubeProcess::find($this->processId);
        $service = new TubeService($process);

        try {
            $service->startDownload();
        } catch (Exception $e) {
            TubeFailEvent::dispatch($process->uuid, $e->getMessage());
        }
    }
}
