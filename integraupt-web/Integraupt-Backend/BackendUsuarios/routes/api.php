<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EstudianteController;
use App\Http\Controllers\Api\DocenteController;
use App\Http\Controllers\Api\AdministrativoController;
use App\Http\Controllers\Api\CatalogoController;

/*
|--------------------------------------------------------------------------
| API Routes - BackendUsuarios (puerto 8092)
|--------------------------------------------------------------------------
*/

Route::prefix('estudiantes')->group(function () {
    Route::get('/', [EstudianteController::class, 'index']);
    Route::get('/{id}', [EstudianteController::class, 'show'])->whereNumber('id');
    Route::post('/', [EstudianteController::class, 'store']);
    Route::put('/{id}', [EstudianteController::class, 'update'])->whereNumber('id');
    Route::patch('/{id}/estado', [EstudianteController::class, 'estado'])->whereNumber('id');
    Route::delete('/{id}', [EstudianteController::class, 'destroy'])->whereNumber('id');
});

Route::prefix('docentes')->group(function () {
    Route::get('/', [DocenteController::class, 'index']);
    Route::get('/{id}', [DocenteController::class, 'show'])->whereNumber('id');
    Route::post('/', [DocenteController::class, 'store']);
    Route::put('/{id}', [DocenteController::class, 'update'])->whereNumber('id');
    Route::patch('/{id}/estado', [DocenteController::class, 'estado'])->whereNumber('id');
    Route::delete('/{id}', [DocenteController::class, 'destroy'])->whereNumber('id');
});

Route::prefix('administrativos')->group(function () {
    Route::get('/', [AdministrativoController::class, 'index']);
    Route::get('/{id}', [AdministrativoController::class, 'show'])->whereNumber('id');
    Route::post('/', [AdministrativoController::class, 'store']);
    Route::put('/{id}', [AdministrativoController::class, 'update'])->whereNumber('id');
    Route::patch('/{id}/estado', [AdministrativoController::class, 'estado'])->whereNumber('id');
    Route::delete('/{id}', [AdministrativoController::class, 'destroy'])->whereNumber('id');
});

Route::prefix('catalogos')->group(function () {
    Route::get('/tipos-documento', [CatalogoController::class, 'tiposDocumento']);
    Route::get('/roles', [CatalogoController::class, 'roles']);
    Route::get('/escuelas', [CatalogoController::class, 'escuelas']);
});
