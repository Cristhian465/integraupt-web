<?php

namespace App\Services;

use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Evento;
use App\Models\EventoInscripcion;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class InscripcionService
{
    public function listar(int $idEvento): Collection
    {
        return EventoInscripcion::with('usuario')
            ->where('IdEvento', $idEvento)
            ->orderBy('FechaInscripcion')
            ->get();
    }

    public function inscribir(int $idEvento, int $idUsuario): EventoInscripcion
    {
        $evento = Evento::findOrFail($idEvento);

        if ($evento->Estado !== Evento::ESTADO_PUBLICADO) {
            throw new InvalidArgumentException('Solo es posible inscribirse a eventos publicados.');
        }

        $tipoUsuario = $this->resolverTipoYEscuela($idUsuario);

        if ($tipoUsuario === null) {
            throw new InvalidArgumentException('El usuario no es un estudiante ni un docente registrado.');
        }

        [$tipo, $idEscuelaUsuario, $idFacultadUsuario] = $tipoUsuario;

        $this->validarPertenencia($evento, $idEscuelaUsuario, $idFacultadUsuario);

        $yaInscrito = EventoInscripcion::where('IdEvento', $idEvento)
            ->where('IdUsuario', $idUsuario)
            ->where('Estado', '!=', EventoInscripcion::ESTADO_CANCELADO)
            ->exists();

        if ($yaInscrito) {
            throw new InvalidArgumentException('El usuario ya esta inscrito en este evento.');
        }

        if ($evento->AforoMaximo !== null) {
            $ocupados = EventoInscripcion::where('IdEvento', $idEvento)
                ->where('Estado', '!=', EventoInscripcion::ESTADO_CANCELADO)
                ->count();

            if ($ocupados >= $evento->AforoMaximo) {
                throw new InvalidArgumentException('El evento ha alcanzado su aforo maximo.');
            }
        }

        $inscripcion = EventoInscripcion::create([
            'IdEvento' => $idEvento,
            'IdUsuario' => $idUsuario,
            'TipoUsuario' => $tipo,
            'Estado' => EventoInscripcion::ESTADO_INSCRITO,
            'CodigoQr' => (string) Str::uuid(),
        ]);

        return $inscripcion->refresh();
    }

    public function cancelar(int $idInscripcion): EventoInscripcion
    {
        $inscripcion = EventoInscripcion::findOrFail($idInscripcion);
        $inscripcion->update(['Estado' => EventoInscripcion::ESTADO_CANCELADO]);

        return $inscripcion->refresh();
    }

    public function checkin(string $codigoQr): EventoInscripcion
    {
        $inscripcion = EventoInscripcion::where('CodigoQr', $codigoQr)->first();

        if (! $inscripcion) {
            throw new RuntimeException('Codigo QR no reconocido.');
        }

        if ($inscripcion->Estado === EventoInscripcion::ESTADO_CANCELADO) {
            throw new InvalidArgumentException('Esta inscripcion fue cancelada.');
        }

        if ($inscripcion->Estado === EventoInscripcion::ESTADO_ASISTIO) {
            throw new InvalidArgumentException('Este asistente ya registro su ingreso.');
        }

        $inscripcion->update(['Estado' => EventoInscripcion::ESTADO_ASISTIO]);

        return $inscripcion->refresh();
    }

    /**
     * @return array{0: string, 1: int|null, 2: int|null}|null [tipoUsuario, idEscuela, idFacultad]
     */
    private function resolverTipoYEscuela(int $idUsuario): ?array
    {
        $estudiante = Estudiante::with('escuela')->where('IdUsuario', $idUsuario)->first();
        if ($estudiante) {
            return [EventoInscripcion::TIPO_ESTUDIANTE, $estudiante->Escuela, $estudiante->escuela?->IdFacultad];
        }

        $docente = Docente::with('escuela')->where('IdUsuario', $idUsuario)->first();
        if ($docente) {
            return [EventoInscripcion::TIPO_DOCENTE, $docente->Escuela, $docente->escuela?->IdFacultad];
        }

        return null;
    }

    private function validarPertenencia(Evento $evento, ?int $idEscuelaUsuario, ?int $idFacultadUsuario): void
    {
        if ($evento->Alcance === Evento::ALCANCE_ESCUELA) {
            if ($idEscuelaUsuario !== $evento->IdEscuela) {
                throw new InvalidArgumentException('Este evento es exclusivo para una escuela a la que no perteneces.');
            }

            return;
        }

        if ($idFacultadUsuario !== $evento->IdFacultad) {
            throw new InvalidArgumentException('Este evento es exclusivo para una facultad a la que no perteneces.');
        }
    }
}
