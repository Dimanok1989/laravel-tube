<?php

namespace Kolgaev\Tube;

use Illuminate\Support\Facades\Storage;
use Kolgaev\Tube\Enums\Tubes;

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
     * @var null|\Kolgaev\Tube\Enums\Tubes
     */
    protected $tube;

    /**
     * Идентификатор видео
     * 
     * @var null|string
     */
    protected $tubeId;

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
    }

    private function client()
    {
        $client = $this->tube->client();

        return new $client($this->url);
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

    public function getMeta()
    {
        return $this->client()->getMeta();
    }
}
