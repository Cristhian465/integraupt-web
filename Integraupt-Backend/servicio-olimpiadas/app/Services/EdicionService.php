<?php

namespace App\Services;

use App\Models\OlimpiadaDisciplina;
use App\Models\OlimpiadaEdicion;
use App\Models\OlimpiadaEdicionDisciplina;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class EdicionService
{
    public function listar(): Collection
    {
        return OlimpiadaEdicion::orderByDesc('AnioInicio')->orderByDesc('SemestreInicio')->get();
    }

    public function obtenerActual(): ?OlimpiadaEdicion
    {
        $actual = OlimpiadaEdicion::whereIn('Estado', ['inscripcion_abierta', 'inscripcion_cerrada', 'en_curso'])
            ->orderByDesc('AnioInicio')
            ->orderByDesc('SemestreInicio')
            ->first();

        if ($actual) {
            return $actual;
        }

        return OlimpiadaEdicion::orderByDesc('AnioInicio')->orderByDesc('SemestreInicio')->first();
    }

    public function obtener(int $id): OlimpiadaEdicion
    {
        $edicion = OlimpiadaEdicion::find($id);

        if (!$edicion) {
            throw new ModelNotFoundException('La edición indicada no existe.');
        }

        return $edicion;
    }

    public function crear(array $datos): OlimpiadaEdicion
    {
        $anioInicio = (int) $datos['anioInicio'];
        $semestreInicio = (int) $datos['semestreInicio'];

        if (!in_array($semestreInicio, [1, 2], true)) {
            throw new InvalidArgumentException('El semestre de inicio debe ser 1 o 2.');
        }

        $existe = OlimpiadaEdicion::where('AnioInicio', $anioInicio)
            ->where('SemestreInicio', $semestreInicio)
            ->exists();

        if ($existe) {
            throw new InvalidArgumentException('Ya existe una edición registrada para ese año y semestre de inicio.');
        }

        return OlimpiadaEdicion::create([
            'Nombre' => trim($datos['nombre']),
            'AnioInicio' => $anioInicio,
            'SemestreInicio' => $semestreInicio,
            'AnioFin' => $anioInicio + 2,
            'SemestreFin' => $semestreInicio,
            'Estado' => $datos['estado'] ?? 'planificada',
            'FechaInicioJuegos' => $datos['fechaInicioJuegos'] ?? null,
            'FechaFinJuegos' => $datos['fechaFinJuegos'] ?? null,
            'Observaciones' => $datos['observaciones'] ?? null,
        ]);
    }

    public function actualizar(int $id, array $datos): OlimpiadaEdicion
    {
        $edicion = $this->obtener($id);

        $edicion->fill([
            'Nombre' => isset($datos['nombre']) ? trim($datos['nombre']) : $edicion->Nombre,
            'FechaInicioJuegos' => $datos['fechaInicioJuegos'] ?? $edicion->FechaInicioJuegos,
            'FechaFinJuegos' => $datos['fechaFinJuegos'] ?? $edicion->FechaFinJuegos,
            'Observaciones' => $datos['observaciones'] ?? $edicion->Observaciones,
        ]);
        $edicion->save();

        return $edicion;
    }

    public function cambiarEstado(int $id, string $estado): OlimpiadaEdicion
    {
        $edicion = $this->obtener($id);
        $edicion->Estado = $estado;
        $edicion->save();

        return $edicion;
    }

    public function abrirInscripcion(int $id, ?string $fechaCierre = null): OlimpiadaEdicion
    {
        $edicion = $this->obtener($id);

        if (in_array($edicion->Estado, ['en_curso', 'finalizada', 'cancelada'], true)) {
            throw new InvalidArgumentException('No se puede abrir la inscripción de una edición en curso, finalizada o cancelada.');
        }

        $edicion->Estado = 'inscripcion_abierta';
        $edicion->FechaAperturaInscripcion = Carbon::now();
        $edicion->FechaCierreInscripcion = $fechaCierre ? Carbon::parse($fechaCierre) : null;
        $edicion->save();

        return $edicion;
    }

    public function cerrarInscripcion(int $id): OlimpiadaEdicion
    {
        $edicion = $this->obtener($id);

        if ($edicion->Estado !== 'inscripcion_abierta') {
            throw new InvalidArgumentException('La edición indicada no tiene una ventana de inscripción abierta.');
        }

        $edicion->Estado = 'inscripcion_cerrada';
        $edicion->FechaCierreInscripcion = Carbon::now();
        $edicion->save();

        return $edicion;
    }

    public function listarDisciplinas(int $edicionId): Collection
    {
        $this->obtener($edicionId);

        return OlimpiadaEdicionDisciplina::with('disciplina')
            ->where('Edicion', $edicionId)
            ->get();
    }

    public function vincularDisciplina(int $edicionId, array $datos): OlimpiadaEdicionDisciplina
    {
        $edicion = $this->obtener($edicionId);
        $disciplinaId = (int) $datos['disciplinaId'];

        $disciplina = OlimpiadaDisciplina::find($disciplinaId);
        if (!$disciplina) {
            throw new ModelNotFoundException('La disciplina indicada no existe.');
        }

        $existe = OlimpiadaEdicionDisciplina::where('Edicion', $edicion->IdEdicion)
            ->where('Disciplina', $disciplinaId)
            ->exists();

        if ($existe) {
            throw new InvalidArgumentException('Esa disciplina ya está vinculada a la edición indicada.');
        }

        return OlimpiadaEdicionDisciplina::create([
            'Edicion' => $edicion->IdEdicion,
            'Disciplina' => $disciplinaId,
            'CupoMaximoPorFacultad' => $datos['cupoMaximoPorFacultad'] ?? $disciplina->CupoMaximoDefault,
            'ReglasEspecificas' => $datos['reglasEspecificas'] ?? null,
            'Estado' => $datos['estado'] ?? 'activa',
        ]);
    }

    public function actualizarVinculo(int $edicionDisciplinaId, array $datos): OlimpiadaEdicionDisciplina
    {
        $vinculo = OlimpiadaEdicionDisciplina::find($edicionDisciplinaId);
        if (!$vinculo) {
            throw new ModelNotFoundException('La disciplina vinculada a la edición no existe.');
        }

        $vinculo->fill([
            'CupoMaximoPorFacultad' => $datos['cupoMaximoPorFacultad'] ?? $vinculo->CupoMaximoPorFacultad,
            'ReglasEspecificas' => $datos['reglasEspecificas'] ?? $vinculo->ReglasEspecificas,
        ]);
        $vinculo->save();

        return $vinculo;
    }

    public function cambiarEstadoVinculo(int $edicionDisciplinaId, string $estado): OlimpiadaEdicionDisciplina
    {
        $vinculo = OlimpiadaEdicionDisciplina::find($edicionDisciplinaId);
        if (!$vinculo) {
            throw new ModelNotFoundException('La disciplina vinculada a la edición no existe.');
        }

        $vinculo->Estado = $estado;
        $vinculo->save();

        return $vinculo;
    }
}
