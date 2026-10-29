<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_id')->constrained('horarios')->restrictOnDelete();
            $table->foreignId('estudiante_solicitante_id')->constrained('estudiantes')->restrictOnDelete();
            $table->enum('modalidad', ['individual', 'grupal']);
            $table->string('codigo_grupo', 30)->nullable();
            $table->enum('tipo_equipo', ['cpu', 'tablet']);
            $table->date('fecha_uso');
            $table->foreignId('equipo_id')->nullable()->constrained('equipos')->restrictOnDelete();
            $table->enum('estado', ['pendiente', 'aprobada', 'parcialmente_aprobada', 'rechazada', 'cancelada'])->default('pendiente');
            $table->foreignId('atendido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_decision')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['horario_id', 'estado', 'created_at'], 'solicitudes_cola_index');
            $table->index(['estudiante_solicitante_id', 'estado']);
            $table->index('codigo_grupo');
            $table->index('fecha_uso');
        });

        DB::statement("
            ALTER TABLE solicitudes
            ADD CONSTRAINT chk_solicitudes_grupo
            CHECK (
                (modalidad = 'individual' AND codigo_grupo IS NULL)
                OR (modalidad = 'grupal' AND codigo_grupo IS NOT NULL)
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
