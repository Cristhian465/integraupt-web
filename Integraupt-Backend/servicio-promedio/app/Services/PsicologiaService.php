<?php

namespace App\Services;

use App\Models\BloqueHorario;
use App\Models\CitaPsicologia;
use App\Models\Psicologo;
use Carbon\Carbon;
use InvalidArgumentException;
use LogicException;

class PsicologiaService
{
    public function listarPsicologos()
    {
        return Psicologo::where('Estado', 1)
            ->orderBy('Nombre')
            ->get()
            ->map(fn (Psicologo $psicologo) => [
                'id' => $psicologo->IdPsicologo,
                'nombre' => $psicologo->Nombre,
                'especialidad' => $psicologo->Especialidad,
            ])
            ->all();
    }

    public function listarBloquesDisponibles(int $psicologoId, string $fecha): array
    {
        $ocupados = CitaPsicologia::where('Psicologo', $psicologoId)
            ->where('Fecha', $fecha)
            ->where('Estado', '!=', 'Cancelada')
            ->pluck('Bloque');

        return BloqueHorario::whereNotIn('IdBloque', $ocupados)
            ->orderBy('Orden')
            ->get()
            ->map(fn (BloqueHorario $bloque) => [
                'id' => $bloque->IdBloque,
                'nombre' => $bloque->Nombre,
                'horaInicio' => $bloque->HoraInicio,
                'horaFin' => $bloque->HoraFinal,
            ])
            ->all();
    }

    public function listarCitasPorUsuario(int $usuarioId): array
    {
        return CitaPsicologia::with(['psicologo', 'bloque'])
            ->where('Estudiante', $usuarioId)
            ->orderByDesc('Fecha')
            ->get()
            ->map(fn (CitaPsicologia $cita) => $this->mapearCita($cita))
            ->all();
    }

    public function registrarCita(array $datos): array
    {
        $usuarioId = (int) ($datos['usuarioId'] ?? 0);
        $psicologoId = (int) ($datos['psicologoId'] ?? 0);
        $bloqueId = (int) ($datos['bloqueId'] ?? 0);
        $fecha = $datos['fecha'] ?? null;
        $motivo = isset($datos['motivo']) ? trim((string) $datos['motivo']) : null;

        if ($usuarioId < 1 || $psicologoId < 1 || $bloqueId < 1 || !$fecha) {
            throw new InvalidArgumentException('Faltan datos obligatorios para registrar la cita.');
        }

        $fechaSolicitada = Carbon::parse($fecha)->format('Y-m-d');

        if (Carbon::parse($fechaSolicitada)->lt(Carbon::today())) {
            throw new InvalidArgumentException('No puedes agendar una cita en una fecha pasada.');
        }

        $cupoOcupado = CitaPsicologia::where('Psicologo', $psicologoId)
            ->where('Bloque', $bloqueId)
            ->where('Fecha', $fechaSolicitada)
            ->where('Estado', '!=', 'Cancelada')
            ->exists();

        if ($cupoOcupado) {
            throw new LogicException('El horario seleccionado ya no está disponible para este psicólogo.');
        }

        $cita = CitaPsicologia::create([
            'Estudiante' => $usuarioId,
            'Psicologo' => $psicologoId,
            'Bloque' => $bloqueId,
            'Fecha' => $fechaSolicitada,
            'Motivo' => $motivo !== '' ? $motivo : null,
            'Estado' => 'Pendiente',
            'FechaSolicitud' => Carbon::now(),
        ]);

        $cita->load(['psicologo', 'bloque']);

        return $this->mapearCita($cita);
    }

    public function cancelarCita(int $citaId, int $usuarioId): array
    {
        $cita = CitaPsicologia::with(['psicologo', 'bloque'])
            ->where('IdCita', $citaId)
            ->where('Estudiante', $usuarioId)
            ->first();

        if (!$cita) {
            throw new InvalidArgumentException('No se encontró la cita indicada para este usuario.');
        }

        if ($cita->Estado === 'Cancelada') {
            throw new LogicException('La cita ya se encuentra cancelada.');
        }

        $cita->Estado = 'Cancelada';
        $cita->save();

        return $this->mapearCita($cita);
    }

    private function mapearCita(CitaPsicologia $cita): array
    {
        return [
            'id' => $cita->IdCita,
            'usuarioId' => $cita->Estudiante,
            'psicologoId' => $cita->Psicologo,
            'psicologoNombre' => $cita->psicologo?->Nombre,
            'bloqueId' => $cita->Bloque,
            'horaInicio' => $cita->bloque?->HoraInicio,
            'horaFin' => $cita->bloque?->HoraFinal,
            'fecha' => $cita->Fecha?->format('Y-m-d'),
            'motivo' => $cita->Motivo,
            'estado' => $cita->Estado,
            'fechaSolicitud' => $cita->FechaSolicitud?->format('Y-m-d\TH:i:s'),
        ];
    }
}
