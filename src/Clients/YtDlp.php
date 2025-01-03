<?php

namespace Kolgaev\Tube\Clients;

use App\Support\Arr;
use Closure;
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

        $this->meta = new MetaResource(...$resource);

        return $this->meta;
    }

    /**
     * Получает наименование формата видео
     * 
     * @param int|string $formatId
     * @return null|string
     */
    private function findVideoFormatName($formatId)
    {
        if (!$format = collect($this->meta->formats)->firstWhere('id', $formatId)) {
            return null;
        }

        return MetaFormatResource::getFormatNote($format);
    }

    /**
     * Получает имя файла по шаблону
     * 
     * @param string $format
     * @param string $path
     * @return string
     */
    private function getFilename($format, $path)
    {
        $command = collect([
            $this->ytdlp(),
            "-f $format",
            "--get-filename",
            "-o '$path'",
            $this->url
        ])->filter()->join(" ");

        return trim(Process::run($command)->output());
    }

    /**
     * Процесс загрузки видео
     * 
     * @param string|int $video
     * @param null|string|int $audio
     * @param null|\Closure $callback
     * @return array
     */
    public function download($video, $audio = null, ?Closure $callback = null)
    {
        $format = $video . (!empty($audio) ? "+{$audio}" : "");
        $formatNote = $this->findVideoFormatName($video) ?: "%(height)s";

        $dir = $path = $this->service->path($this->meta->extractor, $this->meta->id);

        $basename = Str::slug($this->meta->title) . ".[%(vcodec)s].{$formatNote}.%(ext)s";
        $path = "$dir/$basename";

        $thumbnail = $dir . "/thumbnail.%(ext)s";
        $thubnailExists = false;

        if (file_exists($dir)) {
            foreach (scandir($dir) as $file) {
                if (strpos($file, "thumbnail.") !== false) {
                    $thubnailExists = true;
                    break;
                }
            }
        }

        $command = collect([
            $this->ytdlp(),
            "-f $format",
            "--socket-timeout 30",
            "-4",
            "--fragment-retries 150",
            "-o '$path'",
            $thubnailExists ? null : "--write-thumbnail",
            $thubnailExists ? null : "-o 'thumbnail:$thumbnail'",
            $this->url
        ])->filter()->join(" ");

        $count = 0;
        $start = microtime(true);

        $process = Process::timeout(3600)
            ->run($command, function (string $type, string $output) use ($callback, &$count, &$start) {

                if ($type != "out" || !($callback instanceof Closure)) {
                    return;
                }

                $pattern = '/(\d+\.\d+)% of\s+([\d.]+[G|M]iB)\s+at\s+([\d.]+[K|M]iB\/s)\s+ETA\s+([\d:]+)/';
                preg_match_all($pattern, Str::squish($output), $matches, PREG_SET_ORDER);

                if (Str::position($output, "Destination:") !== false) {
                    $count++;
                }

                $percent = !empty($matches[0][1]) ? (float) $matches[0][1] : null;

                if ((microtime(true) - $start) < 1 && $percent < 100 && is_numeric($percent)) {
                    return;
                }

                $start = microtime(true);

                $callback(new DownloadOutputResource(
                    $output,
                    $count,
                    $percent,
                    $matches[0][2] ?? null,
                    $matches[0][3] ?? null,
                    $matches[0][4] ?? null,
                ));
            });

        if ($process->failed()) {
            throw new DownloadFileErrorException($process->errorOutput());
        }

        return [
            'format' => $formatNote,
            'path' => "tube/{$this->meta->extractor}/{$this->meta->id}/" . $this->getFilename($format, $basename),
        ];
    }
}
