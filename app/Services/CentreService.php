<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CentreService
{
    public function getCentresForUser($userId)
    {
        return $this->baseCentresQuery()
            ->where('projet.idEmploye', $userId)
            ->orderBy('dateDebut', 'asc')
            ->get();
    }

    public function getCentreDetails($idCfp, $userId)
    {
        return $this->baseCentresQuery(true)
            ->where('projet.idCfp', $idCfp)
            ->where('projet.idEmploye', $userId)
            ->orderBy('dateDebut', 'asc')
            ->first();
    }

    public function getProjectsForCentre($idCfp, $userId)
    {
        return $this->baseProjectsQuery()
            ->where('projet.idEmploye', $userId)
            ->where('projet.idCfp', $idCfp)
            ->orderBy('dateDebut', 'asc')
            ->get();
    }

    // Méthodes privées pour factoriser la construction des requêtes

    private function baseCentresQuery($details = false)
    {
        $query = DB::table('customers AS cu')
            ->join('v_projet_emps AS projet', 'cu.idCustomer', '=', 'projet.idCfp');

        if ($details) {
            $query->select(
                'projet.cfp_name',
                'cu.siteWeb',
                'cu.customer_addr_quartier as lieu',
                'cu.customer_slogan as slogan',
                DB::raw('COUNT(projet.idProjet) as nb_projet')
            );
        } else {
            $query->select(
                'projet.idProjet',
                'projet.cfp_name',
                'projet.idCfp',
                'cu.customer_addr_quartier as lieu',
                'cu.customer_slogan as slogan',
                DB::raw('COUNT(projet.idProjet) as nb_projet')
            );
        }

        return $query;
    }

    private function baseProjectsQuery()
    {
        return DB::table('v_projet_emps AS projet')
            ->leftJoin('module_ressources', 'module_ressources.idModule', '=', 'projet.idModule')
            ->select(
                'projet.idProjet',
                'projet.dateDebut',
                'projet.dateFin',
                'projet.cfp_name',
                'projet.module_name',
                'projet.project_description',
                DB::raw('COUNT(module_ressources.idModuleRessource) as nb_module_ressources')
            )
            ->groupBy(
                'projet.idProjet',
                'projet.dateDebut',
                'projet.dateFin',
                'projet.cfp_name',
                'projet.module_name',
                'projet.project_description'
            );
    }
}
