<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('sigla', 20)->unique();
            $table->unsignedTinyInteger('semestre');
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE materias ADD CONSTRAINT chk_materias_semestre CHECK (semestre BETWEEN 1 AND 4)');
    }

    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};
