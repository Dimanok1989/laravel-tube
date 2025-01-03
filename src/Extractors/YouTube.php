<?php

namespace Kolgaev\Tube\Extractors;

use Kolgaev\Tube\Interfaces\ExtractorInterface;

class YouTube extends Extractor implements ExtractorInterface
{
    const EXTRACTOR = 'youtube';

    /**
     * Парсер ссылки
     * 
     * @return void
     */
    public function handle(): void
    {
        $this->setName(self::EXTRACTOR);

        $parseUrl = parse_url($this->url);
        $host = $parseUrl['host'] ?? "";

        if ($host == "youtu.be") {
            $this->setId(pathinfo($parseUrl['path'] ?? "", PATHINFO_BASENAME));
        } else if (mb_strpos($host, "youtube.com") !== false) {
            parse_str($parseUrl['query'] ?? "", $query);
            $this->setId($query['v'] ?? null);
        }
    }
}