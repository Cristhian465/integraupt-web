<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResultadoRequest;
use App\Models\OlimpiadaInscripcion;
use App\Models\OlimpiadaParticipacionFacultad;
use App\Models\OlimpiadaResultado;
use App\Services\ResultadoService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ResultadoController extends Controller
{
    public function __construct(private ResultadoService $resultadoService)
    {
    }

    public function fixture($edicionDisciplinaId)
    {
        $resultados = $this->resultadoService->listarFixture((int) $edicionDisciplinaId)
            ->map(fn (OlimpiadaResultado $r) => $this->mapearResultado($r))
            ->all();

        return response()->json($resultados);
    }

    public function tabla($edicionDisciplinaId)
    {
        $tabla = $this->resultadoService->listarTabla((int) $edicionDisciplinaId)
            ->map(fn (OlimpiadaParticipacionFacultad $p) => $this->mapearParticipacion($p))
            ->all();

        return response()->json($tabla);
    }

    public function participantes($edicionDisciplinaId)
    {
        $participantes = $this->resultadoService->participantesPorFacultad((int) $edicionDisciplinaId)
            ->map(fn (OlimpiadaInscripcion $i) => [
                'inscripcionId' => $i->IdInscripcion,
                'usuarioId' => $i->Usuario,
                'usuarioNombre' => $i->usuario?->nombre_completo,
                'facultadId' => $i->Facultad,
                'facultadNombre' => $i->facultad?->Nombre,
            ])->all();

        return response()->json($participantes);
    }

    public function store(ResultadoRequest $request)
    {
        $resultado = $this->resultadoService->crear($request->validated());
        return response()->json($this->mapearResultado($resultado), 201);
    }

    public function update(ResultadoRequest $request, $id)
    {
        try {
            $resultado = $this->resultadoService->actualizar((int) $id, $request->validated());
            return response()->json($this->mapearResultado($resultado));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    private function mapearResultado(OlimpiadaResultado $resultado): array
    {
        return [
            'id' => $resultado->IdResultado,
            'edicionDisciplinaId' => $resultado->EdicionDisciplina,
            'facultadLocalId' => $resultado->FacultadLocal,
            'facultadLocalNombre' => $resultado->facultadLocal?->Nombre,
            'facultadVisitanteId' => $resultado->FacultadVisitante,
            'facultadVisitanteNombre' => $resultado->facultadVisitante?->Nombre,
            'fase' => $resultado->Fase,
            'grupo' => $resultado->Grupo,
            'fechaPartido' => $resultado->FechaPartido,
            'puntajeLocal' => $resultado->PuntajeLocal,
            'puntajeVisitante' => $resultado->PuntajeVisitante,
            'facultadGanadoraId' => $resultado->FacultadGanadora,
            'facultadGanadoraNombre' => $resultado->facultadGanadora?->Nombre,
            'estado' => $resultado->Estado,
            'observaciones' => $resultado->Observaciones,
        ];
    }

    private function mapearParticipacion(OlimpiadaParticipacionFacultad $p): array
    {
        return [
            'facultadId' => $p->Facultad,
            'facultadNombre' => $p->facultad?->Nombre,
            'facultadAbreviatura' => $p->facultad?->Abreviatura,
            'partidosJugados' => $p->PartidosJugados,
            'partidosGanados' => $p->PartidosGanados,
            'partidosEmpatados' => $p->PartidosEmpatados,
            'partidosPerdidos' => $p->PartidosPerdidos,
            'puntosAFavor' => $p->PuntosAFavor,
            'puntosEnContra' => $p->PuntosEnContra,
            'puntos' => $p->Puntos,
            'posicion' => $p->Posicion,
        ];
    }
}
