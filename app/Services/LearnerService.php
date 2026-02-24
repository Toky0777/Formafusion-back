<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class LearnerService
{
    public function getLearner($key, $idCustomer)
    {
        return DB::table('v_apprenant_etp_alls')
            ->select('idEmploye', 'emp_initial_name', 'emp_fonction', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active', 'idEtp', 'ville', 'idVille')
            ->where(function ($query)  use ($idCustomer) {
                $query->where('idCfp', $idCustomer)
                    ->orWhere('id_cfp', $idCustomer)
                    ->orWhere('id_cfp_appr', $idCustomer);
            })
            ->where(function ($query) use ($key) {
                $query->where('emp_name', 'like', "%$key%")
                    ->orWhere('emp_firstname', 'like', "%$key%")
                    ->orWhere(DB::raw('CONCAT(emp_name, " ", COALESCE(emp_firstname, ""))'), 'like', "%$key%");
            })
            ->groupBy('idEmploye', 'emp_initial_name', 'emp_fonction', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active', 'idEtp', 'ville', 'idVille')
            ->orderBy('idEmploye', 'DESC')
            ->get();
    }

    public function countLearner($key, $idCustomer)
    {
        $learners = DB::table('v_apprenant_etp_alls')
            ->select('idEmploye')
            ->where(function ($query)  use ($idCustomer) {
                $query->where('idCfp', $idCustomer)
                    ->orWhere('id_cfp', $idCustomer)
                    ->orWhere('id_cfp_appr', $idCustomer);
            })
            ->where(function ($query) use ($key) {
                $query->where('emp_name', 'like', "%$key%")
                    ->orWhere('emp_firstname', 'like', "%$key%")
                    ->orWhere(DB::raw('CONCAT(emp_name, " ", COALESCE(emp_firstname, ""))'), 'like', "%$key%");
            })
            ->groupBy('idEmploye')
            ->orderBy('idEmploye', 'DESC')
            ->get();

        return count($learners);
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
}
