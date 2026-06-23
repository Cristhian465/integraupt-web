<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class CatalogoRepository
{
    public function listarCursos(): array
    {
        return DB::table('cursos')
            ->select('IdCurso as id', 'Nombre as nombre')
            ->where('Estado', 1)
            ->orderBy('Nombre', 'asc')
            ->get()
            ->toArray();
    }

    public function listarDocentes(): array
    {
        return DB::table('usuario as u')
            ->leftJoin('docente as d', 'd.IdUsuario', '=', 'u.IdUsuario')
            ->select('u.IdUsuario as id', 'u.Nombre as nombres', 'u.Apellido as apellidos', 'd.CodigoDocente as codigo')
            ->where('u.Estado', 1)
            ->where(function($query) {
                $query->where('u.Rol', 3)->orWhereNotNull('d.IdDocente');
            })
            ->orderBy('u.Nombre', 'asc')
            ->orderBy('u.Apellido', 'asc')
            ->get()
            ->toArray();
    }

    public function listarEspacios(): array
    {
        return DB::table('espacio')
            ->select('IdEspacio as id', 'Codigo as codigo', 'Nombre as nombre', 'Tipo as tipo', 'Capacidad as capacidad')
            ->where('Estado', 1)
            ->orderBy('Nombre', 'asc')
            ->get()
            ->toArray();
    }

    public function listarBloques(): array
    {
        return DB::table('bloqueshorarios')
            ->select('IdBloque as id', 'Nombre as nombre', DB::raw("DATE_FORMAT(HoraInicio, '%H:%i') as horaInicio"), DB::raw("DATE_FORMAT(HoraFinal, '%H:%i') as horaFinal"))
            ->orderBy('Orden', 'asc')
            ->get()
            ->toArray();
    }

    public function existeCurso(int $id): bool
    {
        return DB::table('cursos')->where('IdCurso', $id)->exists();
    }

    public function existeDocente(int $id): bool
    {
        return DB::table('usuario')->where('IdUsuario', $id)->exists();
    }

    public function existeEspacio(int $id): bool
    {
        return DB::table('espacio')->where('IdEspacio', $id)->exists();
    }

    public function existeBloque(int $id): bool
    {
        return DB::table('bloqueshorarios')->where('IdBloque', $id)->exists();
    }
}
