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
        // Relación de usuarios con docentes y estudiantes
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('docente_id')
                ->references('id')
                ->on('docentes')
                ->nullOnDelete();
            $table->foreign('estudiante_id')
                ->references('id')
                ->on('estudiantes')
                ->nullOnDelete();
        });
        // Relación de materias con docentes
        Schema::table('materias', function (Blueprint $table) {
            $table->foreign('docente_id')
                ->references('id')
                ->on('docentes')
                ->restrictOnDelete();
        });
        // Relaciones de asignaciones
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->foreign('equipo_id')
                ->references('id')
                ->on('equipos')
                ->restrictOnDelete();
            $table->foreign('estudiante_id')
                ->references('id')
                ->on('estudiantes')
                ->restrictOnDelete();
            $table->foreign('materia_id')
                ->references('id')
                ->on('materias')
                ->nullOnDelete();
        });
        // Relaciones de incidencias
        Schema::table('incidencias', function (Blueprint $table) {
            $table->foreign('equipo_id')
                ->references('id')
                ->on('equipos')
                ->restrictOnDelete();
            $table->foreign('asignacion_id')
                ->references('id')
                ->on('asignaciones')
                ->nullOnDelete();
        });
        // Relación de accesorios con equipos
        Schema::table('accesorios', function (Blueprint $table) {
            $table->foreign('equipo_id')
                ->references('id')
                ->on('equipos')
                ->restrictOnDelete();
        });
        // Relación de heartbeats con equipos
        Schema::table('heartbeats', function (Blueprint $table) {
            $table->foreign('equipo_id')
                ->references('id')
                ->on('equipos')
                ->restrictOnDelete();
        });
        // Relaciones de diagnósticos
        Schema::table('diagnosticos', function (Blueprint $table) {
            $table->foreign('equipo_id')
                ->references('id')
                ->on('equipos')
                ->restrictOnDelete();
            $table->foreign('heartbeat_id')
                ->references('id')
                ->on('heartbeats')
                ->restrictOnDelete();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diagnosticos', function (Blueprint $table) {
            $table->dropForeign(['heartbeat_id']);
            $table->dropForeign(['equipo_id']);
        });
        Schema::table('heartbeats', function (Blueprint $table) {
            $table->dropForeign(['equipo_id']);
        });
        Schema::table('accesorios', function (Blueprint $table) {
            $table->dropForeign(['equipo_id']);
        });
        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropForeign(['asignacion_id']);
            $table->dropForeign(['equipo_id']);
        });
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->dropForeign(['materia_id']);
            $table->dropForeign(['estudiante_id']);
            $table->dropForeign(['equipo_id']);
        });
        Schema::table('materias', function (Blueprint $table) {
            $table->dropForeign(['docente_id']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['estudiante_id']);
            $table->dropForeign(['docente_id']);
        });
    }
};