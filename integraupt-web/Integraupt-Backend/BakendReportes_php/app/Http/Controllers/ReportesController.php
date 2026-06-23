<?php

namespace App\Http\Controllers;

use App\Services\ReportesService;
use App\Exports\ReportesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportesController extends Controller
{
    protected $reportesService;

    public function __construct(ReportesService $reportesService)
    {
        $this->reportesService = $reportesService;
    }

    public function obtenerEstadisticasGenerales()
    {
        $estadisticas = $this->reportesService->obtenerEstadisticasGenerales();
        return response()->json($estadisticas);
    }

    public function obtenerUsoEspacios()
    {
        $usoEspacios = $this->reportesService->obtenerUsoEspacios();
        return response()->json($usoEspacios);
    }

    public function obtenerReservasPorMes()
    {
        $reservasPorMes = $this->reportesService->obtenerReservasPorMes();
        return response()->json($reservasPorMes);
    }

    public function exportarPDF()
    {
        try {
            $estadisticas = $this->reportesService->obtenerEstadisticasGenerales();
            $usoEspacios = $this->reportesService->obtenerUsoEspacios();
            $reservasPorMes = $this->reportesService->obtenerReservasPorMes();

            $data = [
                'estadisticas' => $estadisticas,
                'usoEspacios' => $usoEspacios,
                'reservasPorMes' => $reservasPorMes,
                'fechaGeneracion' => Carbon::now()->format('d/m/Y H:i')
            ];

            $pdf = Pdf::loadView('reports.pdf', $data);
            
            $timestamp = Carbon::now()->format('Ymd_His');
            $filename = "reporte_estadisticas_" . $timestamp . ".pdf";

            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    public function exportarExcel()
    {
        try {
            $estadisticas = $this->reportesService->obtenerEstadisticasGenerales();
            $usoEspacios = $this->reportesService->obtenerUsoEspacios();
            $reservasPorMes = $this->reportesService->obtenerReservasPorMes();

            $timestamp = Carbon::now()->format('Ymd_His');
            $filename = "reporte_estadisticas_" . $timestamp . ".xlsx";

            return Excel::download(new ReportesExport($estadisticas, $usoEspacios, $reservasPorMes), $filename);
            
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }
}
