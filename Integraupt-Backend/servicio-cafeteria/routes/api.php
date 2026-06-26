<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CafeteriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PedidoController;

Route::prefix('catalogos')->group(function () {
    Route::get('/facultades', [CatalogoController::class, 'listarFacultades']);
    Route::get('/escuelas', [CatalogoController::class, 'listarEscuelas']);
    Route::get('/administrativos', [CatalogoController::class, 'buscarAdministrativos']);
    Route::get('/mi-facultad', [CatalogoController::class, 'miFacultad']);
});

Route::prefix('cafeterias')->group(function () {
    Route::get('/', [CafeteriaController::class, 'index']);
    Route::post('/', [CafeteriaController::class, 'store']);
    Route::get('/encargado', [CafeteriaController::class, 'deEncargado']);
    Route::get('/cliente', [CafeteriaController::class, 'delCliente']);
    Route::get('/{id}', [CafeteriaController::class, 'show']);
    Route::put('/{id}', [CafeteriaController::class, 'update']);

    Route::get('/{idCafeteria}/productos', [ProductoController::class, 'index']);
    Route::post('/{idCafeteria}/productos', [ProductoController::class, 'store']);
    Route::put('/{idCafeteria}/productos/{id}', [ProductoController::class, 'update']);
    Route::patch('/{idCafeteria}/productos/{id}/estado', [ProductoController::class, 'cambiarEstado']);

    Route::get('/{idCafeteria}/pedidos', [PedidoController::class, 'porCafeteria']);
    Route::post('/{idCafeteria}/pedidos', [PedidoController::class, 'store']);
});

Route::prefix('pedidos')->group(function () {
    Route::get('/mios', [PedidoController::class, 'misPedidos']);
    Route::post('/checkin', [PedidoController::class, 'checkin']);
    Route::patch('/{idPedido}/aprobar', [PedidoController::class, 'aprobar']);
    Route::patch('/{idPedido}/rechazar', [PedidoController::class, 'rechazar']);
    Route::patch('/{idPedido}/estado', [PedidoController::class, 'cambiarEstado']);
    Route::patch('/{idPedido}/cancelar', [PedidoController::class, 'cancelar']);
});
