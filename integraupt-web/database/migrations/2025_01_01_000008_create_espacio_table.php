<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('espacio', function (Blueprint $table) {
            $table->increments('IdEspacio');
            $table->string('Codigo', 20)->unique();
            $table->string('Nombre', 100);
            $table->enum('Tipo', ['Laboratorio', 'Salon'])->default('Laboratorio');
            $table->integer('Capacidad');
            $table->text('Equipamiento')->nullable();
            $table->unsignedInteger('Escuela');
            $table->integer('Estado')->default(1);

            $table->foreign('Escuela')
                  ->references('IdEscuela')
                  ->on('escuela');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('espacio');
    }
};
