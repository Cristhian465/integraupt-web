<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olimpiada_edicion', function (Blueprint $table) {
            $table->increments('IdEdicion');
            $table->string('Nombre', 150);
            $table->unsignedSmallInteger('AnioInicio');
            $table->unsignedTinyInteger('SemestreInicio');
            $table->unsignedSmallInteger('AnioFin')->nullable();
            $table->unsignedTinyInteger('SemestreFin')->nullable();
            $table->enum('Estado', [
                'planificada',
                'inscripcion_abierta',
                'inscripcion_cerrada',
                'en_curso',
                'finalizada',
                'cancelada',
            ])->default('planificada');
            $table->dateTime('FechaAperturaInscripcion')->nullable();
            $table->dateTime('FechaCierreInscripcion')->nullable();
            $table->date('FechaInicioJuegos')->nullable();
            $table->date('FechaFinJuegos')->nullable();
            $table->text('Observaciones')->nullable();
            $table->dateTime('FechaRegistro')->nullable()->useCurrent();

            $table->unique(['AnioInicio', 'SemestreInicio']);
        });

        Schema::create('olimpiada_disciplina', function (Blueprint $table) {
            $table->increments('IdDisciplina');
            $table->string('Nombre', 150)->unique();
            $table->text('Descripcion')->nullable();
            $table->enum('TipoParticipacion', ['individual', 'equipo'])->default('equipo');
            $table->text('Reglas')->nullable();
            $table->unsignedInteger('CupoMaximoDefault')->nullable();
            $table->enum('Estado', ['activa', 'inactiva'])->default('activa');
            $table->dateTime('FechaRegistro')->nullable()->useCurrent();
        });

        Schema::create('olimpiada_edicion_disciplina', function (Blueprint $table) {
            $table->increments('IdEdicionDisciplina');
            $table->unsignedInteger('Edicion');
            $table->unsignedInteger('Disciplina');
            $table->unsignedInteger('CupoMaximoPorFacultad')->nullable();
            $table->text('ReglasEspecificas')->nullable();
            $table->enum('Estado', ['activa', 'inactiva'])->default('activa');

            $table->foreign('Edicion')->references('IdEdicion')->on('olimpiada_edicion')->onDelete('cascade');
            $table->foreign('Disciplina')->references('IdDisciplina')->on('olimpiada_disciplina');
            $table->unique(['Edicion', 'Disciplina']);
        });

        Schema::create('olimpiada_inscripcion', function (Blueprint $table) {
            $table->bigIncrements('IdInscripcion');
            $table->unsignedInteger('EdicionDisciplina');
            $table->unsignedInteger('Usuario');
            $table->unsignedInteger('Facultad');
            $table->enum('Estado', ['inscrito', 'cancelado', 'rechazado'])->default('inscrito');
            $table->text('Observaciones')->nullable();
            $table->dateTime('FechaInscripcion')->nullable()->useCurrent();

            $table->foreign('EdicionDisciplina')->references('IdEdicionDisciplina')->on('olimpiada_edicion_disciplina')->onDelete('cascade');
            $table->foreign('Usuario')->references('IdUsuario')->on('usuario');
            $table->foreign('Facultad')->references('IdFacultad')->on('facultad');
            $table->unique(['EdicionDisciplina', 'Usuario']);
        });

        Schema::create('olimpiada_resultado', function (Blueprint $table) {
            $table->bigIncrements('IdResultado');
            $table->unsignedInteger('EdicionDisciplina');
            $table->unsignedInteger('FacultadLocal');
            $table->unsignedInteger('FacultadVisitante')->nullable();
            $table->string('Fase', 50)->default('grupos');
            $table->string('Grupo', 50)->nullable();
            $table->dateTime('FechaPartido')->nullable();
            $table->unsignedInteger('PuntajeLocal')->nullable();
            $table->unsignedInteger('PuntajeVisitante')->nullable();
            $table->unsignedInteger('FacultadGanadora')->nullable();
            $table->enum('Estado', ['programado', 'en_curso', 'finalizado', 'cancelado', 'suspendido'])->default('programado');
            $table->text('Observaciones')->nullable();
            $table->dateTime('FechaRegistro')->nullable()->useCurrent();

            $table->foreign('EdicionDisciplina')->references('IdEdicionDisciplina')->on('olimpiada_edicion_disciplina')->onDelete('cascade');
            $table->foreign('FacultadLocal')->references('IdFacultad')->on('facultad');
            $table->foreign('FacultadVisitante')->references('IdFacultad')->on('facultad');
            $table->foreign('FacultadGanadora')->references('IdFacultad')->on('facultad');
        });

        Schema::create('olimpiada_participacion_facultad', function (Blueprint $table) {
            $table->increments('IdParticipacion');
            $table->unsignedInteger('EdicionDisciplina');
            $table->unsignedInteger('Facultad');
            $table->unsignedInteger('PartidosJugados')->default(0);
            $table->unsignedInteger('PartidosGanados')->default(0);
            $table->unsignedInteger('PartidosEmpatados')->default(0);
            $table->unsignedInteger('PartidosPerdidos')->default(0);
            $table->unsignedInteger('PuntosAFavor')->default(0);
            $table->unsignedInteger('PuntosEnContra')->default(0);
            $table->unsignedInteger('Puntos')->default(0);
            $table->unsignedInteger('Posicion')->nullable();

            $table->foreign('EdicionDisciplina')->references('IdEdicionDisciplina')->on('olimpiada_edicion_disciplina')->onDelete('cascade');
            $table->foreign('Facultad')->references('IdFacultad')->on('facultad');
            $table->unique(['EdicionDisciplina', 'Facultad'], 'olimp_participacion_edic_fac_unique');
        });

        DB::table('olimpiada_disciplina')->insert([
            ['Nombre' => 'Fútbol', 'TipoParticipacion' => 'equipo', 'CupoMaximoDefault' => 18, 'Estado' => 'activa'],
            ['Nombre' => 'Vóleibol', 'TipoParticipacion' => 'equipo', 'CupoMaximoDefault' => 12, 'Estado' => 'activa'],
            ['Nombre' => 'Básquetbol', 'TipoParticipacion' => 'equipo', 'CupoMaximoDefault' => 12, 'Estado' => 'activa'],
            ['Nombre' => 'Atletismo', 'TipoParticipacion' => 'individual', 'CupoMaximoDefault' => 10, 'Estado' => 'activa'],
            ['Nombre' => 'Ajedrez', 'TipoParticipacion' => 'individual', 'CupoMaximoDefault' => 6, 'Estado' => 'activa'],
            ['Nombre' => 'Tenis de Mesa', 'TipoParticipacion' => 'individual', 'CupoMaximoDefault' => 6, 'Estado' => 'activa'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('olimpiada_participacion_facultad');
        Schema::dropIfExists('olimpiada_resultado');
        Schema::dropIfExists('olimpiada_inscripcion');
        Schema::dropIfExists('olimpiada_edicion_disciplina');
        Schema::dropIfExists('olimpiada_disciplina');
        Schema::dropIfExists('olimpiada_edicion');
    }
};
