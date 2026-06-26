<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ElectionController;

// Estudiantes
Route::get('/elecciones/activa', [ElectionController::class, 'activeElection']);
Route::post('/elecciones/{id}/votar', [ElectionController::class, 'castVote']);
Route::post('/elecciones/{id}/mi-voto', [ElectionController::class, 'myVote']);

// Administradores
Route::get('/elecciones', [ElectionController::class, 'index']);
Route::post('/elecciones', [ElectionController::class, 'store']);
Route::post('/elecciones/{id}/partidos', [ElectionController::class, 'storeParty']);
Route::post('/elecciones/{id}/toggle', [ElectionController::class, 'toggleActive']);
Route::get('/elecciones/{id}/resultados', [ElectionController::class, 'results']);