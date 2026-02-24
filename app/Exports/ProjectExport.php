<?php

namespace App\Exports;

use App\Models\Projet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProjectExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $projectId;
    protected $idSession;

    public function __construct($idProjet, $idSession)
    {
        $this->projectId = $idProjet;
        $this->idSession = $idSession;
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
        return collect(Projet::getProjects($this->projectId, $this->idSession));
    }
}
