<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('silabo', function (Blueprint $table) {
            // Quitar columnas del esquema anterior si existen
            if (Schema::hasColumn('silabo', 'CursoId')) {
                $table->dropColumn('CursoId');
            }
            if (Schema::hasColumn('silabo', 'Ciclo')) {
                $table->dropColumn('Ciclo');
            }

            // Agregar columnas nuevas si no existen
            if (!Schema::hasColumn('silabo', 'CodigoCurso')) {
                $table->string('CodigoCurso', 20)->nullable()->after('IdSilabo');
            }
            if (!Schema::hasColumn('silabo', 'NombreCurso')) {
                $table->string('NombreCurso', 255)->nullable()->after('CodigoCurso');
            }
            if (!Schema::hasColumn('silabo', 'CicloNumero')) {
                $table->tinyInteger('CicloNumero')->nullable()->after('NombreCurso');
            }
            if (!Schema::hasColumn('silabo', 'Semestre')) {
                $table->string('Semestre', 10)->nullable()->after('CicloNumero');
            }
            if (!Schema::hasColumn('silabo', 'Horas')) {
                $table->tinyInteger('Horas')->nullable()->after('Semestre');
            }
            if (!Schema::hasColumn('silabo', 'Creditos')) {
                $table->tinyInteger('Creditos')->nullable()->after('Horas');
            }
            if (!Schema::hasColumn('silabo', 'Docente')) {
                $table->string('Docente', 200)->nullable()->after('Creditos');
            }
            if (!Schema::hasColumn('silabo', 'HorarioCursoId')) {
                $table->unsignedBigInteger('HorarioCursoId')->nullable()->after('Docente');
            }
        });
    }

    public function down(): void
    {
        Schema::table('silabo', function (Blueprint $table) {
            $cols = ['CodigoCurso', 'NombreCurso', 'CicloNumero', 'Semestre', 'Horas', 'Creditos', 'Docente', 'HorarioCursoId'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('silabo', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
