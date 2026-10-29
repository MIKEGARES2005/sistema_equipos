<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->constrained('activos')->restrictOnDelete();
            $table->foreignId('asignacion_id')->nullable()->constrained('asignaciones')->nullOnDelete();
            $table->foreignId('alerta_id')->nullable()->unique()->constrained('alertas')->restrictOnDelete();
            $table->string('tipo', 40);
            $table->text('descripcion');
            $table->enum('prioridad', ['baja', 'media', 'alta', 'critica'])->default('media');
            $table->enum('estado', ['abierta', 'en_revision', 'resuelta', 'descartada'])->default('abierta');
            $table->enum('origen', ['manual', 'automatica', 'estudiante'])->default('manual');
            $table->foreignId('reportado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resuelto_por')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_reporte')->useCurrent();
            $table->dateTime('fecha_resolucion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['activo_id', 'estado']);
            $table->index(['estado', 'prioridad']);
            $table->index('asignacion_id');
            $table->index('tipo');
        });

        DB::statement("
            ALTER TABLE incidencias
            ADD COLUMN incidencia_abierta VARCHAR(80)
                GENERATED ALWAYS AS (
                    IF(estado IN ('abierta','en_revision'), CONCAT(activo_id, '-', tipo), NULL)
                ) STORED
        ");
        DB::statement('ALTER TABLE incidencias ADD UNIQUE incidencias_abiertas_unique (incidencia_abierta)');

        DB::statement("
            ALTER TABLE incidencias
            ADD CONSTRAINT chk_incidencias_cierre
            CHECK (
                estado IN ('abierta','en_revision')
                OR (resuelto_por IS NOT NULL AND fecha_resolucion IS NOT NULL)
            )
        ");

        DB::statement('
            ALTER TABLE incidencias
            ADD CONSTRAINT chk_incidencias_resolucion
            CHECK (fecha_resolucion IS NULL OR resuelto_por IS NOT NULL)
        ');

        DB::statement("
            ALTER TABLE incidencias
            ADD CONSTRAINT chk_incidencias_reportante
            CHECK (origen = 'automatica' OR reportado_por IS NOT NULL)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};
