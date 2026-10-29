<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('codigos_qr', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['clase', 'activo']);
            $table->char('token_hash', 64)->unique();
            $table->foreignId('activo_id')->nullable()->constrained('activos')->nullOnDelete();
            $table->foreignId('horario_id')->nullable()->constrained('horarios')->nullOnDelete();
            $table->date('fecha_uso')->nullable();
            $table->dateTime('expira_en')->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('creado_por')->constrained('users')->restrictOnDelete();
            $table->string('activo_qr_unico', 40)->nullable()->unique();
            $table->string('clase_qr_unica', 60)->nullable()->unique();
            $table->timestamps();

            $table->index(['tipo', 'activo']);
            $table->index(['activo_id', 'fecha_uso']);
            $table->index(['horario_id', 'fecha_uso']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codigos_qr');
    }
};
