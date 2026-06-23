<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\HorarioCurso;

class HorarioCursoRepository
{
    private function baseQuery()
    {
        return DB::table('horario_curso as hc')
            ->leftJoin('cursos as c', 'c.IdCurso', '=', 'hc.Curso')
            ->leftJoin('usuario as u', 'u.IdUsuario', '=', 'hc.Docente')
            ->leftJoin('espacio as e', 'e.IdEspacio', '=', 'hc.Espacio')
            ->leftJoin('bloqueshorarios as b', 'b.IdBloque', '=', 'hc.Bloque')
            ->select([
                'hc.IdHorarioCurso as idHorarioCurso',
                'hc.Curso as curso',
                'hc.Docente as docente',
                'hc.Espacio as espacio',
                'hc.Bloque as bloque',
                'hc.DiaSemana as diaSemana',
                'hc.FechaInicio as fechaInicio',
                'hc.FechaFin as fechaFin',
                'hc.Estado as estado',
                'c.Nombre as nombreCurso',
                DB::raw("CONCAT(u.Nombre, ' ', u.Apellido) as nombreDocente"),
                'e.Nombre as nombreEspacio',
                'e.Codigo as codigoEspacio',
                'b.Nombre as nombreBloque',
                DB::raw("DATE_FORMAT(b.HoraInicio, '%H:%i') as horaInicio"),
                DB::raw("DATE_FORMAT(b.HoraFinal, '%H:%i') as horaFinal")
            ]);
    }

    public function listarDetalle(): array
    {
        return $this->baseQuery()
            ->orderBy('hc.IdHorarioCurso', 'desc')
            ->get()
            ->toArray();
    }

    public function buscarDetallePorId(int $id)
    {
        return $this->baseQuery()
            ->where('hc.IdHorarioCurso', $id)
            ->first();
    }
}
