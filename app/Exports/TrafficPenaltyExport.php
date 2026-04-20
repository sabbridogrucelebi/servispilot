<?php

namespace App\Exports;

use App\Models\TrafficPenalty;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class TrafficPenaltyExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected array $filters;
    protected int $companyId;

    public function __construct(array $filters = [], int $companyId = 0)
    {
        $this->filters = $filters;
        $this->companyId = $companyId;
    }

    public function collection(): Collection
    {
        $query = TrafficPenalty::with('vehicle')
            ->where('company_id', $this->companyId)
            ->latest('penalty_date')
            ->latest('id');

        if (!empty($this->filters['vehicle_id'])) {
            $query->where('vehicle_id', $this->filters['vehicle_id']);
        }

        if (!empty($this->filters['payment_status'])) {
            $query->where('payment_status', $this->filters['payment_status']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('penalty_date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('penalty_date', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['discount_status'])) {
            if ($this->filters['discount_status'] === 'active') {
                $query->where('payment_status', 'unpaid')
                    ->whereDate('penalty_date', '>=', now()->copy()->subMonth()->toDateString());
            }

            if ($this->filters['discount_status'] === 'expired') {
                $query->where('payment_status', 'unpaid')
                    ->whereDate('penalty_date', '<', now()->copy()->subMonth()->toDateString());
            }

            if ($this->filters['discount_status'] === 'critical') {
                $query->where('payment_status', 'unpaid')
                    ->whereDate('penalty_date', '>=', now()->copy()->subDays(27)->toDateString())
                    ->whereDate('penalty_date', '<=', now()->toDateString());
            }
        }

        if (!empty($this->filters['search'])) {
            $search = trim((string) $this->filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where('penalty_no', 'like', "%{$search}%")
                    ->orWhere('driver_name', 'like', "%{$search}%")
                    ->orWhere('penalty_article', 'like', "%{$search}%")
                    ->orWhere('penalty_location', 'like', "%{$search}%")
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
            'Ceza Tarihi',
            'Ceza Saati',
            'Ceza No',
            'Araç Plakası',
            'Marka / Model',
            'Şoför',
            'Ceza Maddesi',
            'Ceza Yeri',
            'Ceza Tutarı',
            'İndirimli Tutar',
            'Ödeme Durumu',
            'Ödeme Tarihi',
            'Ödenen Tutar',
            'İndirim Son Günü',
            'Belge',
            'Dekont',
            'Not',
        ];
    }

    public function map($penalty): array
    {
        return [
            optional($penalty->penalty_date)->format('d.m.Y') ?: '-',
            $penalty->penalty_time ?: '-',
            $penalty->penalty_no ?: '-',
            $penalty->vehicle?->plate ?: '-',
            trim(($penalty->vehicle?->brand ?: '-') . ' ' . ($penalty->vehicle?->model ?: '')),
            $penalty->driver_name ?: '-',
            $penalty->penalty_article ?: '-',
            $penalty->penalty_location ?: '-',
            number_format((float) ($penalty->penalty_amount ?? 0), 2, ',', '.') . ' ₺',
            number_format((float) ($penalty->discounted_amount ?? 0), 2, ',', '.') . ' ₺',
            $this->formatPaymentStatus($penalty->payment_status),
            optional($penalty->payment_date)->format('d.m.Y') ?: '-',
            number_format((float) ($penalty->paid_amount ?? 0), 2, ',', '.') . ' ₺',
            optional($penalty->discount_deadline)->format('d.m.Y') ?: '-',
            $penalty->traffic_penalty_document ? 'Var' : 'Yok',
            $penalty->payment_receipt ? 'Var' : 'Yok',
            $penalty->notes ?: '-',
        ];
    }

    protected function formatPaymentStatus(?string $status): string
    {
        return match ($status) {
            'paid' => 'Ödendi',
            'unpaid' => 'Ödenmedi',
            default => $status ?: '-',
        };
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastColumn = 'Q';

                $event->sheet->insertNewRowBefore(1, 1);
                $event->sheet->mergeCells("A1:{$lastColumn}1");
                $event->sheet->setCellValue('A1', 'Trafik Cezaları Excel Raporu');

                $event->sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => ['rgb' => 'DC2626'],
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