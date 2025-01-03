<?php

namespace Kolgaev\Tube\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kolgaev\Tube\Models\Tube;
use Kolgaev\Tube\TubeService;

class TubeDownloadJob implements ShouldQueue, ShouldBeUnique, ShouldBeEncrypted
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
        protected int $tubeId
    ) {
        //
    }

    /**
     * Уникальный идентификатор задания
     * 
     * @return string
     */
    public function uniqueId(): string
    {
        return "tube-download-video-id-" . $this->tubeId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tube = Tube::findOrFail($this->tubeId);
        (new TubeService($tube))->handle();
    }

    public function failed(Exception $e)
    {
        Log::error('Job failed: ' . $e->getMessage());
    }
}
