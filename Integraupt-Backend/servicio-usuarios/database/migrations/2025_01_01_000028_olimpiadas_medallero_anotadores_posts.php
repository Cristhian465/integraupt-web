<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexNames = collect(DB::select("SHOW INDEX FROM olimpiada_edicion_disciplina"))->pluck('Key_name')->unique()->all();

        if (!Schema::hasColumn('olimpiada_edicion_disciplina', 'Categoria')) {
            Schema::table('olimpiada_edicion_disciplina', function (Blueprint $table) {
                $table->enum('Categoria', ['general', 'varones', 'damas', 'mixto'])->default('general')->after('Disciplina');
            });
        }

        if (!in_array('olimp_edicion_disciplina_edicion_idx', $indexNames)) {
            Schema::table('olimpiada_edicion_disciplina', function (Blueprint $table) {
                $table->index('Edicion', 'olimp_edicion_disciplina_edicion_idx');
            });
        }

        if (!in_array('olimp_edicion_disciplina_cat_unique', $indexNames)) {
            Schema::table('olimpiada_edicion_disciplina', function (Blueprint $table) use ($indexNames) {
                if (in_array('olimpiada_edicion_disciplina_edicion_disciplina_unique', $indexNames)) {
                    $table->dropUnique(['Edicion', 'Disciplina']);
                }
                $table->unique(['Edicion', 'Disciplina', 'Categoria'], 'olimp_edicion_disciplina_cat_unique');
            });
        }

        Schema::create('olimpiada_anotador', function (Blueprint $table) {
            $table->bigIncrements('IdAnotador');
            $table->unsignedInteger('EdicionDisciplina');
            $table->unsignedInteger('Facultad');
            $table->string('NombreJugador', 150);
            $table->unsignedInteger('Cantidad')->default(1);
            $table->text('Observaciones')->nullable();
            $table->dateTime('FechaRegistro')->nullable()->useCurrent();

            $table->foreign('EdicionDisciplina')->references('IdEdicionDisciplina')->on('olimpiada_edicion_disciplina')->onDelete('cascade');
            $table->foreign('Facultad')->references('IdFacultad')->on('facultad');
        });

        Schema::create('olimpiada_post', function (Blueprint $table) {
            $table->bigIncrements('IdPost');
            $table->unsignedInteger('Edicion');
            $table->string('Titulo', 200);
            $table->text('Contenido');
            $table->string('ImagenUrl', 500)->nullable();
            $table->string('Autor', 150)->nullable();
            $table->dateTime('FechaPublicacion')->nullable()->useCurrent();

            $table->foreign('Edicion')->references('IdEdicion')->on('olimpiada_edicion')->onDelete('cascade');
        });

        Schema::create('olimpiada_comentario', function (Blueprint $table) {
            $table->bigIncrements('IdComentario');
            $table->unsignedBigInteger('Post');
            $table->unsignedInteger('Usuario');
            $table->text('Contenido');
            $table->dateTime('FechaComentario')->nullable()->useCurrent();

            $table->foreign('Post')->references('IdPost')->on('olimpiada_post')->onDelete('cascade');
            $table->foreign('Usuario')->references('IdUsuario')->on('usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olimpiada_comentario');
        Schema::dropIfExists('olimpiada_post');
        Schema::dropIfExists('olimpiada_anotador');

        Schema::table('olimpiada_edicion_disciplina', function (Blueprint $table) {
            $table->dropUnique('olimp_edicion_disciplina_cat_unique');
            $table->unique(['Edicion', 'Disciplina']);
            $table->dropIndex('olimp_edicion_disciplina_edicion_idx');
            $table->dropColumn('Categoria');
        });
    }
};
