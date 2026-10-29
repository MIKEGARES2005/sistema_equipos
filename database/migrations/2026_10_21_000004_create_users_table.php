<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->enum('tipo_usuario', ['administrador', 'docente', 'estudiante']);
            $table->unsignedBigInteger('docente_id')->nullable();
            $table->unsignedBigInteger('estudiante_id')->nullable();
            $table->unsignedTinyInteger('intentos_fallidos')->default(0);
            $table->timestamp('bloqueado_hasta')->nullable();
            $table->boolean('activo')->default(true);
            $table->boolean('debe_cambiar_password')->default(false);
            $table->timestamp('username_cambiado_en')->nullable();
            $table->unsignedTinyInteger('bloqueos_consecutivos')->default(0);
            $table->timestamp('ultimo_acceso')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->unique('docente_id');
            $table->unique('estudiante_id');
            $table->index('tipo_usuario');
            $table->index('activo');
            $table->index(['docente_id', 'estudiante_id'], 'users_perfil_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('docente_id')->references('id')->on('docentes')->restrictOnDelete();
            $table->foreign('estudiante_id')->references('id')->on('estudiantes')->restrictOnDelete();
        });

        DB::statement("
            ALTER TABLE users ADD CONSTRAINT chk_users_perfil CHECK (
                (tipo_usuario = 'estudiante' AND estudiante_id IS NOT NULL AND docente_id IS NULL)
                OR (tipo_usuario = 'docente' AND docente_id IS NOT NULL AND estudiante_id IS NULL)
                OR (tipo_usuario = 'administrador' AND estudiante_id IS NULL)
            )
        ");

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
