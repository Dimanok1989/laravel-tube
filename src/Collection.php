<?php

namespace Kolgaev\Tube;

use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Traits\Macroable;

class Collection extends SupportCollection
{
    public function __get($name)
    {
        return $this->get($name);
    }
}
