<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PsicologiaController;

Route::get('/psicologos', [PsicologiaController::class, 'listarPsicologos']);
Route::get('/psicologos/{psicologoId}/bloques-disponibles', [PsicologiaController::class, 'bloquesDisponibles']);

Route::get('/citas', [PsicologiaController::class, 'listarCitas']);
Route::post('/citas', [PsicologiaController::class, 'registrarCita']);
Route::delete('/citas/{id}', [PsicologiaController::class, 'cancelarCita']);
