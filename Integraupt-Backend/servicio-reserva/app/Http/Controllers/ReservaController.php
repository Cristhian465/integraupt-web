<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservaActualizacionRequest;
use App\Http\Requests\ReservaCreacionRequest;
use App\Services\ReservaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReservaController extends Controller
{
    public function __construct(private ReservaService $reservaService)
    {
    }

    public function listarReservas(): JsonResponse
    {
        return response()->json($this->reservaService->listarTodas());
    }

    public function obtenerReserva(int $id): JsonResponse|Response
    {
        $reserva = $this->reservaService->buscarPorId($id);
        if ($reserva === null) {
            return response()->noContent(404);
        }

        return response()->json($reserva);
    }

    public function obtenerReservasPorUsuario(int $usuarioId, Request $request): JsonResponse
    {
        $estado = $request->query('estado');
        $estados = $estado !== null ? (is_array($estado) ? $estado : [$estado]) : null;

        return response()->json($this->reservaService->listarPorUsuario($usuarioId, $estados));
    }

    public function obtenerResumenReservasUsuario(int $usuarioId): JsonResponse
    {
        return response()->json($this->reservaService->listarResumenPorUsuario($usuarioId));
    }

    public function crearReserva(ReservaCreacionRequest $request): JsonResponse
    {
        $resultado = $this->reservaService->crearReserva($request->validated());

        return response()->json([
            'reserva' => $resultado['reserva'],
            'qr' => $resultado['qr'],
        ], 201);
    }

    public function obtenerQrReserva(int $id): JsonResponse|Response
    {
        $resultado = $this->reservaService->obtenerReservaConQr($id);
        if ($resultado === null) {
            return response()->noContent(404);
        }

        return response()->json([
            'reserva' => $resultado['reserva'],
            'qr' => $resultado['qr'],
        ]);
    }

    public function actualizarReserva(int $id, ReservaActualizacionRequest $request): JsonResponse
    {
        $reserva = $this->reservaService->actualizarReserva($id, $request->validated());

        return response()->json($reserva);
    }

    public function eliminarReserva(int $id): Response
    {
        $this->reservaService->eliminarReserva($id);

        return response()->noContent();
    }
}
