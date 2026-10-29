<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_registro', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->string('ci', 20);
            $table->string('ci_extension', 2)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('matricula', 30);
            $table->unsignedTinyInteger('semestre')->default(1);
            $table->string('generacion_ingreso', 7)->nullable();
            $table->string('correo_institucional', 150)->nullable();
            $table->string('correo_personal', 150)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->text('motivo_rechazo')->nullable();
            $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_revision')->nullable();
            $table->foreignId('usuario_creado_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ci_pendiente', 30)->nullable()->unique();
            $table->string('matricula_pendiente', 40)->nullable()->unique();
            $table->timestamps();

            $table->index(['estado', 'created_at'], 'solicitudes_registro_estado_index');
            $table->index('ci');
            $table->index('matricula');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_registro');
    }
};
