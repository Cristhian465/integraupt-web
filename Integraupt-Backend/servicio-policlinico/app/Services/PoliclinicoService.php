<?php

namespace App\Services;

use App\Models\BloqueHorario;
use App\Models\CitaPoliclinico;
use App\Models\Medico;
use App\Models\TipoAtencion;
use Carbon\Carbon;
use InvalidArgumentException;
use LogicException;

class PoliclinicoService
{
    private const ESTADOS_VALIDOS = ['Pendiente', 'Confirmada', 'Atendida', 'Cancelada'];

    public function listarTiposAtencion(): array
    {
        return TipoAtencion::where('Estado', 1)
            ->orderBy('Nombre')
            ->get()
            ->map(fn (TipoAtencion $tipo) => $this->mapearTipoAtencion($tipo))
            ->all();
    }

    public function listarMedicosPorTipoAtencion(int $tipoAtencionId): array
    {
        return Medico::where('Estado', 1)
            ->whereHas('tiposAtencion', function ($query) use ($tipoAtencionId) {
                $query->where('tipo_atencion.IdTipoAtencion', $tipoAtencionId);
            })
            ->orderBy('Nombre')
            ->get()
            ->map(fn (Medico $medico) => $this->mapearMedico($medico))
            ->all();
    }

    public function listarBloquesDisponibles(int $medicoId, string $fecha): array
    {
        $ocupados = CitaPoliclinico::where('Medico', $medicoId)
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
        return CitaPoliclinico::with(['medico', 'tipoAtencion', 'bloque'])
            ->where('Estudiante', $usuarioId)
            ->orderByDesc('Fecha')
            ->get()
            ->map(fn (CitaPoliclinico $cita) => $this->mapearCita($cita))
            ->all();
    }

    public function registrarCita(array $datos): array
    {
        $usuarioId = (int) ($datos['usuarioId'] ?? 0);
        $medicoId = (int) ($datos['medicoId'] ?? 0);
        $tipoAtencionId = (int) ($datos['tipoAtencionId'] ?? 0);
        $bloqueId = (int) ($datos['bloqueId'] ?? 0);
        $fecha = $datos['fecha'] ?? null;
        $motivo = isset($datos['motivo']) ? trim((string) $datos['motivo']) : null;

        if ($usuarioId < 1 || $medicoId < 1 || $tipoAtencionId < 1 || $bloqueId < 1 || !$fecha) {
            throw new InvalidArgumentException('Faltan datos obligatorios para registrar la cita.');
        }

        $ofreceTipoAtencion = Medico::where('IdMedico', $medicoId)
            ->whereHas('tiposAtencion', function ($query) use ($tipoAtencionId) {
                $query->where('tipo_atencion.IdTipoAtencion', $tipoAtencionId);
            })
            ->exists();

        if (!$ofreceTipoAtencion) {
            throw new InvalidArgumentException('El médico seleccionado no atiende ese tipo de atención.');
        }

        $fechaSolicitada = Carbon::parse($fecha)->format('Y-m-d');

        if (Carbon::parse($fechaSolicitada)->lt(Carbon::today())) {
            throw new InvalidArgumentException('No puedes agendar una cita en una fecha pasada.');
        }

        $cupoOcupado = CitaPoliclinico::where('Medico', $medicoId)
            ->where('Bloque', $bloqueId)
            ->where('Fecha', $fechaSolicitada)
            ->where('Estado', '!=', 'Cancelada')
            ->exists();

        if ($cupoOcupado) {
            throw new LogicException('El horario seleccionado ya no está disponible para este médico.');
        }

        $cita = CitaPoliclinico::create([
            'Estudiante' => $usuarioId,
            'Medico' => $medicoId,
            'TipoAtencion' => $tipoAtencionId,
            'Bloque' => $bloqueId,
            'Fecha' => $fechaSolicitada,
            'Motivo' => $motivo !== '' ? $motivo : null,
            'Estado' => 'Pendiente',
            'FechaSolicitud' => Carbon::now(),
        ]);

        $cita->load(['medico', 'tipoAtencion', 'bloque']);

        return $this->mapearCita($cita);
    }

