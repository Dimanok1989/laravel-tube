<?php

namespace Kolgaev\Tube\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TubeProcess extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'type',
        'tube_id',
        'status',
        'title',
        'description',
        'length',
        'publish_date',
        'user_id',
        'callback_url',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'uuid' => 'uuid',
        'publish_date' => "datetime",
        'data' => "array",
    ];

    /**
     * Файлы, принадлежащие процессу загрущки
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files()
    {
        return $this->hasMany(TubeFile::class);
    }
}
