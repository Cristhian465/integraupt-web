<?php

namespace App\Http\Controllers;

use App\Http\Requests\InscripcionRequest;
use App\Models\EventoInscripcion;
use App\Services\InscripcionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class InscripcionController extends Controller
{
    public function __construct(private InscripcionService $inscripcionService)
    {
    }

    public function index($idEvento)
    {
        $inscripciones = $this->inscripcionService->listar((int) $idEvento)
            ->map(fn (EventoInscripcion $i) => $this->mapear($i))
            ->all();

        return response()->json($inscripciones);
    }

    public function store(InscripcionRequest $request, $idEvento)
    {
        try {
            $inscripcion = $this->inscripcionService->inscribir((int) $idEvento, (int) $request->input('usuarioId'));
            return response()->json($this->mapear($inscripcion), 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Evento no encontrado.'], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy($idEvento, $idInscripcion)
    {
        try {
            $inscripcion = $this->inscripcionService->cancelar((int) $idInscripcion);
            return response()->json($this->mapear($inscripcion));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Inscripcion no encontrada.'], 404);
        }
    }

    public function checkin(Request $request, $idEvento)
    {
        $request->validate(['codigoQr' => 'required|string']);

        try {
            $inscripcion = $this->inscripcionService->checkin($request->input('codigoQr'));
            return response()->json($this->mapear($inscripcion));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function mapear(EventoInscripcion $inscripcion): array
    {
        return [
            'id' => $inscripcion->IdInscripcion,
            'eventoId' => $inscripcion->IdEvento,
            'usuarioId' => $inscripcion->IdUsuario,
            'usuarioNombre' => $inscripcion->usuario?->NombreCompleto,
            'tipoUsuario' => $inscripcion->TipoUsuario,
            'estado' => $inscripcion->Estado,
            'codigoQr' => $inscripcion->CodigoQr,
            'fechaInscripcion' => optional($inscripcion->FechaInscripcion)->toIso8601String(),
        ];
    }
}
