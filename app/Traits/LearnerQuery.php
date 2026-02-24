<?php

namespace App\Traits;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

trait LearnerQuery
{
    use ProjectQuery;

    public function getLearner($key)
    {
        $learners = DB::table('v_apprenant_etp_alls')
            ->select('idEmploye', 'emp_initial_name', 'emp_fonction', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active', 'idEtp', 'ville', 'idVille')
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('id_cfp', Customer::idCustomer())
                    ->orWhere('id_cfp_appr', Customer::idCustomer());
            })
            ->where(function ($query) use ($key) {
                $query->where('emp_name', 'like', "%$key%")
                    ->orWhere('emp_firstname', 'like', "%$key%")
                    ->orWhere(DB::raw('CONCAT(emp_name, " ", COALESCE(emp_firstname, ""))'), 'like', "%$key%");
            })
            ->groupBy('idEmploye', 'emp_initial_name', 'emp_fonction', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active', 'idEtp', 'ville', 'idVille')
            ->orderBy('idEmploye', 'DESC')
            ->get();


        return $learners;
    }

    public function geLeanerByProject($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null) {
            $apprs = DB::table('v_list_apprenants')
                ->select('idEmploye', 'emp_photo')
                ->where('idProjet', $idProjet)
                ->orderBy('emp_name', 'asc')
                ->get();
        } elseif ($idCfp_inter != null) {
            $apprs = DB::table('v_list_apprenant_inter_added')
                ->select('idEmploye', 'emp_photo')
                ->where('idProjet', $idProjet)
                ->orderBy('emp_name', 'asc')
                ->get();
        }
        return count($apprs);
    }

    public function getAllLearnerByProject($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null) {
            $apprs = DB::table('v_list_apprenants')
                ->select('idEmploye', 'emp_photo', 'emp_name')
                ->where('idProjet', $idProjet)
                ->orderBy('emp_name', 'asc')
                ->paginate(4);
        } elseif ($idCfp_inter != null) {
            $apprs = DB::table('v_list_apprenant_inter_added')
                ->select('idEmploye', 'emp_photo', 'emp_name')
                ->where('idProjet', $idProjet)
                ->orderBy('emp_name', 'asc')
                ->paginate(4);
        }
        $results = [];

        foreach($apprs as $appr){
            $results[] = [
                'photo' => $appr->emp_photo,
                'initial_name' => substr($appr->emp_name, 0, 1)
            ];
        }
        return $results;
    }

    public function getApprListByProjet($idProjet)
    {
        $apprIntras = DB::table('v_list_apprenants')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_photo', 'etp_name', 'emp_initial_name')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get()
            ->toArray();

        $apprenantInters = DB::table('v_list_apprenant_inter_added')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_photo', 'etp_name')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get()
            ->toArray();

        $apprs = array_merge($apprIntras, $apprenantInters);

        return $apprs;
    }

    public function countLearnerByModule($id){
        $projects = $this->getProjectByModule($id);

        $totalLearnerFormed = 0;

        foreach($projects as $project){
            $totalLearnerFormed += count($this->getLearnerByProject($project));
        }

        return $totalLearnerFormed;
    }

    public function getProjectByModule($id){
        $projects = DB::table('projets')
                        ->where('idModule', $id)
                        ->where('dateFin', '<', now())
                        ->where('project_is_trashed', 0)
                        ->pluck('idProjet');

        return $projects;
    }
}