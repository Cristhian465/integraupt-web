<?php

use App\Http\Controllers\FormularioReservaController;
use App\Http\Controllers\InterfazEspaciosController;
use Illuminate\Support\Facades\Route;

Route::get('/', [InterfazEspaciosController::class, 'mostrarInterfaz']);
Route::get('/espacios', [InterfazEspaciosController::class, 'mostrarInterfaz']);
Route::get('/reservas/formulario', [FormularioReservaController::class, 'mostrarFormulario']);
