<?php

namespace App\Exports;


use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ApprenantExcelExport implements FromCollection, WithHeadings
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
            "Matricule",
            "Formation",
            "Nom",
            "Prénom",
            "Fonction",
            "Salle du formation",
            "Quartier du formation",
            "Statut",
            "Type de formation",
            "Nom de l'entreprise",
            "Nom du centre de formation",
            "Date de debut",
            "Date de fin",
            "Durer du formation en heure",
            "Taux de presence"
        ];
    }
}
