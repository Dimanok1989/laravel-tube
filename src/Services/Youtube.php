<?php

namespace Kolgaev\Tube\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Process;
use Kolgaev\Tube\Collection;
use Kolgaev\Tube\Events\DownloadFileProgressEvent;
use Kolgaev\Tube\Models\TubeProcess;
use Kolgaev\Tube\Resources\StreamResource;

class Youtube
{
    /**
     * Путь до исполнительного файла phyton
     * 
     * @var string
     */
    protected $phyton;

    /**
     * Мета данные видео
     * 
     * @var null|array
     */
    protected $meta;

    /**
     * Инициализация хостинг сервиса
     * 
     * @param \Kolgaev\Tube\Models\TubeProcess $process
     * @param string $url
     * @return void
     */
    public function __construct(
        protected TubeProcess $process,
        protected string $url
    ) {

        if (empty($this->phyton = exec('which python'))) {
            $this->phyton = exec('which python3');
        }
    }

    /**
     * Получает и сохраняет мета данные видео
     * 
     * @return null|array
     */
    private function setMeta(): ?array
    {
        $result = Process::run([
            $this->phyton,
            realpath(__DIR__ . "/../../pytube/meta.py"),
            "\"{$this->url}\"",
        ]);

        return $this->meta = json_decode($result->output(), true);
    }

    /**
     * Выводит мета данные видео
     * 
     * @return null|array
     */
    public function getMeta(): ?array
    {
        return $this->meta ?: $this->setMeta();
    }

    private function stream($itag)
    {
        return new StreamResource(
            new Collection(
                collect($this->meta['streams'] ?? [])->firstWhere('itag', $itag)
            )
        );
    }

    public function download(string $path, string $filename, ?int $itag = null)
    {
        $stream = $this->stream($itag);

        $filename = collect([
            $filename,
            time(),
            $stream->res,
            $stream->extension,
        ])->filter()->join(".");

        $command = [
            $this->phyton,
            realpath(__DIR__ . "/../../pytube/download.py"),
            $this->url,
            $path,
            $itag,
            $filename,
        ];

        $tik = 1;

        $result = Process::timeout(3600)->run($command, function ($a, $b) use (&$tik) {

            if ($tik == 1) {
                preg_match("/(\d+.\d+)%/", $b, $matches);
                DownloadFileProgressEvent::dispatch(
                    $this->process->uuid ?? null,
                    (float) $matches[1]
                );
            }

            if ($tik == 5) {
                $tik = 0;
            }

            $tik++;
        });

        DownloadFileProgressEvent::dispatch($this->process->uuid ?? null, 100);
    }
}
