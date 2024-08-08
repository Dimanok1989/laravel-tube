<?php

namespace Kolgaev\Tube\Enums;

use Kolgaev\Tube\Services\Youtube;

enum Tubes: string
{
    case youtube = "youtube";

    /**
     * Ссылка на источник
     * 
     * @param string $tubeId
     * @return string
     */
    public function url(string $id)
    {
        return match ($this) {
            static::youtube => "https://www.youtube.com/watch?v=$id",
        };
    }

    public function client()
    {
        return match ($this) {
            static::youtube => Youtube::class,
        };
    }
}