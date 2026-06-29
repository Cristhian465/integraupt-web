<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cuántas sesiones (días) por semana tiene el curso
        if (!Schema::hasColumn('silabo', 'DiasXSemana')) {
            Schema::table('silabo', function (Blueprint $table) {
                $table->tinyInteger('DiasXSemana')->default(1)->after('HorarioCursoId');
            });
        }

        // Qué sesión del día representa este avance (1, 2 o 3)
        if (!Schema::hasColumn('avance_tema', 'Sesion')) {
            Schema::table('avance_tema', function (Blueprint $table) {
                $table->tinyInteger('Sesion')->default(1)->after('HorarioCursoId');
            });
        }
    }

    public function down(): void
    {
        Schema::table('silabo', function (Blueprint $table) {
            if (Schema::hasColumn('silabo', 'DiasXSemana')) $table->dropColumn('DiasXSemana');
        });
        Schema::table('avance_tema', function (Blueprint $table) {
            if (Schema::hasColumn('avance_tema', 'Sesion')) $table->dropColumn('Sesion');
        });
    }
};
