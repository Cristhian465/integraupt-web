<?php

namespace App\Services;

use App\Models\Escuela;
use App\Models\Facultad;
use Illuminate\Support\Collection;

class CatalogoService
{
    public function listarFacultades(): Collection
    {
        return Facultad::orderBy('Nombre')->get();
    }

    public function listarEscuelas(?int $facultadId): Collection
    {
        $query = Escuela::with('facultad')->orderBy('Nombre');

        if ($facultadId !== null) {
            $query->where('IdFacultad', $facultadId);
        }

        return $query->get();
    }
}
