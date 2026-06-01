use App\Http\Controllers\IncidenciaController;

Route::prefix('incidencias')->group(function () {

    Route::post('/', [IncidenciaController::class, 'registrarIncidencia']);

    Route::get('/', [IncidenciaController::class, 'listarParaGestion']);

    Route::get('/reserva/{reservaId}', [IncidenciaController::class, 'listarPorReserva']);

    Route::get('/reserva/{reservaId}/disponibilidad', [IncidenciaController::class, 'verificarDisponibilidad']);

    Route::get('/usuario/{usuarioId}/reservas', [IncidenciaController::class, 'listarReservasPorUsuario']);
});