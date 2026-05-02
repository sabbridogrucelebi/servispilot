<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FuelTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'plaka',
            'yakit_turu',
            'istasyon',
            'tarih',
            'litre',
            'litre_fiyati',
            'km',
            'notlar',
        ];
    }

    public function array(): array
    {
        return [
            [
                '34ABC123',
                'Dizel',
                'Shell Ataşehir',
                '01.05.2026',
                '50',
                '40.50',
                '120500',
                'Ankara seferi için alındı',
            ],
            [
                '06XYZ789',
                'Benzin',
                'Opet Beşiktaş',
                '02.05.2026',
                '35',
                '41.20',
                '60000',
                '',
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ],
        ];
    }
}
