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
        Schema::create('tube_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tube_process_id')
                ->nullable()
                ->comment('Идентификатор процесса')
                ->nullOnDelete();
            $table->string('type')->nullable()->comment('Тип файла');
            $table->string('filename')->nullable()->comment('Имя файла');
            $table->string('extension')->nullable()->comment('Расширение файла');
            $table->string('mime_type')->nullable()->comment('MIME тип файла');
            $table->unsignedBigInteger('size')->nullable()->comment('Размер файла');
            $table->string('quality')->nullable()->comment('Качество файла');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tube_files');
    }
};
