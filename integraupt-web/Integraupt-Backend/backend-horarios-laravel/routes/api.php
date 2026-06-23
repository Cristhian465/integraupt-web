<?php

use App\Http\Controllers\Api\HorarioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes – BackendHorarios (puerto 8085)
|--------------------------------------------------------------------------
| Réplica del HorarioController Java:
|   @RestController
|   @RequestMapping("/api/horarios")
|   @CrossOrigin(origins = "*")
|--------------------------------------------------------------------------
*/

// Rutas fijas ANTES de la ruta con parámetro {id}
Route::get('horarios', [HorarioController::class, 'index']);
Route::get('horarios/disponibles', [HorarioController::class, 'disponibles']);
Route::get('horarios/ocupados', [HorarioController::class, 'ocupados']);
Route::get('horarios/espacio/{espacioId}', [HorarioController::class, 'porEspacio']);
Route::get('horarios/espacio/{espacioId}/semanal', [HorarioController::class, 'semanal']);
Route::get('horarios/dia/{diaSemana}', [HorarioController::class, 'porDia']);

// Rutas con parámetro {id} DESPUÉS de las rutas fijas
Route::get('horarios/{id}', [HorarioController::class, 'show'])->whereNumber('id');
Route::post('horarios', [HorarioController::class, 'store']);
Route::put('horarios/{id}', [HorarioController::class, 'update'])->whereNumber('id');
Route::patch('horarios/{id}/ocupacion', [HorarioController::class, 'actualizarOcupacion'])->whereNumber('id');
Route::delete('horarios/{id}', [HorarioController::class, 'destroy'])->whereNumber('id');
