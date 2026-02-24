<?php

namespace App\Exports;

use App\Models\Projet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProjectEtpExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $projectId;

    public function __construct($idProjet)
    {
        $this->projectId = $idProjet;
    }

    public function headings():array {
        return[
            'Projet',
            'Séances',
            'Module',
            'Type',
            'Ville',
            'Salle de formation',
            'Date',
            'Début',
            'Fin',
            'Formateur'
        ];
    }

    public function collection()
    {
        return collect(Projet::getProjects($this->projectId));
    }
}
