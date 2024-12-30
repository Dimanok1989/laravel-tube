<?php

namespace Kolgaev\Tube\Resources;

use Kolgaev\Tube\Support\Collection;

class MetaFormatResource extends Resource
{
    /**
     * Создание ресурса
     */
    public function __construct(
        public string $id,
        public string $ext,
        public string $resolution,
        public ?int $width,
        public ?int $height,
        public ?int $fps,
        public ?int $audio_channels,
        public ?int $filesize,
        public ?float $tbr,
        public ?string $vcodec,
        public ?float $vbr,
        public ?string $acodec,
        public ?float $abr,
        public ?int $asr,
        public ?string $format,
        public ?string $format_note
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
     * Преобразует размер файла в читаемый формат
     * 
     * @param int $precision
     * @return null|string
     */
    public function sizeFormat($precision = 2)
    {
        if (!$this->filesize) {
            return null;
        }

        $bytes = $this->filesize;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return trim(round($bytes / pow(1024, $factor), $precision) . " " . ($units[$factor] ?? ""));
    }

    /**
     * Выводит наименование формата
     * 
     * @param \Kolgaev\Tube\Resources\MetaFormatResource $item
     * @return string
     */
    public static function getFormatNote(MetaFormatResource $item)
    {
        $format = $item->format_note;

        if (empty($format) && !empty($item->get('height'))) {
            $format = (string)$item->height . "p" . (string)$item->fps;
        }

        return $format ?: $item->resolution;
    }
}
