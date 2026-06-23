<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Models\Incidencia;

class IncidenciaRepository
{
    public function findByReservaIdOrderByFechaReporteDesc($reservaId)
    {
        return Incidencia::where('Reserva', $reservaId)
            ->orderByDesc('FechaReporte')
            ->get();
    }

    public function buscarParaGestion($facultadId, $escuelaId, $espacioId, $search)
    {
        $query = DB::table('incidencia as i')
            ->join('reserva as r', 'r.IdReserva', '=', 'i.Reserva')
            ->join('espacio as e', 'e.IdEspacio', '=', 'r.espacio')
            ->join('escuela as esc', 'esc.IdEscuela', '=', 'e.Escuela')
            ->join('facultad as fac', 'fac.IdFacultad', '=', 'esc.IdFacultad')
            ->leftJoin('usuario as u', 'u.IdUsuario', '=', 'r.usuario')
            ->select(
                'i.IdIncidencia as id',
                'r.IdReserva as reservaId',
                'u.IdUsuario as usuarioId',
                'u.Nombre as usuarioNombre',
                'u.Apellido as usuarioApellido',
                'u.NumDoc as usuarioDocumento',
                'e.IdEspacio as espacioId',
                'e.Codigo as espacioCodigo',
                'e.Nombre as espacioNombre',
                'esc.IdEscuela as escuelaId',
                'esc.Nombre as escuelaNombre',
                'fac.IdFacultad as facultadId',
                'fac.Nombre as facultadNombre',
                'i.Descripcion as descripcion',
                'i.FechaReporte as fechaReporte'
            );

        if ($escuelaId !== null) {
            $query->where('esc.IdEscuela', $escuelaId);
        }

        if ($facultadId !== null) {
            $query->where('fac.IdFacultad', $facultadId);
        }

        if ($espacioId !== null) {
            $query->where('e.IdEspacio', $espacioId);
        }

        if ($search !== null && trim($search) !== '') {
            $searchTerm = '%' . strtolower(trim($search)) . '%';

            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(i.Descripcion) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(e.Nombre) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(e.Codigo) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(COALESCE(u.Nombre, "")) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(COALESCE(u.Apellido, "")) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(CONCAT(COALESCE(u.Nombre, ""), " ", COALESCE(u.Apellido, ""))) LIKE ?', [$searchTerm])
                    ->orWhereRaw('LOWER(COALESCE(u.NumDoc, "")) LIKE ?', [$searchTerm]);
            });
        }

        return $query
            ->orderByDesc('i.FechaReporte')
            ->get();
    }
}
