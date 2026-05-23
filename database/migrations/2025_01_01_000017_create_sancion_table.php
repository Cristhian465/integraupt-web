<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sancion', function (Blueprint $table) {
            $table->bigIncrements('IdSancion');
            $table->unsignedInteger('Usuario');
            $table->text('Motivo');
            $table->date('FechaInicio');
            $table->date('FechaFin');
            $table->enum('Estado', ['ACTIVA', 'CUMPLIDA']);
            $table->enum('TipoUsuario', ['DOCENTE', 'ESTUDIANTE']);

            $table->foreign('Usuario')
                  ->references('IdUsuario')
                  ->on('usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sancion');
    }
};
