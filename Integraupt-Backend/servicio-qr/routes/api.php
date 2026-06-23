<?php

use App\Http\Controllers\ReservaQrController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/qr/reservas')->group(function () {
    Route::post('/', [ReservaQrController::class, 'generar']);
    Route::get('/reserva/{reservaId}', [ReservaQrController::class, 'obtenerPorReserva']);
    Route::get('/{token}', [ReservaQrController::class, 'verificar']);
});
