<?php

namespace Kolgaev\Tube\Resources;

use App\Support\Arr;
use App\Support\Collection;
use Carbon\Carbon;
use Kolgaev\Tube\Enums\MetaFormatKeys;
use Kolgaev\Tube\Exceptions\MetaInvalidFormatItemException;

class MetaResource extends Resource
{
    /**
     * Идентификатор формата с лучшим качеством звука
     * 
     * @var string|null
     */
    public $audioId;

    /**
     * Дата публикации видео
     * 
     * @var \Carbon\Carbon|null
     */
    public $publishDate;

    /**
     * Инициализация ресурса
     * 
     * @param string $id
     * @param string $url
     * @param string $title
     * @param string $fulltitle
     * @param string $description
     * @param string $thumbnail
     * @param string $channel
     * @param string $channel_id
     * @param string $channel_url
     * @param string $uploader_id
     * @param string $uploader_url
     * @param string $upload_date
     * @param string $timestamp
     * @param string $extractor
     * @param string $duration
     * @param string $duration_string
     * @param array $tags
     * @param array $formats
     * @return void
     * 
     * @throws \Kolgaev\Tube\Exceptions\MetaInvalidFormatItemException
     */
    public function __construct(
        public string $id,
        public string $url,
        public string $title,
        public string $fulltitle,
        public string $description,
        public string $thumbnail,
        public string $channel,
        public string $channel_id,
        public string $channel_url,
        public string $uploader_id,
        public string $uploader_url,
        public string $upload_date,
        public string $timestamp,
        public string $extractor,
        public string $duration,
        public string $duration_string,
        public array $tags,
        public array $formats,
    ) {

        $formats = collect($formats)
            ->map(function ($item) {
                foreach (MetaFormatKeys::cases() as $case) {
                    $format[$case->name] = Arr::get($item, $case->value, Arr::get($item, $case->name));
                }
                return new MetaFormatResource(...($format ?? []));
            })
            ->all();

        foreach ($formats as $format) {
            if (!is_a($format, MetaFormatResource::class)) {
                throw new MetaInvalidFormatItemException("Элемент формата должен быть ресурсом \\" . MetaFormatResource::class);
            }
        }

        $this->formats = $formats;

        parent::__construct(
            new Collection(
                compact(
                    'id',
                    'url',
                    'title',
                    'fulltitle',
                    'description',
                    'thumbnail',
                    'channel',
                    'channel_id',
                    'channel_url',
                    'uploader_id',
                    'uploader_url',
                    'upload_date',
                    'timestamp',
                    'extractor',
                    'duration',
                    'duration_string',
                    'tags',
                    'formats',
                )
            )
        );

        $audio = collect($this->formats)
            ->filter(fn($item) => $item->acodec != "none")
            ->filter(fn($item) => $item->vcodec == "none")
            ->filter(fn($item) => $item->resolution == "audio only")
            ->sortBy('abr')
            ->reverse()
            ->first();

        if ($audio instanceof MetaFormatResource) {
            $this->audioId = $audio->id;
        }

        try {
            $this->publishDate = Carbon::createFromTimestampUTC($timestamp)
                ->setTimezone(config('app.timezone'));
        } catch (\Exception) {
            //
        }
    }

    /**
     * Форматы для загрузки в разном качестве
     * Среди всех форматов находит 1080p, 720p и 480p с файлами наименьшего размера
     * 
     * @return array
     */
    public function formats()
    {
        foreach ([480, 720, 1080] as $height) {
            $formats[$height] = $this->findFormatId($height);
        }

        return $formats ?? [];
    }

    /**
     * Ищет идентификатор формата для подходящего файла
     * 
     * @param null|int
     * @return null|int|string
     */
    private function findFormatId($height)
    {
        return collect($this->formats)
            ->filter(fn($item) => $item->vcodec != "none")
            ->filter(
                fn($item) => $item->height == $height
                    || strpos((string)$item->format_note, "{$height}p") !== false
            )
            ->filter(fn($item) => !empty($item->filesize))
            ->sortBy('filesize')
            ->first()
            ->id ?? null;
    }

    /**
     * Список форматов
     */

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return [
            ...$this->resource->toArray(),
            'formats' => collect($this->formats)
                ->map(fn($item) => $item->toArray())
                ->all()
        ];
    }
}
