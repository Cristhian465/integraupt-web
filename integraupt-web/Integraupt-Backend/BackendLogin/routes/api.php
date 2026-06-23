<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes - BackendLogin (puerto 8081)
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/validate', [AuthController::class, 'validateToken']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/health', [AuthController::class, 'health']);
});
