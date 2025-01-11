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
        Schema::create('tubes', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index()->comment('Уникальный идентификатор видео');
            $table->string('extractor')->nullable()->comment('Источник видео');
            $table->string('display_id')->nullable()->comment('Идентификатор видео');
            $table->string('title')->nullable()->comment('Название видео');
            $table->text('description')->nullable()->comment('Описание видео');
            $table->bigInteger('duration')->nullable()->comment('Продолжительность видео в секундах');
            $table->unsignedInteger('status')->default(1)->comment('Статус загрузки');
            $table->string('thumbnail')->nullable()->comment('Ссылка или путь до файла обложки');
            $table->string('channel')->nullable()->comment('Наименование канала');
            $table->timestamp('publish_date')->nullable()->comment('Дата публикации');
            $table->jsonb('data')->nullable()->comment('Дополнительные данные');
            $table->string('disk')->nullable()->comment('Идентификатор хранилища');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tubes');
    }
};
