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
        Schema::create('tube_processes', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index();
            $table->string('type')->nullable()->comment('Тип источника видео');
            $table->string('tube_id')->nullable()->comment('Идентификатор видео');
            $table->unsignedInteger('status')->default(1)->comment('Статус загрузки');
            $table->string('title')->nullable()->comment('Название видео');
            $table->text('description')->nullable()->comment('Описание видео');
            $table->bigInteger('length')->nullable()->comment('Длина видео в секундах');
            $table->timestamp('publish_date')->nullable()->comment('Дата публикации');
            $table->foreignId('user_id')->nullable()->comment('Идентификатор пользователя, инициализировавший заагрузку');
            $table->text('callback_url')->nullable()->comment('Ссылка обратного уведомления');
            $table->jsonb('data')->nullable()->comment('Дополнительные данные');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tube_processes');
    }
};
