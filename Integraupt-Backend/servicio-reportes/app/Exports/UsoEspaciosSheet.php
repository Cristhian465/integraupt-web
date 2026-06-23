<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UsoEspaciosSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $usoEspacios;

    public function __construct($usoEspacios)
    {
        $this->usoEspacios = $usoEspacios;
    }

    public function array(): array
    {
        return $this->usoEspacios;
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Código',
            'Tipo',
            'Total Reservas',
            'Porcentaje Uso'
        ];
    }

    public function title(): string
    {
        return 'Uso de Espacios';
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
