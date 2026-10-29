<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipo_complemento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->constrained('equipos')->restrictOnDelete();
            $table->foreignId('complemento_id')->constrained('complementos')->restrictOnDelete();
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['equipo_id', 'fecha_fin']);
            $table->index(['complemento_id', 'fecha_fin']);
        });

        DB::statement('
            ALTER TABLE equipo_complemento
            ADD COLUMN vinculo_vigente TINYINT
                GENERATED ALWAYS AS (IF(fecha_fin IS NULL, 1, NULL)) STORED
        ');
        DB::statement('ALTER TABLE equipo_complemento ADD UNIQUE equipo_complemento_vigente_unique (complemento_id, vinculo_vigente)');

        DB::statement('ALTER TABLE equipo_complemento ADD CONSTRAINT chk_equipo_complemento_fechas CHECK (fecha_fin IS NULL OR fecha_fin > fecha_inicio)');
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_complemento');
    }
};
