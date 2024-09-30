<?php

namespace Kolgaev\Tube\Jobs;

use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Kolgaev\Tube\Events\TubeDoneEvent;
use Kolgaev\Tube\Events\TubeFailEvent;
use Kolgaev\Tube\Models\TubeProcess;
use Kolgaev\Tube\TubeService;

class DeleteFilesJob implements ShouldQueue
{
    use Queueable;

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
        if (!$process = TubeProcess::find($this->processId)) {
            return;
        }

        (new TubeService($process))->delete();
    }
}
