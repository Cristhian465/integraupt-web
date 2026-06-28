<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('silabo', function (Blueprint $table) {
            $table->id('IdSilabo');
            $table->string('CodigoCurso', 20)->nullable();   // Ej: SI-786
            $table->string('NombreCurso', 255)->nullable();  // Ej: Programación Web I
            $table->tinyInteger('CicloNumero')->nullable();  // 1-12 (inferido del código)
            $table->string('Semestre', 10)->nullable();      // Ej: 2026-I
            $table->tinyInteger('Horas')->nullable();
            $table->tinyInteger('Creditos')->nullable();
            $table->string('Docente', 200)->nullable();
            $table->unsignedBigInteger('HorarioCursoId')->nullable();
            $table->string('ArchivoPdf')->nullable();
            $table->date('FechaCarga')->nullable();
            $table->tinyInteger('Estado')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('silabo');
    }
};
