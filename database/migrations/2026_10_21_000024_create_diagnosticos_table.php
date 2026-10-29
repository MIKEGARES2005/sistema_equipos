<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnosticos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->restrictOnDelete();
            $table->foreignId('heartbeat_id')->nullable()->constrained('heartbeats')->nullOnDelete();
            $table->string('tipo', 30);
            $table->enum('nivel', ['info', 'advertencia', 'critica'])->collation('utf8mb4_bin');
            $table->string('codigo_regla', 50)->collation('utf8mb4_bin');
            $table->text('descripcion')->nullable();
            $table->dateTime('fecha_hora')->useCurrent();
            $table->timestamps();

            $table->unique(['heartbeat_id', 'codigo_regla'], 'diagnosticos_heartbeat_regla_unico');
            $table->index(['equipo_id', 'fecha_hora'], 'diagnosticos_equipo_fecha_index');
            $table->index(['nivel', 'fecha_hora'], 'diagnosticos_nivel_fecha_index');
            $table->index('fecha_hora');
            $table->index('codigo_regla');
        });

        DB::statement('
            ALTER TABLE diagnosticos
            ADD CONSTRAINT chk_diagnosticos_codigo_regla
            CHECK (codigo_regla = UPPER(codigo_regla))
        ');

        DB::statement('
            ALTER TABLE diagnosticos
            ADD CONSTRAINT chk_diagnosticos_tipo
            CHECK (CHAR_LENGTH(tipo) > 0)
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnosticos');
    }
};
