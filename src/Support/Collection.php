<?php

namespace Kolgaev\Tube\Support;

use App\Support\Collection as BaseCollection;

class Collection extends BaseCollection
{
    /**
     * Выводит значение атрибута коллекции
     * 
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }
}
