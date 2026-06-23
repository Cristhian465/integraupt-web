<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserva', function (Blueprint $table) {
            $table->increments('IdReserva');
            $table->unsignedInteger('usuario');
            $table->unsignedInteger('espacio');
            $table->unsignedInteger('bloque');
            $table->unsignedInteger('curso');
            $table->date('fechaReserva');
            $table->dateTime('fechaSolicitud')->useCurrent();
            $table->text('DescripcionUso')->nullable();
            $table->integer('CantidadEstudiantes')->default(1);
            $table->enum('Estado', ['Pendiente', 'Aprobada', 'Rechazada', 'Cancelada'])->default('Pendiente');

            $table->foreign('usuario')
                  ->references('IdUsuario')
                  ->on('usuario');
            $table->foreign('espacio')
                  ->references('IdEspacio')
                  ->on('espacio');
            $table->foreign('bloque')
                  ->references('IdBloque')
                  ->on('bloqueshorarios');
            $table->foreign('curso')
                  ->references('IdCurso')
                  ->on('cursos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva');
    }
};
