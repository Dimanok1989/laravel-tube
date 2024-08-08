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

    protected $meta;

    public function __construct(
        protected string $url
    ) {

        if (empty($this->phyton = exec('which python'))) {
            $this->phyton = exec('which python3');
        }
    }

    private function setMeta()
    {
        $result = Process::run([
            $this->phyton,
            realpath(__DIR__ . "/../../pytube/meta.py"),
            "\"{$this->url}\"",
        ]);

        dd($result->output());
    }

    public function getMeta()
    {
        return $this->meta ?: $this->setMeta();
    }
}
