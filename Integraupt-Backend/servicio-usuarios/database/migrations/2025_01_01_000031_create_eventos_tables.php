<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento', function (Blueprint $table) {
            $table->increments('IdEvento');
            $table->string('Titulo', 200);
            $table->text('Descripcion')->nullable();
            $table->string('TipoEvento', 30);
            $table->string('Alcance', 20);
            $table->unsignedInteger('IdFacultad');
            $table->unsignedInteger('IdEscuela')->nullable();
            $table->unsignedInteger('IdEspacio')->nullable();
            $table->dateTime('FechaInicio');
            $table->dateTime('FechaFin');
            $table->unsignedInteger('AforoMaximo')->nullable();
            $table->boolean('RequiereInscripcion')->default(true);
            $table->string('Estado', 20)->default('borrador');
            $table->unsignedInteger('IdResponsable');
            $table->dateTime('FechaCreacion')->useCurrent();

            $table->foreign('IdFacultad')->references('IdFacultad')->on('facultad');
            $table->foreign('IdEscuela')->references('IdEscuela')->on('escuela');
            $table->foreign('IdEspacio')->references('IdEspacio')->on('espacio');
            $table->foreign('IdResponsable')->references('IdUsuario')->on('usuario');
        });

        Schema::create('evento_inscripcion', function (Blueprint $table) {
            $table->increments('IdInscripcion');
            $table->unsignedInteger('IdEvento');
            $table->unsignedInteger('IdUsuario');
            $table->string('TipoUsuario', 20);
            $table->dateTime('FechaInscripcion')->useCurrent();
            $table->string('Estado', 20)->default('inscrito');
            $table->string('CodigoQr', 64)->unique();

            $table->foreign('IdEvento')->references('IdEvento')->on('evento')->onDelete('cascade');
            $table->foreign('IdUsuario')->references('IdUsuario')->on('usuario');
            $table->unique(['IdEvento', 'IdUsuario']);
        });

        Schema::create('evento_certificado', function (Blueprint $table) {
            $table->increments('IdCertificado');
            $table->unsignedInteger('IdInscripcion');
            $table->string('UrlArchivo', 255);
            $table->dateTime('FechaEmision')->useCurrent();

            $table->foreign('IdInscripcion')->references('IdInscripcion')->on('evento_inscripcion')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_certificado');
        Schema::dropIfExists('evento_inscripcion');
        Schema::dropIfExists('evento');
    }
};
