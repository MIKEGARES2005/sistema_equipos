<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->unique()->constrained('activos')->restrictOnDelete();
            $table->string('identificador', 50)->unique();
            $table->enum('tipo_equipo', ['cpu', 'tablet']);
            $table->enum('modo_uso', ['individual', 'grupal'])->default('individual');
            $table->string('mac', 17)->nullable()->unique();
            $table->boolean('es_servidor')->default(false);
            $table->string('token_hash', 255)->nullable()->unique();
            $table->timestamp('token_creado_en')->nullable();
            $table->timestamp('token_expira_en')->nullable();
            $table->dateTime('ultima_conexion')->nullable();
            $table->string('ip_ultima', 45)->nullable();
            $table->timestamps();

            $table->index('tipo_equipo');
        });

        DB::statement('
            ALTER TABLE equipos
            ADD COLUMN servidor_unico TINYINT
                GENERATED ALWAYS AS (IF(es_servidor = 1, 1, NULL)) STORED
        ');
        DB::statement('ALTER TABLE equipos ADD UNIQUE equipos_servidor_unico_unique (servidor_unico)');
    }

    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
