<?php

namespace App\Services;

use App\Models\OlimpiadaAnotador;
use App\Models\OlimpiadaEdicionDisciplina;
use App\Models\OlimpiadaInscripcion;
use App\Models\OlimpiadaParticipacionFacultad;
use App\Models\OlimpiadaResultado;
use App\Models\OlimpiadaResultadoPosicion;
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
            'Lugar' => $datos['lugar'] ?? null,
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
            'Lugar' => $datos['lugar'] ?? $resultado->Lugar,
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

    public function listarAnotadores(int $edicionDisciplinaId): Collection
    {
        return OlimpiadaAnotador::with('facultad')
            ->where('EdicionDisciplina', $edicionDisciplinaId)
            ->orderByDesc('Cantidad')
            ->get();
    }

    public function crearAnotador(array $datos): OlimpiadaAnotador
    {
        $edicionDisciplinaId = (int) $datos['edicionDisciplinaId'];

        if (!OlimpiadaEdicionDisciplina::where('IdEdicionDisciplina', $edicionDisciplinaId)->exists()) {
            throw new ModelNotFoundException('La disciplina indicada no existe para esta edición.');
        }

        return OlimpiadaAnotador::create([
            'EdicionDisciplina' => $edicionDisciplinaId,
            'Facultad' => $datos['facultadId'],
            'NombreJugador' => trim($datos['nombreJugador']),
            'Cantidad' => $datos['cantidad'] ?? 1,
            'Observaciones' => $datos['observaciones'] ?? null,
        ]);
    }

    public function actualizarAnotador(int $id, array $datos): OlimpiadaAnotador
    {
        $anotador = OlimpiadaAnotador::find($id);
        if (!$anotador) {
            throw new ModelNotFoundException('El anotador indicado no existe.');
        }

        $anotador->fill([
            'Facultad' => $datos['facultadId'] ?? $anotador->Facultad,
            'NombreJugador' => isset($datos['nombreJugador']) ? trim($datos['nombreJugador']) : $anotador->NombreJugador,
            'Cantidad' => $datos['cantidad'] ?? $anotador->Cantidad,
            'Observaciones' => $datos['observaciones'] ?? $anotador->Observaciones,
        ]);
        $anotador->save();

        return $anotador;
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

    public function listarResultadosPosicion(int $edicionDisciplinaId): Collection
    {
        return OlimpiadaResultadoPosicion::with('facultad')
            ->where('EdicionDisciplina', $edicionDisciplinaId)
            ->orderBy('Posicion')
            ->get();
    }

    public function crearResultadoPosicion(array $datos): OlimpiadaResultadoPosicion
    {
        $edicionDisciplinaId = (int) $datos['edicionDisciplinaId'];

        if (!OlimpiadaEdicionDisciplina::where('IdEdicionDisciplina', $edicionDisciplinaId)->exists()) {
            throw new ModelNotFoundException('La disciplina indicada no existe para esta edición.');
        }

        $posicion = OlimpiadaResultadoPosicion::create([
            'EdicionDisciplina' => $edicionDisciplinaId,
            'Facultad' => $datos['facultadId'],
            'Posicion' => $datos['posicion'],
            'Puntos' => $datos['puntos'] ?? 0,
            'Prueba' => $datos['prueba'] ?? null,
            'Fecha' => $datos['fecha'] ?? null,
            'Lugar' => $datos['lugar'] ?? null,
            'Observaciones' => $datos['observaciones'] ?? null,
            'Estado' => $datos['estado'] ?? 'registrado',
        ]);

        $this->recalcularTabla($edicionDisciplinaId);

        return $posicion;
    }

    public function actualizarResultadoPosicion(int $id, array $datos): OlimpiadaResultadoPosicion
    {
        $posicion = OlimpiadaResultadoPosicion::find($id);
        if (!$posicion) {
            throw new ModelNotFoundException('El resultado de posición indicado no existe.');
        }

        $posicion->fill([
            'Facultad' => $datos['facultadId'] ?? $posicion->Facultad,
            'Posicion' => $datos['posicion'] ?? $posicion->Posicion,
            'Puntos' => $datos['puntos'] ?? $posicion->Puntos,
            'Prueba' => $datos['prueba'] ?? $posicion->Prueba,
            'Fecha' => $datos['fecha'] ?? $posicion->Fecha,
            'Lugar' => $datos['lugar'] ?? $posicion->Lugar,
            'Observaciones' => $datos['observaciones'] ?? $posicion->Observaciones,
            'Estado' => $datos['estado'] ?? $posicion->Estado,
        ]);
        $posicion->save();

        $this->recalcularTabla((int) $posicion->EdicionDisciplina);

        return $posicion;
    }

    public function recalcularTabla(int $edicionDisciplinaId): void
    {
        $edicionDisciplina = OlimpiadaEdicionDisciplina::with('disciplina')->find($edicionDisciplinaId);
        if (!$edicionDisciplina) {
            return;
        }

        if ($edicionDisciplina->disciplina && $edicionDisciplina->disciplina->TipoPuntuacion === 'posiciones') {
            $resultadosPosiciones = OlimpiadaResultadoPosicion::where('EdicionDisciplina', $edicionDisciplinaId)
                ->where('Estado', 'registrado')
                ->get();

            $puntosPorFacultad = [];

            // Initialize all faculties that have registered active participants
            $facultadIds = OlimpiadaInscripcion::where('EdicionDisciplina', $edicionDisciplinaId)
                ->where('Estado', 'inscrito')
                ->pluck('Facultad')
                ->unique()
                ->values();

            foreach ($facultadIds as $facultadId) {
                $puntosPorFacultad[$facultadId] = 0;
            }

            // Also add any faculty that has points in the positions table
            foreach ($resultadosPosiciones as $resPos) {
                $facId = $resPos->Facultad;
                if (!isset($puntosPorFacultad[$facId])) {
                    $puntosPorFacultad[$facId] = 0;
                }
                $puntosPorFacultad[$facId] += $resPos->Puntos;
            }

            // Sort faculties by points descending
            $ordenado = collect($puntosPorFacultad)
                ->sortByDesc(fn ($pts) => $pts)
                ->keys()
                ->values();

            // Save rankings
            foreach ($puntosPorFacultad as $facultadId => $puntos) {
                $posicionIndex = $ordenado->search($facultadId);
                $posicion = $posicionIndex !== false ? $posicionIndex + 1 : null;

                OlimpiadaParticipacionFacultad::updateOrCreate(
                    ['EdicionDisciplina' => $edicionDisciplinaId, 'Facultad' => $facultadId],
                    [
                        'PartidosJugados' => 0,
                        'PartidosGanados' => 0,
                        'PartidosEmpatados' => 0,
                        'PartidosPerdidos' => 0,
                        'PuntosAFavor' => 0,
                        'PuntosEnContra' => 0,
                        'Puntos' => $puntos,
                        'Posicion' => $posicion,
                    ]
                );
            }
            return;
        }

        // Match logic
        $disciplinaNombre = $edicionDisciplina->disciplina ? $edicionDisciplina->disciplina->Nombre : '';

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

            // Detección de Walkover (W.O.) mediante observaciones o marcadores por defecto
            $isWO = false;
            if ($resultado->Observaciones && preg_match('/\b(w\.?o\.?|walkover)\b/i', $resultado->Observaciones)) {
                $isWO = true;
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

                if ($disciplinaNombre === 'Ajedrez') {
                    // Ajedrez: Se suman los puntos individuales del match directamente
                    $estadisticas[$equipo]['Puntos'] += $lado['a_favor'];
                    if ($lado['a_favor'] > $lado['en_contra']) {
                        $estadisticas[$equipo]['PartidosGanados']++;
                    } elseif ($lado['a_favor'] < $lado['en_contra']) {
                        $estadisticas[$equipo]['PartidosPerdidos']++;
                    } else {
                        $estadisticas[$equipo]['PartidosEmpatados']++;
                    }
                } elseif ($disciplinaNombre === 'Básquetbol' || $disciplinaNombre === 'Vóleibol') {
                    if ($isWO) {
                        if ($lado['a_favor'] > $lado['en_contra']) {
                            $estadisticas[$equipo]['PartidosGanados']++;
                            $estadisticas[$equipo]['Puntos'] += 2;
                        } else {
                            $estadisticas[$equipo]['PartidosPerdidos']++;
                            $estadisticas[$equipo]['Puntos'] += 0; // W.O. otorga 0 puntos al perdedor
                        }
                    } else {
                        if ($lado['a_favor'] > $lado['en_contra']) {
                            $estadisticas[$equipo]['PartidosGanados']++;
                            $estadisticas[$equipo]['Puntos'] += 2;
                        } else {
                            $estadisticas[$equipo]['PartidosPerdidos']++;
                            $estadisticas[$equipo]['Puntos'] += 1; // Derrota regular otorga 1 punto
                        }
                    }
                } elseif ($disciplinaNombre === 'Fútbol' || $disciplinaNombre === 'Futsal') {
                    if ($isWO) {
                        if ($lado['a_favor'] > $lado['en_contra']) {
                            $estadisticas[$equipo]['PartidosGanados']++;
                            $estadisticas[$equipo]['Puntos'] += 3;
                        } else {
                            $estadisticas[$equipo]['PartidosPerdidos']++;
                            $estadisticas[$equipo]['Puntos'] -= 1; // Derrota por W.O. resta 1 punto (-1)
                        }
                    } else {
                        if ($lado['a_favor'] > $lado['en_contra']) {
                            $estadisticas[$equipo]['PartidosGanados']++;
                            $estadisticas[$equipo]['Puntos'] += 3;
                        } elseif ($lado['a_favor'] < $lado['en_contra']) {
                            $estadisticas[$equipo]['PartidosPerdidos']++;
                            $estadisticas[$equipo]['Puntos'] += 0;
                        } else {
                            $estadisticas[$equipo]['PartidosEmpatados']++;
                            $estadisticas[$equipo]['Puntos'] += 1;
                        }
                    }
                } else {
                    // Fallback estándar (3/1/0)
                    if ($isWO) {
                        if ($lado['a_favor'] > $lado['en_contra']) {
                            $estadisticas[$equipo]['PartidosGanados']++;
                            $estadisticas[$equipo]['Puntos'] += 3;
                        } else {
                            $estadisticas[$equipo]['PartidosPerdidos']++;
                            $estadisticas[$equipo]['Puntos'] += 0;
                        }
                    } else {
                        if ($lado['a_favor'] > $lado['en_contra']) {
                            $estadisticas[$equipo]['PartidosGanados']++;
                            $estadisticas[$equipo]['Puntos'] += 3;
                        } elseif ($lado['a_favor'] < $lado['en_contra']) {
                            $estadisticas[$equipo]['PartidosPerdidos']++;
                            $estadisticas[$equipo]['Puntos'] += 0;
                        } else {
                            $estadisticas[$equipo]['PartidosEmpatados']++;
                            $estadisticas[$equipo]['Puntos'] += 1;
                        }
                    }
                }
            }
        }

        // Ordenamiento por puntos desc, luego diferencia de puntos (a favor - en contra) desc
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
