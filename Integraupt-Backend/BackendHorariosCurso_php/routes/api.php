<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HorarioCursoController;
use App\Http\Controllers\HorarioCatalogoController;

Route::prefix('horarios')->group(function () {
    Route::get('/', [HorarioCursoController::class, 'listar']);
    Route::get('/meta', [HorarioCursoController::class, 'obtenerMeta']);
    Route::get('/{id}', [HorarioCursoController::class, 'obtenerPorId'])->whereNumber('id');
    Route::post('/', [HorarioCursoController::class, 'crear']);
    Route::put('/{id}', [HorarioCursoController::class, 'actualizar'])->whereNumber('id');
    Route::delete('/{id}', [HorarioCursoController::class, 'eliminar'])->whereNumber('id');

    Route::prefix('catalogos')->group(function () {
        Route::get('/cursos', [HorarioCatalogoController::class, 'listarCursos']);
        Route::get('/docentes', [HorarioCatalogoController::class, 'listarDocentes']);
        Route::get('/espacios', [HorarioCatalogoController::class, 'listarEspacios']);
        Route::get('/bloques', [HorarioCatalogoController::class, 'listarBloques']);
    });
});
