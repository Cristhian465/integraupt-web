<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horario_curso', function (Blueprint $table) {
            $table->increments('IdHorarioCurso');
            $table->unsignedInteger('Curso');
            $table->unsignedInteger('Docente');
            $table->unsignedInteger('Espacio');
            $table->unsignedInteger('Bloque');
            $table->enum('DiaSemana', ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado']);
            $table->date('FechaInicio');
            $table->date('FechaFin');
            $table->boolean('Estado')->default(true);

            $table->foreign('Curso')
                  ->references('IdCurso')
                  ->on('cursos');
            $table->foreign('Docente')
                  ->references('IdUsuario')
                  ->on('usuario');
            $table->foreign('Espacio')
                  ->references('IdEspacio')
                  ->on('espacio');
            $table->foreign('Bloque')
                  ->references('IdBloque')
                  ->on('bloqueshorarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horario_curso');
    }
};
