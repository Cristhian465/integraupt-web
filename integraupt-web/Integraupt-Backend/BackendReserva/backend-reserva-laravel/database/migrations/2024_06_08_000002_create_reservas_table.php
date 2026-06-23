<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserva', function (Blueprint $table) {
            $table->id('IdReserva');
            $table->integer('usuario');
            $table->integer('espacio');
            $table->integer('bloque');
            $table->integer('curso');
            $table->date('fechaReserva');
            $table->dateTime('fechaSolicitud');
            $table->text('DescripcionUso')->nullable();
            $table->integer('CantidadEstudiantes');
            $table->string('Estado', 50)->default('Pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva');
    }
};
