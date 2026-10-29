<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docentes', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->string('ci', 20)->unique();
            $table->string('ci_extension', 2)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('correo_personal', 150)->nullable();
            $table->string('cargo', 100);
            $table->string('especialidad', 150)->nullable();
            $table->string('nivel_academico', 100)->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->enum('estado', ['activo', 'inactivo', 'licencia'])->default('activo');
            $table->string('foto_personal', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docentes');
    }
};
