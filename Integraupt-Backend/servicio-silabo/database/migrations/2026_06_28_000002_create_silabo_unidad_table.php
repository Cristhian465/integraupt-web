<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('silabo_unidad', function (Blueprint $table) {
            $table->id('IdUnidad');
            $table->unsignedBigInteger('SilaboId');
            $table->tinyInteger('Numero');
            $table->string('Nombre', 200);
            $table->integer('HorasTotal')->default(0);
            $table->timestamps();

            $table->foreign('SilaboId')->references('IdSilabo')->on('silabo')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('silabo_unidad');
    }
};
