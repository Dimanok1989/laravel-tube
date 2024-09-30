<?php

namespace Kolgaev\Tube\Services;

use Exception;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Kolgaev\Tube\Events\TubeDownloadProgressAudioEvent;
use Kolgaev\Tube\Events\TubeDownloadProgressVideoEvent;
use Kolgaev\Tube\Resources\StreamResource;
use Kolgaev\Tube\TubeService;

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
        protected TubeService $service
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
        $streams = optional($this->service->process())->data['streams'] ?? null;

        if (!empty($streams)) {
            return $this->meta = $this->service->process()->data;
        }

        $result = Process::run([
            $this->phyton,
            realpath(__DIR__ . "/../../pytube/meta.py"),
            $this->service->url(),
        ]);

        if ($result->failed()) {

            $details = collect(explode("\n", $result->errorOutput()))
                ->mapWithKeys(function ($item) {

                    $data = explode(":", $item);

                    return [
                        $data[0] => Str::squish($data[1] ?? "")
                    ];
                })
                ->all();

            throw new Exception(
                $details['Reason'] ?? "Не удалось получить мета-данные о видео"
            );
        }

        $this->meta = json_decode($result->output(), true);

        return $this->meta;
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

    /**
     * Поиск объекта потока по идентификатору
     * 
     * @param int $itag
     * @return \Kolgaev\Tube\Resources\StreamResource
     */
    private function stream(int $itag)
    {
        return new StreamResource(
            collect($this->getMeta()['streams'] ?? [])->firstWhere('itag', $itag)
        );
    }

    /**
     * Скачивание файла
     * 
     * @param string $path
     * @param string $filename
     * @param null|int $itag
     * @return array
     */
    public function download(string $path, string $filename, ?int $itag = null)
    {
        $stream = $this->stream($itag);

        $files[] = $this->downloadVideo($stream, $path, $filename);

        if ($stream->only_audio === false) {
            $files[] = $this->downloadAudio($path, $filename);
        }

        foreach ($files as $path) {
            $this->service->setPermit($path);
        }

        return $files;
    }

    /**
     * Скачиваание видео
     * 
     * @param \Kolgaev\Tube\Resources\StreamResource $stream
     * @param string $path
     * @param string $filename
     * @return string
     */
    public function downloadVideo(StreamResource $stream, string $path, string $filename)
    {
        $filename = collect([
            $filename,
            $stream->res,
            $stream->extension,
        ])->filter()->join(".");

        $command = [
            $this->phyton,
            realpath(__DIR__ . "/../../pytube/download.py"),
            $this->service->url(),
            $path,
            $stream->itag,
            $filename,
        ];

        $this->downloadProcess(
            $command,
            $path,
            $filename,
            TubeDownloadProgressVideoEvent::class,
        );

        $this->service->process()->files()->firstOrCreate([
            'type' => $stream->type,
            'filename' => pathinfo($filename, PATHINFO_FILENAME),
            'extension' => pathinfo($filename, PATHINFO_EXTENSION),
            'mime_type' => $stream->mime_type,
            'size' => $stream->filesize,
            'quality' => $stream->res,
        ]);

        return "$path/$filename";
    }

    /**
     * Скачиваание аудио дорожки
     * 
     * @param string $path
     * @param string $filename
     * @return string
     */
    public function downloadAudio(string $path, string $filename)
    {
        $stream = collect($this->meta['streams'] ?? [])
            ->filter(fn($item) => $item['type'] == "audio")
            ->sortBy('abr', SORT_NATURAL)
            ->map(fn($item) => new StreamResource($item))
            ->last();

        $filename = collect([
            $filename,
            $stream->abr,
            $stream->extension,
        ])->filter()->join(".");

        $command = [
            $this->phyton,
            realpath(__DIR__ . "/../../pytube/download.py"),
            $this->service->url(),
            $path,
            $stream->itag,
            $filename,
        ];

        $this->downloadProcess(
            $command,
            $path,
            $filename,
            TubeDownloadProgressAudioEvent::class,
        );

        $this->service->process()->files()->firstOrCreate([
            'type' => $stream->type,
            'filename' => pathinfo($filename, PATHINFO_FILENAME),
            'extension' => pathinfo($filename, PATHINFO_EXTENSION),
            'mime_type' => $stream->mime_type,
            'size' => $stream->filesize,
            'quality' => $stream->abr,
        ]);

        return "$path/$filename";
    }

    /**
     * Выполняет процесс загрузки файла
     * 
     * @param array $command
     * @param string $path
     * @param string $filename
     * @param string $event
     * @return void
     */
    private function downloadProcess(
        array $command,
        string $path,
        string $filename,
        string $event
    ) {

        $tik = 1;

        if (!file_exists("$path/$filename")) {

            Process::timeout(3600)
                ->run($command, function (string $type, string $output) use (&$tik, $event) {

                    if ($type != "out") {
                        return;
                    }

                    if ($tik == 1) {
                        
                        preg_match("/(\d+.\d+)%/", $output, $matches);

                        $percent = $matches[1] ?? null;

                        if (is_numeric($percent) && $percent < 100) {
                            $event::dispatch(
                                $this->service->process()->uuid ?? null,
                                (float) $matches[1]
                            );
                        }
                    }

                    if ($tik == 5) {
                        $tik = 0;
                    }

                    $tik++;
                });
        }

        $event::dispatch($this->service->process()->uuid ?? null, 100);
    }
}
