<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo', 40);
            $table->string('titulo', 150);
            $table->text('mensaje');
            $table->string('url', 255)->nullable();
            $table->timestamp('leida_en')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'leida_en', 'created_at'], 'notificaciones_usuario_index');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
