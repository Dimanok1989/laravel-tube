<?php

namespace Kolgaev\Tube;

use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kolgaev\Tube\Enums\Tubes;
use Kolgaev\Tube\Events\TubeDownloadedEvent;
use Kolgaev\Tube\Events\TubeDownloadStartEvent;
use Kolgaev\Tube\Exceptions\HandlerBad;
use Kolgaev\Tube\Exceptions\HandlerNotExists;
use Kolgaev\Tube\Exceptions\StreamNotFound;
use Kolgaev\Tube\Interfaces\HandlerInterface;
use Kolgaev\Tube\Models\TubeProcess;
use Kolgaev\Tube\Resources\MetaResource;

class TubeService
{
    /**
     * Ссылка на видео
     * 
     * @var string
     */
    protected $url;

    /**
     * Файловое хранилище
     * 
     * @var \Illuminate\Filesystem\FilesystemAdapter
     */
    protected $storage;

    /**
     * Тип видеохостинга
     * 
     * @var \Kolgaev\Tube\Enums\Tubes
     */
    protected $tube;

    /**
     * Идентификатор видео
     * 
     * @var null|string
     */
    protected $tubeId;

    /**
     * Мета данные
     * 
     * @var null|\Kolgaev\Tube\Resources\MetaResource
     */
    protected $meta;

    /**
     * Модель процесса загрузки видео
     * 
     * @var \Kolgaev\Tube\Models\TubeProcess
     */
    public static $process;

    /**
     * Клиент видеохостинга
     * 
     * @var object
     */
    protected $client;

    /**
     * Инициализация сервиса
     * 
     * @param string|\Kolgaev\Tube\Models\TubeProcess $url
     * @return void
     */
    public function __construct(string|TubeProcess $data)
    {
        $this->storage = Storage::disk('local');

        if (is_string($data)) {

            $this->url = $data;

            $this->parseUrl();

            self::$process = TubeProcess::firstOrCreate([
                'type' => $this->tube->name,
                'tube_id' => $this->tubeId,
            ], [
                'uuid' => Str::orderedUuid()->toString(),
                'status' => TubeProcess::STATUS_CREATED,
                'callback_url' => $this->isHttp() ? config('app.url') : null,
            ]);
        } else if ($data instanceof TubeProcess) {

            $this->url = $data->video_url;
            $this->tube = $data->type;
            $this->tubeId = $data->tube_id;

            self::$process = $data;
        }

        if (!$this->storage->exists($this->tube->name)) {
            $this->storage->makeDirectory($this->tube->name);
            $this->setPermit($this->storage->path($this->tube->name));
        }
    }

    /**
     * Парсинг ссылки и определение источника
     * 
     * @return string
     * 
     * @throws \Exception
     */
    private function parseUrl(): string
    {
        $parseUrl = parse_url($this->url);
        $host = $parseUrl['host'] ?? "";

        if ($host == "youtu.be") {
            $this->tube = Tubes::youtube;
            $tubeId = pathinfo($parseUrl['path'] ?? "", PATHINFO_BASENAME);
        } else if (mb_strpos($host, "youtube.com") !== false) {
            $this->tube = Tubes::youtube;
            parse_str($parseUrl['query'] ?? "", $query);
            $tubeId = $query['v'] ?? null;
        }

        if (empty($tubeId)) {
            throw new \Exception("Не найден идентификатор видео");
        }

        return $this->tubeId = $tubeId;
    }

    /**
     * Режим работы через внешний сервер
     * 
     * @return bool
     */
    private function isHttp()
    {
        return config('tube.mode') == "http";
    }

    /**
     * Обработка процесса
     * 
     * @return void
     */
    public function handle()
    {
        $mode = config('tube.mode');
        $handler = __NAMESPACE__ . "\\Handlers\\" . ucfirst($mode) . "Handler";

        try {

            if (!class_exists($handler)) {
                throw new HandlerNotExists("Обработчик [$mode] не найден");
            }

            $handler = new $handler($this);

            if (!is_a($handler, HandlerInterface::class)) {
                throw new HandlerBad("Неизвестный обработчик [$mode]");
            }

            $handler->handle();
        } catch (Exception $e) {
            $this->process()->update([
                'status' => TubeProcess::STATUS_FAIL,
                'data' => [
                    ...(is_array($this->process()->data) ? $this->process()->data : []),
                    'error' => $e->getMessage(),
                ],
            ]);
        }
    }

