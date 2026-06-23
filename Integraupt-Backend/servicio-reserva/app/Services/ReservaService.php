<?php

namespace App\Services;

use App\Exceptions\ReservaNotFoundException;
use App\Exceptions\ReservaValidationException;
use App\Models\BloqueHorario;
use App\Models\Espacio;
use App\Models\Reserva;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class ReservaService
{
    private const ESTADOS_NORMALIZADOS = [
        'pendiente' => 'Pendiente',
        'aprobado' => 'Aprobada',
        'aprobada' => 'Aprobada',
        'cancelado' => 'Cancelada',
        'cancelada' => 'Cancelada',
        'rechazado' => 'Rechazada',
        'rechazada' => 'Rechazada',
    ];

    public function __construct(
        private ReservaQrClient $reservaQrClient,
        private UsuarioInfoService $usuarioInfoService,
        private SancionVerificationClient $sancionVerificationClient,
    ) {
    }

    public function listarTodas(): Collection
    {
        return Reserva::all();
    }

    public function buscarPorId(int $id): ?Reserva
    {
        return Reserva::find($id);
    }

    public function listarPorUsuario(?int $usuarioId, ?array $estados): array
    {
        if ($usuarioId === null) {
            return [];
        }

        $reservas = $this->obtenerReservasFiltradas($usuarioId, $estados);
        if ($reservas->isEmpty()) {
            return [];
        }

        [$bloques, $espacios] = $this->cargarBloquesYEspacios($reservas);

        return $reservas->map(function (Reserva $reserva) use ($bloques, $espacios) {
            $bloque = $bloques->get($reserva->bloque);
            $espacio = $espacios->get($reserva->espacio);

            return [
                'id' => $reserva->IdReserva,
                'usuarioId' => $reserva->usuario,
                'espacioId' => $reserva->espacio,
                'espacioNombre' => $espacio?->Nombre,
                'bloqueId' => $reserva->bloque,
                'bloqueHoraInicio' => $bloque?->HoraInicio !== null ? substr((string) $bloque->HoraInicio, 0, 5) : null,
                'bloqueHoraFin' => $bloque?->HoraFinal !== null ? substr((string) $bloque->HoraFinal, 0, 5) : null,
                'cursoId' => $reserva->curso,
                'fechaReserva' => $reserva->fechaReserva?->format('Y-m-d'),
                'fechaSolicitud' => $reserva->fechaSolicitud?->format('Y-m-d\TH:i:s'),
                'descripcionUso' => $reserva->DescripcionUso,
                'cantidadEstudiantes' => $reserva->CantidadEstudiantes,
                'estado' => $reserva->Estado,
            ];
        })->values()->all();
    }

    public function listarResumenPorUsuario(?int $usuarioId): array
    {
        if ($usuarioId === null) {
            return [];
        }

        $reservas = Reserva::where('usuario', $usuarioId)
            ->orderByDesc('fechaReserva')
            ->limit(20)
            ->get();

        if ($reservas->isEmpty()) {
            return [];
        }

        [$bloques, $espacios] = $this->cargarBloquesYEspacios($reservas);

        return $reservas->map(function (Reserva $reserva) use ($bloques, $espacios) {
            $bloque = $bloques->get($reserva->bloque);
            if ($bloque === null) {
                return null;
            }

            $espacio = $espacios->get($reserva->espacio);

            return [
                'reservaId' => $reserva->IdReserva,
                'espacioId' => $reserva->espacio,
                'espacioNombre' => $espacio?->Nombre,
                'espacioCodigo' => $espacio?->Codigo,
                'fechaReserva' => $reserva->fechaReserva?->format('Y-m-d'),
                'horaInicio' => $bloque->HoraInicio !== null ? substr((string) $bloque->HoraInicio, 0, 5) : null,
                'horaFin' => $bloque->HoraFinal !== null ? substr((string) $bloque->HoraFinal, 0, 5) : null,
                'estado' => $reserva->Estado,
            ];
        })->filter()->values()->all();
    }

    public function crearReserva(array $datos): array
    {
        $this->validarSancionActiva($datos['usuarioId']);
        $this->validarDuplicidadReserva(
            $datos['usuarioId'],
            $datos['espacioId'],
            $datos['bloqueId'],
            $datos['fechaReserva'],
            null
        );

        $espacio = Espacio::find($datos['espacioId']);
        if ($espacio === null) {
            throw new ReservaNotFoundException('Espacio no encontrado con el id proporcionado');
        }

        $this->validarCantidadEstudiantes($datos['cantidadEstudiantes'], $espacio);

        $reserva = new Reserva();
        $reserva->usuario = $datos['usuarioId'];
        $reserva->espacio = $datos['espacioId'];
        $reserva->bloque = $datos['bloqueId'];
        $reserva->curso = $datos['cursoId'];
        $reserva->fechaReserva = $datos['fechaReserva'];
        $reserva->fechaSolicitud = Carbon::now();
        $reserva->DescripcionUso = $datos['descripcionUso'] ?? null;
        $reserva->CantidadEstudiantes = $datos['cantidadEstudiantes'];
        $reserva->Estado = !empty($datos['estado']) ? $datos['estado'] : 'Pendiente';
        $reserva->save();

        $bloque = BloqueHorario::find($reserva->bloque);
        $qr = $this->reservaQrClient->generarQr($reserva, $espacio, $bloque);

        return ['reserva' => $reserva, 'qr' => $qr];
    }

    public function actualizarReserva(int $id, array $datos): Reserva
    {
        $reservaExistente = Reserva::find($id);
        if ($reservaExistente === null) {
            throw new ReservaNotFoundException('Reserva no encontrada con el id proporcionado');
        }

        $this->validarSancionActiva($datos['usuarioId']);

        $estadoActual = $reservaExistente->Estado;
        if ($estadoActual !== null && strcasecmp(trim($estadoActual), 'Pendiente') !== 0) {
            if ($this->puedeCancelarReserva($reservaExistente, $datos)) {
                $reservaExistente->Estado = $this->normalizarEstado($datos['estado'] ?? null);
                $reservaExistente->save();

                return $reservaExistente;
            }

            throw new ReservaValidationException('Solo se pueden editar reservas que se encuentran en estado Pendiente.');
        }

        if ((int) $reservaExistente->usuario !== (int) $datos['usuarioId']) {
            throw new ReservaValidationException('Solo el propietario puede editar la reserva.');
        }

        if ((int) $reservaExistente->espacio !== (int) $datos['espacioId']) {
            throw new ReservaValidationException('No se puede modificar el espacio asociado a la reserva desde esta opción.');
        }

        $espacio = Espacio::find($reservaExistente->espacio);
        if ($espacio === null) {
            throw new ReservaNotFoundException('Espacio no encontrado con el id proporcionado');
        }

        $this->validarCantidadEstudiantes($datos['cantidadEstudiantes'], $espacio);

        $reservaExistente->bloque = $datos['bloqueId'];
        $reservaExistente->curso = $datos['cursoId'];
        $reservaExistente->fechaReserva = $datos['fechaReserva'];
        $reservaExistente->DescripcionUso = $datos['descripcionUso'] ?? null;
        $reservaExistente->CantidadEstudiantes = $datos['cantidadEstudiantes'];

        if (!empty($datos['estado'])) {
            $reservaExistente->Estado = $this->normalizarEstado($datos['estado']);
        } elseif (empty($reservaExistente->Estado)) {
            $reservaExistente->Estado = 'Pendiente';
        }

        $this->validarDuplicidadReserva(
            $reservaExistente->usuario,
            $reservaExistente->espacio,
            $reservaExistente->bloque,
            $reservaExistente->fechaReserva,
            $reservaExistente->IdReserva
        );

        $reservaExistente->save();

        $bloque = BloqueHorario::find($reservaExistente->bloque);
        $this->reservaQrClient->generarQr($reservaExistente, $espacio, $bloque);

        return $reservaExistente;
    }

    public function obtenerReservaConQr(int $id): ?array
    {
        $reserva = Reserva::find($id);
        if ($reserva === null) {
            return null;
        }

        $espacio = Espacio::find($reserva->espacio);
        $bloque = BloqueHorario::find($reserva->bloque);

        $qr = $this->reservaQrClient->obtenerQrExistente($reserva->IdReserva)
            ?? $this->reservaQrClient->generarQr($reserva, $espacio, $bloque);

        return ['reserva' => $reserva, 'qr' => $qr];
    }

    public function eliminarReserva(int $id): void
    {
        $reserva = Reserva::find($id);
        if ($reserva === null) {
            throw new ReservaNotFoundException('Reserva no encontrada con el id proporcionado');
        }

        $reserva->delete();
    }

    private function obtenerReservasFiltradas(int $usuarioId, ?array $estados): Collection
    {
        if (empty($estados)) {
            return Reserva::where('usuario', $usuarioId)->get();
        }

        $estadosNormalizados = array_values(array_filter(array_map(function ($estado) {
            $estado = trim((string) $estado);

            return $estado !== '' ? ucfirst(strtolower($estado)) : null;
        }, $estados)));

        if (empty($estadosNormalizados)) {
            return Reserva::where('usuario', $usuarioId)->get();
        }

        return Reserva::where('usuario', $usuarioId)->whereIn('Estado', $estadosNormalizados)->get();
    }

    /** @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection} */
    private function cargarBloquesYEspacios(Collection $reservas): array
    {
        $bloqueIds = $reservas->pluck('bloque')->filter()->unique()->values();
        $bloques = BloqueHorario::whereIn('IdBloque', $bloqueIds)->get()->keyBy('IdBloque');

        $espacioIds = $reservas->pluck('espacio')->filter()->unique()->values();
        $espacios = Espacio::whereIn('IdEspacio', $espacioIds)->get()->keyBy('IdEspacio');

        return [$bloques, $espacios];
    }

    private function validarSancionActiva(?int $usuarioId): void
    {
        if ($usuarioId === null) {
            return;
        }

        $solicitante = $this->usuarioInfoService->obtenerSolicitante($usuarioId);
        if ($solicitante === null) {
            return;
        }

        $tipoUsuario = $this->mapearTipoUsuario($solicitante['rol'] ?? null);
        if ($tipoUsuario === null) {
            return;
        }

        $respuesta = $this->sancionVerificationClient->verificarSancionActiva($usuarioId, $tipoUsuario);

        if ($respuesta !== null && !empty($respuesta['sancionado'])) {
            $fechaInicio = $respuesta['fechaInicio'] ?? 'desconocida';
            $fechaFin = $respuesta['fechaFin'] ?? 'desconocida';
            $motivo = $respuesta['motivo'] ?? 'No especificado';

            throw new ReservaValidationException(
                "No puedes registrar reservas porque tienes una sanción activa del {$fechaInicio} al {$fechaFin}. Motivo: {$motivo}."
            );
        }
    }

    private function mapearTipoUsuario(?string $rol): ?string
    {
        if ($rol === null || trim($rol) === '') {
            return null;
        }

        $rolNormalizado = strtoupper(trim($rol));
        if (str_contains($rolNormalizado, 'DOCENTE')) {
            return 'DOCENTE';
        }
        if (str_contains($rolNormalizado, 'ESTUDIANT')) {
            return 'ESTUDIANTE';
        }

        return null;
    }

    private function validarCantidadEstudiantes(?int $cantidadEstudiantes, Espacio $espacio): void
    {
        if ($cantidadEstudiantes === null || $cantidadEstudiantes < 1) {
            throw new ReservaValidationException('La cantidad de estudiantes debe ser mayor a cero.');
        }

        $capacidad = $espacio->Capacidad;
        if ($capacidad !== null && $cantidadEstudiantes > $capacidad) {
            throw new ReservaValidationException(
                "La cantidad de estudiantes ({$cantidadEstudiantes}) supera la capacidad del espacio ({$capacidad})."
            );
        }
    }

    private function validarDuplicidadReserva(
        ?int $usuarioId,
        ?int $espacioId,
        ?int $bloqueId,
        ?string $fechaReserva,
        ?int $reservaActualId
    ): void {
        if ($usuarioId === null || $espacioId === null || $bloqueId === null || $fechaReserva === null) {
            return;
        }

        $porUsuario = Reserva::where('usuario', $usuarioId)
            ->where('bloque', $bloqueId)
            ->where('fechaReserva', $fechaReserva)
            ->first();

        if ($porUsuario !== null && ($reservaActualId === null || $porUsuario->IdReserva !== $reservaActualId)) {
            throw new ReservaValidationException('Ya existe una reserva del usuario para el bloque y fecha seleccionados.');
        }

        $porEspacio = Reserva::where('espacio', $espacioId)
            ->where('bloque', $bloqueId)
            ->where('fechaReserva', $fechaReserva)
            ->first();

        if ($porEspacio !== null && ($reservaActualId === null || $porEspacio->IdReserva !== $reservaActualId)) {
            throw new ReservaValidationException('El bloque seleccionado ya fue reservado en este espacio para la fecha indicada.');
        }
    }

    private function puedeCancelarReserva(Reserva $reservaExistente, array $datos): bool
    {
        if (!$this->esEstadoCancelado($datos['estado'] ?? null)) {
            return false;
        }

        if (!$this->esEstadoAprobado($reservaExistente->Estado)) {
            return false;
        }

        $this->validarAnticipacionCancelacion($reservaExistente);

        return true;
    }

    private function validarAnticipacionCancelacion(Reserva $reservaExistente): void
    {
        if ($reservaExistente->fechaReserva === null || $reservaExistente->bloque === null) {
            throw new ReservaValidationException(
                'No es posible validar la fecha y bloque de la reserva para realizar la cancelación.'
            );
        }

        $bloque = BloqueHorario::find($reservaExistente->bloque);
        if ($bloque === null) {
            throw new ReservaValidationException('No se encontró el bloque horario asociado a la reserva.');
        }

        if ($bloque->HoraInicio === null) {
            throw new ReservaValidationException(
                'El bloque horario asociado a la reserva no tiene hora de inicio configurada.'
            );
        }

        $fechaHoraInicio = Carbon::parse($reservaExistente->fechaReserva->format('Y-m-d') . ' ' . $bloque->HoraInicio);
        $horasRestantes = Carbon::now()->diffInHours($fechaHoraInicio, false);

        if ($horasRestantes < 12) {
            throw new ReservaValidationException(
                'Las reservas aprobadas solo se pueden cancelar con al menos 12 horas de anticipación.'
            );
        }
    }

    private function normalizarEstado(?string $estado): ?string
    {
        if ($estado === null) {
            return null;
        }

        $clave = strtolower(trim($estado));

        return self::ESTADOS_NORMALIZADOS[$clave] ?? trim($estado);
    }

    private function esEstadoCancelado(?string $estado): bool
    {
        return strcasecmp((string) $this->normalizarEstado($estado), 'Cancelada') === 0;
    }

    private function esEstadoAprobado(?string $estado): bool
    {
        return strcasecmp((string) $this->normalizarEstado($estado), 'Aprobada') === 0;
    }
}
