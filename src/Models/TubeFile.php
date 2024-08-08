<?php

namespace Kolgaev\Tube\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TubeFile extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tube_process_id',
        'type',
        'filename',
        'extension',
        'mime_type',
        'size',
        'quality',
    ];
}
