<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PsicologiaController;

Route::get('/psicologos', [PsicologiaController::class, 'listarPsicologos']);
Route::get('/psicologos/{psicologoId}/bloques-disponibles', [PsicologiaController::class, 'bloquesDisponibles']);

Route::get('/citas', [PsicologiaController::class, 'listarCitas']);
Route::post('/citas', [PsicologiaController::class, 'registrarCita']);
Route::delete('/citas/{id}', [PsicologiaController::class, 'cancelarCita']);

Route::get('/admin/citas', [PsicologiaController::class, 'listarCitasAdmin']);
Route::patch('/admin/citas/{id}/estado', [PsicologiaController::class, 'cambiarEstadoCita']);

Route::get('/admin/psicologos', [PsicologiaController::class, 'listarPsicologosAdmin']);
Route::post('/admin/psicologos', [PsicologiaController::class, 'crearPsicologo']);
Route::patch('/admin/psicologos/{id}', [PsicologiaController::class, 'actualizarPsicologo']);
