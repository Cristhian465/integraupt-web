<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\InscripcionController;

Route::prefix('catalogos')->group(function () {
    Route::get('/facultades', [CatalogoController::class, 'listarFacultades']);
    Route::get('/escuelas', [CatalogoController::class, 'listarEscuelas']);
    Route::get('/espacios', [CatalogoController::class, 'listarEspacios']);
    Route::get('/estudiantes', [CatalogoController::class, 'buscarEstudiantes']);
    Route::get('/docentes', [CatalogoController::class, 'buscarDocentes']);
    Route::get('/mi-facultad', [CatalogoController::class, 'miFacultad']);
});

Route::get('/mis-inscripciones', [InscripcionController::class, 'misInscripciones']);

Route::prefix('eventos')->group(function () {
    Route::get('/', [EventoController::class, 'index']);
    Route::post('/', [EventoController::class, 'store']);
    Route::get('/{id}', [EventoController::class, 'show']);
    Route::put('/{id}', [EventoController::class, 'update']);
    Route::patch('/{id}/estado', [EventoController::class, 'cambiarEstado']);
    Route::get('/{id}/reporte', [EventoController::class, 'reporte']);

    Route::get('/{idEvento}/inscripciones', [InscripcionController::class, 'index']);
    Route::post('/{idEvento}/inscripciones', [InscripcionController::class, 'store']);
    Route::delete('/{idEvento}/inscripciones/{idInscripcion}', [InscripcionController::class, 'destroy']);
    Route::post('/{idEvento}/checkin', [InscripcionController::class, 'checkin']);
});
