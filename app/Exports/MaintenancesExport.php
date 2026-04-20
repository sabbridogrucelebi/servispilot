<?php

namespace App\Exports;

use App\Models\VehicleMaintenance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class MaintenancesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = VehicleMaintenance::with(['vehicle', 'creator'])
            ->latest('service_date')
            ->latest('id');

        if (!empty($this->filters['vehicle_id'])) {
            $query->where('vehicle_id', $this->filters['vehicle_id']);
        }

        if (!empty($this->filters['maintenance_type'])) {
            $query->where('maintenance_type', $this->filters['maintenance_type']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        $startDate = $this->filters['start_date'] ?? null;
        $endDate = $this->filters['end_date'] ?? null;
        $search = $this->filters['search'] ?? null;

        if (!empty($startDate)) {
            $query->whereDate('service_date', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->whereDate('service_date', '<=', $endDate);
        }

        if (empty($startDate) && empty($endDate)) {
            $query->whereDate('service_date', '<=', now()->toDateString());
        }

        if (!empty($search)) {
            $search = trim((string) $search);

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('service_name', 'like', "%{$search}%")
                    ->orWhere('maintenance_type', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                        $vehicleQuery->where('plate', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    });
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Tarih',
            'Araç Plakası',
            'Marka / Model',
            'Bakım Türü',
            'İşlem Adı',
            'KM',
            'Usta',
            'Durum',
            'Tutar',
            'Not',
        ];
    }

    public function map($maintenance): array
    {
        return [
            optional($maintenance->service_date)->format('d.m.Y') ?: '-',
            $maintenance->vehicle?->plate ?: '-',
            trim(($maintenance->vehicle?->brand ?: '-') . ' ' . ($maintenance->vehicle?->model ?: '')),
            $maintenance->maintenance_type ?: '-',
            $maintenance->title ?: '-',
            !is_null($maintenance->km) ? number_format((float) $maintenance->km, 0, ',', '.') . ' KM' : '-',
            $maintenance->service_name ?: '-',
            $this->formatStatus($maintenance->status),
            number_format((float) ($maintenance->amount ?? 0), 2, ',', '.') . ' ₺',
            $maintenance->description ?: '-',
        ];
    }

    protected function formatStatus(?string $status): string
    {
        return match ($status) {
            'completed' => 'Tamamlandı',
            'pending' => 'Bekliyor',
            'planned' => 'Planlandı',
            default => $status ?: '-',
        };
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastColumn = 'J';

                $event->sheet->insertNewRowBefore(1, 1);
                $event->sheet->mergeCells("A1:{$lastColumn}1");
                $event->sheet->setCellValue('A1', 'Bakım / Tamir Excel Raporu');

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

                $event->sheet->freezePane('A3');
                $event->sheet->setAutoFilter("A2:{$lastColumn}{$lastRow}");

                foreach (range('A', $lastColumn) as $column) {
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}