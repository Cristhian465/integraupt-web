<?php

namespace App\Models;

class Reserva {
    public ?int $id;
    public ?int $usuarioId;
    public ?int $espacioId;
    public ?int $bloqueId;
    public ?int $cursoId;
    public ?string $fechaReserva; // Y-m-d
    public ?string $fechaSolicitud; // Y-m-d H:i:s
    public ?string $descripcionUso;
    public ?int $cantidadEstudiantes;
    public ?string $estado;

    public function __construct(
        ?int $id = null,
        ?int $usuarioId = null,
        ?int $espacioId = null,
        ?int $bloqueId = null,
        ?int $cursoId = null,
        ?string $fechaReserva = null,
        ?string $fechaSolicitud = null,
        ?string $descripcionUso = null,
        ?int $cantidadEstudiantes = null,
        ?string $estado = null
    ) {
        $this->id = $id;
        $this->usuarioId = $usuarioId;
        $this->espacioId = $espacioId;
        $this->bloqueId = $bloqueId;
        $this->cursoId = $cursoId;
        $this->fechaReserva = $fechaReserva;
        $this->fechaSolicitud = $fechaSolicitud ?? date('Y-m-d H:i:s');
        $this->descripcionUso = $descripcionUso;
        $this->cantidadEstudiantes = $cantidadEstudiantes;
        $this->estado = $estado;
    }
}
