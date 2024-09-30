<?php

namespace Kolgaev\Tube\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Kolgaev\Tube\Events\CompletedEvent;
use Kolgaev\Tube\Events\TubeDoneEvent;
use Kolgaev\Tube\Events\TubeFailEvent;
use Kolgaev\Tube\Models\TubeProcess;

class TubeRenderJob implements ShouldQueue
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
        protected TubeProcess $process
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $storage = Storage::disk('local');

        $video = $this->process
            ->files('type', 'video')
            ->orderByDesc('size')
            ->first();

        $audio = $this->process
            ->files('type', 'audio')
            ->orderByDesc('size')
            ->first();

        if (!$video || !$audio) {
            return;
        }

        $dirname = pathinfo($video->path, PATHINFO_DIRNAME);
        $output = "$dirname/{$this->process->uuid}.{$video->extension}";

        $command = collect([
            'ffmpeg',
            '-i "' . $storage->path($video->path) . '"',
            '-i "' . $storage->path($audio->path) . '"',
            '-c:v copy',
            '-c:a copy',
            '"' . $storage->path($output) . '"',
            '-y'
        ])->join(" ");

        if (!$storage->exists($output)) {

            $result = Process::run($command);

            if ($result->exitCode()) {

                Log::error("TUBE render video exit " . $result->exitCode());
                Log::debug($command);
                Log::debug("\n" . $result->errorOutput());

                TubeFailEvent::dispatch($this->process->uuid, "Ошибка рендера видео");
                return;
            }
        }

        $this->process->update([
            'status' => TubeProcess::STATUS_RENDERED,
        ]);

        TubeDoneEvent::dispatch($this->process->uuid);
    }
}
