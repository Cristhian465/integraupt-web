<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PoliclinicoController;

Route::get('/tipos-atencion', [PoliclinicoController::class, 'listarTiposAtencion']);
Route::get('/medicos', [PoliclinicoController::class, 'listarMedicos']);
Route::get('/medicos/{medicoId}/bloques-disponibles', [PoliclinicoController::class, 'bloquesDisponibles']);

Route::get('/citas', [PoliclinicoController::class, 'listarCitas']);
Route::post('/citas', [PoliclinicoController::class, 'registrarCita']);
Route::delete('/citas/{id}', [PoliclinicoController::class, 'cancelarCita']);

Route::get('/admin/citas', [PoliclinicoController::class, 'listarCitasAdmin']);
Route::patch('/admin/citas/{id}/estado', [PoliclinicoController::class, 'cambiarEstadoCita']);

Route::get('/admin/tipos-atencion', [PoliclinicoController::class, 'listarTiposAtencionAdmin']);
Route::post('/admin/tipos-atencion', [PoliclinicoController::class, 'crearTipoAtencion']);
Route::patch('/admin/tipos-atencion/{id}', [PoliclinicoController::class, 'actualizarTipoAtencion']);

Route::get('/admin/medicos', [PoliclinicoController::class, 'listarMedicosAdmin']);
Route::post('/admin/medicos', [PoliclinicoController::class, 'crearMedico']);
Route::patch('/admin/medicos/{id}', [PoliclinicoController::class, 'actualizarMedico']);
