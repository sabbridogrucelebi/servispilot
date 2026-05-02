<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Contracts\View\View;

class PuantajExport implements FromView, WithStyles, WithTitle, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Puantaj';
    }

    public function view(): View
    {
        return view('trips.export', $this->data);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Sayfa yazdırma ayarları - Yatay A4, tek sayfaya sığdır
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(1);
                $sheet->getPageMargins()->setTop(0.3);
                $sheet->getPageMargins()->setRight(0.2);
                $sheet->getPageMargins()->setLeft(0.2);
                $sheet->getPageMargins()->setBottom(0.3);
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $daysCount = count($this->data['monthDays']);
        $totalCols = $daysCount + 2; // Güzergah + günler + toplam
        $lastCol = Coordinate::stringFromColumnIndex($totalCols);
        $routeCount = count($this->data['serviceRoutes']);

        // ===== ROW 1: Firma Adı (Merged Header) =====
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', ($this->data['selectedCustomer']->company_name ?? 'Puantaj') . ' — ' . ($this->data['monthOptions'][$this->data['selectedMonth']] ?? '') . ' ' . ($this->data['selectedYear'] ?? '') . ' Dönemi Servis Kayıtları');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E3A8A'], // Premium Koyu Mavi
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // ===== ROW 2: Alt Bilgi =====
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'Oluşturulma Tarihi: ' . now()->format('d.m.Y H:i') . ' • Güzergah Sayısı: ' . $routeCount . ' • ServisPilot Premium Motoru');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 9,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF3B82F6'], // Açık Mavi Şerit
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(16);

        // ===== ROW 3: Başlık Satırı =====
        $headerRow = 3;
        $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 8,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E293B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF94A3B8'],
                ],
            ],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(24);

        // ===== DATA ROWS =====
        $dataStartRow = 4;
        $dataEndRow = $dataStartRow + $routeCount - 1;

        if ($routeCount > 0) {
            $sheet->getStyle("A{$dataStartRow}:{$lastCol}{$dataEndRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFD1D5DB'],
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'font' => [
                    'size' => 9,
                    'bold' => true,
                ],
            ]);

            // Güzergah kolonu - sola hizalı, wrap text
            $sheet->getStyle("A{$dataStartRow}:A{$dataEndRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 10,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);

            // Toplam kolonu vurgusu
            $totalColLetter = Coordinate::stringFromColumnIndex($totalCols);
            $sheet->getStyle("{$totalColLetter}{$dataStartRow}:{$totalColLetter}{$dataEndRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 9,
                    'color' => ['argb' => 'FF1E40AF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFEFF6FF'],
                ],
            ]);

            // Zebra deseni
            for ($r = $dataStartRow; $r <= $dataEndRow; $r++) {
                if (($r - $dataStartRow) % 2 === 1) {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF8FAFC'],
                        ],
                    ]);
                }
                $sheet->getRowDimension($r)->setRowHeight(36);
            }

            // Hafta sonu ve bayram renklendirme
            $colIdx = 2;
            foreach ($this->data['monthDays'] as $day) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                if ($day['is_holiday'] ?? false) {
                    $sheet->getStyle("{$colLetter}{$headerRow}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF7C3AED'],
                        ],
                    ]);
                } elseif ($day['is_weekend'] ?? false) {
                    $sheet->getStyle("{$colLetter}{$headerRow}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFBE123C'],
                        ],
                    ]);
                    $sheet->getStyle("{$colLetter}{$dataStartRow}:{$colLetter}{$dataEndRow}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFFF1F2'],
                        ],
                    ]);
                }
                $colIdx++;
            }
        }

        // ===== SUMMARY ROWS =====
        $summaryStart = $dataEndRow + 1;
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A{$summaryStart}:{$lastCol}{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFD1D5DB'],
                ],
            ],
        ]);

        for ($r = $summaryStart; $r <= $highestRow; $r++) {
            $sheet->getStyle("A{$r}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF4B5563']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF9FAFB'],
                ],
            ]);
            $sheet->getStyle("B{$r}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF111827']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->mergeCells("B{$r}:{$lastCol}{$r}");
            $sheet->getRowDimension($r)->setRowHeight(25);
            
            // Eğer Net Fatura satırı ise (genellikle en son satırdır) yeşil vurgu yap
            if ($r == $highestRow) {
                $sheet->getStyle("A{$r}:B{$r}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FF047857']], // Zümrüt Yeşili Yazı
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFD1FAE5'], // Açık Yeşil Arka Plan
                    ],
                ]);
            }
        }

        // ===== COLUMN WIDTHS =====
        $sheet->getColumnDimension('A')->setWidth(35); // Güzergah Sütunu Geniş
        for ($c = 2; $c <= $totalCols; $c++) {
            $colLetter = Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($colLetter)->setWidth($c === $totalCols ? 12 : 6);
        }

        // Freeze pane
        $sheet->freezePane("B4");

        return [];
    }
}
