<?php

namespace App\DTO;

use JsonSerializable;

class AdminReservaCardDto implements JsonSerializable {
    public ?int $id;
    public ?string $estado;
    public ?string $fechaReserva; // Y-m-d
    public ?string $fechaSolicitud; // Y-m-d H:i:s
    public ?string $descripcionUso;
    public ?int $cantidadEstudiantes;
    public ?string $espacioCodigo;
    public ?string $espacioNombre;
    public ?string $espacioTipo;
    public ?int $escuelaId;
    public ?string $escuelaNombre;
    public ?int $facultadId;
    public ?string $facultadNombre;
    public ?string $bloqueNombre;
    public ?string $horaInicio;
    public ?string $horaFin;
    public ?string $cursoNombre;
    public ?int $solicitanteId;
    public ?string $solicitanteNombre;
    public ?string $solicitanteApellido;
    public ?string $solicitanteCorreo;
    public ?string $ultimaAccion;
    public ?string $motivo;
    public ?string $comentarios;
    public ?string $fechaGestion;
    public ?int $gestorId;
    public ?string $gestorNombre;
    public ?string $gestorApellido;

    public function __construct(
        ?int $id,
        ?string $estado,
        ?string $fechaReserva,
        ?string $fechaSolicitud,
        ?string $descripcionUso,
        ?int $cantidadEstudiantes,
        ?string $espacioCodigo,
        ?string $espacioNombre,
        ?string $espacioTipo,
        ?int $escuelaId,
        ?string $escuelaNombre,
        ?int $facultadId,
        ?string $facultadNombre,
        ?string $bloqueNombre,
        ?string $horaInicio,
        ?string $horaFin,
        ?string $cursoNombre,
        ?int $solicitanteId,
        ?string $solicitanteNombre,
        ?string $solicitanteApellido,
        ?string $solicitanteCorreo,
        ?string $ultimaAccion,
        ?string $motivo,
        ?string $comentarios,
        ?string $fechaGestion,
        ?int $gestorId,
        ?string $gestorNombre,
        ?string $gestorApellido
    ) {
        $this->id = $id;
        $this->estado = $estado;
        $this->fechaReserva = $fechaReserva;
        $this->fechaSolicitud = $fechaSolicitud;
        $this->descripcionUso = $descripcionUso;
        $this->cantidadEstudiantes = $cantidadEstudiantes;
        $this->espacioCodigo = $espacioCodigo;
        $this->espacioNombre = $espacioNombre;
        $this->espacioTipo = $espacioTipo;
        $this->escuelaId = $escuelaId;
        $this->escuelaNombre = $escuelaNombre;
        $this->facultadId = $facultadId;
        $this->facultadNombre = $facultadNombre;
        $this->bloqueNombre = $bloqueNombre;
        $this->horaInicio = $horaInicio;
        $this->horaFin = $horaFin;
        $this->cursoNombre = $cursoNombre;
        $this->solicitanteId = $solicitanteId;
        $this->solicitanteNombre = $solicitanteNombre;
        $this->solicitanteApellido = $solicitanteApellido;
        $this->solicitanteCorreo = $solicitanteCorreo;
        $this->ultimaAccion = $ultimaAccion;
        $this->motivo = $motivo;
        $this->comentarios = $comentarios;
        $this->fechaGestion = $fechaGestion;
        $this->gestorId = $gestorId;
        $this->gestorNombre = $gestorNombre;
        $this->gestorApellido = $gestorApellido;
    }

    public function solicitanteNombreCompleto(): ?string {
        return self::buildNombreCompleto($this->solicitanteNombre, $this->solicitanteApellido);
    }

    public function gestorNombreCompleto(): ?string {
        return self::buildNombreCompleto($this->gestorNombre, $this->gestorApellido);
    }

    private static function buildNombreCompleto(?string $nombres, ?string $apellidos): ?string {
        if ($nombres === null && $apellidos === null) {
            return null;
        }
        if ($nombres === null) {
            return $apellidos;
        }
        if ($apellidos === null) {
            return $nombres;
        }
        return $nombres . ' ' . $apellidos;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'estado' => $this->estado,
            'fechaReserva' => $this->fechaReserva,
            'fechaSolicitud' => $this->fechaSolicitud,
            'descripcionUso' => $this->descripcionUso,
            'cantidadEstudiantes' => $this->cantidadEstudiantes,
            'espacioCodigo' => $this->espacioCodigo,
            'espacioNombre' => $this->espacioNombre,
            'espacioTipo' => $this->espacioTipo,
            'escuelaId' => $this->escuelaId,
            'escuelaNombre' => $this->escuelaNombre,
            'facultadId' => $this->facultadId,
            'facultadNombre' => $this->facultadNombre,
            'bloqueNombre' => $this->bloqueNombre,
            'horaInicio' => $this->horaInicio,
            'horaFin' => $this->horaFin,
            'cursoNombre' => $this->cursoNombre,
            'solicitanteId' => $this->solicitanteId,
            'solicitanteNombre' => $this->solicitanteNombre,
            'solicitanteApellido' => $this->solicitanteApellido,
            'solicitanteCorreo' => $this->solicitanteCorreo,
            'ultimaAccion' => $this->ultimaAccion,
            'motivo' => $this->motivo,
            'comentarios' => $this->comentarios,
            'fechaGestion' => $this->fechaGestion,
            'gestorId' => $this->gestorId,
            'gestorNombre' => $this->gestorNombre,
            'gestorApellido' => $this->gestorApellido,
            'solicitanteNombreCompleto' => $this->solicitanteNombreCompleto(),
            'gestorNombreCompleto' => $this->gestorNombreCompleto()
        ];
    }
}
