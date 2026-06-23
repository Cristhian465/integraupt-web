<?php

namespace App\Models;

class Escuela {
    public ?int $id;
    public ?int $facultadId;
    public ?string $nombre;

    public function __construct(?int $id = null, ?int $facultadId = null, ?string $nombre = null) {
        $this->id = $id;
        $this->facultadId = $facultadId;
        $this->nombre = $nombre;
    }
}
