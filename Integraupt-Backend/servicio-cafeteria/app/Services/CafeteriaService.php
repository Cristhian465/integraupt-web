<?php

namespace App\Services;

use App\Models\Cafeteria;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class CafeteriaService
{
    public function listar(): Collection
    {
        return Cafeteria::with(['facultad', 'encargado'])->orderBy('Nombre')->get();
    }

    public function encontrar(int $id): Cafeteria
    {
        return Cafeteria::with(['facultad', 'encargado'])->findOrFail($id);
    }

    public function porFacultad(int $idFacultad): ?Cafeteria
    {
        return Cafeteria::with(['facultad', 'encargado'])->where('IdFacultad', $idFacultad)->first();
    }

    public function deEncargado(int $idUsuario): ?Cafeteria
    {
        return Cafeteria::with(['facultad', 'encargado'])->where('IdEncargado', $idUsuario)->first();
    }

    public function crear(array $datos): Cafeteria
    {
        $existente = Cafeteria::where('IdFacultad', $datos['IdFacultad'])->exists();
        if ($existente) {
            throw new InvalidArgumentException('Esa facultad ya tiene una cafeteria registrada.');
        }

        return Cafeteria::create($datos)->refresh();
    }

    public function actualizar(int $id, array $datos): Cafeteria
    {
        $cafeteria = Cafeteria::findOrFail($id);
        $cafeteria->update($datos);

        return $cafeteria->refresh();
    }
}
