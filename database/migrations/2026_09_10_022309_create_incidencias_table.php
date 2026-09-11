<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipo_id');
            $table->unsignedBigInteger('asignacion_id')->nullable();
            $table->string('tipo', 30);
            $table->text('descripcion');
            $table->string('prioridad', 20);
            $table->string('estado', 20);
            $table->dateTime('fecha_reporte');
            $table->dateTime('fecha_resolucion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};