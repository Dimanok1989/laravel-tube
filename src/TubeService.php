<?php

namespace Kolgaev\Tube;

use Closure;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kolgaev\Tube\Clients\Client;
use Kolgaev\Tube\Enums\DownloadStatuses;
use Kolgaev\Tube\Events\TubeDownloadedFileEvent;
use Kolgaev\Tube\Events\TubeDownloadedFileProgressEvent;
use Kolgaev\Tube\Events\TubeDownloadFileErrorEvent;
use Kolgaev\Tube\Events\TubeDownloadFileEvent;
use Kolgaev\Tube\Events\TubeInitDownloadEvent;
use Kolgaev\Tube\Events\TubeReceivedMetaEvent;
use Kolgaev\Tube\Exceptions\ExtractorInvalidException;
use Kolgaev\Tube\Extractors\Extractor;
use Kolgaev\Tube\Models\Tube;
use Kolgaev\Tube\Resources\DownloadOutputResource;
use Kolgaev\Tube\Resources\MetaFormatResource;
use Kolgaev\Tube\Resources\MetaResource;

class TubeService
{
    /**
     * Клиент загрузчика видео
     * 
     * @var \Kolgaev\Tube\Clients\Client
     */
    protected $client;

    /**
     * Файловое хранилище
     * 
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $storage;

    /**
     * Процесс загрузки
     * 
     * @var \Kolgaev\Tube\Models\Tube
     */
    public static $tube;

    /**
     * Мета данные
     * 
     * @var null|\Kolgaev\Tube\Resources\MetaResource
     */
    public $meta;

    /**
     * Инициализация сервиса
     * 
     * @param string $url
     * @param null|\Illuminate\Console\OutputStyle $output
     */
    public function __construct(
        protected string $url,
        protected ?OutputStyle $output = null
    ) {

        $this->initStorage();
        $this->client = (new Client($this))();
        $this->initModel();
    }

    /**
     * Инициализация файлового хранилища
     * 
     * @return static
     */
    private function initStorage()
    {
        $this->storage = Storage::disk(
            $this->getDiskName()
        );

        $this->storage->makeDirectory('tube');

        return $this;
    }

    /**
     * Файловое хранилище
     * 
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public function storage()
    {
        return $this->storage;
    }

    /**
     * Наименование файлового хранилища
     * 
     * @return string
     */
    public static function getDiskName()
    {
        return config('tube.disk', config('filesystems.default', 'local')) ?: 'local';
    }

    /**
     * Модель процесса загрузки
     * 
     * @return \Kolgaev\Tube\Models\Tube
     * @throws \Kolgaev\Tube\Exceptions\ExtractorInvalidException
     */
    private function initModel()
    {
        $extractor = Extractor::parse($this->url);

        if (empty($extractor['name']) || empty($extractor['id'])) {
            throw new ExtractorInvalidException("Экстрактор не определен");
        }

        self::$tube = Tube::firstOrCreate([
            'extractor' => $extractor['name'],
            'display_id' => $extractor['id'],
        ], [
            'uuid' => Str::orderedUuid()->toString(),
            'status' => DownloadStatuses::init_download,
        ]);

        TubeInitDownloadEvent::dispatch(self::$tube);

        return self::$tube;
    }

    /**
     * Возвращает модель процесса загрузки
     * 
     * @return null|\Kolgaev\Tube\Models\Tube
     */
    public static function getTube()
    {
        return self::$tube;
    }

    /**
     * Запуск процесса
     * 
     * @return void
     */
    public function handle()
    {
        //
    }

    /**
     * Выводит ссылку на видео
     * 
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Формирует путь до каталога с видео
     * 
     * @param string[] $path
     * @return string
     */
    public function path(...$path)
    {
        return $this->storage->path(collect([
            'tube',
            ...$path
        ])->filter()->join(DIRECTORY_SEPARATOR));
    }

    /**
     * Получает мета данные видео
     * 
     * @return \Kolgaev\Tube\Resources\MetaResource
     */
    public function getMeta(): MetaResource
    {
        $meta = $this->client->getMeta();

        TubeReceivedMetaEvent::dispatch(self::$tube, $meta);

        return $this->meta = $meta;
    }

    /**
     * Процесс загрузки видео
     * 
     * @param string|int $video
     * @param null|string|int $audio
     * @param null|\Closure $callback
     * @return void
     */
    public function download($video, $audio = null, ?Closure $callback = null)
    {
        TubeDownloadFileEvent::dispatch(self::$tube, $video, $audio);

        $cb = function (DownloadOutputResource $output) use ($callback) {
            if (is_numeric($output->percent)) {
                TubeDownloadedFileProgressEvent::dispatch(self::$tube, $output);
            }
            if ($callback instanceof Closure) {
                $callback($output);
            }
        };

        try {
            $path = $this->client->download($video, $audio, $cb);
            TubeDownloadedFileEvent::dispatch(self::$tube, $path, $this->meta, $video, $audio);
        } catch (\Exception $e) {
            TubeDownloadFileErrorEvent::dispatch(self::$tube, $e->getMessage());
        }

        return $path ?? null;
    }
}
