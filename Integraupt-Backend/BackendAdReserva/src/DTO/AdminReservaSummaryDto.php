<?php

namespace App\DTO;

class AdminReservaSummaryDto {
    public int $pendientes;
    public int $aprobadas;
    public int $rechazadas;
    public int $canceladas;

    public function __construct(int $pendientes, int $aprobadas, int $rechazadas, int $canceladas) {
        $this->pendientes = $pendientes;
        $this->aprobadas = $aprobadas;
        $this->rechazadas = $rechazadas;
        $this->canceladas = $canceladas;
    }

    public static function empty(): self {
        return new self(0, 0, 0, 0);
    }
}
