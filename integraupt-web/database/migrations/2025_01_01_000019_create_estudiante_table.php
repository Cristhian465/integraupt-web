<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiante', function (Blueprint $table) {
            $table->bigInteger('IdEstudiante')->primary();
            $table->unsignedInteger('IdUsuario')->unique();
            $table->string('Codigo', 255)->unique();
            $table->unsignedInteger('Escuela');

            $table->foreign('IdUsuario')->references('IdUsuario')->on('usuario')->onDelete('cascade');
            $table->foreign('Escuela')->references('IdEscuela')->on('escuela');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiante');
    }
};
