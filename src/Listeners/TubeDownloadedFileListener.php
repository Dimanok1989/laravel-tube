<?php

namespace Kolgaev\Tube\Listeners;

use Illuminate\Support\Facades\Storage;
use Kolgaev\Tube\Enums\DownloadStatuses;
use Kolgaev\Tube\Events\TubeDownloadedFileEvent;
use Kolgaev\Tube\Resources\MetaFormatResource;
use Kolgaev\Tube\TubeService;

class TubeDownloadedFileListener
{
    /**
     * Обработка события
     * 
     * @param \Kolgaev\Tube\Events\TubeDownloadedFileEvent $event
     * @return void
     */
    public function handle(TubeDownloadedFileEvent $event): void
    {
        $dir = pathinfo($event->path['path'], PATHINFO_DIRNAME);
        $filename = pathinfo($event->path['path'], PATHINFO_FILENAME);
        $extension = pathinfo($event->path['path'], PATHINFO_EXTENSION);

        $videoFormat = collect($event->meta->formats)->firstWhere('id', $event->video);
        $audioFormat = collect($event->meta->formats)->firstWhere('id', $event->audio);

        $event->tube->update([
            'status' => DownloadStatuses::downloaded_file,
        ]);

        $disk = TubeService::getDiskName();
        $storage = Storage::disk($disk);

        $event->tube->videos()->updateOrCreate([
            'format_id' => $event->video,
            'filename' => $filename,
            'extension' => $extension,
            'format' => MetaFormatResource::getFormatNote($videoFormat),
        ], [
            'disk' => $disk,
            'path' => $dir,
            'mime_type' => $storage->mimeType($event->path['path']),
            'filesize' => $storage->size($event->path['path']),
            'resolution' => $videoFormat->resolution,
            'fps' => $videoFormat->fps ?? null,
            'vcodec' => $videoFormat->vcodec ?? null,
            'acodec' => $audioFormat->acodec ?? null,
        ]);

        $event->tube->logs()->create([
            'status' => DownloadStatuses::downloaded_file,
            'message' => "[{$event->video}] PATH:" . $event->path['path'],
        ]);
    }
}
