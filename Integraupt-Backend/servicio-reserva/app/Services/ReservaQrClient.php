<?php

namespace App\Services;

use App\Models\BloqueHorario;
use App\Models\Espacio;
use App\Models\Reserva;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReservaQrClient
{
    private readonly string $baseUrl;

    public function __construct(private UsuarioInfoService $usuarioInfoService)
    {
        $this->baseUrl = rtrim(config('services.qr_reserva.base_url', 'http://localhost:8090'), '/');
    }

    public function generarQr(Reserva $reserva, ?Espacio $espacio, ?BloqueHorario $bloque): ?array
    {
        if ($reserva->IdReserva === null) {
            throw new \InvalidArgumentException('La reserva debe estar persistida antes de generar el QR');
        }

        $payload = $this->construirRequest($reserva, $espacio, $bloque);

        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/api/v1/qr/reservas", $payload);

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $ex) {
            Log::warning("No se pudo generar el QR para la reserva {$reserva->IdReserva}: {$ex->getMessage()}");

            return null;
        }
    }

    public function obtenerQrExistente(?int $reservaId): ?array
    {
        if ($reservaId === null) {
            return null;
        }

        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/api/v1/qr/reservas/reserva/{$reservaId}");

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $ex) {
            Log::warning("No se pudo recuperar el QR existente para la reserva {$reservaId}: {$ex->getMessage()}");

            return null;
        }
    }

    private function construirRequest(Reserva $reserva, ?Espacio $espacio, ?BloqueHorario $bloque): array
    {
        $solicitante = $this->usuarioInfoService->obtenerSolicitante($reserva->usuario) ?? [
            'nombreCompleto' => 'Usuario ' . ($reserva->usuario ?? 'desconocido'),
            'codigo' => $reserva->usuario !== null ? (string) $reserva->usuario : 'SIN-CODIGO',
        ];

        return [
            'reservaId' => $reserva->IdReserva,
            'laboratorio' => $this->obtenerNombreEspacio($reserva, $espacio),
            'fecha' => $reserva->fechaReserva?->format('Y-m-d') ?? '',
            'hora' => $this->obtenerHorario($reserva, $bloque),
            'estado' => $reserva->Estado,
            'solicitanteNombre' => $solicitante['nombreCompleto'],
            'solicitanteCodigo' => $solicitante['codigo'],
        ];
    }

    private function obtenerNombreEspacio(Reserva $reserva, ?Espacio $espacio): string
    {
        if ($espacio !== null && trim((string) $espacio->Nombre) !== '') {
            return trim($espacio->Nombre);
        }

        return $reserva->espacio !== null ? "Espacio {$reserva->espacio}" : 'Espacio por confirmar';
    }

    private function obtenerHorario(Reserva $reserva, ?BloqueHorario $bloque): string
    {
        if ($bloque !== null) {
            $horario = trim($this->formatearHorario($bloque));
            $nombreBloque = trim((string) $bloque->Nombre);

            if ($horario !== '' && $nombreBloque !== '') {
                return "{$nombreBloque} ({$horario})";
            }
            if ($horario !== '') {
                return $horario;
            }
            if ($nombreBloque !== '') {
                return $nombreBloque;
            }
        }

        return $reserva->bloque !== null ? "Bloque {$reserva->bloque}" : 'Horario por confirmar';
    }

    private function formatearHorario(BloqueHorario $bloque): string
    {
        $inicio = $bloque->HoraInicio !== null ? substr((string) $bloque->HoraInicio, 0, 5) : null;
        $fin = $bloque->HoraFinal !== null ? substr((string) $bloque->HoraFinal, 0, 5) : null;

        if ($inicio !== null && $fin !== null) {
            return "{$inicio} - {$fin}";
        }

        return $inicio ?? $fin ?? '';
    }
}
