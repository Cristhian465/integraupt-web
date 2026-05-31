<?php

namespace App\DTO;

class AdminReservaFilter {
    public ?string $estado;
    public ?string $tipoEspacio;
    public ?int $facultadId;
    public ?int $escuelaId;
    public ?string $fechaReserva; // Y-m-d
    public ?string $search;
    public ?int $usuarioId;
    public ?string $rol;

    public function __construct(
        ?string $estado,
        ?string $tipoEspacio,
        ?int $facultadId,
        ?int $escuelaId,
        ?string $fechaReserva,
        ?string $search,
        ?int $usuarioId,
        ?string $rol
    ) {
        $this->estado = $estado;
        $this->tipoEspacio = $tipoEspacio;
        $this->facultadId = $facultadId;
        $this->escuelaId = $escuelaId;
        $this->fechaReserva = $fechaReserva;
        $this->search = $search;
        $this->usuarioId = $usuarioId;
        $this->rol = $rol;
    }

    public function getEstado(): ?string {
        return $this->estado !== null && trim($this->estado) !== '' ? trim($this->estado) : null;
    }

    public function getTipoEspacio(): ?string {
        return $this->tipoEspacio !== null && trim($this->tipoEspacio) !== '' ? trim($this->tipoEspacio) : null;
    }

    public function getFacultadId(): ?int {
        return $this->facultadId;
    }

    public function getEscuelaId(): ?int {
        return $this->escuelaId;
    }

    public function getFechaReserva(): ?string {
        return $this->fechaReserva !== null && trim($this->fechaReserva) !== '' ? trim($this->fechaReserva) : null;
    }

    public function getSearch(): ?string {
        return $this->search !== null && trim($this->search) !== '' ? trim($this->search) : null;
    }

    public function getUsuarioId(): ?int {
        return $this->usuarioId;
    }

    public function getRol(): ?string {
        return $this->rol !== null && trim($this->rol) !== '' ? trim($this->rol) : null;
    }
}
