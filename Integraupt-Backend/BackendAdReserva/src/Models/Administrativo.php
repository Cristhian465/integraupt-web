<?php

namespace App\Models;

class Administrativo {
    public ?int $id;
    public ?int $usuarioId;
    public ?int $escuelaId;

    public function __construct(?int $id = null, ?int $usuarioId = null, ?int $escuelaId = null) {
        $this->id = $id;
        $this->usuarioId = $usuarioId;
        $this->escuelaId = $escuelaId;
    }
}
