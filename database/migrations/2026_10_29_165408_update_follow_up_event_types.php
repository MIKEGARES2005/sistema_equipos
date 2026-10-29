<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE historial_seguimiento_estudiantes MODIFY tipo_evento ENUM('congelamiento', 'retiro', 'reincorporacion', 'observacion', 'abandono') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE historial_seguimiento_estudiantes MODIFY tipo_evento ENUM('cambio_semestre', 'congelamiento', 'retiro', 'egreso', 'reincorporacion', 'observacion', 'permanencia') NOT NULL");
    }
};
