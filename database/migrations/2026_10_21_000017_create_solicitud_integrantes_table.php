<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitud_integrantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->restrictOnDelete();
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado', 'retirado'])->default('pendiente');
            $table->foreignId('atendido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_decision')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['solicitud_id', 'estudiante_id'], 'solicitud_integrantes_unico');
            $table->index('estudiante_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_integrantes');
    }
};
