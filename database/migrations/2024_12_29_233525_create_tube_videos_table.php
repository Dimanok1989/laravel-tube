<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tube_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tube_id')
                ->nullable()
                ->comment('Идентификатор процесса')
                ->constrained()
                ->nullOnDelete();
            $table->string('disk')->nullable()->comment('Наименование файлогового хранилища');
            $table->string('path')->nullable()->comment('Путь до каталога с файлом');
            $table->string('filename')->nullable()->comment('Имя файла');
            $table->string('extension')->nullable()->comment('Расширение файла');
            $table->string('mime_type')->nullable()->comment('MIME тип файла');
            $table->unsignedBigInteger('filesize')->nullable()->comment('Размер файла');
            $table->string('format')->nullable()->comment('Наименование качества видео');
            $table->string('resolution')->nullable()->comment('Разрешение видео');
            $table->string('vcodec')->nullable()->comment('Кодек видео');
            $table->string('acodec')->nullable()->comment('Кодек аудио');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tube_videos');
    }
};
