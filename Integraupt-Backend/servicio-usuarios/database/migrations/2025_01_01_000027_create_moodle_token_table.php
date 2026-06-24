<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moodle_token', function (Blueprint $table) {
            $table->increments('IdMoodleToken');
            $table->unsignedInteger('Estudiante')->unique();
            $table->unsignedInteger('MoodleUserId');
            $table->string('MoodleUsername', 150);
            $table->text('Token');
            $table->text('PrivateToken')->nullable();
            $table->dateTime('FechaConexion')->useCurrent();
            $table->dateTime('FechaActualizacion')->nullable();

            $table->foreign('Estudiante')->references('IdUsuario')->on('usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moodle_token');
    }
};
