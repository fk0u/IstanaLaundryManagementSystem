<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GenericViewExport implements FromView, ShouldAutoSize, WithTitle, WithStyles, WithEvents
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
        return view($this->viewName, $this->viewData);
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

                if ($highestRow > 0 && ! empty($highestColumn)) {
                    $cellRange = "A1:{$highestColumn}{$highestRow}";
                    $sheet->getStyle($cellRange)->getFont()->setName('Calibri');
                    $sheet->getStyle($cellRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                }
            },
        ];
    }
}

