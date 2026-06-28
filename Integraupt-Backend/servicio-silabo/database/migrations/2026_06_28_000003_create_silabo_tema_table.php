<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('silabo_tema', function (Blueprint $table) {
            $table->id('IdTema');
            $table->unsignedBigInteger('UnidadId');
            $table->tinyInteger('Semana');
            $table->text('ContenidoConceptual')->nullable();
            $table->text('ContenidoProcedimental')->nullable();
            $table->tinyInteger('Orden')->default(0);
            $table->timestamps();

            $table->foreign('UnidadId')->references('IdUnidad')->on('silabo_unidad')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('silabo_tema');
    }
};
