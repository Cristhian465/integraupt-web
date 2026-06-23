<?php

use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\EspacioController;
use App\Http\Controllers\ReservaController;
use Illuminate\Support\Facades\Route;

Route::prefix('catalogos')->group(function () {
    Route::get('/facultades', [CatalogoController::class, 'listarFacultades']);
    Route::get('/escuelas', [CatalogoController::class, 'listarEscuelas']);
});

Route::prefix('espacios')->group(function () {
    Route::get('/', [EspacioController::class, 'listarEspacios']);
    Route::get('/{espacioId}/cursos', [EspacioController::class, 'listarCursosPorEspacio']);
    Route::get('/{espacioId}/bloques', [EspacioController::class, 'listarBloquesPorEspacio']);
});

Route::prefix('reservas')->group(function () {
    Route::get('/', [ReservaController::class, 'listarReservas']);
    Route::post('/', [ReservaController::class, 'crearReserva']);
    Route::get('/usuario/{usuarioId}/resumen', [ReservaController::class, 'obtenerResumenReservasUsuario']);
    Route::get('/usuario/{usuarioId}', [ReservaController::class, 'obtenerReservasPorUsuario']);
    Route::get('/{id}/qr', [ReservaController::class, 'obtenerQrReserva']);
    Route::get('/{id}', [ReservaController::class, 'obtenerReserva']);
    Route::put('/{id}', [ReservaController::class, 'actualizarReserva']);
    Route::delete('/{id}', [ReservaController::class, 'eliminarReserva']);
});
