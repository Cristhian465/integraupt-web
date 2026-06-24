<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PromedioController;

Route::post('/promedio/calcular', [PromedioController::class, 'calcular']);
