<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reserva_qr', function (Blueprint $table) {
            $table->timestamp('verificado_en')->nullable()->after('generado_en');
        });
    }

    public function down(): void
    {
        Schema::table('reserva_qr', function (Blueprint $table) {
            $table->dropColumn('verificado_en');
        });
    }
};
