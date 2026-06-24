<?php

namespace App\Services;

use App\Models\Escuela;
use App\Models\Estudiante;
use App\Models\Facultad;
use Illuminate\Support\Collection;

class CatalogoService
{
    public function obtenerFacultades(): Collection
    {
        return Facultad::orderBy('Nombre')->get();
    }

    public function obtenerEscuelas(?int $facultadId): Collection
    {
        $query = Escuela::with('facultad')->orderBy('Nombre');

        if ($facultadId !== null) {
            $query->where('IdFacultad', $facultadId);
        }

        return $query->get();
    }

    public function buscarEstudiantes(string $busqueda): Collection
    {
        $busqueda = trim($busqueda);
        if ($busqueda === '') {
            return collect();
        }

        return Estudiante::with(['usuario', 'escuela.facultad'])
            ->where(function ($query) use ($busqueda) {
                $query->where('Codigo', 'like', "%{$busqueda}%")
                    ->orWhereHas('usuario', function ($q) use ($busqueda) {
                        $q->where('Nombre', 'like', "%{$busqueda}%")
                            ->orWhere('Apellido', 'like', "%{$busqueda}%");
                    });
            })
            ->limit(15)
            ->get();
    }
}
