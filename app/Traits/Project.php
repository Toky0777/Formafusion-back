<?php

namespace App\Traits;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;


trait Project
{
    public function formatPrice($price)
    {
        if ($price == 0) {
            $price = 0 . " Ar";
        } else if ($price < 1000000 and $price != 0) {
            $price = number_format($price / 1000, 2) . "K Ar";
        } else if ($price < 1000000000) {
            $price = number_format($price / 1000000, 2) . 'M Ar';
        } else {
            $price = number_format($price / 1000000000, 2) . 'B Ar';
        }

        return $price;
    }

    public function getNomDossier($idProjet)
    {
        $dossier = DB::table('dossiers')
            ->select('dossiers.idDossier', 'nomDossier')
            ->join('projets', 'dossiers.idDossier', 'projets.idDossier')
            ->where('idProjet', $idProjet)
            ->first();

        return $dossier->nomDossier ?? null;
    }

    public function getNombreDocument($idProjet)
    {
        $nbDocument = DB::table('projets')
            ->join('dossiers', 'projets.idDossier', '=', 'dossiers.idDossier')
            ->leftJoin('documents', 'dossiers.idDossier', '=', 'documents.idDossier')
            ->select(DB::raw('COUNT(documents.idDocument) as document_count'))
            ->where('idProjet', $idProjet)
            ->first();

        return $nbDocument->document_count;
    }

    public function getSessionProject($idProjet)
    {
        $countSession = DB::table('v_seances')
            ->select('idSeance', 'dateSeance', 'heureDebut', 'id_google_seance', 'heureFin', 'idSalle', 'idProjet', 'salle_name', 'salle_quartier', 'project_title', 'project_description', 'idModule', 'module_name', 'ville')
            ->where('idProjet', $idProjet)
            ->get();

        return count($countSession);
    }

    public function getFormProject($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
            ->groupBy('idFormateur', 'name', 'firstName', 'photoForm', 'initialNameForm')
            ->where('idProjet', $idProjet)->get();

        return $forms->toArray();
    }

    public function getApprenantProject($idProjet, $idCfp_inter)
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

    public function getAllApprenantProject($idProjet, $idCfp_inter)
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

        foreach ($apprs as $appr) {
            $results[] = [
                'photo' => $appr->emp_photo,
                'initial_name' => substr($appr->emp_name, 0, 1)
            ];
        }
        return $results;
    }

    public function getCustomer($idProjet)
    {
        $idEtp = DB::table('v_projet_cfps')
            ->where('idProjet', $idProjet)
            ->pluck('idEtp');

        $customers = DB::table('customers')
            ->select('idCustomer', 'logo', 'customerName')
            ->whereIn('idCustomer', $idEtp)
            ->get();

        return $customers;
    }

    public function getLearnerProject($idProjet)
    {
        $learners = DB::table('v_formateur_cfps')
            ->select('photoForm', 'idFormateur', 'initialNameForm')
            ->where('idProjet', $idProjet)
            ->get();
        return $learners;
    }

    public function getProjectTotalPrice($idProjet)
    {
        $projectPrice = DB::table('v_projet_cfps')
            ->select(DB::raw('SUM(project_price_pedagogique + project_price_annexe) AS project_total_price'))
            ->where('idProjet', $idProjet)
            ->first();

        return $projectPrice->project_total_price;
    }

    public function getSessionHour($idProjet)
    {
        $countSessionHour = DB::table('v_seances')
            ->selectRaw("IFNULL(TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))), '%H:%i'), '0') as sumHourSession")
            ->where('idProjet', $idProjet)
            ->first();

        return $countSessionHour->sumHourSession;
    }

    public function getNote($idProjet)
    {
        $checkEvaluation = DB::table('eval_chauds')->select('idProjet')->get();
        $checkEvaluationCount = count($checkEvaluation);

        if ($checkEvaluationCount > 0) {
            $notationProjet = DB::table('v_evaluation_alls')
                ->select('idProjet', 'idEmploye', 'generalApreciate')
                ->where('idProjet', $idProjet)
                ->groupBy('idProjet', 'idEmploye')
                ->get();

            $generalNotation = DB::table('v_general_note_evaluation')
                ->select(DB::raw('SUM(generalApreciate) as generalNote'))
                ->where('idProjet', $idProjet)
                ->first();

            $countNotationProjet = count($notationProjet);

            if ($countNotationProjet > 0) {
                $noteGeneral = $generalNotation->generalNote / $countNotationProjet;
                return array_merge([$noteGeneral], [$countNotationProjet]);
            } else {
                $noteGeneral = 0;
                return array_merge([$noteGeneral], [$countNotationProjet]);
            }
        } else {
            $countNotationProjet = 0;
            $noteGeneral = 0;
            return array_merge([$noteGeneral], [$countNotationProjet]);
        }
    }

    public function getParticulierProject($idProjet, $idCfp_inter)
    {
        $parts = []; // Initialiser $parts comme un tableau vide

        if ($idCfp_inter != null) {
            $parts = DB::table('v_particuliers_projet')
                ->select('idParticulier', 'part_name', 'part_firstname', 'part_email', 'part_cin', 'part_matricule', 'part_role_id', 'part_has_role', 'user_is_in_service', 'idCfp', 'idProjet')
                ->where('idProjet', $idProjet)
                ->orderBy('part_name', 'asc')
                ->get();
        }
        return count($parts);
    }

    public function getEtpProjectInter($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null) {
            $etp = DB::table('v_projet_cfps')
                ->select('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
                ->where('idProjet', $idProjet)
                ->whereNot('idEtp', Customer::idCustomer())
                ->groupBy('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
                ->get();
        } elseif ($idCfp_inter != null) {
            $etp = DB::table('v_list_entreprise_inter')
                ->select('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
                ->where('idProjet', $idProjet)
                ->where('etp_name', '!=', 'null')
                ->orderBy('etp_name', 'asc')
                ->groupBy('idEtp')
                ->get();
        }

        return $etp->toArray();
    }

    public function getRestauration($idProjet)
    {
        $restaurations = DB::table('project_restaurations')
            ->select('idRestauration', 'paidBy')
            ->where('idProjet', $idProjet)
            ->get()
            ->toArray();
        return $restaurations;
    }

    public function checkEmg($idProjet)
    {
        $query = DB::table('emargements')->where('idProjet', $idProjet);

        if ($query) {
            return $query->count();
        } else {
            return null;
        }
    }

    public function checkEval($idProjet)
    {
        $query = DB::table('eval_chauds')->where('idProjet', $idProjet);

        if ($query) {
            return $query->count();
        } else {
            return null;
        }
    }

    public function averageEvalApprenant($idProjet)
    {
        return DB::table('eval_apprenant')
            ->select(DB::raw('AVG(avant) as avg_avant'), DB::raw('AVG(apres) as avg_apres'))
            ->where('idProjet', $idProjet)
            ->first() ?? 0;
    }

    public function getApprListProjet($idProjet)
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

        // return response()->json(['apprs' => $apprs]);
        return $apprs;
    }
}
