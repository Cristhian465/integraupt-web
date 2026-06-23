<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidencia', function (Blueprint $table) {
            $table->increments('IdIncidencia');
            $table->unsignedInteger('Reserva');
            $table->text('Descripcion');
            $table->dateTime('FechaReporte')->nullable()->useCurrent();

            $table->foreign('Reserva')
                  ->references('IdReserva')
                  ->on('reserva');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidencia');
    }
};
