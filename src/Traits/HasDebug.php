<?php

namespace Kolgaev\Tube\Traits;

use Illuminate\Support\Facades\Log;

trait HasDebug
{
    /**
     * Отладочное логирование
     * 
     * @param array $data
     * @return void
     */
    public function toDubugLog(array $data = [])
    {
        if (!env('TUBE_DEBUG')) {
            return;
        }

        Log::debug('[Tube][' . static::class . ']', [
            ...get_object_vars($this),
            ...$data,
        ]);
    }
}
