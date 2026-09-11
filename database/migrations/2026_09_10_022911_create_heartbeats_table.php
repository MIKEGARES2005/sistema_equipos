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
        Schema::create('heartbeats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('equipo_id');
            $table->decimal('cpu_uso', 5, 2)->nullable();
            $table->decimal('ram_uso', 5, 2)->nullable();
            $table->decimal('almacenamiento_uso', 5, 2)->nullable();
            $table->decimal('bateria', 5, 2)->nullable();
            $table->decimal('temperatura', 5, 2)->nullable();
            $table->dateTime('fecha_hora');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heartbeats');
    }
};