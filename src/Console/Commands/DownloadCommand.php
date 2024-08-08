<?php

namespace Kolgaev\Tube\Console\Commands;

use Illuminate\Console\Command;
use Kolgaev\Tube\TubeService;

class DownloadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tube:download
                            {url : Ссылка на видео}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Скачивание видео';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $service = new TubeService($this->argument('url'));

        dd($service->getMeta());

        dd($this->argument('url'));
    }
}
