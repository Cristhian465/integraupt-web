<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_gimnasio', function (Blueprint $table) {
            $table->increments('IdAsistencia');
            $table->unsignedInteger('IdUsuario');
            $table->dateTime('FechaIngreso');
            $table->dateTime('FechaSalida')->nullable();

            $table->foreign('IdUsuario')->references('IdUsuario')->on('usuario')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia_gimnasio');
    }
};
