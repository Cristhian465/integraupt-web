<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class AuditoriaReservaRepository
{
    private function getBaseQuery()
    {
        return DB::table('auditoriareserva as a')
            ->join('reserva as r', 'r.IdReserva', '=', 'a.IdReserva')
            ->leftJoin('usuario as u', 'u.IdUsuario', '=', 'a.UsuarioCambio')
            ->leftJoin('espacio as e', 'e.IdEspacio', '=', 'r.espacio')
            ->select(
                'a.IdAudit as idAudit',
                'a.IdReserva as idReserva',
                'a.EstadoAnterior as estadoAnterior',
                'a.EstadoNuevo as estadoNuevo',
                'a.FechaCambio as fechaCambio',
                'a.UsuarioCambio as usuarioCambioId',
                DB::raw("CONCAT(
                    COALESCE(u.Nombre, ''),
                    CASE WHEN u.Nombre IS NOT NULL AND u.Apellido IS NOT NULL THEN ' ' ELSE '' END,
                    COALESCE(u.Apellido, '')
                ) AS usuarioCambioNombre"),
                'u.NumDoc as usuarioCambioDocumento',
                'r.Estado as estadoReservaActual',
                'r.DescripcionUso as descripcionUso',
                'r.fechaReserva as fechaReserva',
                'e.Codigo as codigoEspacio',
                'e.Nombre as nombreEspacio'
            );
    }

    public function buscarResumenes($reservaId, $estado, $usuarioTerm, $fechaInicio, $fechaFin)
    {
        $query = $this->getBaseQuery();

        if ($reservaId !== null) {
            $query->where('a.IdReserva', $reservaId);
        }

        if ($estado !== null) {
            $query->where(function ($q) use ($estado) {
                $q->whereRaw('UPPER(a.EstadoNuevo) = UPPER(?)', [$estado])
                  ->orWhereRaw('UPPER(a.EstadoAnterior) = UPPER(?)', [$estado]);
            });
        }

        if ($usuarioTerm !== null) {
            $query->where(function ($q) use ($usuarioTerm) {
                $q->where('u.Nombre', 'LIKE', '%' . $usuarioTerm . '%')
                  ->orWhere('u.Apellido', 'LIKE', '%' . $usuarioTerm . '%')
                  ->orWhere('u.NumDoc', 'LIKE', '%' . $usuarioTerm . '%');
            });
        }

        if ($fechaInicio !== null) {
            $query->where('a.FechaCambio', '>=', $fechaInicio);
        }

        if ($fechaFin !== null) {
            $query->where('a.FechaCambio', '<=', $fechaFin);
        }

        $query->orderByDesc('a.FechaCambio');

        return $query->get();
    }

    public function listarPorReserva($reservaId)
    {
        return $this->getBaseQuery()
            ->where('a.IdReserva', $reservaId)
            ->orderByDesc('a.FechaCambio')
            ->get();
    }

    public function obtenerDetalle($idAudit)
    {
        return $this->getBaseQuery()
            ->where('a.IdAudit', $idAudit)
            ->first();
    }
}
