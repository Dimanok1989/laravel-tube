<?php

namespace Kolgaev\Tube\Clients;

use App\Support\Arr;
use Closure;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Kolgaev\Tube\Enums\MetaFormatKeys;
use Kolgaev\Tube\Enums\MetaKeys;
use Kolgaev\Tube\Exceptions\MetaFailedException;
use Kolgaev\Tube\Interfaces\ClientIterface;
use Kolgaev\Tube\Resources\DownloadOutputResource;
use Kolgaev\Tube\Resources\MetaFormatResource;
use Kolgaev\Tube\Resources\MetaResource;
use Kolgaev\Tube\TubeService;

class YtDlp implements ClientIterface
{
    /**
     * Ссылка на видео
     * 
     * @var string
     */
    protected $url;

    /**
     * Мета данные видео
     * 
     * @var null|\Kolgaev\Tube\Resources\MetaResource
     */
    protected $meta;

    /**
     * Опция для команды с использованием прокси
     * 
     * @var null|string
     */
    protected $proxy;

    /**
     * Инициализация загрузчика
     * 
     * @param \Kolgaev\Tube\TubeService $service
     * @return void
     */
    public function __construct(protected TubeService $service)
    {
        $proxy = config('tube.proxy');

        if (!empty($proxy['url'])) {
            $this->proxy = "--proxy http://";
            if ($proxy['user'] ?? null) {
                $this->proxy .= $proxy['user'];
                $this->proxy .= (($proxy['password'] ?? null) ? ":{$proxy['password']}" : "");
                $this->proxy .= "@";
            }
            $this->proxy .= $proxy['url'];
            $this->proxy .= ":{$proxy['port']}";
        }

        $this->url = $this->service->getUrl();
    }

    /**
     * Подготовка команды с yt-dlp
     * 
     * @return string
     */
    private function ytdlp()
    {
        return collect([
            'yt-dlp',
            $this->proxy
        ])->filter()->join(" ");
    }

    /**
     * Определение идентификатора и источника видео
     * 
     * @return null|string
     */
    public function getExtractor()
    {
        $command = collect([
            $this->ytdlp(),
            "--get-filename",
            " -o '%(extractor)s/%(display_id)s'",
            $this->url
        ])->filter()->join(" ");

        $process = Process::run($command);

        $path = collect(explode("\n", trim($process->output())))
            ->map(fn($item) => trim($item))
            ->reverse()
            ->first();

        $parts = explode("/", (string)$path);

        return [
            'extractor' => $parts[0] ?? null,
            'id' => $parts[1] ?? null,
        ];
    }

    /**
     * Получение мета данных
     * 
     * @return array
     */
    public function getMeta(): MetaResource
    {
        $command = $this->ytdlp() . ' --skip-download --dump-json ' . $this->url;

        $data = Cache::remember(
            $command,
            now()->addMonth(),
            function () use ($command) {

                $process = Process::run($command);

                if ($process->failed()) {
                    throw new MetaFailedException($process->errorOutput() ?: "Ошибка получения информации о видео");
                }

                return json_decode($process->output(), true);
            }
        );

        foreach (MetaKeys::cases() as $case) {
            $resource[$case->name] = Arr::get($data, $case->value);
        }

        $resource['formats'] = collect($data['formats'] ?? [])
            ->map(function ($item) {

                foreach (MetaFormatKeys::cases() as $case) {
                    $format[$case->name] = Arr::get($item, $case->value);
                }

                return new MetaFormatResource(...($format ?? []));
            })
            ->all();

        $this->meta = new MetaResource(...$resource);

        return $this->meta;
    }

    /**
     * Процесс загрузки видео
     * 
     * @param string|int $video
     * @param null|string|int $audio
     * @param null|\Closure $callback
     */
    public function download($video, $audio = null, ?Closure $callback = null)
    {
        $format = $video . (!empty($audio) ? "+{$audio}" : "");
        $path = $this->service->path($this->meta->extractor, $this->meta->id, "%(title)s.%(ext)s");

        $command = collect([
            $this->ytdlp(),
            "-f $format",
            "--socket-timeout 30",
            "-4",
            "--fragment-retries 150",
            "-o '$path'",
            "--write-thumbnail",
            $this->url
        ])->filter()->join(" ");

        $count = 0;

        $process = Process::timeout(3600)
            ->run($command, function (string $type, string $output) use ($callback, &$count) {

                if ($type != "out" || !($callback instanceof Closure)) {
                    return;
                }

                $pattern = '/(\d+\.\d+)% of\s+([\d.]+[G|M]iB)\s+at\s+([\d.]+[K|M]iB\/s)\s+ETA\s+([\d:]+)/';
                preg_match_all($pattern, Str::squish($output), $matches, PREG_SET_ORDER);

                $percent = !empty($matches[1]) ? (float) $matches[1] : null;

                try {
                    $callback(new DownloadOutputResource(
                        $output,
                        $count,
                        $percent,
                        $matches[2] ?? null,
                        $matches[3] ?? null,
                        $matches[4] ?? null,
                    ));
                } catch (Exception) {
                    //
                }
            });

        if ($process->failed()) {
            throw new Exception($process->errorOutput());
        }
    }
}
