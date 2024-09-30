<?php

namespace Kolgaev\Tube\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Процесс, к которому относится файл
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function process()
    {
        return $this->belongsTo(TubeProcess::class, 'tube_process_id');
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
