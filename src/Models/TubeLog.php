<?php

namespace Kolgaev\Tube\Models;

use Illuminate\Database\Eloquent\Model;
use Kolgaev\Tube\Enums\DownloadStatuses;

class TubeLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tube_id',
        'status',
        'message',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => DownloadStatuses::class,
        'data' => "array",
    ];

    /**
     * Процесс, к которому относится файл
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tube()
    {
        return $this->belongsTo(Tube::class, 'tube_id');
    }
}
