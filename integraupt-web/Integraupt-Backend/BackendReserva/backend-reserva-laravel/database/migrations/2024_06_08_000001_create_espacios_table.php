<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('espacio', function (Blueprint $table) {
            $table->id('IdEspacio');
            $table->string('Nombre', 100);
            $table->string('Tipo', 50);
            $table->integer('Capacidad');
            $table->string('Ubicacion', 200)->nullable();
            $table->string('Estado', 20)->default('Disponible');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('espacio');
    }
};
