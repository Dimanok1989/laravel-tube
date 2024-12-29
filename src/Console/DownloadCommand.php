<?php

namespace Kolgaev\Tube\Console;

use Illuminate\Console\Command;
use Kolgaev\Tube\Resources\DownloadOutputResource;
use Kolgaev\Tube\Resources\MetaFormatResource;
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
        $service = new TubeService(
            $this->argument('url'),
            $this->output
        );

        $meta = $service->getMeta();

        $formatId = (string)select(
            label: 'Выберите качество видео',
            options: collect($meta->formats)
                ->filter(fn($item) => $item->fps > 15)
                ->sortBy('format_note', SORT_NATURAL)
                ->reverse()
                ->mapWithKeys(fn($item) => [
                    $item->id => $this->stremName($item)
                ])
                ->all(),
            // default: $default,
            scroll: 15,
        );

        if (empty($formatId)) {
            $this->error("Формат видео не определён");
            return;
        }

        $service->download($formatId, $meta->audioId, function (DownloadOutputResource $output) {
            $this->write($output->output);
        });
    }

    /**
     * Наименование для опции качества видео
     * 
     * @param \Kolgaev\Tube\Resources\MetaFormatResource $item
     * @return string
     */
    private function stremName(MetaFormatResource $item)
    {
        return "{$item->format} {$item->ext}"
            . (($item->acodec != "none") ? " + audio" : "");
    }
}
