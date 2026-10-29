<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignacion_checklist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_id')->constrained('asignaciones')->restrictOnDelete();
            $table->foreignId('complemento_id')->constrained('complementos')->restrictOnDelete();
            $table->enum('momento', ['entrega', 'devolucion']);
            $table->boolean('presente')->nullable();
            $table->enum('estado', ['bueno', 'regular', 'danado'])->nullable();
            $table->text('observacion')->nullable();
            $table->foreignId('verificado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verificado_at')->nullable();
            $table->timestamps();

            $table->unique(['asignacion_id', 'complemento_id', 'momento'], 'asignacion_checklist_unico');
            $table->index(['asignacion_id', 'momento']);
        });

        DB::statement('
            ALTER TABLE asignacion_checklist
            ADD CONSTRAINT chk_asignacion_checklist_presencia
            CHECK (
                presente IS NULL
                OR (presente = 1 AND estado IS NOT NULL)
                OR (presente = 0 AND estado IS NULL)
            )
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('asignacion_checklist');
    }
};
