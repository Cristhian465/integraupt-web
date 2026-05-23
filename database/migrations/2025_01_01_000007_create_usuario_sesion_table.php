<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_sesion', function (Blueprint $table) {
            $table->increments('IdSesion');
            $table->unsignedInteger('IdUsuario');
            $table->string('Dispositivo', 50)->nullable();
            $table->string('IP', 45)->nullable();
            $table->boolean('Activa')->default(true);

            $table->foreign('IdUsuario')
                  ->references('IdUsuario')
                  ->on('usuario')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_sesion');
    }
};
