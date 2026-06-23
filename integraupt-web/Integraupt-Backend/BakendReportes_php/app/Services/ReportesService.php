<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportesService
{
    public function obtenerEstadisticasGenerales()
    {
        // Total estudiantes
        $totalEstudiantes = DB::selectOne("SELECT COUNT(*) as count FROM estudiante WHERE IdUsuario IN (SELECT IdUsuario FROM usuario WHERE Estado = 1)")->count;

        // Total docentes
        $totalDocentes = DB::selectOne("SELECT COUNT(*) as count FROM docente WHERE IdUsuario IN (SELECT IdUsuario FROM usuario WHERE Estado = 1)")->count;

        // Reservas activas (aprobadas y con fecha futura)
        $reservasActivas = DB::selectOne("
            SELECT COUNT(*) as count FROM reserva 
            WHERE Estado = 'Aprobada' 
            AND fechaReserva >= CURDATE()
        ")->count;

        // Tasa de uso (porcentaje de espacios con al menos una reserva en el último mes)
        $haceUnMes = Carbon::now()->subMonth()->format('Y-m-d');
        $espaciosConReservas = DB::selectOne("
            SELECT COUNT(DISTINCT espacio) as count FROM reserva 
            WHERE fechaReserva >= ? AND Estado = 'Aprobada'
        ", [$haceUnMes])->count;

        $totalEspacios = DB::selectOne("SELECT COUNT(*) as count FROM espacio WHERE Estado = 1")->count;

        $tasaUso = $totalEspacios > 0 ? ($espaciosConReservas * 100.0 / $totalEspacios) : 0.0;

        // Reservas este mes
        $inicioMesActual = Carbon::now()->startOfMonth()->format('Y-m-d');
        $finMesActual = Carbon::now()->endOfMonth()->format('Y-m-d');

        $reservasEsteMes = DB::selectOne("
            SELECT COUNT(*) as count FROM reserva 
            WHERE fechaReserva BETWEEN ? AND ? 
            AND Estado = 'Aprobada'
        ", [$inicioMesActual, $finMesActual])->count;

        // Reservas mes anterior
        $inicioMesAnterior = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $finMesAnterior = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');

        $reservasMesAnterior = DB::selectOne("
            SELECT COUNT(*) as count FROM reserva 
            WHERE fechaReserva BETWEEN ? AND ? 
            AND Estado = 'Aprobada'
        ", [$inicioMesAnterior, $finMesAnterior])->count;

        // Variación
        if ($reservasMesAnterior == 0) {
            $variacionReservas = $reservasEsteMes > 0 ? "↑ 100%" : "→ 0%";
        } else {
            $variacion = (($reservasEsteMes - $reservasMesAnterior) * 100.0) / $reservasMesAnterior;
            if ($variacion > 0) {
                $variacionReservas = sprintf("↑ %.1f%%", $variacion);
            } else if ($variacion < 0) {
                $variacionReservas = sprintf("↓ %.1f%%", abs($variacion));
            } else {
                $variacionReservas = "→ 0%";
            }
        }

        return [
            'totalEstudiantes' => $totalEstudiantes,
            'totalDocentes' => $totalDocentes,
            'reservasActivas' => $reservasActivas,
            'tasaUso' => round($tasaUso, 1),
            'reservasEsteMes' => $reservasEsteMes,
            'reservasMesAnterior' => $reservasMesAnterior,
            'variacionReservas' => $variacionReservas
        ];
    }

    public function obtenerUsoEspacios()
    {
        $sql = "
            SELECT 
                e.Nombre AS nombreEspacio,
                e.Codigo AS codigoEspacio,
                e.Tipo AS tipoEspacio,
                COUNT(r.IdReserva) AS totalReservas,
                ROUND((COUNT(r.IdReserva) * 100.0 / NULLIF((
                    SELECT COUNT(*) FROM reserva 
                    WHERE Estado = 'Aprobada' 
                    AND fechaReserva >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                ), 0)), 1) AS porcentajeUso
            FROM espacio e
            LEFT JOIN reserva r ON e.IdEspacio = r.espacio 
                AND r.Estado = 'Aprobada'
                AND r.fechaReserva >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
            WHERE e.Estado = 1
            GROUP BY e.IdEspacio, e.Nombre, e.Codigo, e.Tipo
            ORDER BY totalReservas DESC
            LIMIT 10
        ";

        $results = DB::select($sql);

        // Map to expected array structure
        return array_map(function ($row) {
            return [
                'nombreEspacio' => $row->nombreEspacio,
                'codigoEspacio' => $row->codigoEspacio,
                'tipoEspacio' => $row->tipoEspacio,
                'totalReservas' => $row->totalReservas,
                'porcentajeUso' => (float) $row->porcentajeUso ?: 0.0
            ];
        }, $results);
    }

    public function obtenerReservasPorMes()
    {
        $sql = "
            SELECT 
                DATE_FORMAT(fechaReserva, '%M') AS mes,
                YEAR(fechaReserva) AS anio,
                COUNT(*) AS totalReservas
            FROM reserva 
            WHERE Estado = 'Aprobada'
                AND fechaReserva >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY YEAR(fechaReserva), MONTH(fechaReserva), DATE_FORMAT(fechaReserva, '%M')
            ORDER BY anio DESC, MONTH(fechaReserva) DESC
            LIMIT 6
        ";

        $results = DB::select($sql);

        return array_map(function ($row) {
            return [
                'mes' => $row->mes,
                'anio' => $row->anio,
                'totalReservas' => $row->totalReservas
            ];
        }, $results);
    }
}
