<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psicologo', function (Blueprint $table) {
            $table->increments('IdPsicologo');
            $table->string('Nombre', 150);
            $table->string('Especialidad', 150)->nullable();
            $table->boolean('Estado')->default(1);
        });

        Schema::create('cita_psicologia', function (Blueprint $table) {
            $table->increments('IdCita');
            $table->unsignedInteger('Estudiante');
            $table->unsignedInteger('Psicologo');
            $table->unsignedInteger('Bloque');
            $table->date('Fecha');
            $table->text('Motivo')->nullable();
            $table->string('Estado', 30)->default('Pendiente');
            $table->dateTime('FechaSolicitud')->useCurrent();

            $table->foreign('Estudiante')->references('IdUsuario')->on('usuario');
            $table->foreign('Psicologo')->references('IdPsicologo')->on('psicologo');
            $table->foreign('Bloque')->references('IdBloque')->on('bloqueshorarios');
        });

        DB::table('psicologo')->insert([
            ['Nombre' => 'Mariela Quispe Flores', 'Especialidad' => 'Psicología clínica', 'Estado' => 1],
            ['Nombre' => 'Jorge Salas Vargas', 'Especialidad' => 'Psicología educativa', 'Estado' => 1],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cita_psicologia');
        Schema::dropIfExists('psicologo');
    }
};
