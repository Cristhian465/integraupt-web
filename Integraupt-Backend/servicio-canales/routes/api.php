<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CanalController;
use App\Http\Controllers\MensajeController;
use App\Http\Controllers\TemaController;
use App\Http\Controllers\ReaccionController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\EscribiendoController;

Route::prefix('catalogos')->group(function () {
    Route::get('/usuarios', [UsuarioController::class, 'buscar']);
});

Route::post('/upload', [FileController::class, 'upload']);
Route::get('/preview', [PreviewController::class, 'fetch']);

Route::prefix('canales')->group(function () {
    Route::get('/', [CanalController::class, 'index']);
    Route::post('/', [CanalController::class, 'store']);
    Route::get('/{id}', [CanalController::class, 'show']);
    Route::put('/{id}', [CanalController::class, 'update']);
    Route::patch('/{id}/estado', [CanalController::class, 'cambiarEstado']);

    Route::post('/{id}/miembros', [CanalController::class, 'agregarMiembros']);
    Route::delete('/{id}/miembros/{idUsuario}', [CanalController::class, 'quitarMiembro']);

    Route::get('/{idCanal}/temas', [TemaController::class, 'index']);
    Route::post('/{idCanal}/temas', [TemaController::class, 'store']);
    Route::put('/{idCanal}/temas/{idTema}', [TemaController::class, 'update']);
    Route::delete('/{idCanal}/temas/{idTema}', [TemaController::class, 'destroy']);

    Route::get('/{idCanal}/mensajes', [MensajeController::class, 'index']);
    Route::post('/{idCanal}/mensajes', [MensajeController::class, 'store']);
    Route::put('/{idCanal}/mensajes/{idMensaje}', [MensajeController::class, 'update']);
    Route::delete('/{idCanal}/mensajes/{idMensaje}', [MensajeController::class, 'destroy']);

    Route::post('/{idCanal}/mensajes/{idMensaje}/reacciones', [ReaccionController::class, 'toggle']);

    Route::post('/{idCanal}/escribiendo', [EscribiendoController::class, 'marcar']);
    Route::get('/{idCanal}/escribiendo', [EscribiendoController::class, 'listar']);
});
