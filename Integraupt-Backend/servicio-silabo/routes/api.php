<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SilaboController;
use App\Http\Controllers\UnidadController;
use App\Http\Controllers\TemaController;
use App\Http\Controllers\AvanceController;

// ─── Parseo de PDF (debe ir ANTES de la ruta /{id}) ────────────────────────
Route::post('/silabos/parsear-pdf',   [SilaboController::class, 'parsearPdf']);

// ─── Sílabos ───────────────────────────────────────────────────────────────
Route::get('/silabos',                [SilaboController::class, 'index']);
Route::post('/silabos',               [SilaboController::class, 'store']);
Route::get('/silabos/{id}',           [SilaboController::class, 'show']);
Route::post('/silabos/{id}',          [SilaboController::class, 'update']);
Route::delete('/silabos/{id}',        [SilaboController::class, 'destroy']);
Route::get('/silabos/{id}/avance',    [SilaboController::class, 'avance']);

// ─── Unidades ──────────────────────────────────────────────────────────────
Route::post('/silabos/{silaboId}/unidades', [UnidadController::class, 'store']);
Route::put('/unidades/{id}',                [UnidadController::class, 'update']);
Route::delete('/unidades/{id}',             [UnidadController::class, 'destroy']);

// ─── Temas ─────────────────────────────────────────────────────────────────
Route::post('/unidades/{unidadId}/temas',   [TemaController::class, 'store']);
Route::put('/temas/{id}',                   [TemaController::class, 'update']);
Route::delete('/temas/{id}',                [TemaController::class, 'destroy']);

// ─── Avances ───────────────────────────────────────────────────────────────
Route::get('/avances',                      [AvanceController::class, 'index']);
Route::post('/avances',                     [AvanceController::class, 'store']);
Route::get('/avances/docente/{docenteId}',  [AvanceController::class, 'byDocente']);
Route::put('/avances/{id}/estado',          [AvanceController::class, 'updateEstado']);
Route::delete('/avances/{id}',              [AvanceController::class, 'destroy']);
