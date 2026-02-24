<?php

namespace App\Exports;


use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientExcelExport implements FromCollection, WithHeadings
{
    public $data;
    public function __construct($data)
    {
        $this->data = $data;
    }
    public function collection()
    {
        return $this->data;
    }
    public function headings(): array
    {
        return [
            "Nom de l'entreprise",
            "Nom",
            "Prénom",
            "Fonction",
            "Matricule",
            "Formation",
            "Type de formation",
            "Status",
            "Salle du formation",
            "Date de debut",
            "Date de fin",
            "Durer du formation en heure",
            "Presence"
        ];
    }
}
