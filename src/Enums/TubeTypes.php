<?php

namespace Kolgaev\Tube\Enums;

use Kolgaev\Tube\Extractors\Youtube;

enum TubeTypes: string
{
    case YouTube = "youtube";

    /**
     * Ссылка на видео
     * 
     * @param string $videoId
     * @return string|null
     */
    public function url($videoId)
    {
        $fn = match ($this) {
            static::YouTube => fn($id) => "https://youtube.com/watch?v=$id",
            default => fn() => null,
        };

        return $fn($videoId);
    }

    /**
     * Экстрактор видео
     * 
     * @return string|null
     */
    public function extracrot()
    {
        return match ($this) {
            static::YouTube => Youtube::class,
            default => null,
        };
    }
}
