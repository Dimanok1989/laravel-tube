<?php

namespace Kolgaev\Tube\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kolgaev\Tube\Enums\DownloadStatuses;

class Tube extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'extractor',
        'display_id',
        'title',
        'description',
        'duration',
        'status',
        'thumbnail',
        'channel',
        'publish_date',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => DownloadStatuses::class,
        'publish_date' => "datetime",
        'data' => "array",
    ];

    /**
     * Файлы, принадлежащие процессу загрущки
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function videos()
    {
        return $this->hasMany(TubeVideo::class);
    }

    /**
     * Логи процесса загрузки
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany(TubeLog::class);
    }
}
