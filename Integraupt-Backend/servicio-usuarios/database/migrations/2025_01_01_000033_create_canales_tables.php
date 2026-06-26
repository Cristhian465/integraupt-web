<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canal', function (Blueprint $table) {
            $table->increments('IdCanal');
            $table->string('Nombre', 150);
            $table->text('Descripcion')->nullable();
            $table->unsignedInteger('IdCreador');
            $table->string('TipoCreador', 20);
            $table->string('Estado', 20)->default('activo');
            $table->dateTime('FechaCreacion')->useCurrent();

            $table->foreign('IdCreador')->references('IdUsuario')->on('usuario');
        });

        Schema::create('canal_miembro', function (Blueprint $table) {
            $table->increments('IdCanalMiembro');
            $table->unsignedInteger('IdCanal');
            $table->unsignedInteger('IdUsuario');
            $table->string('Rol', 20)->default('miembro');
            $table->dateTime('FechaUnion')->useCurrent();

            $table->foreign('IdCanal')->references('IdCanal')->on('canal')->onDelete('cascade');
            $table->foreign('IdUsuario')->references('IdUsuario')->on('usuario');
            $table->unique(['IdCanal', 'IdUsuario']);
        });

        Schema::create('canal_mensaje', function (Blueprint $table) {
            $table->increments('IdMensaje');
            $table->unsignedInteger('IdCanal');
            $table->unsignedInteger('IdUsuario');
            $table->text('Contenido');
            $table->dateTime('FechaEnvio')->useCurrent();

            $table->foreign('IdCanal')->references('IdCanal')->on('canal')->onDelete('cascade');
            $table->foreign('IdUsuario')->references('IdUsuario')->on('usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canal_mensaje');
        Schema::dropIfExists('canal_miembro');
        Schema::dropIfExists('canal');
    }
};
