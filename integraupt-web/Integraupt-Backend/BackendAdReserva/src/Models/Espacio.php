<?php

namespace App\Models;

class Espacio {
    public ?int $id;
    public ?string $codigo;
    public ?string $nombre;
    public ?string $tipo;
    public ?int $escuelaId;

    public function __construct(
        ?int $id = null,
        ?string $codigo = null,
        ?string $nombre = null,
        ?string $tipo = null,
        ?int $escuelaId = null
    ) {
        $this->id = $id;
        $this->codigo = $codigo;
        $this->nombre = $nombre;
        $this->tipo = $tipo;
        $this->escuelaId = $escuelaId;
    }
}
