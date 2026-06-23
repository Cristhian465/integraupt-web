<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportesController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('reportes')->group(function () {
    Route::get('/estadisticas-generales', [ReportesController::class, 'obtenerEstadisticasGenerales']);
    Route::get('/uso-espacios', [ReportesController::class, 'obtenerUsoEspacios']);
    Route::get('/reservas-mes', [ReportesController::class, 'obtenerReservasPorMes']);
    Route::get('/exportacion/pdf', [ReportesController::class, 'exportarPDF']);
    Route::get('/exportacion/excel', [ReportesController::class, 'exportarExcel']);
});
