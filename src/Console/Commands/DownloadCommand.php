<?php

namespace Kolgaev\Tube\Console\Commands;

use Illuminate\Console\Command;

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
        dd($this->argument('url'));
    }
}
