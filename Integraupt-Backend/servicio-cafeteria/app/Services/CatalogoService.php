<?php

namespace App\Services;

use App\Models\Administrativo;
use App\Models\Docente;
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

    public function buscarAdministrativos(string $busqueda): Collection
    {
        $busqueda = trim($busqueda);
        if ($busqueda === '') {
            return collect();
        }

        return Administrativo::with('usuario')
            ->whereHas('usuario', function ($q) use ($busqueda) {
                $q->where('Nombre', 'like', "%{$busqueda}%")
                    ->orWhere('Apellido', 'like', "%{$busqueda}%")
                    ->orWhere('NumDoc', 'like', "%{$busqueda}%");
            })
            ->limit(15)
            ->get();
    }

    /**
     * Resuelve la facultad a la que pertenece un usuario (estudiante o docente) via su escuela.
     */
    public function resolverFacultadDeUsuario(int $idUsuario): ?int
    {
        $estudiante = Estudiante::with('escuela')->where('IdUsuario', $idUsuario)->first();
        if ($estudiante) {
            return $estudiante->escuela?->IdFacultad;
        }

        $docente = Docente::with('escuela')->where('IdUsuario', $idUsuario)->first();
        if ($docente) {
            return $docente->escuela?->IdFacultad;
        }

        return null;
    }
}
