<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditoriaController;

Route::get('/auditoria', [AuditoriaController::class, 'index']);
