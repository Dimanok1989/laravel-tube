<?php

namespace Kolgaev\Tube\Listeners;

use Illuminate\Support\Facades\Http;
use Kolgaev\Tube\Events\TubeDoneEvent;
use Kolgaev\Tube\Events\TubeFailEvent;
use Kolgaev\Tube\Models\TubeProcess;
use Kolgaev\Tube\TubeService;

/**
 * Слушатель, обрабатывающий событие завершения всего процесса скачивания
 */
class DownloadDoneProcess
{
    /**
     * Обработка события завершения скачивания
     * 
     * @param \Kolgaev\Tube\Events\TubeDoneEvent|\Kolgaev\Tube\Events\TubeFailEvent $event
     * @return void
     */
    public function handle(TubeDoneEvent|TubeFailEvent $event): void
    {
        if (!$process = TubeProcess::whereUuid($event->uuid)->first()) {
            return;
        }

        $handle = match ($event::class) {
            TubeDoneEvent::class => fn($process, $event) => $this->handleDone($process, $event),
            TubeFailEvent::class => fn($process, $event) => $this->handleFail($process, $event),
            default => fn() => null
        };

        call_user_func($handle, $process, $event);
    }

    /**
     * Обработка события успешного завершения скачивания
     * 
     * @param \Kolgaev\Tube\Models\TubeProcess $process
     * @param \Kolgaev\Tube\Events\TubeDoneEvent $event
     * @return void
     */
    private function handleDone(TubeProcess $process, TubeDoneEvent $event)
    {
        $process->update([
            'status' => TubeProcess::STATUS_DONE
        ]);

        if ($process->callback_url ?? null) {
            Http::baseUrl($process->callback_url)
                ->withHeader('X-Sing', (new TubeService($process))->sing())
                ->post(route('kolgaev.tube.webhook', $process->uuid, false), [
                    'type' => $event::class,
                ]);
        }
    }

    /**
     * Обработка события завершения скачивания c ошибкой
     * 
     * @param \Kolgaev\Tube\Models\TubeProcess $process
     * @param \Kolgaev\Tube\Events\TubeFailEvent $event
     * @return void
     */
    private function handleFail(TubeProcess $process, TubeFailEvent $event)
    {
        $process->update([
            'status' => TubeProcess::STATUS_FAIL,
            'data' => [
                ...(is_array($process->data) ? $process->data : []),
                'error' => $event->errorMessage ?? null,
            ]
        ]);

        if ($process->callback_url ?? null) {
            Http::baseUrl($process->callback_url)
                ->withHeader('X-Sing', (new TubeService($process))->sing())
                ->post(route('kolgaev.tube.webhook', $process->uuid, false), [
                    'type' => $event::class,
                    'message' => $event->errorMessage ?? null,
                ]);
        }
    }
}
