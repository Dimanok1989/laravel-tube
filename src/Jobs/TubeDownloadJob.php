<?php

namespace Kolgaev\Tube\Jobs;

use App\Events\Tube\DownloadProgressEvent;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Kolgaev\Tube\Models\Tube;
use Kolgaev\Tube\TubeService;
use Throwable;

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
     * Модель видео
     * 
     * @var null|\Kolgaev\Tube\Models\Tube
     */
    protected $tube;

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
        $this->tube = Tube::findOrFail($this->tubeId);
        (new TubeService($this->tube))->handle();
    }

    /**
     * Обработка ошибки задания
     * 
     * @param null|\Throwable $exception
     * @return void
     */
    public function failed(?Throwable $exception): void
    {
        Log::error("tube-download-video-id-" . $this->tubeId, [
            'exception' => $exception
        ]);

        DownloadProgressEvent::dispatch($this->tube->uuid, self::class, [
            'error' => optional($exception->getMessage()) ?: "Ошибка загрузки видео",
        ]);
    }
}
