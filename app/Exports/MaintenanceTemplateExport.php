<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MaintenanceTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'plaka',
            'bakim_turu',
            'bakim_adi',
            'servis_adi',
            'servis_tarihi',
            'km',
            'tutar',
            'sonraki_servis_tarihi',
            'sonraki_servis_km',
            'aciklama',
        ];
    }

    public function array(): array
    {
        return [
            [
                '34ABC123',
                'Periyodik Bakım',
                'Yağ + Filtre Değişimi',
                'Oto Bakım Merkezi',
                '2026-04-20',
                '45000',
                '3500.00',
                '2026-10-20',
                '55000',
                'Motor yağı ve yağ filtresi değiştirildi',
            ],
            [
                '06XYZ789',
                'Lastik',
                '4 Adet Lastik Değişimi',
                'Lastikçi Ahmet',
                '2026-04-15',
                '60000',
                '8000.00',
                '',
                '',
                '4 adet kış lastiği takıldı',
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
