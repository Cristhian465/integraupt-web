<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->increments('IdHorario');
            $table->unsignedInteger('espacio');
            $table->unsignedInteger('bloque');
            $table->enum('diaSemana', ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado']);
            $table->boolean('ocupado')->default(false);

            $table->foreign('espacio')
                  ->references('IdEspacio')
                  ->on('espacio')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            $table->foreign('bloque')
                  ->references('IdBloque')
                  ->on('bloqueshorarios')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
