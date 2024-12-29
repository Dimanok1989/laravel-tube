<?php

namespace Kolgaev\Tube;

use Closure;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kolgaev\Tube\Clients\Client;
use Kolgaev\Tube\Enums\DownloadStatuses;
use Kolgaev\Tube\Exceptions\ExtractorInvalidException;
use Kolgaev\Tube\Extractors\Extractor;
use Kolgaev\Tube\Models\Tube;
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
     * @var null|\Kolgaev\Tube\Models\Tube
     */
    public static $tube;

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
     * Наименование файлового хранилища
     * 
     * @return string
     */
    public static function getDiskName()
    {
        return config('tube.disk', config('filesystems.default', 'local'));
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

        return self::$tube = Tube::firstOrCreate([
            'extractor' => $extractor['name'],
            'display_id' => $extractor['id'],
        ], [
            'uuid' => Str::orderedUuid()->toString(),
            'status' => DownloadStatuses::start_download,
        ]);
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

        self::$tube->update([
            'title' => $meta->fulltitle,
            'description' => $meta->description,
            'duration' => $meta->duration,
            'status' => DownloadStatuses::gets_metadata,
            'thumbnail' => $meta->thumbnail,
            'channel' => $meta->channel,
            'publish_date' => $meta->publishDate,
            'data' => $meta->toArray(),
        ]);

        return $meta;
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
        $this->client->download($video, $audio, $callback);
    }
}
