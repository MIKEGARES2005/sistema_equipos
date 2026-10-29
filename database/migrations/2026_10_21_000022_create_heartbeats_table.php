<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heartbeats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->restrictOnDelete();
            $table->decimal('cpu_uso', 5, 2)->nullable();
            $table->decimal('ram_uso', 5, 2)->nullable();
            $table->decimal('almacenamiento_uso', 5, 2)->nullable();
            $table->decimal('bateria', 5, 2)->nullable();
            $table->decimal('temperatura', 5, 2)->nullable();
            $table->boolean('cargando')->nullable();
            $table->string('ip', 45)->nullable();
            $table->json('metricas')->nullable();
            $table->dateTime('fecha_hora')->useCurrent();

            $table->index(['equipo_id', 'fecha_hora'], 'heartbeats_equipo_fecha_index');
            $table->index('fecha_hora');
        });

        DB::statement('
            ALTER TABLE heartbeats
            ADD CONSTRAINT chk_heartbeats_rangos
            CHECK (
                (cpu_uso IS NULL OR (cpu_uso >= 0 AND cpu_uso <= 100))
                AND (ram_uso IS NULL OR (ram_uso >= 0 AND ram_uso <= 100))
                AND (almacenamiento_uso IS NULL OR (almacenamiento_uso >= 0 AND almacenamiento_uso <= 100))
                AND (bateria IS NULL OR (bateria >= 0 AND bateria <= 100))
                AND (temperatura IS NULL OR (temperatura >= -50 AND temperatura <= 150))
            )
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('heartbeats');
    }
};
