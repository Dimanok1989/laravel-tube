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
        Schema::create('tube_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tube_id')
                ->nullable()
                ->comment('Идентификатор процесса')
                ->constrained()
                ->nullOnDelete();
            $table->unsignedInteger('status')->nullable()->comment('Статус загрузки');
            $table->text('message')->nullable()->comment('Текст сообщения');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tube_logs');
    }
};
