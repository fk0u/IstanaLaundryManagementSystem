<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GenericViewExport implements FromView, WithTitle, WithStyles, WithEvents
{
    protected string $viewName;

    protected array $viewData;

    protected string $title;

    public function __construct(string $viewName, array $viewData, string $title = 'Laporan Istana Laundry')
    {
        $this->viewName = $viewName;
        $this->viewData = $viewData;
        $this->title = mb_substr($title, 0, 31);
    }

    public function view(): View
    {
        $originalView = view($this->viewName, $this->viewData);

        return new class($originalView) implements View {
            protected View $view;

            public function __construct(View $view)
            {
                $this->view = $view;
            }

            public function render(): string
            {
                $html = $this->view->render();

                // 1. Sanitize <title> tag to prevent PhpSpreadsheet HTML reader from throwing >31 char sheet title exception
                $html = preg_replace('/<title>.*?<\/title>/is', '<title>Sheet1</title>', $html);

                // 2. Convert unescaped ampersands to &amp; so DOMDocument::loadHTML() in PhpSpreadsheet does not crash
                return preg_replace('/&(?!#?[a-z0-9]+;)/i', '&amp;', $html);
            }

            public function name(): string
            {
                return $this->view->name();
            }

            public function with($key, $value = null)
            {
                return $this->view->with($key, $value);
            }

            public function getData(): array
            {
                return $this->view->getData();
            }
        };
    }

    public function title(): string
    {
        return $this->title;
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridLines(true);

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                if ($highestRow <= 0 || empty($highestColumn)) {
                    return;
                }

                // 1. Set global font
                $fullRange = "A1:{$highestColumn}{$highestRow}";
                $sheet->getStyle($fullRange)->getFont()->setName('Calibri');
                $sheet->getStyle($fullRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // 2. Identify Table Header Row dynamically
                $headerRow = null;
                for ($r = 1; $r <= min(15, $highestRow); $r++) {
                    $valA = mb_strtoupper(trim((string) $sheet->getCell("A{$r}")->getValue()));
                    $valB = mb_strtoupper(trim((string) $sheet->getCell("B{$r}")->getValue()));

                    if (
                        str_contains($valA, 'NO') || str_contains($valA, 'KODE') || str_contains($valA, 'SKU') ||
                        str_contains($valB, 'NAMA') || str_contains($valB, 'NOMOR') || str_contains($valB, 'TANGGAL')
                    ) {
                        $headerRow = $r;
                        break;
                    }
                }

                // Fallback to row 5 or 6 if not detected
                if (! $headerRow) {
                    $headerRow = $highestRow >= 6 ? 6 : 1;
                }

                // 3. Style Header Row (Corporate Dark Navy #0F172A with Bold White Text)
                $headerRange = "A{$headerRow}:{$highestColumn}{$headerRow}";
                $sheet->getRowDimension($headerRow)->setRowHeight(28);
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 10,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '0F172A'],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'bottom' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => 'FF6600'],
                        ],
                    ],
                ]);

                // 4. Freeze Panes below table header so table headers stay locked on scroll
                $sheet->freezePane('A'.($headerRow + 1));

                // 5. Apply AutoFilter on table header
                try {
                    $sheet->setAutoFilter("A{$headerRow}:{$highestColumn}{$highestRow}");
                } catch (\Throwable $e) {
                    // Ignore if sheet range is invalid for autofilter
                }

                // 6. Style Data Rows (Zebra striping + Borders + Row Heights + Totals Row)
                for ($r = $headerRow + 1; $r <= $highestRow; $r++) {
                    $valA = mb_strtoupper(trim((string) $sheet->getCell("A{$r}")->getValue()));
                    $valB = mb_strtoupper(trim((string) $sheet->getCell("B{$r}")->getValue()));

                    $isTotalRow = str_contains($valA, 'TOTAL') || str_contains($valA, 'GRAND') ||
                                  str_contains($valB, 'TOTAL') || str_contains($valB, 'AKUMULASI');

                    if ($isTotalRow) {
                        $sheet->getRowDimension($r)->setRowHeight(26);
                        $sheet->getStyle("A{$r}:{$highestColumn}{$r}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'C2410C'],
                                'size' => 10.5,
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FFF7ED'],
                            ],
                            'borders' => [
                                'top' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => 'EA580C'],
                                ],
                                'bottom' => [
                                    'borderStyle' => Border::BORDER_DOUBLE,
                                    'color' => ['rgb' => 'EA580C'],
                                ],
                            ],
                        ]);
                    } else {
                        $sheet->getRowDimension($r)->setRowHeight(22);
                        $bg = ($r % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
                        $sheet->getStyle("A{$r}:{$highestColumn}{$r}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $bg],
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => 'E2E8F0'],
                                ],
                            ],
                        ]);
                    }
                }

                // 7. Lock Column Widths (MINIMUM 20 - 35 units per column so table NEVER shrinks!)
                $currCol = 'A';
                while (true) {
                    $maxLen = 0;
                    for ($r = 1; $r <= $highestRow; $r++) {
                        $cellVal = (string) $sheet->getCell("{$currCol}{$r}")->getValue();
                        $maxLen = max($maxLen, mb_strlen(strip_tags($cellVal)));
                    }

                    // Enforce a generous locked column width (minimum 20, max 45)
                    $lockedWidth = min(45, max(20, $maxLen + 4));

                    $sheet->getColumnDimension($currCol)->setAutoSize(false);
                    $sheet->getColumnDimension($currCol)->setWidth($lockedWidth);

                    if ($currCol === $highestColumn) {
                        break;
                    }
                    $currCol++;
                }
            },
        ];
    }
}


