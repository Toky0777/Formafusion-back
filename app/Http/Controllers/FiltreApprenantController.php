<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FiltreApprenantController extends Controller
{

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
                ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_photo', 'emp_matricule', 'emp_phone', 'etp_name')
                ->where('idProjet', $idProjet)
                ->orderBy('emp_name', 'asc')
                ->get();
        } elseif ($idCfp_inter != null) {
            $apprs = DB::table('v_list_apprenant_inter_added')
                ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_fonction', 'emp_email', 'emp_photo', 'emp_matricule', 'etp_name', 'idEtp')
                ->where('idProjet', $idProjet)
                ->orderBy('emp_name', 'asc')
                ->get();
        }

        return count($apprs);
    }

    public function getProjectTotalPrice($idProjet)
    {
        $projectPrice = DB::table('v_projet_cfps')
            ->select(DB::raw('SUM(project_price_pedagogique + project_price_annexe) AS project_total_price'))
            ->where('idProjet', $idProjet)
            ->first();

        return $projectPrice->project_total_price;
    }

    public function getEtpProjectInter($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null) {
            $etp = DB::table('v_projet_cfps')
                ->select('etp_name', 'etp_logo', 'etp_initial_name')
                ->where('idProjet', $idProjet)
                ->orderBy('etp_name', 'asc')
                ->get();
        } elseif ($idCfp_inter != null) {
            $etp = DB::table('v_list_entreprise_inter')
                ->select('etp_name', 'etp_logo', 'etp_initial_name')
                ->where('idProjet', $idProjet)
                ->where('etp_name', '!=', 'null')
                ->orderBy('etp_name', 'asc')
                ->get();
        }

        return $etp->toArray();
    }

    // Filtres
    public function getDropdownItem()
    {

        $userId = Auth::user()->id;

        $status = DB::table('v_projet_emps')
            ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idEmploye', $userId)
            ->where('headYear', Carbon::now()->format('Y'))
            ->groupBy('project_status')
            ->orderBy('project_status', 'asc')
            ->get();

        $etps = DB::table('v_union_projets')
            ->select(DB::raw('COUNT(DISTINCT(v_union_projets.idProjet)) AS projet_nb'), DB::raw('COALESCE(idEtp, idEtp_inter) AS idEtp'), DB::raw('customers.customerName AS etp_name'))
            ->leftJoin('entreprises', function ($join) {
                $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
            })
            ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
            ->leftJoin('project_forms', 'v_union_projets.idProjet', '=', 'project_forms.idProjet')
            ->groupBy(DB::raw('COALESCE(idEtp, idEtp_inter)'))
            ->orderBy('etp_name', 'asc')
            ->get();

        $filteredEtps = $etps->filter(function ($etp) {
            return $etp->etp_name !== null && $etp->etp_name !== '';
        });

        //fonction pour selectionne tous les id des formateurs...        
        $queries  = $this->getIdFormateur();
        $formateurs = [];
        foreach ($queries as $key => $queryInfo) {
            $query = DB::table('v_formateur_cfps')
                ->select('idProjet', 'idFormateur', 'project_type', 'idEtp', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
                ->where('idFormateur', $queryInfo->idFormateur)
                ->groupBy('idProjet', 'idFormateur', 'project_type', 'idEtp');
            $forms = $query->get();
            $project_count = count($forms);
            $formateurs[$key] = [
                'idFormateur' => $forms[0]->idFormateur,
                'form_name' => $forms[0]->form_name,
                'form_firstname' => $forms[0]->form_firstname,
                'projet_nb' => $project_count
            ];
        }

        $types = DB::table('v_projet_emps')
            ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idEmploye', $userId)
            ->where('headYear', Carbon::now()->format('Y'))
            ->orderBy('project_type', 'asc')
            ->groupBy('project_type')
            ->get();

        $periodePrev3 = DB::table('v_projet_emps')
            ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idEmploye', $userId)
            ->where('headYear', Carbon::now()->format('Y'))
            // ->where('p_id_periode', "prev_3_month")
            ->whereRaw("p_id_periode COLLATE utf8mb4_unicode_ci = 'prev_3_month'")
            ->groupBy('p_id_periode')
            ->first();

        $periodePrev6 = DB::table('v_projet_emps')
            ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idEmploye', $userId)
            ->where('headYear', Carbon::now()->format('Y'))
            ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
            ->first();

        $periodePrev12 = DB::table('v_projet_emps')
            ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idEmploye', $userId)
            ->where('headYear', Carbon::now()->format('Y'))
            ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
            ->first();

        $periodeNext3 = DB::table('v_projet_emps')
            ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idEmploye', $userId)
            ->where('headYear', Carbon::now()->format('Y'))
            ->where('p_id_periode', "next_3_month")
            ->groupBy('p_id_periode')
            ->first();

        $periodeNext6 = DB::table('v_projet_emps')
            ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idEmploye', $userId)
            ->where('headYear', Carbon::now()->format('Y'))
            ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
            ->first();

        $periodeNext12 = DB::table('v_projet_emps')
            ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idEmploye', $userId)
            ->where('headYear', Carbon::now()->format('Y'))
            ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
            ->first();

        $modules = DB::table('v_projet_emps')
            ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idEmploye', $userId)
            ->where('headYear', Carbon::now()->format('Y'))
            ->where('module_name', '!=', 'Default module')
            ->orderBy('module_name', 'asc')
            ->groupBy('idModule', 'module_name')
            ->get();

        $villes = DB::table('v_projet_emps')
            ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idEmploye', $userId)
            ->where('headYear', Carbon::now()->format('Y'))
            ->orderBy('ville', 'asc')
            ->groupBy('idVille', 'ville')
            ->get();

        //ajout mois...
        $months = DB::table('v_projet_emps')
            ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
            //->select('headDate', 'headMonthDebut')
            ->groupBy('headDate')
            ->orderBy('dateDebut', 'asc')
            ->where('dateDebut', '!=', 'null')
            ->get();

        return response()->json([
            'status' => $status,
            'etps' => [...$filteredEtps],
            'types' => $types,
            'periodePrev3' => $periodePrev3,
            'periodePrev6' => $periodePrev6,
            'periodePrev12' => $periodePrev12,
            'periodeNext3' => $periodeNext3,
            'periodeNext6' => $periodeNext6,
            'periodeNext12' => $periodeNext12,
            'modules' => $modules,
            'villes' => $villes,
            'months' => $months,
        ]);
    }

    //fonction pour selectionne tous les id des formateurs...
    public function getIdFormateur()
    {
        $allId = [];
        // $allId = DB::select("SELECT idFormateur FROM `v_formateur_cfps` WHERE idCfp = ? GROUP BY idFormateur ", [$this->idCfp()] );       
        $allId = DB::table('v_formateur_cfps')
            ->select('idFormateur')
            ->groupBy('idFormateur')
            ->get();

        return $allId;
    }

    //
    public function filterItems(Request $req)
    {
        $idStatus = explode(',', $req->idStatut);
        $idEtps = explode(',', $req->idEtp);
        $idTypes = explode(',', $req->idType);
        $idPeriodes = $req->idPeriode;
        $idModules = explode(',', $req->idModule);
        $idVilles = explode(',', $req->idVille);
        $idMois = explode(',', $req->idMois);

        // $idFinancements = explode(',', $req->idFinancement);


        $userId = Auth::user()->id;

        if ((isset($idEtps) && $idEtps[0] > 0)) {
            $query = DB::table('v_union_projets')
                ->select('v_union_projets.idProjet', 'dateDebut', 'dateFin', 'module_name', 'etp_name', 'ville', 'project_status', 'project_type', 'paiement', 'headDate', 'headMonthDebut', 'headMonthFin', 'headYear', 'headDayDebut', 'headDayFin', 'module_image', 'etp_logo', 'etp_initial_name', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville',  'idCfp_inter', 'modalite', 'project_description', 'total_ht', 'total_ttc', 'idCfp_inter', 'idModule')
                ->leftJoin('project_forms', 'v_union_projets.idProjet', '=', 'project_forms.idProjet')
                ->leftJoin('formateurs', 'formateurs.idFormateur', '=', 'project_forms.idFormateur')
                ->where('headYear', Carbon::now()->format('Y'))
                ->groupBy('v_union_projets.idProjet')
                ->orderBy('dateDebut', 'asc');
        } else {

            $query = DB::table('v_projet_emps')
                ->select('idProjet', 'dateDebut', 'dateFin', 'idModule', 'module_name', 'etp_name', 'ville', 'project_status', 'project_type', 'headDate', 'headMonthDebut', 'headMonthFin', 'headYear', 'headDayDebut', 'headDayFin', 'module_image', 'etp_logo', 'etp_initial_name', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite')
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->groupBy('idProjet')
                ->orderBy('dateDebut', 'asc');
        }

        if ($idStatus[0] != null) {
            $query->whereIn('project_status', $idStatus);

            $etps = DB::table('v_union_projets')
                ->select(DB::raw('COUNT(idProjet) AS projet_nb'), DB::raw('COALESCE(idEtp, idEtp_inter) AS idEtp'), DB::raw('customers.customerName AS etp_name'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->whereIn('project_status', $idStatus)
                ->groupBy(DB::raw('COALESCE(idEtp, idEtp_inter)'))
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_projet_emps')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->whereIn('project_status', $idStatus)
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $periodePrev3 = DB::table('v_projet_emps')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "prev_3_month")
                ->whereIn('project_status', $idStatus)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_projet_emps')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('project_status', $idStatus)
                ->first();

            $periodePrev12 = DB::table('v_projet_emps')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('project_status', $idStatus)
                ->first();

            $periodeNext3 = DB::table('v_projet_emps')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "next_3_month")
                ->whereIn('project_status', $idStatus)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_projet_emps')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->whereIn('project_status', $idStatus)
                ->first();

            $periodeNext12 = DB::table('v_projet_emps')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->whereIn('project_status', $idStatus)
                ->first();

            $modules = DB::table('v_projet_emps')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->whereIn('project_status', $idStatus)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_projet_emps')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->whereIn('project_status', $idStatus)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            $months = DB::table('v_projet_emps')
                ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->whereIn('project_status', $idStatus)
                ->get();

            $projectDates = DB::table('v_projet_emps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('project_status', $idStatus)
                ->get();
        }

        if ($idEtps[0] != null) {
            $query->where(function ($expr) use ($idEtps) {
                $expr->wherein('idEtp', $idEtps)
                    ->orWherein('idEtp_inter', $idEtps)
                ;
            });

            $status = DB::table('v_union_projets')
                ->select('project_status', DB::raw('COUNT(DISTINCT(idProjet)) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();
            $types = DB::table('v_union_projets')

                ->select('project_type', DB::raw('COUNT(DISTINCT(idProjet)) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();


            $periodePrev3 = DB::table('v_union_projets')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(DISTINCT(idProjet)) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "prev_3_month")
                //->whereIn('idEtp', $idEtps)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(DISTINCT(idProjet)) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('idEtp', $idEtps)
                ->first();

            $periodePrev12 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(DISTINCT(idProjet)) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('idEtp', $idEtps)
                ->first();

            $periodeNext3 = DB::table('v_union_projets')
                ->select('p_id_periode', DB::raw('COUNT(DISTINCT(idProjet)) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "next_3_month")
                // ->whereIn('idEtp', $idEtps)
                ->groupBy('p_id_periode')
                ->first();
            $periodeNext6 = DB::table('v_union_projets')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(DISTINCT(idProjet)) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                //->whereIn('idEtp', $idEtps)
                ->first();

            $periodeNext12 = DB::table('v_union_projets')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(DISTINCT(idProjet)) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                //->whereIn('idEtp', $idEtps)
                ->first();

            $modules = DB::table('v_union_projets')
                ->select('idModule', 'module_name', DB::raw('COUNT(DISTINCT(idProjet)) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();
            $villes = DB::table('v_union_projets')
                ->select('idVille', 'ville', DB::raw('COUNT(DISTINCT(idProjet)) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            $months = DB::table('v_union_projets')
                ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))

                ->orderBy('dateDebut', 'asc')
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->groupBy('headDate')
                ->get();

            $projectDates = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where('headYear', Carbon::now()->format('Y'))
                //->whereIn('idEtp', $idEtps)
                ->get();
        }

        if ($idTypes[0] != null) {
            $query->whereIn('project_type', $idTypes);

            $status = DB::table('v_projet_emps')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->whereIn('project_type', $idTypes)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();

            $etps = DB::table('v_union_projets')
                ->select(DB::raw('COUNT(idProjet) AS projet_nb'), DB::raw('COALESCE(idEtp, idEtp_inter) AS idEtp'), DB::raw('customers.customerName AS etp_name'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->whereIn('project_type', $idTypes)
                ->groupBy(DB::raw('COALESCE(idEtp, idEtp_inter)'))
                ->orderBy('etp_name', 'asc')
                ->get();

            $periodePrev3 = DB::table('v_projet_emps')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "prev_3_month")
                ->whereIn('project_type', $idTypes)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_projet_emps')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('project_type', $idTypes)
                ->first();

            $periodePrev12 = DB::table('v_projet_emps')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('project_type', $idTypes)
                ->first();

            $periodeNext3 = DB::table('v_projet_emps')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "next_3_month")
                ->whereIn('project_type', $idTypes)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_projet_emps')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->whereIn('project_type', $idTypes)
                ->first();

            $periodeNext12 = DB::table('v_projet_emps')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->whereIn('project_type', $idTypes)
                ->first();

            $modules = DB::table('v_projet_emps')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->whereIn('project_type', $idTypes)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_projet_emps')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->whereIn('project_type', $idTypes)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            // $financements = DB::table('v_projet_emps')
            //     ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
            //     ->where('idEmploye', $userId)
            //     ->whereIn('project_type', $idTypes)
            //     ->orderBy('paiement', 'asc')
            //     ->groupBy('idPaiement', 'paiement')
            //     ->get();

            $months = DB::table('v_projet_emps')
                ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->whereIn('project_type', $idTypes)
                ->get();


            $projectDates = DB::table('v_projet_emps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('project_type', $idTypes)
                ->get();
        }

        if ($idPeriodes != null) {
            switch ($idPeriodes) {
                case 'prev_3_month':
                    $query->where('p_id_periode', $idPeriodes);

                    $projectDates = DB::table('v_projet_emps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', 'asc')
                        ->where('idEmploye', $userId)
                        ->where('headYear', Carbon::now()->format('Y'))
                        ->where('p_id_periode', $idPeriodes)
                        ->get();

                    break;
                case 'prev_6_month':
                    $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                    $projectDates = DB::table('v_projet_emps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', 'asc')
                        ->where('idEmploye', $userId)
                        ->where('headYear', Carbon::now()->format('Y'))
                        ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                        ->get();

                    break;
                case 'prev_12_month':
                    $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                    $projectDates = DB::table('v_projet_emps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', 'asc')
                        ->where('idEmploye', $userId)
                        ->where('headYear', Carbon::now()->format('Y'))
                        ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                        ->get();

                    break;
                case 'next_3_month':
                    $query->where('p_id_periode', $idPeriodes);

                    $projectDates = DB::table('v_projet_emps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', 'asc')
                        ->where('idEmploye', $userId)
                        ->where('headYear', Carbon::now()->format('Y'))
                        ->where('p_id_periode', $idPeriodes)
                        ->get();

                    break;
                case 'next_6_month':
                    $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);

                    $projectDates = DB::table('v_projet_emps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', 'asc')
                        ->where('idEmploye', $userId)
                        ->where('headYear', Carbon::now()->format('Y'))
                        ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                        ->get();
                    break;
                case 'next_12_month':
                    $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                    $projectDates = DB::table('v_projet_emps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', 'asc')
                        ->where('idEmploye', $userId)
                        ->where('headYear', Carbon::now()->format('Y'))
                        ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                        ->get();

                    break;

                default:
                    $query->where('p_id_periode', $idPeriodes);

                    $projectDates = DB::table('v_projet_emps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', 'asc')
                        ->where('idEmploye', $userId)

                        ->where('headYear', Carbon::now()->format('Y'))
                        ->where('p_id_periode', $idPeriodes)
                        ->get();

                    break;
            }

            $status = DB::table('v_projet_emps')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('p_id_periode', $idPeriodes)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();

            $etps = DB::table('v_projet_emps')
                ->select('idEtp', 'etp_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('p_id_periode', $idPeriodes)
                ->groupBy('idEtp', 'etp_name')
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_projet_emps')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('p_id_periode', $idPeriodes)
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $modules = DB::table('v_projet_emps')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('p_id_periode', $idPeriodes)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_projet_emps')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('p_id_periode', $idPeriodes)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            $months = DB::table('v_projet_emps')
                ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->whereIn('p_id_periode', $idPeriodes)
                ->get();
        }

        if ($idModules[0] != null) {
            $query->whereIn('idModule', $idModules);

            $status = DB::table('v_projet_emps')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->whereIn('idModule', $idModules)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();

            $etps = DB::table('v_union_projets')
                ->select(DB::raw('COALESCE(idEtp, idEtp_inter) AS idEtp'), DB::raw('customers.customerName AS etp_name'), DB::raw('COUNT(DISTINCT(idProjet)) AS projet_nb'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->whereIn('idModule', $idModules)
                ->groupBy(DB::raw('COALESCE(idEtp, idEtp_inter)'))
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_projet_emps')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->whereIn('idModule', $idModules)
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $periodePrev3 = DB::table('v_projet_emps')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "prev_3_month")
                ->whereIn('idModule', $idModules)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_projet_emps')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('idModule', $idModules)
                ->first();

            $periodePrev12 = DB::table('v_projet_emps')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('idModule', $idModules)
                ->first();

            $periodeNext3 = DB::table('v_projet_emps')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "next_3_month")
                ->whereIn('idModule', $idModules)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_projet_emps')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->whereIn('idModule', $idModules)
                ->first();

            $periodeNext12 = DB::table('v_projet_emps')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->whereIn('idModule', $idModules)
                ->first();

            $villes = DB::table('v_projet_emps')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->whereIn('idModule', $idModules)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            // $financements = DB::table('v_projet_emps')
            //     ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
            //     ->where('idEmploye', $userId)
            //     ->whereIn('idModule', $idModules)
            //     ->orderBy('paiement', 'asc')
            //     ->groupBy('idPaiement', 'paiement')
            //     ->get();
            $months = DB::table('v_projet_emps')
                ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')

                ->whereIn('idModule', $idModules)
                ->get();

            $projectDates = DB::table('v_projet_emps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('idModule', $idModules)
                ->get();
        }

        if ($idVilles[0] != null) {
            $query->whereIn('idVille', $idVilles);

            $status = DB::table('v_projet_emps')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->whereIn('idVille', $idVilles)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();

            $etps = DB::table('v_union_projets')
                ->select(DB::raw('COALESCE(idEtp, idEtp_inter) AS idEtp'), DB::raw('customers.customerName AS etp_name'), DB::raw('COUNT(DISTINCT(idProjet)) AS projet_nb'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->whereIn('idVille', $idVilles)
                ->groupBy(DB::raw('COALESCE(idEtp, idEtp_inter)'))
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_projet_emps')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->whereIn('idVille', $idVilles)
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $periodePrev3 = DB::table('v_projet_emps')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "prev_3_month")
                ->whereIn('idVille', $idVilles)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_projet_emps')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('idVille', $idVilles)
                ->first();

            $periodePrev12 = DB::table('v_projet_emps')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('idVille', $idVilles)
                ->first();

            $periodeNext3 = DB::table('v_projet_emps')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "next_3_month")
                ->whereIn('idVille', $idVilles)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_projet_emps')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->whereIn('idVille', $idVilles)
                ->first();

            $periodeNext12 = DB::table('v_projet_emps')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->whereIn('idVille', $idVilles)
                ->first();

            $modules = DB::table('v_projet_cfps')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->whereIn('idVille', $idVilles)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            // $financements = DB::table('v_projet_emps')
            //     ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
            //     ->where('idEmploye', $userId)
            //     ->whereIn('idVille', $idVilles)
            //     ->orderBy('paiement', 'asc')
            //     ->groupBy('idPaiement', 'paiement')
            //     ->get();


            $months = DB::table('v_projet_cfps')
                ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->whereIn('idVille', $idVilles)
                ->get();

            $projectDates = DB::table('v_projet_emps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('idVille', $idVilles)
                ->get();
        }

        // if ($idFinancements[0] != null) {
        //     $query->whereIn('idPaiement', $idFinancements);

        //     $status = DB::table('v_projet_emps')
        //         ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
        //         ->where('idEmploye', $userId)
        //         ->whereIn('idPaiement', $idFinancements)
        //         ->groupBy('project_status')
        //         ->orderBy('project_status', 'asc')
        //         ->get();

        //     $etps = DB::table('v_projet_emps')
        //         ->select('idEtp', 'etp_name', DB::raw('COUNT(idProjet) AS projet_nb'))
        //         ->where('idEmploye', $userId)
        //         ->whereIn('idPaiement', $idFinancements)
        //         ->groupBy('idEtp', 'etp_name')
        //         ->orderBy('etp_name', 'asc')
        //         ->get();

        //     $types = DB::table('v_projet_emps')
        //         ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
        //         ->where('idEmploye', $userId)
        //         ->whereIn('idPaiement', $idFinancements)
        //         ->orderBy('project_type', 'asc')
        //         ->groupBy('project_type')
        //         ->get();

        //     $periodePrev3 = DB::table('v_projet_emps')
        //         ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
        //         ->where('idEmploye', $userId)
        //         ->where('headYear', Carbon::now()->format('Y'))
        //         ->where('p_id_periode', "prev_3_month")
        //         ->whereIn('idPaiement', $idFinancements)
        //         ->groupBy('p_id_periode')
        //         ->first();

        //     $periodePrev6 = DB::table('v_projet_emps')
        //         ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
        //         ->where('idEmploye', $userId)
        //         ->where('headYear', Carbon::now()->format('Y'))
        //         ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
        //         ->whereIn('idPaiement', $idFinancements)
        //         ->first();

        //     $periodePrev12 = DB::table('v_projet_emps')
        //         ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
        //         ->where('idEmploye', $userId)
        //         ->where('headYear', Carbon::now()->format('Y'))
        //         ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
        //         ->whereIn('idPaiement', $idFinancements)
        //         ->first();

        //     $periodeNext3 = DB::table('v_projet_emps')
        //         ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
        //         ->where('idEmploye', $userId)
        //         ->where('headYear', Carbon::now()->format('Y'))
        //         ->where('p_id_periode', "next_3_month")
        //         ->whereIn('idPaiement', $idFinancements)
        //         ->groupBy('p_id_periode')
        //         ->first();

        //     $periodeNext6 = DB::table('v_projet_emps')
        //         ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
        //         ->where('idEmploye', $userId)
        //         ->where('headYear', Carbon::now()->format('Y'))
        //         ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
        //         ->whereIn('idPaiement', $idFinancements)
        //         ->first();

        //     $periodeNext12 = DB::table('v_projet_emps')
        //         ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
        //         ->where('idEmploye', $userId)
        //         ->where('headYear', Carbon::now()->format('Y'))
        //         ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
        //         ->whereIn('idPaiement', $idFinancements)
        //         ->first();

        //     $modules = DB::table('v_projet_emps')
        //         ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
        //         ->where('idEmploye', $userId)
        //         ->whereIn('idPaiement', $idFinancements)
        //         ->orderBy('module_name', 'asc')
        //         ->groupBy('idModule', 'module_name')
        //         ->get();

        //     $villes = DB::table('v_projet_emps')
        //         ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
        //         ->where('idEmploye', $userId)
        //         ->whereIn('idPaiement', $idFinancements)
        //         ->orderBy('ville', 'asc')
        //         ->groupBy('idVille', 'ville')
        //         ->get();

        //     $projectDates = DB::table('v_projet_emps')
        //         ->select('headDate', 'headMonthDebut')
        //         ->groupBy('headDate')
        //         ->orderBy('dateDebut', 'asc')
        //         ->where('idEmploye', $userId)
        //         ->where('headYear', Carbon::now()->format('Y'))
        //         ->whereIn('idPaiement', $idFinancements)
        //         ->get();
        // }

        if ($idStatus[0] == null && $idEtps[0] == null && $idTypes[0] == null && $idPeriodes == null && $idModules[0] == null && $idVilles[0] == null && $idMois[0] == null) {
            $status = DB::table('v_projet_emps')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();


            $etps = DB::table('v_union_projets')
                ->select(DB::raw('COALESCE(idEtp, idEtp_inter) AS idEtp'), DB::raw('customers.customerName AS etp_name'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->groupBy(DB::raw('COALESCE(idEtp, idEtp_inter)'))
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_projet_emps')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $periodePrev3 = DB::table('v_projet_emps')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "prev_3_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_projet_emps')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->first();

            $periodePrev12 = DB::table('v_projet_emps')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->first();

            $periodeNext3 = DB::table('v_projet_emps')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "next_3_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_projet_emps')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->first();

            $periodeNext12 = DB::table('v_projet_emps')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->first();

            $modules = DB::table('v_projet_emps')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_projet_emps')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEmploye', $userId)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            // $financements = DB::table('v_projet_emps')
            //     ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
            //     ->where('idEmploye', $userId)
            //     ->orderBy('paiement', 'asc')
            //     ->groupBy('idPaiement', 'paiement')
            //     ->get();

            $months = DB::table('v_projet_emps')
                ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->get();

            $projectDates = DB::table('v_projet_emps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->get();
        }

        $projects = $query->get();

        $projets = [];
        foreach ($projects as $project) {
            $projets[] = [
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'formateurs' => $this->getFormProject($project->idProjet),
                'apprCount' => $this->getApprenantProject($project->idProjet, $project->idCfp_inter),
                'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                'idProjet' => $project->idProjet,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'ville' => $project->ville,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'modalite' => $project->modalite,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'etp_initial_name' => $project->etp_initial_name,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'ville' => $project->ville,
                'headYear' => $project->headYear,
                'headMonthDebut' => $project->headMonthDebut,
                'headMonthFin' => $project->headMonthFin,
                'headDayDebut' => $project->headDayDebut,
                'headDayFin' => $project->headDayFin,
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'general_note' => $this->getNote($project->idProjet),
                'idModule' => $project->idModule,
                'restaurations' => $this->getRestauration($project->idProjet),
                'apprs' => $this->getApprListProjet($project->idProjet)
            ];
        }

        if ($idStatus[0] != null) {
            return response()->json([
                'projets' => $projets,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'months' => $months,
                'projectDates' => $projectDates
            ]);
        } elseif ($idEtps[0] != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'months' => $months,
                'projectDates' => $projectDates
            ]);
        } elseif ($idTypes[0] != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'months' => $months,
                'projectDates' => $projectDates
            ]);
        } elseif ($idPeriodes != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'types' => $types,
                'modules' => $modules,
                'villes' => $villes,
                'months' => $months,
                'projectDates' => $projectDates
            ]);
        } elseif ($idModules[0] != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'villes' => $villes,
                'months' => $months,
                'projectDates' => $projectDates
            ]);
        } elseif ($idVilles[0] != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'months' => $months,
                'projectDates' => $projectDates
            ]);
        } elseif ($idMois[0] != null) {
            //dd($financements);

            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'projectDates' => $projectDates
            ]);
        } else {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'months' => $months,
                'projectDates' => $projectDates
            ]);
        }
    }

    // 3
    public function filterItem(Request $req)
    {
        $idStatus = explode(',', $req->idStatut);
        $idEtps = explode(',', $req->idEtp);
        $idTypes = explode(',', $req->idType);
        $idPeriodes = $req->idPeriode;
        $idModules = explode(',', $req->idModule);
        $idVilles = explode(',', $req->idVille);
        $idMois = explode(',', $req->idMois);
        // $idFinancements = explode(',', $req->idFinancement);

        $userId = Auth::user()->id;

        if ((isset($idEtps) && $idEtps[0] > 0)) {
            $query = DB::table('v_union_projets')
                ->select('v_union_projets.idProjet', 'dateDebut', 'dateFin', 'module_name', 'etp_name', 'ville', 'project_status', 'project_type', 'paiement', 'headDate', 'headMonthDebut', 'headMonthFin', 'headYear', 'headDayDebut', 'headDayFin', 'module_image', 'etp_logo', 'etp_initial_name', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'project_description', 'total_ht', 'total_ttc', 'idModule')
                ->join('project_forms', 'v_union_projets.idProjet', '=', 'project_forms.idProjet')
                ->where('headYear', Carbon::now()->format('Y'))
                ->groupBy('v_union_projets.idProjet')
                ->orderBy('dateDebut', 'asc');
        } else {
            $query = DB::table('v_projet_cfps')
                ->select('idProjet', 'dateDebut', 'dateFin', 'module_name', 'etp_name', 'ville', 'project_status', 'project_type', 'paiement', 'headDate', 'headMonthDebut', 'headMonthFin', 'headYear', 'headDayDebut', 'headDayFin', 'module_image', 'etp_logo', 'etp_initial_name', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'project_description', 'total_ht', 'total_ttc', 'idModule')
                ->where('headYear', Carbon::now()->format('Y'))
                ->groupBy('idProjet')
                ->orderBy('dateDebut', 'asc');
        }

        $queryDate = DB::table('v_projet_emps')
            ->select('headDate', 'headMonthDebut')
            ->groupBy('headDate')
            ->orderBy('dateDebut', 'asc')
            ->where('idEmploye', $userId)
            ->where('headYear', Carbon::now()->format('Y'));

        if ($idStatus[0] != null) {
            $query->whereIn('project_status', $idStatus);

            $queryDate = DB::table('v_projet_emps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('project_status', $idStatus);

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
                $queryDate->whereIn('idEtp', $idEtps);
            }

            if ($idTypes[0] != null) {
                $query->whereIn('project_type', $idTypes);
                $queryDate->whereIn('project_type', $idTypes);
            }

            if ($idPeriodes != null) {
                switch ($idPeriodes) {
                    case 'prev_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'prev_6_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                        break;
                    case 'prev_12_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                        break;
                    case 'next_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'next_6_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        break;
                    case 'next_12_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                        break;

                    default:
                        $query->where('p_id_periode', $idPeriodes);

                        $queryDate = DB::table('v_projet_emps')
                            ->select('headDate', 'headMonthDebut')
                            ->groupBy('headDate')
                            ->orderBy('dateDebut', 'asc')
                            ->where('idEmploye', $userId)
                            ->where('headYear', Carbon::now()->format('Y'))
                            ->where('p_id_periode', $idPeriodes);
                        break;
                }
            }

            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
                $queryDate->whereIn('idModule', $idModules);
            }

            if ($idVilles[0] != null) {
                $query->whereIn('idVille', $idVilles);
                $queryDate->whereIn('idVille', $idVilles);
            }

            // if ($idFinancements[0] != null) {
            //     $query->whereIn('idPaiement', $idFinancements);
            //     $queryDate->whereIn('idPaiement', $idFinancements);
            // }
        }

        if ($idEtps[0] != null) {

            $query->where(function ($expr) use ($idEtps) {
                $expr->wherein('idEtp', $idEtps)
                    ->orWherein('idEtp_inter', $idEtps);
            });
            $projectDates = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where('headYear', Carbon::now()->format('Y'))
                //->whereIn('idEtp', $idEtps)
                ->get();
        }

        if ($idTypes[0] != null) {
            $query->whereIn('project_type', $idTypes);

            $queryDate = DB::table('v_projet_emps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('project_type', $idTypes);

            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
                $queryDate->whereIn('project_status', $idStatus);
            }

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
                $queryDate->whereIn('idEtp', $idEtps);
            }

            if ($idPeriodes != null) {
                switch ($idPeriodes) {
                    case 'prev_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'prev_6_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                        break;
                    case 'prev_12_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                        break;
                    case 'next_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'next_6_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        break;
                    case 'next_12_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                        break;

                    default:
                        $query->where('p_id_periode', $idPeriodes);

                        $queryDate = DB::table('v_projet_emps')
                            ->select('headDate', 'headMonthDebut')
                            ->groupBy('headDate')
                            ->orderBy('dateDebut', 'asc')
                            ->where('idEmploye', $userId)
                            ->where('headYear', Carbon::now()->format('Y'))
                            ->where('p_id_periode', $idPeriodes);
                        break;
                }
            }

            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
                $queryDate->whereIn('idModule', $idModules);
            }

            if ($idVilles[0] != null) {
                $query->whereIn('idVille', $idVilles);
                $queryDate->whereIn('idVille', $idVilles);
            }
        }

        if ($idPeriodes != null) {
            switch ($idPeriodes) {
                case 'prev_3_month':
                    $query->where('p_id_periode', $idPeriodes);

                    $queryDate = DB::table('v_projet_emps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', 'asc')
                        ->where('idEmploye', $userId)
                        ->where('headYear', Carbon::now()->format('Y'))
                        ->where('p_id_periode', $idPeriodes);

                    break;
                case 'prev_6_month':
                    $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                    $queryDate = DB::table('v_projet_emps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', 'asc')
                        ->where('idEmploye', $userId)
                        ->where('headYear', Carbon::now()->format('Y'))
                        ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                    break;
                case 'prev_12_month':
                    $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                    $queryDate = DB::table('v_projet_emps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', 'asc')
                        ->where('idEmploye', $userId)
                        ->where('headYear', Carbon::now()->format('Y'))
                        ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                    break;
                case 'next_3_month':
                    $query->where('p_id_periode', $idPeriodes);

                    $queryDate = DB::table('v_projet_emps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', 'asc')
                        ->where('idEmploye', $userId)
                        ->where('headYear', Carbon::now()->format('Y'))
                        ->where('p_id_periode', $idPeriodes);

                    break;
                case 'next_6_month':
                    $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);

                    $queryDate = DB::table('v_projet_emps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', 'asc')
                        ->where('idEmploye', $userId)
                        ->where('headYear', Carbon::now()->format('Y'))
                        ->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                    break;
                case 'next_12_month':
                    $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                    $queryDate = DB::table('v_projet_emps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', 'asc')
                        ->where('idEmploye', $userId)
                        ->where('headYear', Carbon::now()->format('Y'))
                        ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                    break;

                default:
                    $query->where('p_id_periode', $idPeriodes);

                    $queryDate = DB::table('v_projet_emps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', 'asc')
                        ->where('idEmploye', $userId)
                        ->where('headYear', Carbon::now()->format('Y'))
                        ->where('p_id_periode', $idPeriodes);
                    break;
            }

            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
                $queryDate->whereIn('project_status', $idStatus);
            }

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
                $queryDate->whereIn('idEtp', $idEtps);
            }

            if ($idTypes[0] != null) {
                $query->whereIn('project_type', $idTypes);
                $queryDate->whereIn('project_type', $idTypes);
            }

            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
                $queryDate->whereIn('idModule', $idModules);
            }

            if ($idVilles[0] != null) {
                $query->whereIn('idVille', $idVilles);
                $queryDate->whereIn('idVille', $idVilles);
            }
        }

        if ($idModules[0] != null) {
            $query->whereIn('idModule', $idModules);

            $queryDate = DB::table('v_projet_emps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('idModule', $idModules);

            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
                $queryDate->whereIn('project_status', $idStatus);
            }

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
                $queryDate->whereIn('idEtp', $idEtps);
            }

            if ($idTypes[0] != null) {
                $query->whereIn('project_type', $idTypes);
                $queryDate->whereIn('project_type', $idTypes);
            }

            if ($idPeriodes != null) {
                switch ($idPeriodes) {
                    case 'prev_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'prev_6_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                        break;
                    case 'prev_12_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                        break;
                    case 'next_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'next_6_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        break;
                    case 'next_12_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                        break;

                    default:
                        $query->where('p_id_periode', $idPeriodes);

                        $queryDate = DB::table('v_projet_emps')
                            ->select('headDate', 'headMonthDebut')
                            ->groupBy('headDate')
                            ->orderBy('dateDebut', 'asc')
                            ->where('idEmploye', $userId)
                            ->where('headYear', Carbon::now()->format('Y'))
                            ->where('p_id_periode', $idPeriodes);
                        break;
                }
            }

            if ($idVilles[0] != null) {
                $query->whereIn('idVille', $idVilles);
                $queryDate->whereIn('idVille', $idVilles);
            }
        }

        if ($idVilles[0] != null) {
            $query->whereIn('idVille', $idVilles);

            $queryDate = DB::table('v_projet_emps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->where('idEmploye', $userId)
                ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('idVille', $idVilles);

            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
                $queryDate->whereIn('project_status', $idStatus);
            }

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
                $queryDate->whereIn('idEtp', $idEtps);
            }

            if ($idTypes[0] != null) {
                $query->whereIn('project_type', $idTypes);
                $queryDate->whereIn('project_type', $idTypes);
            }

            if ($idPeriodes != null) {
                switch ($idPeriodes) {
                    case 'prev_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'prev_6_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                        break;
                    case 'prev_12_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                        break;
                    case 'next_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'next_6_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        break;
                    case 'next_12_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                        break;

                    default:
                        $query->where('p_id_periode', $idPeriodes);

                        $queryDate = DB::table('v_projet_emps')
                            ->select('headDate', 'headMonthDebut')
                            ->groupBy('headDate')
                            ->orderBy('dateDebut', 'asc')
                            ->where('idEmploye', $userId)
                            ->where('headYear', Carbon::now()->format('Y'))
                            ->where('p_id_periode', $idPeriodes);
                        break;
                }
            }

            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
                $queryDate->whereIn('idModule', $idModules);
            }

            // if ($idFinancements[0] != null) {
            //     $query->whereIn('idPaiement', $idFinancements);
            //     $queryDate->whereIn('idPaiement', $idFinancements);
            // }
        }

        // if ($idFinancements[0] != null) {
        //     $query->whereIn('idPaiement', $idFinancements);

        //     $queryDate = DB::table('v_projet_emps')
        //         ->select('headDate', 'headMonthDebut')
        //         ->groupBy('headDate')
        //         ->orderBy('dateDebut', 'asc')
        //         ->where('idEmploye', $userId)
        //         ->where('headYear', Carbon::now()->format('Y'))
        //         ->whereIn('idPaiement', $idFinancements);

        //     if ($idStatus[0] != null) {
        //         $query->whereIn('project_status', $idStatus);
        //         $queryDate->whereIn('project_status', $idStatus);
        //     }

        //     if ($idEtps[0] != null) {
        //         $query->whereIn('idEtp', $idEtps);
        //         $queryDate->whereIn('idEtp', $idEtps);
        //     }

        //     if ($idTypes[0] != null) {
        //         $query->whereIn('project_type', $idTypes);
        //         $queryDate->whereIn('project_type', $idTypes);
        //     }

        //     if ($idPeriodes != null) {
        //         switch ($idPeriodes) {
        //             case 'prev_3_month':
        //                 $query->where('p_id_periode', $idPeriodes);
        //                 $queryDate->where('p_id_periode', $idPeriodes);

        //                 break;
        //             case 'prev_6_month':
        //                 $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
        //                 $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

        //                 break;
        //             case 'prev_12_month':
        //                 $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
        //                 $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

        //                 break;
        //             case 'next_3_month':
        //                 $query->where('p_id_periode', $idPeriodes);
        //                 $queryDate->where('p_id_periode', $idPeriodes);

        //                 break;
        //             case 'next_6_month':
        //                 $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
        //                 $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
        //                 break;
        //             case 'next_12_month':
        //                 $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
        //                 $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

        //                 break;

        //             default:
        //                 $query->where('p_id_periode', $idPeriodes);

        //                 $queryDate = DB::table('v_projet_emps')
        //                     ->select('headDate', 'headMonthDebut')
        //                     ->groupBy('headDate')
        //                     ->orderBy('dateDebut', 'asc')
        //                     ->where('idEmploye', $userId)
        //                     ->where('headYear', Carbon::now()->format('Y'))
        //                     ->where('p_id_periode', $idPeriodes);
        //                 break;
        //         }
        //     }

        //     if ($idModules[0] != null) {
        //         $query->whereIn('idModule', $idModules);
        //         $queryDate->whereIn('idModule', $idModules);
        //     }

        //     if ($idVilles[0] != null) {
        //         $query->whereIn('idVille', $idVilles);
        //         $queryDate->whereIn('idVille', $idVilles);
        //     }
        // }

        if ($idMois[0] != null) {
            $query->whereIn(DB::raw('MONTH(dateDebut)'), $idMois)
                ->groupBy('idProjet');


            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
                $queryDate->whereIn('project_status', $idStatus);
            }

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
                $queryDate->whereIn('idEtp', $idEtps);
            }

            if ($idTypes[0] != null) {
                $query->whereIn('project_type', $idTypes);
                $queryDate->whereIn('project_type', $idTypes);
            }

            if ($idPeriodes != null) {
                switch ($idPeriodes) {
                    case 'prev_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'prev_6_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                        break;
                    case 'prev_12_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                        break;
                    case 'next_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'next_6_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        break;
                    case 'next_12_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                        break;

                    default:
                        $query->where('p_id_periode', $idPeriodes);

                        $queryDate = DB::table('v_projet_cfps')
                            ->select('headDate', 'headMonthDebut')
                            ->groupBy('headDate')
                            ->orderBy('dateDebut', 'asc')
                            ->where('headYear', Carbon::now()->format('Y'))
                            ->where('p_id_periode', $idPeriodes);
                        break;
                }
            }


            $projectDates = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->where('headYear', Carbon::now()->format('Y'))

                ->get();
            //dd($projectDates);
        }

        $projects = $query->get();
        $projectDates = $queryDate->get();

        $projets = [];
        foreach ($projects as $project) {
            $projets[] = [
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'formateurs' => $this->getFormProject($project->idProjet),
                'apprCount' => $this->getApprenantProject($project->idProjet, $project->idCfp_inter),
                'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                'idProjet' => $project->idProjet,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'ville' => $project->ville,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'modalite' => $project->modalite,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'etp_initial_name' => $project->etp_initial_name,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'ville' => $project->ville,
                'headYear' => $project->headYear,
                'headMonthDebut' => $project->headMonthDebut,
                'headMonthFin' => $project->headMonthFin,
                'headDayDebut' => $project->headDayDebut,
                'headDayFin' => $project->headDayFin,
                'project_description' => $project->project_description,
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'general_note' => $this->getNote($project->idProjet),
                'idModule' => $project->idModule,
                'restaurations' => $this->getRestauration($project->idProjet),
                'apprs' => $this->getApprListProjet($project->idProjet),
            ];
        }

        return response()->json([
            'projets' => $projets,
            'projectDates' => $projectDates
        ]);
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

    public function getRestauration($idProjet)
    {
        $restaurations = DB::table('project_restaurations')
            ->select('idRestauration', 'paidBy')
            ->where('idProjet', $idProjet)
            ->get()
            ->toArray();
        return $restaurations;
    }

    private function getApprListProjet($idProjet)
    {
        $apprIntras = DB::table('v_list_apprenants')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_photo', 'etp_name')
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
