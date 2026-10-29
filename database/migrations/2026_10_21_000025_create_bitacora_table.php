<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('materia_id')->nullable()->constrained('materias')->nullOnDelete();
            $table->string('rol', 30)->nullable();
            $table->string('accion', 60);
            $table->string('entidad', 60)->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->json('antes')->nullable();
            $table->json('despues')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entidad', 'entidad_id'], 'bitacora_entidad_index');
            $table->index(['user_id', 'created_at'], 'bitacora_usuario_fecha_index');
            $table->index(['accion', 'created_at'], 'bitacora_accion_fecha_index');
            $table->index('created_at');
        });

        DB::unprepared("
            CREATE TRIGGER trg_bitacora_before_update
            BEFORE UPDATE ON bitacora
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'bitacora: las entradas son inalterables (RN-19/RNF-05)';
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_bitacora_before_delete
            BEFORE DELETE ON bitacora
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'bitacora: las entradas no se pueden eliminar (RN-19/RNF-05)';
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_bitacora_before_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_bitacora_before_delete');
        Schema::dropIfExists('bitacora');
    }
};
