<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complementos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activo_id')->unique()->constrained('activos')->restrictOnDelete();
            $table->string('tipo_complemento', 60);
            $table->enum('estado_fisico', ['bueno', 'regular', 'danado'])->default('bueno');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complementos');
    }
};
