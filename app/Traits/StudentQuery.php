<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait StudentQuery
{
    use ProjectQuery;
    
    public function getStudents(mixed $idProjets){
        if (is_countable($idProjets)) {
            $countStudents = [];
            foreach($idProjets as $idProjet){
                $countStudents = array_merge($countStudents, $this->countByTypeProject($idProjet));
            }
        } else {
            $countStudents = $this->countByTypeProject($idProjets);
        }
        
        return $countStudents;
    }

    public function countByTypeProject($idProjet){
        return ($this->checkTypeProject($idProjet) == 1) ? $this->countStudentsByProjectIntra($idProjet) : $this->countStudentsByProjectInter($idProjet);
    }

    public function countStudentsByProjectIntra($idProjet){
        $students = DB::table('v_apprenant_etp_alls2')
                ->select('idEmploye')
                ->where('idProjet', $idProjet)
                ->pluck('idEmploye');

        return $students->toArray();
    }

    public function countStudentsByProjectInter($idProjet){
        $students = DB::table('v_list_apprenants_inter')
                ->select('idEmploye')
                ->where('idProjet', $idProjet)
                ->pluck('idEmploye');

        return $students->toArray();
    }

    public function checkTypeProject($idProjet){
        $typeProject = DB::table('projets')
                        ->select('idTypeProjet')
                        ->where('idProjet', $idProjet)
                        ->first();

        return $typeProject->idTypeProjet;
    }
}
