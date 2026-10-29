<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->string('matricula', 30)->unique();
            $table->unsignedTinyInteger('semestre');
            $table->string('generacion_ingreso', 7)->nullable();
            $table->string('ci', 20)->unique();
            $table->string('ci_extension', 2)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('correo_institucional', 150)->nullable();
            $table->string('correo_personal', 150)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('foto_personal', 255)->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'egresado', 'retirado', 'congelado'])->default('activo');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('ALTER TABLE estudiantes ADD CONSTRAINT chk_estudiantes_semestre CHECK (semestre BETWEEN 1 AND 4)');
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
