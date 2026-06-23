<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administrativo', function (Blueprint $table) {
            $table->increments('IdAdministrativo');
            $table->unsignedInteger('IdUsuario')->unique();
            $table->unsignedInteger('Escuela')->nullable();
            $table->enum('Turno', ['Mañana', 'Tarde', 'Noche', 'Completo'])->default('Completo');
            $table->string('Extension', 10)->nullable();
            $table->date('FechaIncorporacion');

            $table->foreign('IdUsuario')->references('IdUsuario')->on('usuario')->onDelete('cascade');
            $table->foreign('Escuela')->references('IdEscuela')->on('escuela');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrativo');
    }
};
