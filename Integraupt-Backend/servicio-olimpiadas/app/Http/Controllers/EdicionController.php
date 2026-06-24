<?php

namespace App\Http\Controllers;

use App\Http\Requests\EdicionDisciplinaRequest;
use App\Http\Requests\EdicionRequest;
use App\Models\OlimpiadaEdicion;
use App\Models\OlimpiadaEdicionDisciplina;
use App\Services\EdicionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use InvalidArgumentException;

class EdicionController extends Controller
{
    public function __construct(private EdicionService $edicionService)
    {
    }

    public function index()
    {
        $ediciones = $this->edicionService->listar()->map(fn (OlimpiadaEdicion $e) => $this->mapear($e))->all();
        return response()->json($ediciones);
    }

    public function actual()
    {
        $edicion = $this->edicionService->obtenerActual();
        return response()->json($edicion ? $this->mapear($edicion) : null);
    }

    public function show($id)
    {
        try {
            $edicion = $this->edicionService->obtener((int) $id);
            return response()->json($this->mapear($edicion));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function store(EdicionRequest $request)
    {
        try {
            $edicion = $this->edicionService->crear($request->validated());
            return response()->json($this->mapear($edicion), 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function update(EdicionRequest $request, $id)
    {
        try {
            $edicion = $this->edicionService->actualizar((int) $id, $request->validated());
            return response()->json($this->mapear($edicion));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:planificada,inscripcion_abierta,inscripcion_cerrada,en_curso,finalizada,cancelada',
        ]);

        try {
            $edicion = $this->edicionService->cambiarEstado((int) $id, $request->input('estado'));
            return response()->json($this->mapear($edicion));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function abrirInscripcion(Request $request, $id)
    {
        $request->validate(['fechaCierreInscripcion' => 'nullable|date']);

        try {
            $edicion = $this->edicionService->abrirInscripcion((int) $id, $request->input('fechaCierreInscripcion'));
            return response()->json($this->mapear($edicion));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function cerrarInscripcion($id)
    {
        try {
            $edicion = $this->edicionService->cerrarInscripcion((int) $id);
            return response()->json($this->mapear($edicion));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function listarDisciplinas($id)
    {
        try {
            $disciplinas = $this->edicionService->listarDisciplinas((int) $id)
                ->map(fn (OlimpiadaEdicionDisciplina $vinculo) => $this->mapearVinculo($vinculo))
                ->all();
            return response()->json($disciplinas);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function vincularDisciplina(EdicionDisciplinaRequest $request, $id)
    {
        try {
            $vinculo = $this->edicionService->vincularDisciplina((int) $id, $request->validated());
            $vinculo->load('disciplina');
            return response()->json($this->mapearVinculo($vinculo), 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function actualizarVinculo(EdicionDisciplinaRequest $request, $edicionDisciplinaId)
    {
        try {
            $vinculo = $this->edicionService->actualizarVinculo((int) $edicionDisciplinaId, $request->validated());
            $vinculo->load('disciplina');
            return response()->json($this->mapearVinculo($vinculo));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function cambiarEstadoVinculo(Request $request, $edicionDisciplinaId)
    {
        $request->validate(['estado' => 'required|in:activa,inactiva']);

        try {
            $vinculo = $this->edicionService->cambiarEstadoVinculo((int) $edicionDisciplinaId, $request->input('estado'));
            $vinculo->load('disciplina');
            return response()->json($this->mapearVinculo($vinculo));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    private function mapear(OlimpiadaEdicion $edicion): array
    {
        return [
            'id' => $edicion->IdEdicion,
            'nombre' => $edicion->Nombre,
            'anioInicio' => $edicion->AnioInicio,
            'semestreInicio' => $edicion->SemestreInicio,
            'anioFin' => $edicion->AnioFin,
            'semestreFin' => $edicion->SemestreFin,
            'estado' => $edicion->Estado,
            'fechaAperturaInscripcion' => $edicion->FechaAperturaInscripcion,
            'fechaCierreInscripcion' => $edicion->FechaCierreInscripcion,
            'fechaInicioJuegos' => $edicion->FechaInicioJuegos,
            'fechaFinJuegos' => $edicion->FechaFinJuegos,
            'observaciones' => $edicion->Observaciones,
            'inscripcionAbierta' => $edicion->inscripcionAbierta(),
        ];
    }

    private function mapearVinculo(OlimpiadaEdicionDisciplina $vinculo): array
    {
        return [
            'id' => $vinculo->IdEdicionDisciplina,
            'edicionId' => $vinculo->Edicion,
            'disciplinaId' => $vinculo->Disciplina,
            'disciplinaNombre' => $vinculo->disciplina?->Nombre,
            'tipoParticipacion' => $vinculo->disciplina?->TipoParticipacion,
            'cupoMaximoPorFacultad' => $vinculo->CupoMaximoPorFacultad,
            'reglasEspecificas' => $vinculo->ReglasEspecificas,
            'estado' => $vinculo->Estado,
            'inscritosActivos' => $vinculo->inscritosActivos(),
        ];
    }
}
