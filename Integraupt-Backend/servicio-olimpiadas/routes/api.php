<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\DisciplinaController;
use App\Http\Controllers\EdicionController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\ResultadoController;

Route::prefix('catalogos')->group(function () {
    Route::get('/facultades', [CatalogoController::class, 'listarFacultades']);
    Route::get('/escuelas', [CatalogoController::class, 'listarEscuelas']);
});

Route::prefix('disciplinas')->group(function () {
    Route::get('/', [DisciplinaController::class, 'index']);
    Route::post('/', [DisciplinaController::class, 'store']);
    Route::put('/{id}', [DisciplinaController::class, 'update']);
    Route::patch('/{id}/estado', [DisciplinaController::class, 'cambiarEstado']);
});

Route::prefix('ediciones')->group(function () {
    Route::get('/', [EdicionController::class, 'index']);
    Route::get('/actual', [EdicionController::class, 'actual']);
    Route::get('/{id}', [EdicionController::class, 'show']);
    Route::post('/', [EdicionController::class, 'store']);
    Route::put('/{id}', [EdicionController::class, 'update']);
    Route::patch('/{id}/estado', [EdicionController::class, 'cambiarEstado']);
    Route::patch('/{id}/inscripcion/abrir', [EdicionController::class, 'abrirInscripcion']);
    Route::patch('/{id}/inscripcion/cerrar', [EdicionController::class, 'cerrarInscripcion']);

    Route::get('/{id}/disciplinas', [EdicionController::class, 'listarDisciplinas']);
    Route::post('/{id}/disciplinas', [EdicionController::class, 'vincularDisciplina']);
});

Route::prefix('edicion-disciplinas')->group(function () {
    Route::put('/{edicionDisciplinaId}', [EdicionController::class, 'actualizarVinculo']);
    Route::patch('/{edicionDisciplinaId}/estado', [EdicionController::class, 'cambiarEstadoVinculo']);

    Route::get('/{edicionDisciplinaId}/fixture', [ResultadoController::class, 'fixture']);
    Route::get('/{edicionDisciplinaId}/tabla', [ResultadoController::class, 'tabla']);
    Route::get('/{edicionDisciplinaId}/participantes', [ResultadoController::class, 'participantes']);
});

Route::prefix('inscripciones')->group(function () {
    Route::post('/', [InscripcionController::class, 'store']);
    Route::patch('/{id}/cancelar', [InscripcionController::class, 'cancelar']);
    Route::get('/mias', [InscripcionController::class, 'porUsuario']);
    Route::get('/edicion-disciplina/{edicionDisciplinaId}', [InscripcionController::class, 'porEdicionDisciplina']);
});

Route::prefix('resultados')->group(function () {
    Route::post('/', [ResultadoController::class, 'store']);
    Route::put('/{id}', [ResultadoController::class, 'update']);
});
