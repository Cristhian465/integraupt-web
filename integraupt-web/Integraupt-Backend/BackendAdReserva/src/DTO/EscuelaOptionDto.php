<?php

namespace App\DTO;

class EscuelaOptionDto {
    public ?int $id;
    public ?string $nombre;
    public ?int $facultadId;

    public function __construct(?int $id, ?string $nombre, ?int $facultadId) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->facultadId = $facultadId;
    }
}
