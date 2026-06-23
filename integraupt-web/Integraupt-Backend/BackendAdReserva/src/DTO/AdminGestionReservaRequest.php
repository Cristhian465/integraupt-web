<?php

namespace App\DTO;

class AdminGestionReservaRequest {
    public ?int $usuarioGestionId;
    public ?string $accion;
    public ?string $motivo;
    public ?string $comentarios;

    public function __construct(?int $usuarioGestionId, ?string $accion, ?string $motivo, ?string $comentarios) {
        $this->usuarioGestionId = $usuarioGestionId;
        $this->accion = $accion;
        $this->motivo = $motivo;
        $this->comentarios = $comentarios;
    }
}
