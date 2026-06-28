<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avance_tema', function (Blueprint $table) {
            $table->id('IdAvance');
            $table->unsignedBigInteger('SilaboTemaId');
            $table->unsignedBigInteger('DocenteId');
            $table->unsignedBigInteger('HorarioCursoId')->nullable();
            $table->date('FechaClase');
            $table->text('Comentario')->nullable();
            // Estado: pendiente | aprobado | rechazado
            $table->string('Estado', 20)->default('pendiente');
            $table->text('ObservacionCoordinador')->nullable();
            $table->timestamps();

            $table->foreign('SilaboTemaId')->references('IdTema')->on('silabo_tema')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avance_tema');
    }
};
