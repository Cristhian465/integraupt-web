<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserva_qr', function (Blueprint $table) {
            $table->id();
            $table->string('token', 255)->unique();
            $table->unsignedBigInteger('reserva_id')->index();
            $table->string('laboratorio');
            $table->string('fecha');
            $table->string('hora');
            $table->string('estado');
            $table->string('solicitante_nombre');
            $table->string('solicitante_codigo');
            $table->timestamp('generado_en');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_qr');
    }
};
