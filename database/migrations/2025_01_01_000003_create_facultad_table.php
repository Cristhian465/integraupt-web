<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facultad', function (Blueprint $table) {
            $table->increments('IdFacultad');
            $table->string('Nombre', 255)->unique();
            $table->string('Abreviatura', 255)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facultad');
    }
};
