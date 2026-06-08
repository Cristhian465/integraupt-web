<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuditoriaService;
use App\Services\AuditoriaExportService;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuditoriaController extends Controller
{
    private $service;
    private $exportService;

    public function __construct(
        AuditoriaService $service,
        AuditoriaExportService $exportService
    ) {
        $this->service = $service;
        $this->exportService = $exportService;
    }

    public function listar(Request $request)
    {
        $filtro = $this->construirFiltro($request);
        return response()->json($this->service->buscar($filtro));
    }

    public function detalle($id)
    {
        $id = (int) $id;
        if ($id < 1) {
            return response()->json(['error' => 'Id inválido'], 400);
        }
        return response()->json($this->service->buscarPorId($id));
    }

    public function porReserva($reservaId)
    {
        $reservaId = (int) $reservaId;
        if ($reservaId < 1) {
            return response()->json(['error' => 'Id inválido'], 400);
        }
        return response()->json($this->service->listarPorReserva($reservaId));
    }

    public function exportarPdf(Request $request)
    {
        $filtro = $this->construirFiltro($request);
        $data = $this->exportService->generarPdf($filtro);
        $filename = "reporte_auditoria_" . round(microtime(true) * 1000) . ".pdf";

        return response($data)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportarExcel(Request $request)
    {
        $filtro = $this->construirFiltro($request);
        $data = $this->exportService->generarExcel($filtro);
        $filename = "reporte_auditoria_" . round(microtime(true) * 1000) . ".xlsx";

        return response($data)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function construirFiltro(Request $request)
    {
        return [
            'reservaId' => $request->reservaId,
            'estado' => $request->estado,
            'usuario' => $request->usuario,
            'fechaInicio' => $this->parseFecha($request->fechaInicio, false),
            'fechaFin' => $this->parseFecha($request->fechaFin, true),
        ];
    }

    private function parseFecha($valor, $endOfDay = false)
    {
        if (empty($valor)) {
            return null;
        }

        try {
            if (strlen($valor) == 10) {
                $fecha = Carbon::parse($valor);
                return $endOfDay ? $fecha->endOfDay()->format('Y-m-d H:i:s') : $fecha->startOfDay()->format('Y-m-d H:i:s');
            }
            return Carbon::parse($valor)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            throw new HttpException(400, "Formato de fecha no valido. Usa ISO-8601 (ej. 2025-11-11T15:30:00).");
        }
    }
}