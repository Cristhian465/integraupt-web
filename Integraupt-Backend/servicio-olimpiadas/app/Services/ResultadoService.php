<?php

namespace App\Services;

use App\Models\OlimpiadaEdicionDisciplina;
use App\Models\OlimpiadaInscripcion;
use App\Models\OlimpiadaParticipacionFacultad;
use App\Models\OlimpiadaResultado;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class ResultadoService
{
    public function listarFixture(int $edicionDisciplinaId): Collection
    {
        return OlimpiadaResultado::with(['facultadLocal', 'facultadVisitante', 'facultadGanadora'])
            ->where('EdicionDisciplina', $edicionDisciplinaId)
            ->orderBy('Fase')
            ->orderBy('Grupo')
            ->orderBy('FechaPartido')
            ->get();
    }

    public function listarTabla(int $edicionDisciplinaId): Collection
    {
        return OlimpiadaParticipacionFacultad::with('facultad')
            ->where('EdicionDisciplina', $edicionDisciplinaId)
            ->orderBy('Posicion')
            ->get();
    }

    public function crear(array $datos): OlimpiadaResultado
    {
        $edicionDisciplinaId = (int) $datos['edicionDisciplinaId'];

        if (!OlimpiadaEdicionDisciplina::where('IdEdicionDisciplina', $edicionDisciplinaId)->exists()) {
            throw new ModelNotFoundException('La disciplina indicada no existe para esta edición.');
        }

        $resultado = OlimpiadaResultado::create([
            'EdicionDisciplina' => $edicionDisciplinaId,
            'FacultadLocal' => $datos['facultadLocalId'],
            'FacultadVisitante' => $datos['facultadVisitanteId'] ?? null,
            'Fase' => $datos['fase'] ?? 'grupos',
            'Grupo' => $datos['grupo'] ?? null,
            'FechaPartido' => $datos['fechaPartido'] ?? null,
            'PuntajeLocal' => $datos['puntajeLocal'] ?? null,
            'PuntajeVisitante' => $datos['puntajeVisitante'] ?? null,
            'Estado' => $datos['estado'] ?? 'programado',
            'Observaciones' => $datos['observaciones'] ?? null,
        ]);

        $this->actualizarGanadorYTabla($resultado);

        return $resultado;
    }

    public function actualizar(int $id, array $datos): OlimpiadaResultado
    {
        $resultado = OlimpiadaResultado::find($id);
        if (!$resultado) {
            throw new ModelNotFoundException('El resultado indicado no existe.');
        }

        $resultado->fill([
            'FacultadLocal' => $datos['facultadLocalId'] ?? $resultado->FacultadLocal,
            'FacultadVisitante' => $datos['facultadVisitanteId'] ?? $resultado->FacultadVisitante,
            'Fase' => $datos['fase'] ?? $resultado->Fase,
            'Grupo' => $datos['grupo'] ?? $resultado->Grupo,
            'FechaPartido' => $datos['fechaPartido'] ?? $resultado->FechaPartido,
            'PuntajeLocal' => $datos['puntajeLocal'] ?? $resultado->PuntajeLocal,
            'PuntajeVisitante' => $datos['puntajeVisitante'] ?? $resultado->PuntajeVisitante,
            'Estado' => $datos['estado'] ?? $resultado->Estado,
            'Observaciones' => $datos['observaciones'] ?? $resultado->Observaciones,
        ]);
        $resultado->save();

        $this->actualizarGanadorYTabla($resultado);

        return $resultado;
    }

    public function participantesPorFacultad(int $edicionDisciplinaId): Collection
    {
        return OlimpiadaInscripcion::with(['usuario', 'facultad'])
            ->where('EdicionDisciplina', $edicionDisciplinaId)
            ->where('Estado', 'inscrito')
            ->orderBy('Facultad')
            ->get();
    }

    private function actualizarGanadorYTabla(OlimpiadaResultado $resultado): void
    {
        if ($resultado->Estado === 'finalizado' && $resultado->PuntajeLocal !== null && $resultado->PuntajeVisitante !== null) {
            if ($resultado->PuntajeLocal > $resultado->PuntajeVisitante) {
                $resultado->FacultadGanadora = $resultado->FacultadLocal;
            } elseif ($resultado->PuntajeVisitante > $resultado->PuntajeLocal) {
                $resultado->FacultadGanadora = $resultado->FacultadVisitante;
            } else {
                $resultado->FacultadGanadora = null;
            }
            $resultado->saveQuietly();
        }

        $this->recalcularTabla((int) $resultado->EdicionDisciplina);
    }

    public function recalcularTabla(int $edicionDisciplinaId): void
    {
        $facultadIds = OlimpiadaInscripcion::where('EdicionDisciplina', $edicionDisciplinaId)
            ->where('Estado', 'inscrito')
            ->pluck('Facultad')
            ->unique()
            ->values();

        $resultadosFinalizados = OlimpiadaResultado::where('EdicionDisciplina', $edicionDisciplinaId)
            ->where('Estado', 'finalizado')
            ->whereNotNull('PuntajeLocal')
            ->whereNotNull('PuntajeVisitante')
            ->get();

        $estadisticas = [];
        foreach ($facultadIds as $facultadId) {
            $estadisticas[$facultadId] = [
                'PartidosJugados' => 0,
                'PartidosGanados' => 0,
                'PartidosEmpatados' => 0,
                'PartidosPerdidos' => 0,
                'PuntosAFavor' => 0,
                'PuntosEnContra' => 0,
                'Puntos' => 0,
            ];
        }

        foreach ($resultadosFinalizados as $resultado) {
            if (!$resultado->FacultadVisitante) {
                continue;
            }

            foreach ([
                ['equipo' => $resultado->FacultadLocal, 'rival' => $resultado->FacultadVisitante, 'a_favor' => $resultado->PuntajeLocal, 'en_contra' => $resultado->PuntajeVisitante],
                ['equipo' => $resultado->FacultadVisitante, 'rival' => $resultado->FacultadLocal, 'a_favor' => $resultado->PuntajeVisitante, 'en_contra' => $resultado->PuntajeLocal],
            ] as $lado) {
                $equipo = $lado['equipo'];
                if (!isset($estadisticas[$equipo])) {
                    $estadisticas[$equipo] = [
                        'PartidosJugados' => 0,
                        'PartidosGanados' => 0,
                        'PartidosEmpatados' => 0,
                        'PartidosPerdidos' => 0,
                        'PuntosAFavor' => 0,
                        'PuntosEnContra' => 0,
                        'Puntos' => 0,
                    ];
                }

                $estadisticas[$equipo]['PartidosJugados']++;
                $estadisticas[$equipo]['PuntosAFavor'] += $lado['a_favor'];
                $estadisticas[$equipo]['PuntosEnContra'] += $lado['en_contra'];

                if ($lado['a_favor'] > $lado['en_contra']) {
                    $estadisticas[$equipo]['PartidosGanados']++;
                    $estadisticas[$equipo]['Puntos'] += 3;
                } elseif ($lado['a_favor'] < $lado['en_contra']) {
                    $estadisticas[$equipo]['PartidosPerdidos']++;
                } else {
                    $estadisticas[$equipo]['PartidosEmpatados']++;
                    $estadisticas[$equipo]['Puntos'] += 1;
                }
            }
        }

        $ordenado = collect($estadisticas)
            ->sortByDesc(fn ($s) => [$s['Puntos'], $s['PuntosAFavor'] - $s['PuntosEnContra']])
            ->keys()
            ->values();

        foreach ($estadisticas as $facultadId => $stats) {
            $posicion = $ordenado->search($facultadId);

            OlimpiadaParticipacionFacultad::updateOrCreate(
                ['EdicionDisciplina' => $edicionDisciplinaId, 'Facultad' => $facultadId],
                array_merge($stats, ['Posicion' => $posicion !== false ? $posicion + 1 : null])
            );
        }
    }
}
