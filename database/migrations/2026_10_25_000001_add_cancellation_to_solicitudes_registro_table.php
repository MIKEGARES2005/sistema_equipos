<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_registro', function (Blueprint $table): void {
            $table->string('token_cancelacion', 64)->nullable()->unique()->after('matricula_pendiente');
        });

        Schema::table('solicitudes_registro', function (Blueprint $table): void {
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada', 'cancelada'])->default('pendiente')->change();
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_registro', function (Blueprint $table): void {
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente')->change();
            $table->dropUnique(['token_cancelacion']);
            $table->dropColumn('token_cancelacion');
        });
    }
};
