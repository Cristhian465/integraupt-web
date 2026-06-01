use App\Http\Controllers\SancionController;

Route::prefix('sanciones')->group(function () {

    // crear sanción
    Route::post('/', [SancionController::class, 'registrar']);

    // listar todas
    Route::get('/', [SancionController::class, 'obtenerTodas']);

    // activas
    Route::get('/activas', [SancionController::class, 'obtenerActivas']);

    // levantar sanción
    Route::patch('/{id}/levantar', [SancionController::class, 'levantar']);

    // verificación completa
    Route::get('/verificacion', [SancionController::class, 'verificar']);

    // boolean simple
    Route::get('/estado', [SancionController::class, 'tieneSancionActiva']);
});