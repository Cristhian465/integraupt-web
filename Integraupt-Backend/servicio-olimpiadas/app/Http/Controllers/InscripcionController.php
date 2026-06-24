<?php

namespace App\Http\Controllers;

use App\Http\Requests\InscripcionRequest;
use App\Models\OlimpiadaInscripcion;
use App\Services\InscripcionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use InvalidArgumentException;
use LogicException;

class InscripcionController extends Controller
{
    public function __construct(private InscripcionService $inscripcionService)
    {
    }

    public function store(InscripcionRequest $request)
    {
        try {
            $inscripcion = $this->inscripcionService->inscribir($request->validated());
            return response()->json($this->mapear($inscripcion), 201);
        } catch (\Exception $e) {
            return $this->manejarExcepcion($e);
        }
    }

    public function cancelar(Request $request, $id)
    {
        $request->validate(['usuarioId' => 'required|integer']);

        try {
            $inscripcion = $this->inscripcionService->cancelar((int) $id, (int) $request->input('usuarioId'));
            return response()->json($this->mapear($inscripcion));
        } catch (\Exception $e) {
            return $this->manejarExcepcion($e);
        }
    }

    public function porUsuario(Request $request)
    {
        $request->validate(['usuarioId' => 'required|integer']);

        $inscripciones = $this->inscripcionService->listarPorUsuario((int) $request->query('usuarioId'))
            ->map(function (OlimpiadaInscripcion $inscripcion) {
                return [
                    'id' => $inscripcion->IdInscripcion,
                    'edicionDisciplinaId' => $inscripcion->EdicionDisciplina,
                    'disciplinaNombre' => $inscripcion->edicionDisciplina?->disciplina?->Nombre,
                    'edicionNombre' => $inscripcion->edicionDisciplina?->edicion?->Nombre,
                    'facultadId' => $inscripcion->Facultad,
                    'facultadNombre' => $inscripcion->facultad?->Nombre,
                    'estado' => $inscripcion->Estado,
                    'fechaInscripcion' => $inscripcion->FechaInscripcion,
                ];
            })->all();

        return response()->json($inscripciones);
    }

    public function porEdicionDisciplina(Request $request, $edicionDisciplinaId)
    {
        $facultadId = $request->query('facultadId') !== null ? (int) $request->query('facultadId') : null;

        $inscripciones = $this->inscripcionService->listarPorEdicionDisciplina((int) $edicionDisciplinaId, $facultadId)
            ->map(function (OlimpiadaInscripcion $inscripcion) {
                return [
                    'id' => $inscripcion->IdInscripcion,
                    'usuarioId' => $inscripcion->Usuario,
                    'usuarioNombre' => $inscripcion->usuario?->nombre_completo,
                    'facultadId' => $inscripcion->Facultad,
                    'facultadNombre' => $inscripcion->facultad?->Nombre,
                    'estado' => $inscripcion->Estado,
                    'fechaInscripcion' => $inscripcion->FechaInscripcion,
                ];
            })->all();

        return response()->json($inscripciones);
    }

    private function mapear(OlimpiadaInscripcion $inscripcion): array
    {
        return [
            'id' => $inscripcion->IdInscripcion,
            'edicionDisciplinaId' => $inscripcion->EdicionDisciplina,
            'usuarioId' => $inscripcion->Usuario,
            'facultadId' => $inscripcion->Facultad,
            'estado' => $inscripcion->Estado,
            'observaciones' => $inscripcion->Observaciones,
            'fechaInscripcion' => $inscripcion->FechaInscripcion,
        ];
    }

    private function manejarExcepcion(\Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
        if ($e instanceof InvalidArgumentException) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
        if ($e instanceof LogicException) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
        return response()->json(['message' => $e->getMessage()], 500);
    }
}
