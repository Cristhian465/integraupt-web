<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('silabo', function (Blueprint $table) {
            if (!Schema::hasColumn('silabo', 'CorreoDocente')) {
                $table->string('CorreoDocente', 150)->nullable()->after('Docente');
            }
        });
    }

    public function down(): void
    {
        Schema::table('silabo', function (Blueprint $table) {
            if (Schema::hasColumn('silabo', 'CorreoDocente')) {
                $table->dropColumn('CorreoDocente');
            }
        });
    }
};
