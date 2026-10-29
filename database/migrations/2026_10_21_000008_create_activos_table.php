<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->string('nombre', 150);
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('numero_serie', 100)->nullable()->unique();
            $table->enum('estado_uso', ['disponible', 'reservado', 'asignado', 'prestado', 'mantenimiento', 'baja', 'danado'])->default('disponible');
            $table->string('ubicacion', 150)->nullable();
            $table->date('fecha_adquisicion')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->string('imagen_referencial', 255)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado_uso');
            $table->index('categoria_id');
        });

        DB::statement("
            ALTER TABLE activos
            ADD CONSTRAINT chk_activos_baja
            CHECK (estado_uso <> 'baja' OR fecha_baja IS NOT NULL)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('activos');
    }
};
