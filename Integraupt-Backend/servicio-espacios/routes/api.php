<?php

use App\Http\Controllers\Api\EspacioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rutas API que replican los endpoints de EspacioControlador.java:
|
| GET    /api/espacios           → EspacioController@index
| GET    /api/espacios/escuelas  → EspacioController@escuelas
| GET    /api/espacios/{id}      → EspacioController@show
| POST   /api/espacios           → EspacioController@store
| PUT    /api/espacios/{id}      → EspacioController@update
| DELETE /api/espacios/{id}      → EspacioController@destroy
|
| NOTA: La ruta /escuelas se registra ANTES de /{id} para evitar que
| Laravel interprete "escuelas" como un parámetro numérico {id}.
|
*/

Route::prefix('espacios')->group(function () {
    // Ruta explícita para escuelas (debe ir antes de {id})
    Route::get('/escuelas', [EspacioController::class, 'escuelas']);

    // Rutas CRUD estándar
    Route::get('/', [EspacioController::class, 'index']);
    Route::get('/{id}', [EspacioController::class, 'show'])->where('id', '[0-9]+');
    Route::post('/', [EspacioController::class, 'store']);
    Route::put('/{id}', [EspacioController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/{id}', [EspacioController::class, 'destroy'])->where('id', '[0-9]+');
});
