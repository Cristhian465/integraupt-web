<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_atencion', function (Blueprint $table) {
            $table->increments('IdTipoAtencion');
            $table->string('Nombre', 100);
            $table->boolean('Estado')->default(1);
        });

        Schema::create('medico', function (Blueprint $table) {
            $table->increments('IdMedico');
            $table->string('Nombre', 150);
            $table->boolean('Estado')->default(1);
        });

        Schema::create('medico_tipo_atencion', function (Blueprint $table) {
            $table->increments('IdMedicoTipoAtencion');
            $table->unsignedInteger('Medico');
            $table->unsignedInteger('TipoAtencion');

            $table->foreign('Medico')->references('IdMedico')->on('medico');
            $table->foreign('TipoAtencion')->references('IdTipoAtencion')->on('tipo_atencion');
            $table->unique(['Medico', 'TipoAtencion']);
        });

        Schema::create('cita_policlinico', function (Blueprint $table) {
            $table->increments('IdCita');
            $table->unsignedInteger('Estudiante');
            $table->unsignedInteger('Medico');
            $table->unsignedInteger('TipoAtencion');
            $table->unsignedInteger('Bloque');
            $table->date('Fecha');
            $table->text('Motivo')->nullable();
            $table->string('Estado', 30)->default('Pendiente');
            $table->dateTime('FechaSolicitud')->useCurrent();

            $table->foreign('Estudiante')->references('IdUsuario')->on('usuario');
            $table->foreign('Medico')->references('IdMedico')->on('medico');
            $table->foreign('TipoAtencion')->references('IdTipoAtencion')->on('tipo_atencion');
            $table->foreign('Bloque')->references('IdBloque')->on('bloqueshorarios');
        });

        $tipos = [
            'Medicina General',
            'Enfermería',
            'Tópico de Emergencia',
        ];

        foreach ($tipos as $nombre) {
            DB::table('tipo_atencion')->insert(['Nombre' => $nombre, 'Estado' => 1]);
        }

        $medicoGeneralId = DB::table('medico')->insertGetId(['Nombre' => 'Dr. Renzo Huanca Flores', 'Estado' => 1]);
        $enfermeraId = DB::table('medico')->insertGetId(['Nombre' => 'Lic. Carmen Rojas Vela', 'Estado' => 1]);

        $idMedicinaGeneral = DB::table('tipo_atencion')->where('Nombre', 'Medicina General')->value('IdTipoAtencion');
        $idEnfermeria = DB::table('tipo_atencion')->where('Nombre', 'Enfermería')->value('IdTipoAtencion');
        $idTopico = DB::table('tipo_atencion')->where('Nombre', 'Tópico de Emergencia')->value('IdTipoAtencion');

        DB::table('medico_tipo_atencion')->insert([
            ['Medico' => $medicoGeneralId, 'TipoAtencion' => $idMedicinaGeneral],
            ['Medico' => $medicoGeneralId, 'TipoAtencion' => $idTopico],
            ['Medico' => $enfermeraId, 'TipoAtencion' => $idEnfermeria],
            ['Medico' => $enfermeraId, 'TipoAtencion' => $idTopico],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cita_policlinico');
        Schema::dropIfExists('medico_tipo_atencion');
        Schema::dropIfExists('medico');
        Schema::dropIfExists('tipo_atencion');
    }
};
