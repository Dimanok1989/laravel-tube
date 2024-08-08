<?php

namespace Kolgaev\Tube\Services;

use Illuminate\Support\Facades\Process;

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
     * @param string $url
     * @return void
     */
    public function __construct(
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
}
