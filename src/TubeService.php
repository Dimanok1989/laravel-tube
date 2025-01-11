<?php

namespace Kolgaev\Tube;

use Closure;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kolgaev\Tube\Clients\Client;
use Kolgaev\Tube\Enums\DownloadStatuses;
use Kolgaev\Tube\Events\TubeDownloadDoneEvent;
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
     * Ссылка на видео
     * 
     * @var string
     */
    protected $url;

    /**
     * Мета данные
     * 
     * @var null|\Kolgaev\Tube\Resources\MetaResource
     */
    public $meta;

    /**
     * Инициализация сервиса
     * 
     * @param \Kolgaev\Tube\Models\Tube|string $url
     * @param null|\Illuminate\Console\OutputStyle $output
     */
    public function __construct(
        Tube|string $tube,
        protected ?OutputStyle $output = null
    ) {

        if (is_string($tube) && filter_var($tube, FILTER_VALIDATE_URL)) {
            $this->url = $tube;
        } else if ($tube instanceof Tube) {
            $this->url = $tube->url;
        }

        $this->initStorage();
        $this->client = (new Client($this))();
        $this->initModel($tube);
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
     * @param \Kolgaev\Tube\Models\Tube|string $tube
     * @return \Kolgaev\Tube\Models\Tube
     * 
     * @throws \Kolgaev\Tube\Exceptions\ExtractorInvalidException
     */
    private function initModel(Tube|string $tube)
    {
        if ($tube instanceof Tube) {
            return self::$tube = $tube;
        }

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
            'disk' => $this->getDiskName(),
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
        $meta = $this->getMeta();

        foreach ($meta->formats() as $video) {

            if (self::$tube->videos->firstWhere('format_id', $video)) {
                continue;
            }

            $this->download($video, $meta->audioId, function ($output) {
                if (env('TUBE_DEBUG')) {
                    $this->log()->debug($output);
                }
            });
        }

        TubeDownloadDoneEvent::dispatch(self::$tube->refresh());
    }

    /**
     * Выводит ссылку на видео
     * 
     * @return string
     */
    public function getUrl()
    {
        return $this->url ?? self::$tube->url ?? null;
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

        $cb = function (DownloadOutputResource $output) use ($callback, $video, $audio) {
            if (is_numeric($output->percent)) {
                TubeDownloadedFileProgressEvent::dispatch(self::$tube, $output, $this->meta, $video, $audio);
            }
            if ($callback instanceof Closure) {
                $callback($output);
            }
        };

        for ($i = 0; $i < 3; $i++) {
            try {
                $path = $this->client->download($video, $audio, $cb);
                TubeDownloadedFileEvent::dispatch(self::$tube, $path, $this->meta, $video, $audio);
                break;
            } catch (\Exception $e) {
                TubeDownloadFileErrorEvent::dispatch(self::$tube, $e->getMessage(), $video, $audio);
            }
        }

        return $path ?? null;
    }

    /**
     * Канал логирования
     * 
     * @return \Psr\Log\LoggerInterface
     */
    public function log()
    {
        return Log::build([
            'driver' => 'daily',
            'path' => storage_path('logs/kolgaev/tube.log'),
            'days' => 14,
        ]);
    }
}
