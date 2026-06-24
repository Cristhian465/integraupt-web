<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GimnasioController;

Route::post('/ingreso', [GimnasioController::class, 'registrarIngreso']);
Route::post('/salida', [GimnasioController::class, 'registrarSalida']);
Route::get('/estado/{usuarioId}', [GimnasioController::class, 'estadoSesion']);
Route::get('/asistencias', [GimnasioController::class, 'listarAsistencias']);
