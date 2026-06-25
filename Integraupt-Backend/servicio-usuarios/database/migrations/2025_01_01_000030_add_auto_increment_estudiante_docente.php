<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `estudiante` MODIFY `IdEstudiante` BIGINT(20) NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE `docente` MODIFY `IdDocente` BIGINT(20) NOT NULL AUTO_INCREMENT');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `estudiante` MODIFY `IdEstudiante` BIGINT(20) NOT NULL');
        DB::statement('ALTER TABLE `docente` MODIFY `IdDocente` BIGINT(20) NOT NULL');
    }
};
