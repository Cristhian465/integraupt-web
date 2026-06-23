<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class AuditoriaExportService
{
    private $auditoriaService;

    public function __construct(AuditoriaService $auditoriaService)
    {
        $this->auditoriaService = $auditoriaService;
    }

    public function generarPdf(array $filtro)
    {
        $registros = $this->auditoriaService->buscar($filtro);

        $html = '<h2 style="text-align: center; font-family: sans-serif;">Reporte de Auditoria de Reservas</h2>';
        $html .= '<p style="font-family: sans-serif;">Total de eventos: ' . count($registros) . '</p>';
        $html .= '<table border="1" width="100%" cellspacing="0" cellpadding="5" style="font-family: sans-serif; font-size: 12px;">';
        $html .= '<thead><tr>';
        $html .= '<th># Audit</th><th>Reserva</th><th>Cambio</th><th>Usuario</th><th>Espacio</th><th>Fecha</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($registros as $registro) {
            $fecha = $registro['fechaCambio'] ? Carbon::parse($registro['fechaCambio'])->format('d/m/Y H:i') : '-';
            $cambio = $registro['estadoAnterior'] . ' -> ' . $registro['estadoNuevo'];
            $usuario = $registro['usuarioCambioNombre'] ?? 'Sin usuario';
            $espacio = $registro['nombreEspacio'] ?? 'Sin espacio';

            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($registro['idAudit']) . '</td>';
            $html .= '<td>#' . htmlspecialchars($registro['idReserva']) . '</td>';
            $html .= '<td>' . htmlspecialchars($cambio) . '</td>';
            $html .= '<td>' . htmlspecialchars($usuario) . '</td>';
            $html .= '<td>' . htmlspecialchars($espacio) . '</td>';
            $html .= '<td>' . htmlspecialchars($fecha) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        if (app()->bound('dompdf.wrapper')) {
            $pdf = app('dompdf.wrapper');
            return $pdf->loadHTML($html)->output();
        }

        // Si no está instalado barryvdh/laravel-dompdf, retornamos HTML.
        return $html;
    }

    public function generarExcel(array $filtro)
    {
        $registros = $this->auditoriaService->buscar($filtro);

        $output = fopen('php://temp', 'w');
        
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['# Audit', 'Reserva', 'Cambio', 'Usuario', 'Espacio', 'Fecha']);

        foreach ($registros as $registro) {
            $fecha = $registro['fechaCambio'] ? Carbon::parse($registro['fechaCambio'])->format('d/m/Y H:i') : '-';
            $cambio = $registro['estadoAnterior'] . ' -> ' . $registro['estadoNuevo'];
            $usuario = $registro['usuarioCambioNombre'] ?? 'Sin usuario';
            $espacio = $registro['nombreEspacio'] ?? 'Sin espacio';

            fputcsv($output, [
                $registro['idAudit'],
                $registro['idReserva'],
                $cambio,
                $usuario,
                $espacio,
                $fecha
            ]);
        }

        rewind($output);
        $csvData = stream_get_contents($output);
        fclose($output);

        return $csvData;
    }
}
