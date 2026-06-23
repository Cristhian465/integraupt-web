<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docente', function (Blueprint $table) {
            $table->bigInteger('IdDocente')->primary();
            $table->unsignedInteger('IdUsuario')->unique();
            $table->string('CodigoDocente', 255)->unique();
            $table->unsignedInteger('Escuela')->nullable();
            $table->enum('TipoContrato', ['Tiempo Completo', 'Medio Tiempo', 'Contratado']);
            $table->string('Especialidad', 100)->nullable();
            $table->date('FechaIncorporacion');

            $table->foreign('IdUsuario')
                  ->references('IdUsuario')
                  ->on('usuario')
                  ->onDelete('cascade');
            $table->foreign('Escuela')
                  ->references('IdEscuela')
                  ->on('escuela');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docente');
    }
};
