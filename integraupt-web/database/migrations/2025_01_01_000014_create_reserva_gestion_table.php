<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserva_gestion', function (Blueprint $table) {
            $table->increments('IdGestion');
            $table->unsignedInteger('IdReserva');
            $table->unsignedInteger('UsuarioGestion')->comment('Admin que gestiona');
            $table->dateTime('FechaGestion')->useCurrent();
            $table->enum('Accion', ['Aprobar', 'Rechazar']);
            $table->text('Motivo')->comment('Motivo de la acción');
            $table->text('Comentarios')->nullable()->comment('Comentarios adicionales');

            $table->foreign('IdReserva')
                  ->references('IdReserva')
                  ->on('reserva')
                  ->onDelete('cascade');
            $table->foreign('UsuarioGestion')
                  ->references('IdUsuario')
                  ->on('usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_gestion');
    }
};
