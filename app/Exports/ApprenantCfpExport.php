<?php

namespace App\Exports;

use App\Models\Apprenant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ApprenantCfpExport implements FromCollection, WithHeadings
{
    protected $idProjet;
    protected $idSession;

    public function __construct($idProjet, $idSession)
    {
        $this->idProjet = $idProjet;
        $this->idSession = $idSession;
    }

    public function headings():array {
        return[
            // 'Projet',
            'Matricule',
            'Nom',
            'Prénoms',
            'Adresse email',
            'Tel',
            'Fonction',
            'Cin',
            'Adresse'
        ];
    }

    public function collection()
    {
        return collect(Apprenant::getApprenantCfps($this->idProjet, $this->idSession));
    }
}
