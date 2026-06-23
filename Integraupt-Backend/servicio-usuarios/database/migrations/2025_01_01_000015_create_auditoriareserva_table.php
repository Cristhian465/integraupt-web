<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoriareserva', function (Blueprint $table) {
            $table->increments('IdAudit');
            $table->unsignedInteger('IdReserva');
            $table->string('EstadoAnterior', 50);
            $table->string('EstadoNuevo', 50);
            $table->dateTime('FechaCambio')->useCurrent();
            $table->unsignedInteger('UsuarioCambio')->nullable();

            $table->foreign('IdReserva')
                  ->references('IdReserva')
                  ->on('reserva')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            $table->foreign('UsuarioCambio')
                  ->references('IdUsuario')
                  ->on('usuario')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoriareserva');
    }
};
