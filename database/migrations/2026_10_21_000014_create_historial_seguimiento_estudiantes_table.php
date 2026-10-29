<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_seguimiento_estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->restrictOnDelete();
            $table->foreignId('gestion_id')->nullable()->constrained('gestiones')->nullOnDelete();
            $table->enum('tipo_evento', ['cambio_semestre', 'congelamiento', 'retiro', 'egreso', 'reincorporacion', 'observacion', 'permanencia']);
            $table->text('descripcion')->nullable();
            $table->date('fecha_evento');
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['estudiante_id', 'fecha_evento'], 'historial_estudiante_fecha_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_seguimiento_estudiantes');
    }
};
