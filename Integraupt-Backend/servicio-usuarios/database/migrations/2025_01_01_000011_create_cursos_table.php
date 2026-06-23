<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->increments('IdCurso');
            $table->string('Nombre', 100);
            $table->unsignedInteger('Facultad');
            $table->unsignedInteger('Escuela');
            $table->string('Ciclo', 5);
            $table->boolean('Estado')->default(true);

            $table->foreign('Facultad')
                  ->references('IdFacultad')
                  ->on('facultad');
            $table->foreign('Escuela')
                  ->references('IdEscuela')
                  ->on('escuela');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
