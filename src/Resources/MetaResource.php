<?php

namespace Kolgaev\Tube\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\DelegatesToResource;

class MetaResource extends Resource
{
    use DelegatesToResource;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return is_array($this->resource)
            ? $this->resource
            : parent::toArray();
    }

    /**
     * Получает идентификатор потока с видео в HD
     * 
     * @param int $resolution
     * @return int|null
     */
    public function getItag(int $res = 1080, string $mime = "webm")
    {
        return collect($this->streams ?? $this->resource['streams'] ?? null)
            ->filter(fn ($item) => ($item->type ?? $item['type'] ?? null) == "video")
            ->filter(fn ($item) => strpos($item->res ?? $item['res'] ?? "", (string) $res) !== false)
            ->filter(fn ($item) => strpos($item->mime_type ?? $item['mime_type'] ?? "", $mime) !== false)
            ->first()['itag'] ?? null;
    }

    public function getTitle()
    {
        return $this->title ?? $this->resource['title'] ?? null;
    }

    public function getDescription()
    {
        return $this->description ?? $this->resource['description'] ?? null;
    }

    public function getLength()
    {
        return $this->length ?? $this->resource['length'] ?? null;
    }

    public function getPublishDate()
    {
        $date = $this->publish_date ?? $this->resource['publish_date'] ?? null;

        if ($date) {
            try {
                return Carbon::parse($date)->setTimezone(config('app.timezone', 'UTC'));
            } catch (\Exception) {
                //
            }
        }

        return null;
    }

    
}
