<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alinea el esquema académico con el dominio que ya usa la aplicación
     * (semestres por nombre, gestiones por código) y agrega las notas de parciales.
     */
    public function up(): void
    {
        $this->dropCheck('estudiantes', 'chk_estudiantes_semestre');
        $this->dropCheck('materias', 'chk_materias_semestre');
        $this->dropCheck('horarios', 'chk_horarios_dia');

        Schema::table('estudiantes', function (Blueprint $table): void {
            $table->string('semestre', 20)->change();
        });

        Schema::table('materias', function (Blueprint $table): void {
            $table->string('semestre', 20)->change();
            $table->string('sigla', 20)->nullable()->change();
        });

        Schema::table('horarios', function (Blueprint $table): void {
            $table->string('dia_semana', 20)->change();
            $table->foreignId('gestion_id')->nullable()->change();
            $table->string('gestion', 7)->nullable()->after('materia_id');
        });

        Schema::table('inscripciones', function (Blueprint $table): void {
            $table->foreignId('gestion_id')->nullable()->change();
            $table->string('gestion', 7)->nullable()->after('materia_id');
        });

        DB::statement(<<<'SQL'
            UPDATE inscripciones i
            LEFT JOIN gestiones g ON g.id = i.gestion_id
            SET i.gestion = COALESCE(g.codigo, '2026-II')
            WHERE i.gestion IS NULL
        SQL);

        Schema::table('inscripciones', function (Blueprint $table): void {
            $table->string('gestion', 7)->nullable(false)->change();
            $table->enum('estado', ['Cursando', 'Aprobado', 'Reprobado', 'Inactivo'])
                ->default('Cursando')
                ->change();
            $table->enum('resultado_reportado', ['Aprobado', 'Reprobado'])->nullable()->change();
            $table->enum('resultado_final', ['Aprobado', 'Reprobado'])->nullable()->change();

            $table->decimal('parcial_1', 5, 2)->nullable()->after('fecha_inscripcion');
            $table->foreignId('parcial_1_por')->nullable()->after('parcial_1')->constrained('users')->nullOnDelete();
            $table->timestamp('parcial_1_en')->nullable()->after('parcial_1_por');

            $table->decimal('parcial_2', 5, 2)->nullable()->after('parcial_1_en');
            $table->foreignId('parcial_2_por')->nullable()->after('parcial_2')->constrained('users')->nullOnDelete();
            $table->timestamp('parcial_2_en')->nullable()->after('parcial_2_por');

            $table->decimal('nota_final', 5, 2)->nullable()->after('parcial_2_en');
            $table->timestamp('nota_final_en')->nullable()->after('nota_final');

            $table->unique(
                ['estudiante_id', 'materia_id', 'gestion'],
                'inscripciones_estudiante_materia_gestion_codigo_unique'
            );
        });

        if (! Schema::hasTable('configuracion_academica')) {
            Schema::create('configuracion_academica', function (Blueprint $table): void {
                $table->id();
                $table->string('gestion_vigente', 7)->default('2026-II');
                $table->timestamps();
            });
        }

        DB::table('configuracion_academica')->updateOrInsert(
            ['id' => 1],
            [
                'gestion_vigente' => '2026-II',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }

    public function down(): void
    {
        Schema::table('inscripciones', function (Blueprint $table): void {
            $table->dropUnique('inscripciones_estudiante_materia_gestion_codigo_unique');

            $table->dropConstrainedForeignId('parcial_1_por');
            $table->dropConstrainedForeignId('parcial_2_por');

            $table->dropColumn([
                'parcial_1',
                'parcial_1_en',
                'parcial_2',
                'parcial_2_en',
                'nota_final',
                'nota_final_en',
                'gestion',
            ]);

            $table->enum('estado', ['pendiente', 'confirmada', 'retirada'])
                ->default('pendiente')
                ->change();
            $table->enum('resultado_reportado', ['aprobado', 'reprobado'])->nullable()->change();
            $table->enum('resultado_final', ['aprobado', 'reprobado'])->nullable()->change();
        });

        Schema::table('horarios', function (Blueprint $table): void {
            $table->dropColumn('gestion');
            $table->unsignedTinyInteger('dia_semana')->change();
        });

        Schema::table('materias', function (Blueprint $table): void {
            $table->unsignedTinyInteger('semestre')->change();
            $table->string('sigla', 20)->nullable(false)->change();
        });

        Schema::table('estudiantes', function (Blueprint $table): void {
            $table->unsignedTinyInteger('semestre')->change();
        });

        DB::statement('ALTER TABLE estudiantes ADD CONSTRAINT chk_estudiantes_semestre CHECK (semestre BETWEEN 1 AND 4)');
        DB::statement('ALTER TABLE materias ADD CONSTRAINT chk_materias_semestre CHECK (semestre BETWEEN 1 AND 4)');
        DB::statement('ALTER TABLE horarios ADD CONSTRAINT chk_horarios_dia CHECK (dia_semana BETWEEN 1 AND 7)');

        Schema::dropIfExists('configuracion_academica');
    }

    private function dropCheck(string $table, string $constraint): void
    {
        $exists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->exists();

        if (! $exists) {
            return;
        }

        try {
            DB::statement("ALTER TABLE `{$table}` DROP CHECK `{$constraint}`");
        } catch (Throwable) {
            DB::statement("ALTER TABLE `{$table}` DROP CONSTRAINT `{$constraint}`");
        }
    }
};
