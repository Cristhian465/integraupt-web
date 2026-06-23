<?php

namespace App\Models;

class ReservaGestion {
    public ?int $id;
    public ?int $reservaId;
    public ?int $usuarioGestionId;
    public ?string $fechaGestion; // Y-m-d H:i:s
    public ?string $accion;
    public ?string $motivo;
    public ?string $comentarios;

    public function __construct(
        ?int $id = null,
        ?int $reservaId = null,
        ?int $usuarioGestionId = null,
        ?string $fechaGestion = null,
        ?string $accion = null,
        ?string $motivo = null,
        ?string $comentarios = null
    ) {
        $this->id = $id;
        $this->reservaId = $reservaId;
        $this->usuarioGestionId = $usuarioGestionId;
        $this->fechaGestion = $fechaGestion ?? date('Y-m-d H:i:s');
        $this->accion = $accion;
        $this->motivo = $motivo;
        $this->comentarios = $comentarios;
    }
}
