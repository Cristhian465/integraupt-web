<?php

namespace App\Services;

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

    public function buscarDocentes(string $busqueda): Collection
    {
        $busqueda = trim($busqueda);
        if ($busqueda === '') {
            return collect();
        }

        return Docente::with(['usuario', 'escuela.facultad'])
            ->where(function ($query) use ($busqueda) {
                $query->where('CodigoDocente', 'like', "%{$busqueda}%")
                    ->orWhereHas('usuario', function ($q) use ($busqueda) {
                        $q->where('Nombre', 'like', "%{$busqueda}%")
                            ->orWhere('Apellido', 'like', "%{$busqueda}%");
                    });
            })
            ->limit(15)
            ->get();
    }

    /**
     * @return array{facultadId: int|null, escuelaId: int|null}|null
     */
    public function resolverFacultadDeUsuario(int $idUsuario): ?array
    {
        $estudiante = Estudiante::with('escuela')->where('IdUsuario', $idUsuario)->first();
        if ($estudiante) {
            return [
                'facultadId' => $estudiante->escuela?->IdFacultad,
                'escuelaId' => $estudiante->Escuela,
            ];
        }

        $docente = Docente::with('escuela')->where('IdUsuario', $idUsuario)->first();
        if ($docente) {
            return [
                'facultadId' => $docente->escuela?->IdFacultad,
                'escuelaId' => $docente->Escuela,
            ];
        }

        return null;
    }
}
