<?php

namespace App\DTO;

class AdminReservaListResponse {
    /** @var AdminReservaCardDto[] */
    public array $reservas;
    public AdminReservaSummaryDto $resumen;

    public function __construct(array $reservas, AdminReservaSummaryDto $resumen) {
        $this->reservas = $reservas;
        $this->resumen = $resumen;
    }
}
