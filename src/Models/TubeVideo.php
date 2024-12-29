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
        'tube_download_id',
        'disk',
        'path',
        'filename',
        'extension',
        'mime_type',
        'format',
        'resolution',
        'filesize',
        'vcodec',
        'acodec',
    ];

    /**
     * Процесс, к которому относится файл
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function download()
    {
        return $this->belongsTo(Tube::class, 'tube_download_id');
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
     * Путь до файла
     * 
     * @return string
     */
    public function getPathAttribute()
    {
        return collect([
            $this->process->type->value ?? null,
            $this->process->tube_id ?? null,
            $this->basename,
        ])->filter()->join(DIRECTORY_SEPARATOR);
    }

    /**
     * Относительный путь до файла
     * 
     * @return string
     */
    public function getFullPathAttribute()
    {
        return Storage::disk('local')->path($this->path);
    }
}
