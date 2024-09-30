<?php

namespace Kolgaev\Tube\Listeners;

use Kolgaev\Tube\Events\TubeDownloadedEvent;
use Kolgaev\Tube\Events\WebhookEvent;
use Kolgaev\Tube\Jobs\UploadFilesJob;

class Webhook
{
    /**
     * Обработка события завершения скачивания
     * 
     * @param \Kolgaev\Tube\Events\WebhookEvent $event
     * @return void
     */
    public function handle(WebhookEvent $event): void 
    {
        if (!$event->process) {
            return;
        }

        $handler = match ($event->data['type'] ?? null) {
            TubeDownloadedEvent::class => fn () => UploadFilesJob::dispatch($event->process),
            default => null,
        };

        if ($handler instanceof \Closure) {
            call_user_func($handler);
        }
    }
}
