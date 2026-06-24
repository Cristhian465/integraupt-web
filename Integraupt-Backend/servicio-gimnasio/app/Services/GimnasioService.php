<?php

namespace App\Services;

use App\Models\AsistenciaGimnasio;
use Carbon\Carbon;
use InvalidArgumentException;
use LogicException;

class GimnasioService
{
    private function esHorarioPermitido(Carbon $ahora): bool
    {
        if ($ahora->isWeekend()) {
            return false;
        }

        $hora = $ahora->hour;
        $minuto = $ahora->minute;
        $horaDecimal = $hora + ($minuto / 60);

        // 07:00-13:00 o 17:00-20:00
        if ($horaDecimal >= 7 && $horaDecimal < 13) {
            return true;
        }
        if ($horaDecimal >= 17 && $horaDecimal < 20) {
            return true;
        }

        return false;
    }

    public function registrarIngreso(int $usuarioId): array
    {
        $ahora = Carbon::now('America/Lima');
        
        if (!$this->esHorarioPermitido($ahora)) {
            // throw new LogicException('Fuera de horario de atención. Horario: L-V 07:00-13:00 y 17:00-20:00.');
        }

        $sesionActiva = AsistenciaGimnasio::where('IdUsuario', $usuarioId)
            ->whereNull('FechaSalida')
            ->first();

        if ($sesionActiva) {
            throw new LogicException('Ya tienes una sesión activa en el gimnasio.');
        }

        $asistencia = AsistenciaGimnasio::create([
            'IdUsuario' => $usuarioId,
            'FechaIngreso' => $ahora->format('Y-m-d H:i:s'),
        ]);

        return $this->mapearAsistencia($asistencia);
    }

    public function registrarSalida(int $usuarioId): array
    {
        $ahora = Carbon::now('America/Lima');

        $sesionActiva = AsistenciaGimnasio::where('IdUsuario', $usuarioId)
            ->whereNull('FechaSalida')
            ->first();

        if (!$sesionActiva) {
            throw new InvalidArgumentException('No tienes una sesión activa en el gimnasio.');
        }

        $sesionActiva->FechaSalida = $ahora->format('Y-m-d H:i:s');
        $sesionActiva->save();

        return $this->mapearAsistencia($sesionActiva);
    }

    public function estadoSesion(int $usuarioId): array
    {
        $sesionActiva = AsistenciaGimnasio::where('IdUsuario', $usuarioId)
            ->whereNull('FechaSalida')
            ->first();

        return [
            'activa' => $sesionActiva !== null,
            'sesion' => $sesionActiva ? $this->mapearAsistencia($sesionActiva) : null,
        ];
    }

    public function listarAsistencias(): array
    {
        return AsistenciaGimnasio::with(['usuario.estudiante.escuelaRel.facultad'])
            ->orderByDesc('FechaIngreso')
            ->get()
            ->map(fn (AsistenciaGimnasio $a) => $this->mapearAsistencia($a))
            ->all();
    }

    private function mapearAsistencia(AsistenciaGimnasio $asistencia): array
    {
        $fechaIngreso = Carbon::parse($asistencia->FechaIngreso);
        $fechaSalida = $asistencia->FechaSalida ? Carbon::parse($asistencia->FechaSalida) : null;
        $duracion = $fechaSalida ? (int) round($fechaIngreso->diffInMinutes($fechaSalida, true)) : null;

        $usuario = $asistencia->usuario;
        $estudiante = $usuario?->estudiante;
        $escuela = $estudiante?->escuelaRel;
        $facultad = $escuela?->facultad;

        $escuelaFacultad = null;
        if ($escuela && $facultad) {
            $escuelaFacultad = $escuela->Nombre . '/' . ($facultad->Abreviatura ?? $facultad->Nombre);
        }

        return [
            'id_asistencia' => $asistencia->IdAsistencia,
            'id_usuario' => $asistencia->IdUsuario,
            'usuario_nombre' => $usuario ? ($usuario->Nombre . ' ' . $usuario->Apellido) : null,
            'codigo_estudiante' => $estudiante?->Codigo,
            'escuela_facultad' => $escuelaFacultad,
            'fecha' => $fechaIngreso->format('Y-m-d'),
            'hora_ingreso' => $fechaIngreso->format('H:i:s'),
            'hora_salida' => $fechaSalida ? $fechaSalida->format('H:i:s') : null,
            'duracion_calculada' => $duracion,
        ];
    }
}
