<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heartbeats_resumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->cascadeOnDelete();
            $table->dateTime('hora');
            $table->unsignedInteger('muestras')->default(0);
            $table->decimal('bateria_min', 5, 2)->nullable();
            $table->decimal('bateria_prom', 5, 2)->nullable();
            $table->decimal('cpu_prom', 5, 2)->nullable();
            $table->decimal('ram_prom', 5, 2)->nullable();
            $table->decimal('almacenamiento_max', 5, 2)->nullable();
            $table->decimal('temperatura_max', 5, 2)->nullable();
            $table->unsignedInteger('segundos_offline')->default(0);
            $table->timestamps();

            $table->unique(['equipo_id', 'hora'], 'heartbeats_resumen_unico');
            $table->index('hora');
        });

        DB::statement('
            ALTER TABLE heartbeats_resumen
            ADD CONSTRAINT chk_heartbeats_resumen_hora
            CHECK (MINUTE(hora) = 0 AND SECOND(hora) = 0)
        ');

        DB::statement('
            ALTER TABLE heartbeats_resumen
            ADD CONSTRAINT chk_heartbeats_resumen_coherencia
            CHECK (
                (muestras = 0 AND segundos_offline = 3600)
                OR (muestras > 0 AND segundos_offline <= 3600)
            )
        ');

        DB::statement('
            ALTER TABLE heartbeats_resumen
            ADD CONSTRAINT chk_heartbeats_resumen_muestras
            CHECK (muestras <= 3600)
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('heartbeats_resumen');
    }
};
