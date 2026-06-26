<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('rol')->insert([
            'IdRol' => 5,
            'Nombre' => 'Encargado Cafe',
        ]);

        Schema::create('cafeteria', function (Blueprint $table) {
            $table->increments('IdCafeteria');
            $table->unsignedInteger('IdFacultad')->unique();
            $table->string('Nombre', 150);
            $table->boolean('Estado')->default(1);
            $table->unsignedInteger('IdEncargado')->nullable();

            $table->foreign('IdFacultad')->references('IdFacultad')->on('facultad');
            $table->foreign('IdEncargado')->references('IdUsuario')->on('usuario');
        });

        Schema::create('cafeteria_producto', function (Blueprint $table) {
            $table->increments('IdProducto');
            $table->unsignedInteger('IdCafeteria');
            $table->string('Nombre', 150);
            $table->text('Descripcion')->nullable();
            $table->decimal('Precio', 8, 2);
            $table->unsignedInteger('Stock')->default(0);
            $table->boolean('Estado')->default(1);

            $table->foreign('IdCafeteria')->references('IdCafeteria')->on('cafeteria')->onDelete('cascade');
        });

        Schema::create('cafeteria_pedido', function (Blueprint $table) {
            $table->increments('IdPedido');
            $table->unsignedInteger('IdCafeteria');
            $table->unsignedInteger('IdUsuario');
            $table->decimal('Total', 8, 2);
            $table->string('Estado', 20)->default('pendiente_revision');
            $table->string('CodigoQr', 64)->nullable()->unique();
            $table->string('ComprobanteUrl', 255)->nullable();
            $table->string('CodigoOperacion', 50)->nullable();
            $table->string('MotivoRechazo', 255)->nullable();
            $table->dateTime('FechaPedido')->useCurrent();
            $table->dateTime('FechaConfirmacionPago')->nullable();
            $table->dateTime('FechaEntrega')->nullable();

            $table->foreign('IdCafeteria')->references('IdCafeteria')->on('cafeteria');
            $table->foreign('IdUsuario')->references('IdUsuario')->on('usuario');
        });

        Schema::create('cafeteria_pedido_item', function (Blueprint $table) {
            $table->increments('IdItem');
            $table->unsignedInteger('IdPedido');
            $table->unsignedInteger('IdProducto');
            $table->unsignedInteger('Cantidad');
            $table->decimal('PrecioUnitario', 8, 2);

            $table->foreign('IdPedido')->references('IdPedido')->on('cafeteria_pedido')->onDelete('cascade');
            $table->foreign('IdProducto')->references('IdProducto')->on('cafeteria_producto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cafeteria_pedido_item');
        Schema::dropIfExists('cafeteria_pedido');
        Schema::dropIfExists('cafeteria_producto');
        Schema::dropIfExists('cafeteria');
        DB::table('rol')->where('IdRol', 5)->delete();
    }
};
