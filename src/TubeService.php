<?php

namespace Kolgaev\Tube;

use Illuminate\Support\Facades\Storage;

class TubeService
{
    protected $storage;

    public function __construct()
    {
        $this->storage = Storage::disk('local');    
    }
}
