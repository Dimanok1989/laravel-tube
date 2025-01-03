<?php

namespace Kolgaev\Tube\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class TubeVideo extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tube_id',
        'format_id',
        'disk',
        'path',
        'filename',
        'extension',
        'mime_type',
        'format',
        'resolution',
        'filesize',
        'fps',
        'vcodec',
        'acodec',
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

    /**
     * Имя файла с расширением
     * 
     * @return string
     */
    public function getBasenameAttribute()
    {
        return collect([
            $this->filename,
            $this->extension,
        ])->filter()->join(".");
    }

    /**
     * Относительный путь до файла
     * 
     * @return string
     */
    public function getFullPathAttribute()
    {
        return Storage::disk($this->disk)->path($this->path . "/" . $this->basename);
    }
}
