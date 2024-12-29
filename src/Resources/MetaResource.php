<?php

namespace Kolgaev\Tube\Resources;

use App\Support\Collection;
use Carbon\Carbon;
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
        string $id,
        string $url,
        string $title,
        string $fulltitle,
        string $description,
        string $thumbnail,
        string $channel,
        string $channel_id,
        string $channel_url,
        string $uploader_id,
        string $uploader_url,
        string $upload_date,
        string $timestamp,
        string $extractor,
        string $duration,
        string $duration_string,
        array $tags,
        array $formats,
    ) {

        foreach ($formats as $format) {
            if (!is_a($format, MetaFormatResource::class)) {
                throw new MetaInvalidFormatItemException("Элемент формата должен быть ресурсом \\" . MetaFormatResource::class);
            }
        }

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

        $audio = collect($formats)
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
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray()
    {
        return [
            ...$this->resource->toArray(),
            'formats' => collect($this->resource->formats)
                ->map(fn($item) => $item->toArray())
                ->all()
        ];
    }
}