    public function cancelarCita(int $citaId, int $usuarioId): array
    {
        $cita = CitaPoliclinico::with(['medico', 'tipoAtencion', 'bloque'])
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

    // ===== Admin =====

    public function listarCitasAdmin(array $filtros): array
    {
        $query = CitaPoliclinico::with(['medico', 'tipoAtencion', 'estudiante', 'bloque']);

        if (!empty($filtros['estado'])) {
            $query->where('Estado', $filtros['estado']);
        }

        if (!empty($filtros['fecha'])) {
            $query->where('Fecha', $filtros['fecha']);
        }

        if (!empty($filtros['medicoId'])) {
            $query->where('Medico', (int) $filtros['medicoId']);
        }

        if (!empty($filtros['tipoAtencionId'])) {
            $query->where('TipoAtencion', (int) $filtros['tipoAtencionId']);
        }

        return $query->orderByDesc('Fecha')
            ->orderByDesc('IdCita')
            ->get()
            ->map(fn (CitaPoliclinico $cita) => $this->mapearCita($cita))
            ->all();
    }

    public function cambiarEstadoCita(int $citaId, string $estado): array
    {
        if (!in_array($estado, self::ESTADOS_VALIDOS, true)) {
            throw new InvalidArgumentException('El estado indicado no es válido.');
        }

        $cita = CitaPoliclinico::with(['medico', 'tipoAtencion', 'estudiante', 'bloque'])
            ->where('IdCita', $citaId)
            ->first();

        if (!$cita) {
            throw new InvalidArgumentException('No se encontró la cita indicada.');
        }

        $cita->Estado = $estado;
        $cita->save();

        return $this->mapearCita($cita);
    }

    public function listarTiposAtencionAdmin(): array
    {
        return TipoAtencion::orderBy('Nombre')
            ->get()
            ->map(fn (TipoAtencion $tipo) => $this->mapearTipoAtencion($tipo))
            ->all();
    }

    public function crearTipoAtencion(array $datos): array
    {
        $nombre = trim((string) ($datos['nombre'] ?? ''));

        if ($nombre === '') {
            throw new InvalidArgumentException('El nombre del tipo de atención es obligatorio.');
        }

        $tipo = TipoAtencion::create(['Nombre' => $nombre, 'Estado' => 1]);

        return $this->mapearTipoAtencion($tipo);
    }

    public function actualizarTipoAtencion(int $tipoAtencionId, array $datos): array
    {
        $tipo = TipoAtencion::find($tipoAtencionId);

        if (!$tipo) {
            throw new InvalidArgumentException('No se encontró el tipo de atención indicado.');
        }

        if (array_key_exists('nombre', $datos)) {
            $nombre = trim((string) $datos['nombre']);
            if ($nombre === '') {
                throw new InvalidArgumentException('El nombre del tipo de atención es obligatorio.');
            }
            $tipo->Nombre = $nombre;
        }

        if (array_key_exists('estado', $datos)) {
            $tipo->Estado = (bool) $datos['estado'];
        }

        $tipo->save();

        return $this->mapearTipoAtencion($tipo);
    }

    public function listarMedicosAdmin(): array
    {
        return Medico::with('tiposAtencion')
            ->orderBy('Nombre')
            ->get()
            ->map(fn (Medico $medico) => $this->mapearMedico($medico, true))
            ->all();
    }

    public function crearMedico(array $datos): array
    {
        $nombre = trim((string) ($datos['nombre'] ?? ''));

        if ($nombre === '') {
            throw new InvalidArgumentException('El nombre del médico es obligatorio.');
        }

        $medico = Medico::create(['Nombre' => $nombre, 'Estado' => 1]);

        $tiposAtencionIds = $this->normalizarIds($datos['tiposAtencionIds'] ?? []);
        if (!empty($tiposAtencionIds)) {
            $medico->tiposAtencion()->sync($tiposAtencionIds);
        }

        $medico->load('tiposAtencion');

        return $this->mapearMedico($medico, true);
    }

    public function actualizarMedico(int $medicoId, array $datos): array
    {
        $medico = Medico::find($medicoId);

        if (!$medico) {
            throw new InvalidArgumentException('No se encontró el médico indicado.');
        }

        if (array_key_exists('nombre', $datos)) {
            $nombre = trim((string) $datos['nombre']);
            if ($nombre === '') {
                throw new InvalidArgumentException('El nombre del médico es obligatorio.');
            }
            $medico->Nombre = $nombre;
        }

        if (array_key_exists('estado', $datos)) {
            $medico->Estado = (bool) $datos['estado'];
        }

        $medico->save();

        if (array_key_exists('tiposAtencionIds', $datos)) {
            $medico->tiposAtencion()->sync($this->normalizarIds($datos['tiposAtencionIds']));
        }

        $medico->load('tiposAtencion');

        return $this->mapearMedico($medico, true);
    }

    private function normalizarIds($ids): array
    {
        if (!is_array($ids)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    private function mapearCita(CitaPoliclinico $cita): array
    {
        $estudianteNombre = trim(($cita->estudiante?->Nombre ?? '') . ' ' . ($cita->estudiante?->Apellido ?? ''));

        return [
            'id' => $cita->IdCita,
            'usuarioId' => $cita->Estudiante,
            'estudianteNombre' => $estudianteNombre !== '' ? $estudianteNombre : null,
            'medicoId' => $cita->Medico,
            'medicoNombre' => $cita->medico?->Nombre,
            'tipoAtencionId' => $cita->TipoAtencion,
            'tipoAtencionNombre' => $cita->tipoAtencion?->Nombre,
            'bloqueId' => $cita->Bloque,
            'horaInicio' => $cita->bloque?->HoraInicio,
            'horaFin' => $cita->bloque?->HoraFinal,
            'fecha' => $cita->Fecha?->format('Y-m-d'),
            'motivo' => $cita->Motivo,
            'estado' => $cita->Estado,
            'fechaSolicitud' => $cita->FechaSolicitud?->format('Y-m-d\TH:i:s'),
        ];
    }

    private function mapearMedico(Medico $medico, bool $conTiposAtencion = false): array
    {
        $data = [
            'id' => $medico->IdMedico,
            'nombre' => $medico->Nombre,
            'estado' => (bool) $medico->Estado,
        ];

        if ($conTiposAtencion) {
            $data['tiposAtencion'] = $medico->tiposAtencion
                ->map(fn (TipoAtencion $tipo) => $this->mapearTipoAtencion($tipo))
                ->all();
        }

        return $data;
    }

    private function mapearTipoAtencion(TipoAtencion $tipo): array
    {
        return [
            'id' => $tipo->IdTipoAtencion,
            'nombre' => $tipo->Nombre,
            'estado' => (bool) $tipo->Estado,
        ];
    }
}
