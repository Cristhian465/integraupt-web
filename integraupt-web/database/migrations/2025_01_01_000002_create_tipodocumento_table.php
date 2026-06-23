<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipodocumento', function (Blueprint $table) {
            $table->increments('IdTipoDoc');
            $table->string('Nombre', 50)->unique();
            $table->string('Abreviatura', 10)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipodocumento');
    }
};
