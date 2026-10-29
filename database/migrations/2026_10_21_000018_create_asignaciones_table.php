<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->enum('tipo', ['asignado', 'prestado']);
            $table->enum('modalidad', ['individual', 'grupal'])->default('individual');
            $table->foreignId('equipo_id')->nullable()->constrained('equipos')->restrictOnDelete();
            $table->foreignId('complemento_id')->nullable()->constrained('complementos')->restrictOnDelete();
            $table->foreignId('estudiante_id')->nullable()->constrained('estudiantes')->restrictOnDelete();
            $table->foreignId('invitado_id')->nullable()->constrained('invitados')->restrictOnDelete();
            $table->foreignId('horario_id')->nullable()->constrained('horarios')->nullOnDelete();
            $table->foreignId('solicitud_id')->nullable()->constrained('solicitudes')->restrictOnDelete();
            $table->string('codigo_grupo', 30)->nullable();
            $table->enum('origen', ['panel_web', 'administracion', 'automatico', 'qr', 'api'])->default('panel_web');
            $table->text('motivo')->nullable();
            $table->foreignId('autorizado_por')->constrained('users')->restrictOnDelete();
            $table->foreignId('entregado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_entrega')->nullable();
            $table->dateTime('entrega_confirmada_at')->nullable();
            $table->dateTime('fecha_devolucion_prevista');
            $table->dateTime('fecha_devolucion_real')->nullable();
            $table->foreignId('devolucion_recibida_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('devolucion_aprobada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('estado', ['pendiente_entrega', 'activa', 'devolucion_pendiente', 'devuelta', 'vencida', 'con_incidencia', 'cancelada'])->default('pendiente_entrega');
            $table->enum('estado_fisico_entrega', ['bueno', 'regular', 'danado'])->default('bueno');
            $table->enum('estado_fisico_devolucion', ['bueno', 'regular', 'danado'])->nullable();
            $table->text('observaciones')->nullable();
            $table->text('motivo_devolucion')->nullable();
            $table->timestamps();

            $table->index(['equipo_id', 'estado']);
            $table->index(['complemento_id', 'estado']);
            $table->index(['estudiante_id', 'estado']);
            $table->index(['invitado_id', 'estado']);
            $table->index(['horario_id', 'estado']);
            $table->index('solicitud_id');
            $table->index('codigo_grupo');
            $table->index('fecha_devolucion_prevista');
        });

        DB::statement("
            ALTER TABLE asignaciones
            ADD COLUMN titular_activo VARCHAR(64)
                GENERATED ALWAYS AS (
                    IF(
                        estado IN ('pendiente_entrega','activa','devolucion_pendiente','vencida','con_incidencia'),
                        CONCAT(
                            COALESCE(equipo_id, 0), '-',
                            COALESCE(complemento_id, 0), '-',
                            COALESCE(estudiante_id, 0), '-',
                            COALESCE(invitado_id, 0)
                        ),
                        NULL
                    )
                ) STORED
        ");
        DB::statement('ALTER TABLE asignaciones ADD UNIQUE asignaciones_titular_activo_unique (titular_activo)');

        DB::statement("
            ALTER TABLE asignaciones
            ADD COLUMN horario_titular_activo VARCHAR(64)
                GENERATED ALWAYS AS (
                    IF(
                        estado IN ('pendiente_entrega','activa','devolucion_pendiente','vencida','con_incidencia')
                           AND horario_id IS NOT NULL AND estudiante_id IS NOT NULL,
                        CONCAT(horario_id, '-', estudiante_id),
                        NULL
                    )
                ) STORED
        ");
        DB::statement('ALTER TABLE asignaciones ADD UNIQUE asignaciones_horario_titular_activo_unique (horario_titular_activo)');

        DB::statement('
            ALTER TABLE asignaciones
            ADD CONSTRAINT chk_asignaciones_titular
            CHECK (
                (estudiante_id IS NOT NULL AND invitado_id IS NULL)
                OR (estudiante_id IS NULL AND invitado_id IS NOT NULL)
            )
        ');

        DB::statement('
            ALTER TABLE asignaciones
            ADD CONSTRAINT chk_asignaciones_recurso
            CHECK (
                (equipo_id IS NOT NULL AND complemento_id IS NULL)
                OR (equipo_id IS NULL AND complemento_id IS NOT NULL)
            )
        ');

        DB::statement("
            ALTER TABLE asignaciones
            ADD CONSTRAINT chk_asignaciones_devolucion_aprobada
            CHECK (
                estado <> 'devuelta'
                OR (devolucion_aprobada_por IS NOT NULL AND fecha_devolucion_real IS NOT NULL)
            )
        ");

        DB::statement("
            ALTER TABLE asignaciones
            ADD CONSTRAINT chk_asignaciones_grupo
            CHECK (
                (modalidad = 'individual' AND codigo_grupo IS NULL)
                OR (modalidad = 'grupal' AND codigo_grupo IS NOT NULL)
            )
        ");

        DB::statement("
            ALTER TABLE asignaciones
            ADD CONSTRAINT chk_asignaciones_entrega
            CHECK (estado IN ('pendiente_entrega', 'cancelada') OR fecha_entrega IS NOT NULL)
        ");

        DB::statement('
            ALTER TABLE asignaciones
            ADD CONSTRAINT chk_asignaciones_fechas
            CHECK (fecha_devolucion_real IS NULL OR fecha_entrega IS NULL OR fecha_devolucion_real >= fecha_entrega)
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};
