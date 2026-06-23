<?php

namespace App\Http\Controllers;

use App\Exceptions\ReservaQrNotFoundException;
use App\Http\Requests\ReservaQrRequest;
use App\Models\ReservaQr;
use App\Services\QrGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ReservaQrController extends Controller
{
    public function __construct(private QrGeneratorService $qrGeneratorService)
    {
    }

    public function generar(ReservaQrRequest $request): JsonResponse
    {
        $data = $request->validated();

        $existente = ReservaQr::where('reserva_id', $data['reservaId'])->first();

        $token = trim((string) ($data['token'] ?? ''));
        if ($token === '') {
            $token = $existente->token ?? (string) Str::uuid();
        }

        $reservaQr = $existente ?? new ReservaQr();
        $reservaQr->fill([
            'token' => $token,
            'reserva_id' => $data['reservaId'],
            'laboratorio' => $data['laboratorio'],
            'fecha' => $data['fecha'],
            'hora' => $data['hora'],
            'estado' => $data['estado'],
            'solicitante_nombre' => $data['solicitanteNombre'],
            'solicitante_codigo' => $data['solicitanteCodigo'],
            'generado_en' => now(),
        ]);
        $reservaQr->save();

        return response()->json($this->buildResponse($reservaQr), 201);
    }

    public function verificar(string $token): JsonResponse
    {
        $reservaQr = ReservaQr::where('token', $token)->first();
        if (!$reservaQr) {
            throw new ReservaQrNotFoundException($token);
        }

        return response()->json([
            'token' => $reservaQr->token,
            'reserva' => $this->buildReserva($reservaQr),
            'generadoEn' => $reservaQr->generado_en,
            'verificadoEn' => now(),
        ]);
    }

    public function obtenerPorReserva(string $reservaId): JsonResponse
    {
        $reservaQr = ReservaQr::where('reserva_id', $reservaId)->first();
        if (!$reservaQr) {
            throw new ReservaQrNotFoundException($reservaId);
        }

        return response()->json($this->buildResponse($reservaQr));
    }

    private function buildResponse(ReservaQr $reservaQr): array
    {
        $verificationUrl = rtrim(config('services.qr.verification_base_url'), '/') . '/qr/reservas/' . $reservaQr->token;

        return [
            'token' => $reservaQr->token,
            'verificationUrl' => $verificationUrl,
            'qrBase64' => $this->qrGeneratorService->generar($verificationUrl),
            'reserva' => $this->buildReserva($reservaQr),
            'generadoEn' => $reservaQr->generado_en,
        ];
    }

    private function buildReserva(ReservaQr $reservaQr): array
    {
        return [
            'reservaId' => $reservaQr->reserva_id,
            'laboratorio' => $reservaQr->laboratorio,
            'fecha' => $reservaQr->fecha,
            'hora' => $reservaQr->hora,
            'estado' => $reservaQr->estado,
            'solicitanteNombre' => $reservaQr->solicitante_nombre,
            'solicitanteCodigo' => $reservaQr->solicitante_codigo,
        ];
    }
}
