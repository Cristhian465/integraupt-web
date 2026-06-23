<?php

namespace App\DTO;

class AdminReservaFiltersResponse {
    /** @var string[] */
    public array $tiposEspacio;
    /** @var SimpleOptionDto[] */
    public array $facultades;
    /** @var EscuelaOptionDto[] */
    public array $escuelas;

    public function __construct(array $tiposEspacio, array $facultades, array $escuelas) {
        $this->tiposEspacio = $tiposEspacio;
        $this->facultades = $facultades;
        $this->escuelas = $escuelas;
    }
}
