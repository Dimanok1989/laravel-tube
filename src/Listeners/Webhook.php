<?php

namespace Kolgaev\Tube\Listeners;

use Kolgaev\Tube\Events\TubeDownloadedEvent;
use Kolgaev\Tube\Events\WebhookEvent;
use Kolgaev\Tube\Jobs\UploadFilesJob;
use Kolgaev\Tube\Traits\HasDebug;

class Webhook
{
    use HasDebug;

    /**
     * Обработка события завершения скачивания
     * 
     * @param \Kolgaev\Tube\Events\WebhookEvent $event
     * @return void
     */
    public function handle(WebhookEvent $event): void
    {
        $this->toDubugLog([
            'type' => $event::class,
            'uuid' => $event->process->uuid ?? null,
            'event' => get_object_vars($event),
        ]);

        if (!$event->process) {
            return;
        }

        $handler = match ($event->data['type'] ?? null) {
            TubeDownloadedEvent::class => fn() => UploadFilesJob::dispatch($event->process),
            default => null,
        };

        if ($handler instanceof \Closure) {
            call_user_func($handler);
        }
    }
}
