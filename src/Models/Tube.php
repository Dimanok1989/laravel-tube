<?php

namespace Kolgaev\Tube\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kolgaev\Tube\Enums\DownloadStatuses;
use Kolgaev\Tube\Enums\TubeTypes;
use Kolgaev\Tube\TubeService;

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
        'disk',
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
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::created(function (self $tube) {
            $tube->disk = $tube->disk ?: TubeService::getDiskName();
        });
    }

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

    /**
     * Ссылка на источник с видео
     * 
     * @return null|string
     */
    public function getUrlAttribute()
    {
        return optional(TubeTypes::tryFrom($this->extractor))->url($this->display_id);
    }
}
