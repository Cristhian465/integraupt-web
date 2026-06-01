use App\Http\Controllers\AuditoriaController;

Route::get('/auditorias', [AuditoriaController::class, 'listar']);
Route::get('/auditorias/{id}', [AuditoriaController::class, 'detalle']);
Route::get('/auditorias/reserva/{reservaId}', [AuditoriaController::class, 'porReserva']);

Route::get('/auditorias/exportacion/pdf', [AuditoriaController::class, 'exportarPdf']);
Route::get('/auditorias/exportacion/excel', [AuditoriaController::class, 'exportarExcel']);