<?php

namespace Kolgaev\Tube\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Kolgaev\Tube\Resources\StreamResource;
use Kolgaev\Tube\TubeService;

use function Laravel\Prompts\select;

class DownloadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tube:download
                            {url : Ссылка на видео}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Скачивание видео';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $service = new TubeService($this->argument('url'));

        try {

            $streams = $service->meta()->streams();

            $default = $streams
                ->filter(fn($item) => strpos((string) $item->res, "1080") !== false)
                ->filter(fn($item) => strpos((string) $item->mime_type, "mp4") !== false)
                ->first()
                ->itag ?? null;

            $itag = select(
                label: 'Выберите качество видео',
                options: $streams
                    ->filter(fn($item) => $item->type == "video")
                    ->sortBy('res', SORT_NATURAL)
                    ->reverse()
                    ->mapWithKeys(fn($item) => [
                        $item->itag => $this->stremName($item)
                    ])
                    ->all(),
                default: $default,
                scroll: 15,
            );
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        if (empty($itag)) {
            $this->error("ITAG не определён");
            return;
        }

        $service->download($itag);
    }

    /**
     * Наименование для опции качества видео
     * 
     * @param \Kolgaev\Tube\Resources\StreamResource $item
     * @return string
     */
    private function stremName(StreamResource $item)
    {
        return "{$item->res} {$item->extension}"
            . ($item->only_audio ? " + audio" : "");
    }
}
