<?php

namespace Kolgaev\Tube\Interfaces;

interface ExtractorInterface
{
    /**
     * Наименование экстрактора
     * 
     * @return null|string
     */
    public function name(): ?string;

    /**
     * Идентификатор видео
     * 
     * @return null|string
     */
    public function id(): ?string;

    /**
     * Парсер ссылки
     * 
     * @return void
     */
    public function handle(): void;
}