<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\SancionController;
use App\Http\Controllers\UsuarioBusquedaController;

Route::prefix('catalogos')->group(function () {
    Route::get('/facultades', [CatalogoController::class, 'listarFacultades']);
    Route::get('/escuelas', [CatalogoController::class, 'listarEscuelas']);
});

Route::prefix('usuarios')->group(function () {
    Route::get('/busqueda', [UsuarioBusquedaController::class, 'buscarUsuarios']);
});

Route::prefix('sanciones')->group(function () {
    Route::post('/', [SancionController::class, 'registrar']);

    // listar todas
    Route::get('/', [SancionController::class, 'obtenerTodas']);

    // activas
    Route::get('/activas', [SancionController::class, 'obtenerActivas']);

    // levantar sanción
    Route::patch('/{id}/levantar', [SancionController::class, 'levantar']);

    // verificación completa
    Route::get('/verificacion', [SancionController::class, 'verificar']);

    // boolean simple
    Route::get('/estado', [SancionController::class, 'tieneSancionActiva']);
});