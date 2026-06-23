<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

        $options = new Options();
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }

    public function generarExcel(array $filtro)
    {
        $registros = $this->auditoriaService->buscar($filtro);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Auditoria');

        $encabezados = ['# Audit', 'Reserva', 'Cambio', 'Usuario', 'Espacio', 'Fecha'];
        $sheet->fromArray($encabezados, null, 'A1');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        $fila = 2;
        foreach ($registros as $registro) {
            $fecha = $registro['fechaCambio'] ? Carbon::parse($registro['fechaCambio'])->format('d/m/Y H:i') : '-';
            $cambio = $registro['estadoAnterior'] . ' -> ' . $registro['estadoNuevo'];
            $usuario = $registro['usuarioCambioNombre'] ?? 'Sin usuario';
            $espacio = $registro['nombreEspacio'] ?? 'Sin espacio';

            $sheet->fromArray([
                $registro['idAudit'],
                $registro['idReserva'],
                $cambio,
                $usuario,
                $espacio,
                $fecha,
            ], null, "A{$fila}");
            $fila++;
        }

        foreach (range('A', 'F') as $columna) {
            $sheet->getColumnDimension($columna)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        $tmpPath = tempnam(sys_get_temp_dir(), 'audit_xlsx_');
        $writer->save($tmpPath);
        $xlsxData = file_get_contents($tmpPath);
        unlink($tmpPath);

        return $xlsxData;
    }
}
