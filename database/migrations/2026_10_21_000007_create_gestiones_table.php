<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gestiones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 7)->unique();
            $table->smallInteger('anio');
            $table->enum('periodo', ['1', '2']);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('activa')->default(false);
            $table->timestamps();
        });

        DB::statement('
            ALTER TABLE gestiones
            ADD COLUMN activa_unica TINYINT
                GENERATED ALWAYS AS (IF(activa = 1, 1, NULL)) STORED
        ');
        DB::statement('ALTER TABLE gestiones ADD UNIQUE gestiones_activa_unica_unique (activa_unica)');

        DB::statement('ALTER TABLE gestiones ADD CONSTRAINT chk_gestiones_fechas CHECK (fecha_fin > fecha_inicio)');
    }

    public function down(): void
    {
        Schema::dropIfExists('gestiones');
    }
};
