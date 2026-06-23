<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_auth', function (Blueprint $table) {
            $table->increments('IdAuth');
            $table->unsignedInteger('IdUsuario')->unique();
            $table->string('CorreoU', 30)->unique();
            $table->string('Password', 255)->default('');
            $table->dateTime('UltimoLogin')->nullable();
            $table->string('SesionToken', 255)->nullable();
            $table->dateTime('SesionExpira')->nullable();
            $table->string('SesionTipo', 20)->nullable();

            $table->foreign('IdUsuario')
                  ->references('IdUsuario')
                  ->on('usuario')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_auth');
    }
};
