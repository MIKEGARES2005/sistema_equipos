<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->restrictOnDelete();
            $table->foreignId('asignacion_id')->nullable()->constrained('asignaciones')->nullOnDelete();
            $table->string('codigo_regla', 50)->collation('utf8mb4_bin');
            $table->enum('severidad', ['info', 'advertencia', 'critica'])->collation('utf8mb4_bin');
            $table->text('mensaje')->nullable();
            $table->enum('estado', ['abierta', 'atendida', 'cerrada'])->default('abierta')->collation('utf8mb4_bin');
            $table->dateTime('abierta_en')->useCurrent();
            $table->foreignId('atendida_por')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('atendida_en')->nullable();
            $table->foreignId('cerrada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('cerrada_en')->nullable();
            $table->timestamps();

            $table->index(['equipo_id', 'estado'], 'alertas_equipo_estado_index');
            $table->index(['estado', 'abierta_en'], 'alertas_estado_abierta_index');
            $table->index('codigo_regla');
        });

        DB::statement("
            ALTER TABLE alertas
            ADD COLUMN alerta_abierta VARCHAR(80)
                GENERATED ALWAYS AS (
                    IF(estado <> 'cerrada', CONCAT(equipo_id, '-', codigo_regla), NULL)
                ) STORED
        ");
        DB::statement('ALTER TABLE alertas ADD UNIQUE alertas_abiertas_unique (alerta_abierta)');

        DB::statement("
            ALTER TABLE alertas
            ADD CONSTRAINT chk_alertas_flujo
            CHECK (
                (estado = 'abierta'
                    AND atendida_por IS NULL AND atendida_en IS NULL
                    AND cerrada_por IS NULL AND cerrada_en IS NULL)
                OR (estado = 'atendida'
                    AND atendida_por IS NOT NULL AND atendida_en IS NOT NULL
                    AND cerrada_por IS NULL AND cerrada_en IS NULL)
                OR (estado = 'cerrada'
                    AND cerrada_por IS NOT NULL AND cerrada_en IS NOT NULL)
            )
        ");

        DB::statement('
            ALTER TABLE alertas
            ADD CONSTRAINT chk_alertas_fechas
            CHECK (
                (atendida_en IS NULL OR atendida_en >= abierta_en)
                AND (cerrada_en IS NULL OR cerrada_en >= abierta_en)
                AND (cerrada_en IS NULL OR atendida_en IS NULL OR cerrada_en >= atendida_en)
            )
        ');

        DB::statement('
            ALTER TABLE alertas
            ADD CONSTRAINT chk_alertas_codigo_regla
            CHECK (codigo_regla = UPPER(codigo_regla))
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
