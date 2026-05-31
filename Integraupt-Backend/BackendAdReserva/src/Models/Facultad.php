<?php

namespace App\Models;

class Facultad {
    public ?int $id;
    public ?string $nombre;
    public ?string $abreviatura;

    public function __construct(?int $id = null, ?string $nombre = null, ?string $abreviatura = null) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->abreviatura = $abreviatura;
    }
}
