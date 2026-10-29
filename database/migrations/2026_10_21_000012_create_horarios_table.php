<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gestion_id')->constrained('gestiones')->restrictOnDelete();
            $table->foreignId('docente_id')->constrained('docentes')->restrictOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->restrictOnDelete();
            $table->unsignedTinyInteger('dia_semana');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('aula', 100)->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();

            $table->unique(['gestion_id', 'docente_id', 'dia_semana', 'hora_inicio'], 'horarios_docente_unico');
            $table->unique(['gestion_id', 'aula', 'dia_semana', 'hora_inicio'], 'horarios_aula_unico');
        });

        DB::statement('ALTER TABLE horarios ADD CONSTRAINT chk_horarios_dia CHECK (dia_semana BETWEEN 1 AND 7)');
        DB::statement('ALTER TABLE horarios ADD CONSTRAINT chk_horarios_horas CHECK (hora_fin > hora_inicio)');

        DB::unprepared("
            CREATE TRIGGER trg_horarios_before_insert
            BEFORE INSERT ON horarios
            FOR EACH ROW
            BEGIN
                IF NEW.estado = 'activo' THEN
                    IF EXISTS (
                        SELECT 1 FROM horarios
                        WHERE gestion_id = NEW.gestion_id
                          AND materia_id = NEW.materia_id
                          AND docente_id <> NEW.docente_id
                          AND estado = 'activo'
                    ) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'horarios: la materia ya tiene otro docente en esta gestion';
                    END IF;

                    IF EXISTS (
                        SELECT 1 FROM horarios
                        WHERE gestion_id = NEW.gestion_id
                          AND docente_id = NEW.docente_id
                          AND dia_semana = NEW.dia_semana
                          AND estado = 'activo'
                          AND NEW.hora_inicio < hora_fin
                          AND NEW.hora_fin > hora_inicio
                    ) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'horarios: el docente tiene un horario solapado';
                    END IF;

                    IF NEW.aula IS NOT NULL AND EXISTS (
                        SELECT 1 FROM horarios
                        WHERE gestion_id = NEW.gestion_id
                          AND aula = NEW.aula
                          AND dia_semana = NEW.dia_semana
                          AND estado = 'activo'
                          AND NEW.hora_inicio < hora_fin
                          AND NEW.hora_fin > hora_inicio
                    ) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'horarios: el aula tiene un horario solapado';
                    END IF;
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER trg_horarios_before_update
            BEFORE UPDATE ON horarios
            FOR EACH ROW
            BEGIN
                IF NEW.estado = 'activo' THEN
                    IF EXISTS (
                        SELECT 1 FROM horarios
                        WHERE gestion_id = NEW.gestion_id
                          AND materia_id = NEW.materia_id
                          AND docente_id <> NEW.docente_id
                          AND estado = 'activo'
                          AND id <> NEW.id
                    ) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'horarios: la materia ya tiene otro docente en esta gestion';
                    END IF;

                    IF EXISTS (
                        SELECT 1 FROM horarios
                        WHERE gestion_id = NEW.gestion_id
                          AND docente_id = NEW.docente_id
                          AND dia_semana = NEW.dia_semana
                          AND estado = 'activo'
                          AND id <> NEW.id
                          AND NEW.hora_inicio < hora_fin
                          AND NEW.hora_fin > hora_inicio
                    ) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'horarios: el docente tiene un horario solapado';
                    END IF;

                    IF NEW.aula IS NOT NULL AND EXISTS (
                        SELECT 1 FROM horarios
                        WHERE gestion_id = NEW.gestion_id
                          AND aula = NEW.aula
                          AND dia_semana = NEW.dia_semana
                          AND estado = 'activo'
                          AND id <> NEW.id
                          AND NEW.hora_inicio < hora_fin
                          AND NEW.hora_fin > hora_inicio
                    ) THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'horarios: el aula tiene un horario solapado';
                    END IF;
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_horarios_before_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_horarios_before_update');
        Schema::dropIfExists('horarios');
    }
};
