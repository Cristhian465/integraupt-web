<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* Schema::table('canal_mensaje', function (Blueprint $table) {
            // ImagenUrl pasa a ser un adjunto genérico (imagen, video, audio o archivo).
            $table->renameColumn('ImagenUrl', 'ArchivoUrl');
        }); */

        /* Schema::table('canal_mensaje', function (Blueprint $table) {
            $table->string('ArchivoTipo', 20)->nullable()->after('ArchivoUrl');
            $table->string('ArchivoNombre', 255)->nullable()->after('ArchivoTipo');
            $table->string('Estado', 20)->default('activo')->after('ArchivoNombre');
            $table->dateTime('EditadoEn')->nullable()->after('FechaEnvio');
        }); */

        // Cada usuario solo puede tener una reacción por mensaje (antes era una por emoji).
        Schema::table('canal_mensaje_reaccion', function (Blueprint $table) {
            $table->dropForeign(['IdMensaje']);
            $table->dropUnique('canal_reaccion_unique');
            $table->unique(['IdMensaje', 'IdUsuario'], 'canal_reaccion_unique');
            $table->foreign('IdMensaje')->references('IdMensaje')->on('canal_mensaje')->onDelete('cascade');
        });

        Schema::create('canal_escribiendo', function (Blueprint $table) {
            $table->unsignedInteger('IdCanal');
            $table->unsignedInteger('IdUsuario');
            $table->unsignedInteger('IdTema')->nullable();
            $table->dateTime('ActualizadoEn');

            $table->primary(['IdCanal', 'IdUsuario']);
            $table->foreign('IdCanal')->references('IdCanal')->on('canal')->onDelete('cascade');
            $table->foreign('IdUsuario')->references('IdUsuario')->on('usuario')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canal_escribiendo');

        Schema::table('canal_mensaje_reaccion', function (Blueprint $table) {
            $table->dropUnique('canal_reaccion_unique');
            $table->unique(['IdMensaje', 'IdUsuario', 'Emoji'], 'canal_reaccion_unique');
        });

        Schema::table('canal_mensaje', function (Blueprint $table) {
            $table->dropColumn(['ArchivoTipo', 'ArchivoNombre', 'Estado', 'EditadoEn']);
        });

        Schema::table('canal_mensaje', function (Blueprint $table) {
            $table->renameColumn('ArchivoUrl', 'ImagenUrl');
        });
    }
};