    /**
     * Выводит ссылку на видео
     * 
     * @return string
     */
    public function url()
    {
        return $this->url;
    }

    /**
     * Клиент видеохостинга
     * 
     * @return object
     */
    private function client()
    {
        if ($this->client) {
            return $this->client;
        }

        $client = $this->tube->client();

        return $this->client = new $client($this);
    }

    /**
     * Выводит модель процесса загрузки
     * 
     * @return \Kolgaev\Tube\Models\TubeProcess
     */
    public function process()
    {
        return self::$process->refresh();
    }

    /**
     * Выводит мета данные видео
     * 
     * @return \Kolgaev\Tube\Resources\MetaResource
     */
    public function meta()
    {
        if ($this->meta) {
            return $this->meta;
        }

        if (!empty(self::$process->data['streams'])) {
            return $this->meta = new MetaResource(self::$process->data);
        }

        $this->meta = new MetaResource(
            $this->client()->getMeta()
        );

        self::$process->title = $this->meta->getTitle();
        self::$process->description = $this->meta->getDescription();
        self::$process->length = $this->meta->getLength();
        self::$process->publish_date = $this->meta->getPublishDate();
        self::$process->data = $this->meta->toArray();
        self::$process->user_id = auth()->id();
        self::$process->save();

        return $this->meta;
    }

    /**
     * Начало скачивание файлов
     * 
     * @param int $itag
     */
    public function download(int $itag)
    {
        TubeDownloadStartEvent::dispatch(
            $this->process()->uuid,
        );

        $filename = Str::slug(self::$process->title ?? null);

        $dir = collect([
            $this->tube->name,
            $this->tubeId,
        ])->join(DIRECTORY_SEPARATOR);

        $this->storage->makeDirectory($dir);
        $path = $this->storage->path($dir);

        $this->setPermit($path);

        $this->client()->download($path, $filename, $itag);

        $this->process()->update([
            'status' => TubeProcess::STATUS_DOWNLOADED,
        ]);

        TubeDownloadedEvent::dispatch(
            $this->process()->uuid,
        );
    }

    /**
     * Начинает скачивание видео в качестве HD и лучшим аудио
     * В случае отсутствия видео в качестве HD, будет скачано видео с
     * наилучшим каачеством
     * 
     * @return void
     */
    public function startDownload()
    {
        if (empty($stream = $this->getDonwnloadStream())) {
            $stream = $this->meta()
                ->streams()
                ->filter(fn($stream) => $stream->type == "video")
                ->sortBy('res', SORT_NATURAL)
                ->last();
        }

        $itag = $stream['itag'] ?? null;

        if (empty($stream['itag'])) {
            throw new StreamNotFound("Данные о потоке видео не найдены");
        }

        $this->download($itag);
    }

    /**
     * Находит предпочитаемый поток для скачивания видео
     * 
     * @return null|array
     */
    public function getDonwnloadStream()
    {
        return $this->meta()
            ->streams()
            ->filter(fn($stream) => $stream->type == "video")
            ->filter(fn($item) => strpos((string) $item->res, "1080") !== false)
            ->filter(fn($item) => strpos((string) $item->mime_type, "mp4") !== false)
            ->first();
    }

    /**
     * Формирование подписи для запроса
     * 
     * @return string
     */
    public function sing()
    {
        return Hash::make(
            $this->singString()
        );
    }

    /**
     * Формирует строку для подписи
     * 
     * @return string
     */
    public function singString()
    {
        return collect([
            self::$process->uuid,
            $this->tube->value,
            $this->tubeId,
        ])->join("|");
    }

    /**
     * Удаление файлов
     * 
     * @return array
     */
    public function delete()
    {
        foreach ($this->process()->files ?? [] as $file) {

            if (!$this->storage->exists($file->path)) {
                continue;
            }

            $this->storage->delete($file->path);

            $files[] = $file->basename;
        }

        if (!empty($file)) {
            $this->storage->deleteDirectory(
                pathinfo($file->path, PATHINFO_DIRNAME)
            );
        }

        return $files ?? [];
    }

    /**
     * Устаналиваает права на файл
     * 
     * @param string $path
     * @return void
     */
    public static function setPermit(string $path)
    {
        try {
            chown($path, env('TUBE_OWNER_USER', 'www-data'));
            chgrp($path, env('TUBE_OWNER_GROUP', 'www-data'));
        } catch (Exception) {
            //
        }

        try {
            chmod($path, 0755);
        } catch (Exception) {
            //
        }
    }
}
