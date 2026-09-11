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
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('tipo_equipo', 30);
            $table->string('marca', 50);
            $table->string('modelo', 100);
            $table->string('numero_serie', 100)->nullable()->unique();
            $table->string('modo_uso', 20);
            $table->string('estado', 30);
            $table->string('mac', 17)->nullable();
            $table->dateTime('ultima_conexion')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};