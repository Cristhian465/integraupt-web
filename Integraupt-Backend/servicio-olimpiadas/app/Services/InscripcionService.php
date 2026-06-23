<?php

namespace App\Services;

use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\OlimpiadaEdicionDisciplina;
use App\Models\OlimpiadaInscripcion;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use LogicException;

class InscripcionService
{
    public function inscribir(array $datos): OlimpiadaInscripcion
    {
        $edicionDisciplinaId = (int) $datos['edicionDisciplinaId'];
        $usuarioId = (int) $datos['usuarioId'];

        $edicionDisciplina = OlimpiadaEdicionDisciplina::with('edicion')->find($edicionDisciplinaId);
        if (!$edicionDisciplina) {
            throw new ModelNotFoundException('La disciplina indicada no existe para esta edición.');
        }

        if ($edicionDisciplina->Estado !== 'activa') {
            throw new InvalidArgumentException('Esta disciplina se encuentra inactiva en la edición actual.');
        }

        $edicion = $edicionDisciplina->edicion;
        if (!$edicion || $edicion->Estado !== 'inscripcion_abierta') {
            throw new InvalidArgumentException('La ventana de inscripción de esta edición no está abierta.');
        }

        $usuario = Usuario::find($usuarioId);
        if (!$usuario) {
            throw new ModelNotFoundException('El usuario indicado no existe.');
        }

        $facultadId = $this->resolverFacultadUsuario($usuarioId);
        if ($facultadId === null) {
            throw new InvalidArgumentException('No fue posible determinar la facultad del usuario para inscribirlo.');
        }

        $yaInscrito = OlimpiadaInscripcion::where('EdicionDisciplina', $edicionDisciplinaId)
            ->where('Usuario', $usuarioId)
            ->where('Estado', 'inscrito')
            ->exists();

        if ($yaInscrito) {
            throw new LogicException('El usuario ya se encuentra inscrito en esta disciplina para la edición actual.');
        }

        if ($edicionDisciplina->CupoMaximoPorFacultad !== null) {
            $inscritosFacultad = OlimpiadaInscripcion::where('EdicionDisciplina', $edicionDisciplinaId)
                ->where('Facultad', $facultadId)
                ->where('Estado', 'inscrito')
                ->count();

            if ($inscritosFacultad >= $edicionDisciplina->CupoMaximoPorFacultad) {
                throw new LogicException('La facultad alcanzó el cupo máximo de inscripciones para esta disciplina.');
            }
        }

        return OlimpiadaInscripcion::create([
            'EdicionDisciplina' => $edicionDisciplinaId,
            'Usuario' => $usuarioId,
            'Facultad' => $facultadId,
            'Estado' => 'inscrito',
            'Observaciones' => $datos['observaciones'] ?? null,
        ]);
    }

    public function cancelar(int $inscripcionId, int $usuarioId): OlimpiadaInscripcion
    {
        $inscripcion = OlimpiadaInscripcion::find($inscripcionId);
        if (!$inscripcion) {
            throw new ModelNotFoundException('La inscripción indicada no existe.');
        }

        if ((int) $inscripcion->Usuario !== $usuarioId) {
            throw new InvalidArgumentException('No tienes permisos para cancelar esta inscripción.');
        }

        $inscripcion->Estado = 'cancelado';
        $inscripcion->save();

        return $inscripcion;
    }

    public function listarPorUsuario(int $usuarioId): Collection
    {
        return OlimpiadaInscripcion::with(['edicionDisciplina.disciplina', 'edicionDisciplina.edicion', 'facultad'])
            ->where('Usuario', $usuarioId)
            ->orderByDesc('IdInscripcion')
            ->get();
    }

    public function listarPorEdicionDisciplina(int $edicionDisciplinaId, ?int $facultadId = null): Collection
    {
        $query = OlimpiadaInscripcion::with(['usuario', 'facultad'])
            ->where('EdicionDisciplina', $edicionDisciplinaId);

        if ($facultadId !== null) {
            $query->where('Facultad', $facultadId);
        }

        return $query->orderBy('Facultad')->get();
    }

    public function resolverFacultadUsuario(int $usuarioId): ?int
    {
        $estudiante = Estudiante::with('escuela')->where('IdUsuario', $usuarioId)->first();
        if ($estudiante && $estudiante->escuela) {
            return $estudiante->escuela->IdFacultad;
        }

        $docente = Docente::with('escuela')->where('IdUsuario', $usuarioId)->first();
        if ($docente && $docente->escuela) {
            return $docente->escuela->IdFacultad;
        }

        return null;
    }
}
