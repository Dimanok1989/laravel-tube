<?php

namespace Kolgaev\Tube\Resources;

use Illuminate\Http\Resources\DelegatesToResource;
use Kolgaev\Tube\Collection;

class StreamResource extends Resource
{
    use DelegatesToResource;

    public $extension;

    public function __construct($resource)
    {
        parent::__construct(new Collection($resource));

        $this->extension = $this->extension();
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

    public function extension()
    {
        return explode("/", $this->mime_type ?: "")[1] ?? null;
    }
}
