<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\EspacioController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('reservas', ReservaController::class);
Route::apiResource('espacios', EspacioController::class)->only(['index', 'show']);
