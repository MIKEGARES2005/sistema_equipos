<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_generados', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['inventario', 'equipos', 'estudiantes', 'docentes', 'semanal', 'mensual', 'anual', 'comprobante', 'otro'])->collation('utf8mb4_bin');
            $table->json('parametros')->nullable();
            $table->date('periodo_inicio')->nullable();
            $table->date('periodo_fin')->nullable();
            $table->foreignId('generado_por')->constrained('users')->restrictOnDelete();
            $table->string('ruta', 255);
            $table->dateTime('vence_en')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tipo', 'created_at'], 'reportes_tipo_fecha_index');
            $table->index(['generado_por', 'created_at'], 'reportes_usuario_fecha_index');
            $table->index('vence_en');
        });

        DB::statement('
            ALTER TABLE reportes_generados
            ADD CONSTRAINT chk_reportes_periodo
            CHECK (periodo_fin IS NULL OR periodo_inicio IS NULL OR periodo_fin >= periodo_inicio)
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_generados');
    }
};
