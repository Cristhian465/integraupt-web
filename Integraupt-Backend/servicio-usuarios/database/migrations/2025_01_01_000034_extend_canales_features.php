<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canal', function (Blueprint $table) {
            $table->string('Color', 7)->nullable()->after('Estado');
            $table->text('FotoUrl')->nullable()->after('Color');
        });

        Schema::create('canal_tema', function (Blueprint $table) {
            $table->increments('IdTema');
            $table->unsignedInteger('IdCanal');
            $table->string('Nombre', 100);
            $table->text('Descripcion')->nullable();
            $table->unsignedInteger('Orden')->default(0);
            $table->dateTime('FechaCreacion')->useCurrent();

            $table->foreign('IdCanal')->references('IdCanal')->on('canal')->onDelete('cascade');
        });

        Schema::table('canal_mensaje', function (Blueprint $table) {
            $table->unsignedInteger('IdTema')->nullable()->after('IdCanal');
            $table->unsignedInteger('IdMensajeRespuesta')->nullable()->after('IdTema');
            $table->text('ImagenUrl')->nullable()->after('Contenido');

            $table->foreign('IdTema')->references('IdTema')->on('canal_tema')->onDelete('set null');
            $table->foreign('IdMensajeRespuesta')->references('IdMensaje')->on('canal_mensaje')->onDelete('set null');
        });

        Schema::create('canal_mensaje_reaccion', function (Blueprint $table) {
            $table->increments('IdReaccion');
            $table->unsignedInteger('IdMensaje');
            $table->unsignedInteger('IdUsuario');
            $table->string('Emoji', 10);
            $table->dateTime('FechaReaccion')->useCurrent();

            $table->foreign('IdMensaje')->references('IdMensaje')->on('canal_mensaje')->onDelete('cascade');
            $table->foreign('IdUsuario')->references('IdUsuario')->on('usuario');
            $table->unique(['IdMensaje', 'IdUsuario', 'Emoji'], 'canal_reaccion_unique');
        });
    }

    public function down(): void
    {
        Schema::table('canal_mensaje', function (Blueprint $table) {
            $table->dropForeign(['IdTema']);
            $table->dropForeign(['IdMensajeRespuesta']);
            $table->dropColumn(['IdTema', 'IdMensajeRespuesta', 'ImagenUrl']);
        });

        Schema::dropIfExists('canal_mensaje_reaccion');
        Schema::dropIfExists('canal_tema');

        Schema::table('canal', function (Blueprint $table) {
            $table->dropColumn(['Color', 'FotoUrl']);
        });
    }
};
