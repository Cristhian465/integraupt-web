<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MoodleController;

Route::post('/conectar', [MoodleController::class, 'conectar']);
Route::get('/sso/iniciar', [MoodleController::class, 'iniciarSso']);
Route::post('/sso/confirmar', [MoodleController::class, 'confirmarSso']);
Route::get('/estado', [MoodleController::class, 'estado']);
Route::delete('/desconectar', [MoodleController::class, 'desconectar']);

Route::get('/cursos', [MoodleController::class, 'cursos']);
Route::get('/cursos/{cursoId}/notas', [MoodleController::class, 'notas']);
Route::get('/eventos', [MoodleController::class, 'eventos']);
