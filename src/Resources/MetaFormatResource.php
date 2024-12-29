<?php

namespace Kolgaev\Tube\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\DelegatesToResource;
use Kolgaev\Tube\Support\Collection;

class MetaFormatResource extends Resource
{
    /**
     * Создание ресурса
     */
    public function __construct(
        string $id,
        string $ext,
        string $resolution,
        ?int $width,
        ?int $height,
        ?int $fps,
        ?int $audio_channels,
        ?int $filesize,
        ?float $tbr,
        ?string $vcodec,
        ?float $vbr,
        ?string $acodec,
        ?float $abr,
        ?int $asr,
        ?string $format,
        ?string $format_note
    ) {
        parent::__construct(
            new Collection(
                compact(
                    'id',
                    'ext',
                    'resolution',
                    'width',
                    'height',
                    'fps',
                    'audio_channels',
                    'filesize',
                    'tbr',
                    'vcodec',
                    'vbr',
                    'acodec',
                    'abr',
                    'asr',
                    'format',
                    'format_note',
                )
            )
        );
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return parent::toArray();
    }
}
