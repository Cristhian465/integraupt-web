<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportesExport implements WithMultipleSheets
{
    protected $estadisticas;
    protected $usoEspacios;
    protected $reservasPorMes;

    public function __construct($estadisticas, $usoEspacios, $reservasPorMes)
    {
        $this->estadisticas = $estadisticas;
        $this->usoEspacios = $usoEspacios;
        $this->reservasPorMes = $reservasPorMes;
    }

    public function sheets(): array
    {
        return [
            new EstadisticasGeneralesSheet($this->estadisticas),
            new UsoEspaciosSheet($this->usoEspacios),
            new ReservasPorMesSheet($this->reservasPorMes),
        ];
    }
}
