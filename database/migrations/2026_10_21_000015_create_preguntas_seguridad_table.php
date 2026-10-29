<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preguntas_seguridad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('pregunta', 255);
            $table->string('respuesta_hash', 255);
            $table->timestamps();

            $table->unique(['user_id', 'pregunta'], 'preguntas_seguridad_user_pregunta_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntas_seguridad');
    }
};
