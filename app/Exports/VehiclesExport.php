<?php

namespace App\Exports;

use App\Models\Fleet\Vehicle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;

class VehiclesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        return Vehicle::latest()->get();
    }

    public function headings(): array
    {
        return [
            'Plaka',
            'Marka',
            'Model',
            'Araç Tipi',
            'Araç Paketi',
            'Model Yılı',
            'Tescil Tarihi',
            'Koltuk Sayısı',
            'Vites Türü',
            'Yakıt Tipi',
            'Renk',
            'Motor No',
            'Şasi No',
            'Ruhsat Belge Seri No',
            'Ruhsat Sahibi',
            'Ruhsat Sahibi Vergi / T.C. No',
            'Muayene Tarihi',
            'Egzoz Tarihi',
            'Sigorta Bitiş Tarihi',
            'Kasko Bitiş Tarihi',
            'Durum',
            'Notlar',
            'Oluşturulma Tarihi',
        ];
    }

    public function map($vehicle): array
    {
        $color = $vehicle->color === 'Diğer'
            ? ($vehicle->other_color ?: 'Diğer')
            : ($vehicle->color ?: '-');

        return [
            $vehicle->plate ?: '-',
            $vehicle->brand ?: '-',
            $vehicle->model ?: '-',
            $vehicle->vehicle_type ?: '-',
            $vehicle->vehicle_package ?: '-',
            $vehicle->model_year ?: '-',
            optional($vehicle->registration_date)->format('d.m.Y') ?: '-',
            $vehicle->seat_count ?: '-',
            $vehicle->gear_type ?: '-',
            $vehicle->fuel_type ?: '-',
            $color,
            $vehicle->engine_no ?: '-',
            $vehicle->chassis_no ?: '-',
            $vehicle->license_serial_no ?: '-',
            $vehicle->license_owner ?: '-',
            $vehicle->owner_tax_or_tc_no ?: '-',
            optional($vehicle->inspection_date)->format('d.m.Y') ?: '-',
            optional($vehicle->exhaust_date)->format('d.m.Y') ?: '-',
            optional($vehicle->insurance_end_date)->format('d.m.Y') ?: '-',
            optional($vehicle->kasko_end_date)->format('d.m.Y') ?: '-',
            $vehicle->is_active ? 'Aktif' : 'Pasif',
            $vehicle->notes ?: '-',
            optional($vehicle->created_at)->format('d.m.Y H:i') ?: '-',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastColumn = 'W';

                // En üste 1 satır ekle, böylece başlıklar kaybolmaz
                $event->sheet->insertNewRowBefore(1, 1);

                // Üst rapor başlığı
                $event->sheet->mergeCells("A1:{$lastColumn}1");
                $event->sheet->setCellValue('A1', 'Araçlar Excel Raporu');

                $event->sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => ['rgb' => '2563EB'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                ]);

                $event->sheet->getRowDimension(1)->setRowHeight(28);

                // Kolon başlık satırı artık 2. satır
                $event->sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => ['rgb' => '334155'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical' => 'center',
                    ],
                ]);

                $lastRow = $event->sheet->getHighestRow();

                // Veri alanı stilleri
                $event->sheet->getStyle("A3:{$lastColumn}{$lastRow}")->applyFromArray([
                    'alignment' => [
                        'vertical' => 'center',
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => 'E2E8F0'],
                        ],
                    ],
                ]);

                // Başlıklar sabit kalsın
                $event->sheet->freezePane('A3');

                // Filtre başlık satırında olsun
                $event->sheet->setAutoFilter("A2:{$lastColumn}{$lastRow}");

                // Tüm sütunları otomatik genişlet
                foreach (range('A', $lastColumn) as $column) {
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}