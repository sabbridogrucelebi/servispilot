<?php

namespace App\Exports;

use App\Models\Fuel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class FuelsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = Fuel::with(['vehicle', 'station'])
            ->orderByDesc('date')
            ->orderByDesc('id');

        if (!empty($this->filters['vehicle_id'])) {
            $query->where('vehicle_id', $this->filters['vehicle_id']);
        }

        $startDate = $this->filters['fuel_start_date'] ?? $this->filters['start_date'] ?? null;
        $endDate = $this->filters['fuel_end_date'] ?? $this->filters['end_date'] ?? null;
        $month = $this->filters['fuel_month'] ?? null;
        $station = $this->filters['fuel_station'] ?? $this->filters['station'] ?? null;
        $fuelType = $this->filters['fuel_type'] ?? null;
        $search = $this->filters['fuel_search'] ?? $this->filters['search'] ?? null;

        if (!empty($startDate)) {
            $query->whereDate('date', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->whereDate('date', '<=', $endDate);
        }

        if (empty($startDate) && empty($endDate) && !empty($month)) {
            // Güvenlik + DB-bağımsız: whereRaw yerine Eloquent ile ay/yıl filtreleme
            if (preg_match('/^(\d{4})-(\d{1,2})$/', (string) $month, $parts)) {
                $query->whereYear('date', (int) $parts[1])
                      ->whereMonth('date', (int) $parts[2]);
            }
        }

        if (!empty($fuelType)) {
            $query->where('fuel_type', $fuelType);
        }

        if (!empty($station)) {
            $station = trim((string) $station);

            $query->where(function ($q) use ($station) {
                $q->where('station_name', $station)
                    ->orWhereHas('station', function ($stationQuery) use ($station) {
                        $stationQuery->where('name', $station);
                    });
            });
        }

        if (!empty($search)) {
            $search = trim((string) $search);

            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhere('station_name', 'like', "%{$search}%")
                    ->orWhere('fuel_type', 'like', "%{$search}%")
                    ->orWhere('km', 'like', "%{$search}%")
                    ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                        $vehicleQuery->where('plate', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%");
                    })
                    ->orWhereHas('station', function ($stationQuery) use ($search) {
                        $stationQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $fuels = $query->get();

        $calculatedRows = collect();

        foreach ($fuels->groupBy('vehicle_id') as $vehicleRows) {
            $sortedRows = $vehicleRows
                ->sortBy(function ($row) {
                    return sprintf(
                        '%s-%010d-%010d',
                        optional($row->date)->format('Ymd') ?? '00000000',
                        (int) ($row->km ?? 0),
                        (int) $row->id
                    );
                })
                ->values();

            $previousRow = null;

            foreach ($sortedRows as $row) {
                $row->km_difference = null;
                $row->km_per_liter = null;

                if (
                    $previousRow &&
                    !is_null($row->km) &&
                    !is_null($previousRow->km) &&
                    (float) $row->km > (float) $previousRow->km
                ) {
                    $kmDifference = (float) $row->km - (float) $previousRow->km;
                    $row->km_difference = $kmDifference;

                    if ((float) $row->liters > 0) {
                        $row->km_per_liter = $kmDifference / (float) $row->liters;
                    }
                }

                $calculatedRows->push($row);
                $previousRow = $row;
            }
        }

        return $calculatedRows
            ->sortByDesc(function ($row) {
                return sprintf(
                    '%s-%010d-%010d',
                    optional($row->date)->format('Ymd') ?? '00000000',
                    (int) ($row->km ?? 0),
                    (int) $row->id
                );
            })
            ->values();
    }

    public function headings(): array
    {
        return [
            'Tarih',
            'Araç Plakası',
            'Marka / Model',
            'İstasyon',
            'Yakıt Türü',
            'KM',
            'KM Farkı',
            'Litre',
            'Birim Fiyat',
            'Net Tutar',
            'KM / Litre',
            'Not',
        ];
    }

    public function map($fuel): array
    {
        return [
            optional($fuel->date)->format('d.m.Y') ?: '-',
            $fuel->vehicle?->plate ?: '-',
            trim(($fuel->vehicle?->brand ?: '-') . ' ' . ($fuel->vehicle?->model ?: '')),
            $fuel->station?->name ?? $fuel->station_name ?? '-',
            $fuel->fuel_type ?: 'Dizel',
            !is_null($fuel->km) ? number_format((float) $fuel->km, 0, ',', '.') : '-',
            !is_null($fuel->km_difference) ? number_format((float) $fuel->km_difference, 0, ',', '.') . ' KM' : '-',
            number_format((float) ($fuel->liters ?? 0), 2, ',', '.'),
            number_format((float) ($fuel->price_per_liter ?? 0), 2, ',', '.') . ' ₺',
            number_format((float) ($fuel->total_cost ?? 0), 2, ',', '.') . ' ₺',
            !is_null($fuel->km_per_liter) ? number_format((float) $fuel->km_per_liter, 2, ',', '.') : '-',
            $fuel->notes ?: '-',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastColumn = 'L';

                $event->sheet->insertNewRowBefore(1, 1);
                $event->sheet->mergeCells("A1:{$lastColumn}1");
                $event->sheet->setCellValue('A1', 'Yakıt Excel Raporu');

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