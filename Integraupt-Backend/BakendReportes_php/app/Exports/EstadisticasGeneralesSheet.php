<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EstadisticasGeneralesSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    protected $estadisticas;

    public function __construct($estadisticas)
    {
        $this->estadisticas = $estadisticas;
    }

    public function array(): array
    {
        $fecha = "Generado: " . Carbon::now()->format('d/m/Y H:i');

        return [
            ['REPORTE DE ESTADÍSTICAS - SISTEMA DE RESERVAS'],
            [$fecha],
            [],
            ['Total Estudiantes', $this->estadisticas['totalEstudiantes']],
            ['Total Docentes', $this->estadisticas['totalDocentes']],
            ['Reservas Activas', $this->estadisticas['reservasActivas']],
            ['Tasa de Uso', $this->estadisticas['tasaUso'] . '%'],
            ['Variación', $this->estadisticas['variacionReservas']]
        ];
    }

    public function title(): string
    {
        return 'Estadísticas Generales';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
