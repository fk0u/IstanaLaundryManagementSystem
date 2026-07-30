<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class GenericViewExport implements FromView, ShouldAutoSize, WithTitle
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
}
