<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->increments('IdUsuario');
            $table->string('Nombre', 255);
            $table->string('Apellido', 255);
            $table->unsignedInteger('TipoDoc');
            $table->string('NumDoc', 255)->nullable()->unique();
            $table->integer('Rol');
            $table->string('Celular', 11)->nullable();
            $table->boolean('Genero')->nullable();
            $table->integer('Estado')->default(1);
            $table->dateTime('FechaRegistro')->nullable()->useCurrent();

            $table->foreign('Rol')
                  ->references('IdRol')
                  ->on('rol');
            $table->foreign('TipoDoc')
                  ->references('IdTipoDoc')
                  ->on('tipodocumento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
