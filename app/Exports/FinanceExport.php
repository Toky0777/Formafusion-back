<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FinanceExport implements FromView, ShouldAutoSize
{
    public function view(): View
    {
        return view('CFP.Reporting.finance.dataFin');
        
    }
}
