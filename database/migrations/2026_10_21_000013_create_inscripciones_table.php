<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->restrictOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->restrictOnDelete();
            $table->foreignId('gestion_id')->constrained('gestiones')->restrictOnDelete();
            $table->enum('tipo', ['normal', 'arrastre']);
            $table->enum('estado', ['pendiente', 'confirmada', 'retirada'])->default('pendiente');

            $table->enum('resultado_reportado', ['aprobado', 'reprobado'])->nullable();
            $table->foreignId('resultado_reportado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_reporte')->nullable();

            $table->enum('resultado_final', ['aprobado', 'reprobado'])->nullable();
            $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_revision')->nullable();

            $table->date('fecha_inscripcion');
            $table->text('observacion_revision')->nullable();
            $table->timestamps();

            $table->unique(['estudiante_id', 'materia_id', 'gestion_id'], 'inscripciones_estudiante_materia_gestion_unique');
        });

        DB::statement('
            ALTER TABLE inscripciones
            ADD CONSTRAINT chk_inscripciones_reporte
            CHECK (
                resultado_reportado IS NULL
                OR (resultado_reportado_por IS NOT NULL AND fecha_reporte IS NOT NULL)
            )
        ');

        DB::statement('
            ALTER TABLE inscripciones
            ADD CONSTRAINT chk_inscripciones_revision
            CHECK (
                resultado_final IS NULL
                OR (resultado_reportado IS NOT NULL AND revisado_por IS NOT NULL AND fecha_revision IS NOT NULL)
            )
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};
