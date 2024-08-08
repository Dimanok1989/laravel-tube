<?php

namespace Kolgaev\Tube\Resources;

use ArrayAccess;
use Illuminate\Http\Resources\DelegatesToResource;

class Resource implements ArrayAccess
{
    use DelegatesToResource;

    /**
     * The resource instance.
     *
     * @var mixed
     */
    public $resource;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }
}
