<?php

namespace Kolgaev\Tube\Resources;

class DownloadOutputResource
{
    public function __construct(
        public string $output,
        public int $count,
        public ?float $percent,
        public ?string $size,
        public ?string $speed,
        public ?string $eta,
    ) {
        //
    }
}
