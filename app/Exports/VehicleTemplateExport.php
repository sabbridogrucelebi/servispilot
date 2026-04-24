<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VehicleTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return [
            'Plaka',
            'Marka',
            'Model',
            'Arac Tipi',
            'Paket',
            'Model Yili',
            'Kayit Tarihi',
            'Koltuk Sayisi',
            'Vites Tipi',
            'Yakit Tipi',
            'Renk',
            'Diger Renk',
            'Motor No',
            'Sasi No',
            'Ruhsat Seri No',
            'Ruhsat Sahibi',
            'TC VKN',
            'Muayene Tarihi',
            'Egzoz Tarihi',
            'Trafik Sigortasi Bitis',
            'Kasko Bitis',
            'Guncel KM',
            'Notlar'
        ];
    }

    public function array(): array
    {
        return [
            [
                '34ABC123',
                'Mercedes-Benz',
                'Sprinter',
                'Minibüs',
                'Orjinal',
                '2022',
                '2022-01-15',
                '16',
                'Manuel',
                'Dizel',
                'Beyaz',
                '',
                'MOT1234567',
                'SAS1234567890',
                'RS12345',
                'Ahmet Yılmaz',
                '12345678901',
                '2025-01-15',
                '2024-01-15',
                '2024-12-31',
                '2025-05-10',
                '45000',
                'Örnek not buraya yazılabilir'
            ],
            [
                '06XYZ06',
                'Volkswagen',
                'Crafter',
                'Midibüs',
                'Ekstra Uzun',
                '2020',
                '2020-05-20',
                '19',
                'Otomatik',
                'Dizel',
                'Gümüş',
                '',
                'MOT9876543',
                'SAS0987654321',
                'RS98765',
                'Örnek Turizm Ltd. Şti.',
                '1234567890',
                '2024-05-20',
                '2024-05-20',
                '2024-06-30',
                '2025-08-15',
                '120000',
                'Araç bakımı yaklaştı'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Make the first row (headings) bold
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
