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
        Schema::table('estudiantes', function (Blueprint $table): void {
            $table->string('matricula', 30)->nullable()->change();
        });

        Schema::table('solicitudes_registro', function (Blueprint $table): void {
            $table->string('matricula', 30)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitudes_registro', function (Blueprint $table): void {
            $table->string('matricula', 30)->nullable(false)->change();
        });

        Schema::table('estudiantes', function (Blueprint $table): void {
            $table->string('matricula', 30)->nullable(false)->change();
        });
    }
};
