<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escuela', function (Blueprint $table) {
            $table->increments('IdEscuela');
            $table->unsignedInteger('IdFacultad');
            $table->string('Nombre', 255);

            $table->foreign('IdFacultad')
                  ->references('IdFacultad')
                  ->on('facultad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escuela');
    }
};
