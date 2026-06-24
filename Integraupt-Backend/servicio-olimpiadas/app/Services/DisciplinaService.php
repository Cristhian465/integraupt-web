<?php

namespace App\Services;

use App\Models\OlimpiadaDisciplina;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DisciplinaService
{
    public function listar(?string $estado = null): Collection
    {
        $query = OlimpiadaDisciplina::query()->orderBy('Nombre');

        if ($estado !== null && $estado !== '') {
            $query->where('Estado', $estado);
        }

        return $query->get();
    }

    public function crear(array $datos): OlimpiadaDisciplina
    {
        return OlimpiadaDisciplina::create([
            'Nombre' => trim($datos['nombre']),
            'Descripcion' => $datos['descripcion'] ?? null,
            'TipoParticipacion' => $datos['tipoParticipacion'] ?? 'equipo',
            'Reglas' => $datos['reglas'] ?? null,
            'CupoMaximoDefault' => $datos['cupoMaximoDefault'] ?? null,
            'Estado' => $datos['estado'] ?? 'activa',
        ]);
    }

    public function actualizar(int $id, array $datos): OlimpiadaDisciplina
    {
        $disciplina = OlimpiadaDisciplina::find($id);

        if (!$disciplina) {
            throw new ModelNotFoundException('La disciplina indicada no existe.');
        }

        $disciplina->fill([
            'Nombre' => isset($datos['nombre']) ? trim($datos['nombre']) : $disciplina->Nombre,
            'Descripcion' => $datos['descripcion'] ?? $disciplina->Descripcion,
            'TipoParticipacion' => $datos['tipoParticipacion'] ?? $disciplina->TipoParticipacion,
            'Reglas' => $datos['reglas'] ?? $disciplina->Reglas,
            'CupoMaximoDefault' => $datos['cupoMaximoDefault'] ?? $disciplina->CupoMaximoDefault,
        ]);
        $disciplina->save();

        return $disciplina;
    }

    public function cambiarEstado(int $id, string $estado): OlimpiadaDisciplina
    {
        $disciplina = OlimpiadaDisciplina::find($id);

        if (!$disciplina) {
            throw new ModelNotFoundException('La disciplina indicada no existe.');
        }

        $disciplina->Estado = $estado;
        $disciplina->save();

        return $disciplina;
    }
}
