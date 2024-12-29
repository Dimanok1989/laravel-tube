<?php

namespace Kolgaev\Tube\Extractors;

use Kolgaev\Tube\Interfaces\ExtractorInterface;

class Extractor
{
    /**
     * Наименование экстрактора
     * 
     * @var null|string
     */
    protected $name;

    /**
     * Идентификатор видео
     * 
     * @var null|string
     */
    protected $id;

    /**
     * Инициализация экстрактора
     * 
     * @param string $url
     * @return void
     */
    public function __construct(protected string $url)
    {
        //
    }

    /**
     * Применяет наименование экстрактора
     * 
     * @return null|string
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Наименование экстрактора
     * 
     * @return null|string
     */
    public function name(): ?string
    {
        return $this->name;
    }

    /**
     * Применяет идентификатор видео
     * 
     * @return null|string
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Идентификатор видео
     * 
     * @return null|string
     */
    public function id(): ?string
    {
        return $this->id;
    }

    /**
     * Парсинг ссылки
     * 
     * @return null|array
     */
    public static function parse($url)
    {
        foreach (scandir(__DIR__) as $file) {

            if (in_array($file, [".", "..", "Extractor.php"])) {
                continue;
            }

            $class = __NAMESPACE__ . "\\" . pathinfo($file, PATHINFO_FILENAME);
            $extractor = new $class($url);

            if (! ($extractor instanceof ExtractorInterface)) {
                continue;
            }

            $extractor->handle();

            if ($extractor->name() && $extractor->id()) {
                return [
                    'name' => $extractor->name(),
                    'id' => $extractor->id(),
                ];
            }
        }

        return null;
    }
}
