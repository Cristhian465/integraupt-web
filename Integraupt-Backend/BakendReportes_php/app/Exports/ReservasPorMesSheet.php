<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReservasPorMesSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $reservasPorMes;

    public function __construct($reservasPorMes)
    {
        $this->reservasPorMes = $reservasPorMes;
    }

    public function array(): array
    {
        return $this->reservasPorMes;
    }

    public function headings(): array
    {
        return [
            'Mes',
            'Año',
            'Total Reservas'
        ];
    }

    public function title(): string
    {
        return 'Reservas por Mes';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['argb' => 'FFC0C0C0']
            ]],
        ];
    }
}
