<?php

namespace Kolgaev\Tube;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kolgaev\Tube\Enums\Tubes;
use Kolgaev\Tube\Models\TubeProcess;
use Kolgaev\Tube\Resources\MetaResource;

class TubeService
{
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
    protected $process;

    protected $client;

    /**
     * Инициализация сервиса
     * 
     * @param string $url
     * @return void
     */
    public function __construct(
        protected string $url
    ) {

        $this->storage = Storage::disk(config('filesystems.default'));

        $this->parseUrl();

        $this->setProcess();
    }

    /**
     * Клиент видеохостинга
     * 
     * @return \Kolgaev\Tube\Enums\Tubes
     */
    private function client()
    {
        if ($this->client) {
            return $this->client;
        }

        $client = $this->tube->client();

        return $this->client = new $client($this->process, $this->url);
    }

    /**
     * Создает процесс загрузки в базе данных
     * 
     * @return void
     */
    protected function setProcess()
    {
        $process = TubeProcess::firstOrCreate([
            'type' => $this->tube->name,
            'tube_id' => $this->tubeId,
            'status' => TubeProcess::STATUS_CREATED,
        ], [
            'uuid' => Str::orderedUuid()->toString(),
        ]);

        $process->title = $this->meta()->getTitle();
        $process->description = $this->meta()->getDescription();
        $process->length = $this->meta()->getLength();
        $process->publish_date = $this->meta()->getPublishDate();
        $process->data = $this->meta()->toArray();
        $process->user_id = auth()->id();

        $process->save();

        $this->process = $process;
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
     * Выводит мета данные видео
     * 
     * @return \Kolgaev\Tube\Resources\MetaResource
     */
    public function meta()
    {
        return $this->meta ?: new MetaResource(
            $this->client()->getMeta()
        );
    }

    /**
     * Начало скачивание файлов
     * 
     * @param int $itag
     */
    public function download(int $itag)
    {
        $filename = Str::slug($this->process->title);

        $dir = collect([
            $this->tube->name,
            $this->process->uuid
        ])->join(DIRECTORY_SEPARATOR);

        $this->storage->makeDirectory($dir);
        $path = $this->storage->path($dir);

        $this->client()->download($path, $filename, $itag);

        dd($this->process->toArray());

        return $this->client()->download($itag);
    }

    private function downloadVideo() {}
}
