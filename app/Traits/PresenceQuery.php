<?php

namespace App\Traits;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait PresenceQuery
{
    public  function getEtpProjectInter($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null || $idCfp_inter == 'null') {
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

    public function getAllPourcentagesForProjects(array $projectIds): array
    {
        if (empty($projectIds)) {
            return [];
        }

        $now = Carbon::now()->toDateString();
        $idCustomer = Customer::idCustomer();

        if (!$idCustomer) {
            return [];
        }

        $result = [];

        try {
            $statutsByProject = DB::table('emargements as e')
                ->select('e.idProjet', 'e.isPresent', DB::raw('COUNT(*) as count'))
                ->join('projets as p', 'e.idProjet', 'p.idProjet')
                ->whereIn('e.idProjet', $projectIds)
                ->where('p.idCustomer', $idCustomer)
                ->whereIn('e.isPresent', [0, 1, 2, 3])
                ->groupBy('e.idProjet', 'e.isPresent')
                ->get()
                ->groupBy('idProjet');

            $seancesByProject = DB::table('v_emargement_appr as ve')
                ->select('ve.idProjet', 've.idSeance')
                ->join('projets as p', 'p.idProjet', 've.idProjet')
                ->whereIn('ve.idProjet', $projectIds)
                ->where('p.idCustomer', $idCustomer)
                ->whereDate('ve.dateSeance', '<=', $now)
                ->groupBy('ve.idProjet', 've.idSeance')
                ->get()
                ->groupBy('idProjet');

            $apprsByProject = DB::table('v_emargement_appr as ve')
                ->select('ve.idProjet', 've.idEmploye')
                ->join('projets as p', 'p.idProjet', 've.idProjet')
                ->where('p.idCustomer', $idCustomer)
                ->whereIn('ve.idProjet', $projectIds)
                ->groupBy('ve.idProjet', 've.idEmploye')
                ->get()
                ->groupBy('idProjet');

            $apprStatuts = DB::table('emargements as e')
                ->select('e.idProjet', 'e.idEmploye', 'e.isPresent')
                ->join('projets as p', 'e.idProjet', 'p.idProjet')
                ->whereIn('e.idProjet', $projectIds)
                ->where('p.idCustomer', $idCustomer)
                ->get()
                ->groupBy(['idProjet', 'idEmploye']);

            foreach ($projectIds as $projectId) {
                $statuts = $statutsByProject[$projectId] ?? collect();
                $seances = $seancesByProject[$projectId] ?? collect();
                $apprs = $apprsByProject[$projectId] ?? collect();
                $projectApprStatuts = $apprStatuts[$projectId] ?? [];

                $seancesCount = count($seances);
                $apprsCount = count($apprs);

                $countPresent = $statuts->where('isPresent', 3)->sum('count') ?? 0;
                $countPartiel = $statuts->where('isPresent', 2)->sum('count') ?? 0;
                $countAbsent = ($statuts->where('isPresent', 1)->sum('count') ?? 0)
                    + ($statuts->where('isPresent', 0)->sum('count') ?? 0);

                $divide = $seancesCount * $apprsCount;

                $pourcentage = [
                    'present' => $divide > 0 ? number_format(($countPresent / $divide) * 100, 1, ',', ' ') : "0",
                    'partiel' => $divide > 0 ? number_format(($countPartiel / $divide) * 100, 1, ',', ' ') : "0",
                    'absent'  => $divide > 0 ? number_format(($countAbsent / $divide) * 100, 1, ',', ' ') : "0",
                ];

                $isCompleted = (
                    floatval(str_replace(',', '.', $pourcentage['present'])) > 0 ||
                    floatval(str_replace(',', '.', $pourcentage['partiel'])) > 0 ||
                    floatval(str_replace(',', '.', $pourcentage['absent'])) > 0
                );

                $NbPresent = 0;
                $NBAbsent = 0;
                $NbASaisir = 0;

                foreach ($apprs as $apprenant) {
                    $id = $apprenant->idEmploye;
                    $statuses = $projectApprStatuts[$id] ?? collect();

                    if ($statuses->isEmpty()) {
                        $NbASaisir++;
                    } else {
                        $statusValues = $statuses->pluck('isPresent')->toArray();
                        if (in_array(3, $statusValues)) {
                            $NbPresent++;
                        } else {
                            $NBAbsent++;
                        }
                    }
                }

                $nbApprenant = [
                    'nb_present' => $NbPresent,
                    'nb_absent' => $NBAbsent,
                    'nb_a_saisir' => $NbASaisir,
                    'total_inscrits' => $apprsCount,
                ];

                $result[$projectId] = [
                    'pourcentage' => $pourcentage,
                    'nbApprenant' => $nbApprenant,
                    'isCompleted' => $isCompleted,
                ];
            }
        } catch (\Exception $e) {
            return [];
        }

        return $result;
    }


    public function getAllEtpsForProjects(array $projectIds): array
    {
        $result = [];

        $projectsInfo = DB::table('v_projet_cfps')
            ->select('idProjet', 'idCfp_inter')
            ->whereIn('idProjet', $projectIds)
            ->get()
            ->keyBy('idProjet');

        foreach ($projectIds as $projectId) {
            $projectInfo = $projectsInfo[$projectId] ?? null;
            $idCfpInter = $projectInfo->idCfp_inter ?? null;
            $etpData = $this->getEtpProjectInter($projectId, $idCfpInter);
            $result[$projectId] = $etpData;
        }

        return $result;
    }

    public function getApprForProject($idProjet)
    {
        $apprs = DB::table('v_list_apprenants as L')
            ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_fonction', 'emp_email', 'emp_photo', 'emp_matricule', 'etp_name', 'idEtp')
            ->where('L.idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();
        return $apprs;
    }

    public function getAllFormateursForProjects(array $projectIds): array
    {
        $formateurs = DB::table('v_formateur_cfps')
            ->select('idProjet', 'idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
            ->whereIn('idProjet', $projectIds)
            ->groupBy('idProjet', 'idFormateur', 'name', 'firstName', 'photoForm', 'initialNameForm')
            ->get()
            ->groupBy('idProjet')
            ->map(function ($group) {
                return $group->toArray();
            })
            ->toArray();

        return $formateurs;
    }

    public function getAttendanceByProject($idProjet)
    {
        $roleId = DB::table('role_users')
            ->select('role_id')
            ->where('user_id', auth()->user()->id)
            ->first();

        if ($roleId->role_id === 5) {
            $allPourcentages = $this->getAllPourcentagesForProjectsFormateur([$idProjet]);
        } else {
            $allPourcentages = $this->getAllPourcentagesForProjects([$idProjet]);
        }



        $now = Carbon::now()->toDateString();

        $getSeance = DB::table('v_emargement_appr')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', $now)
            ->groupBy('idSeance')
            ->select('idSeance', 'idProjet', 'heureDebut', 'heureFin', 'dateSeance', 'isPresent', 'idEmploye')
            ->get();

        $getPresence = DB::table('v_emargement_appr')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', $now)
            ->select('idSeance', 'dateSeance', 'idProjet', 'isPresent', 'idEmploye')
            ->get();

        $countDate = DB::table('v_seances')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', $now)
            ->groupBy('dateSeance')
            ->select('idProjet', 'idSeance', DB::raw('COUNT(*) as count'), 'dateSeance')
            ->get();

        $apprs = DB::table('v_list_apprenants as L')
            ->select(
                'L.idEmploye',
                'emp_initial_name',
                'emp_name',
                'emp_firstname',
                'emp_fonction',
                'emp_email',
                'emp_photo',
                'emp_matricule',
                'etp_name',
                'idEtp',
            )
            ->where('L.idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();

        $projectData = $allPourcentages[$idProjet] ?? [];
        $pourcentage = $projectData['pourcentage'] ?? [];

        $percentPresent = $pourcentage['present'] ?? "0";
        $percentPartiel = $pourcentage['partiel'] ?? "0";
        $percentAbsent = $pourcentage['absent'] ?? "0";



        return [
            'apprs' => $apprs,
            'getSeance' => $getSeance,
            'getPresence' => $getPresence,
            'countDate' => $countDate,
            'percentPresent' => $percentPresent,
            'percentPartiel' => $percentPartiel,
            'percentAbsent' => $percentAbsent,
        ];
    }


    public function getAttendanceByProjectInter($idProjet)
    {

        $roleId = DB::table('role_users')
            ->select('role_id')
            ->where('user_id', auth()->user()->id)
            ->first();

        if ($roleId->role_id === 5) {
            $allPourcentages = $this->getAllPourcentagesForProjectsFormateur([$idProjet]);
        } else {
            $allPourcentages = $this->getAllPourcentagesForProjects([$idProjet]);
        }

        $apprs = DB::table('v_list_apprenants_inter')
            ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_fonction', 'emp_email', 'emp_photo', 'emp_matricule', 'etp_name')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();

        $getSeance = DB::table('v_emargement_appr_inter')
            ->select('idSeance', 'idProjet', 'heureDebut', 'heureFin', 'dateSeance', 'isPresent', 'idEmploye')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', Carbon::now()->toDateString()) // Ajoute la condition pour la date
            ->groupBy('idSeance')
            ->get();

        $getPresence = DB::table('v_emargement_appr_inter')
            ->select('idSeance', 'idProjet', 'isPresent', 'idEmploye')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', Carbon::now()->toDateString()) // Ajoute la condition pour la date
            ->get();

        $countDate = DB::table('v_seances')
            ->select('idProjet', 'dateSeance', 'idSeance', DB::raw('COUNT(*) as count'))
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', Carbon::now()->toDateString()) // Ajoute la condition pour la date
            ->groupBy('dateSeance')
            ->get();
        $projectData = $allPourcentages[$idProjet] ?? [];
        $pourcentage = $projectData['pourcentage'] ?? [];

        $percentPresent = $pourcentage['present'] ?? "0";
        $percentPartiel = $pourcentage['partiel'] ?? "0";
        $percentAbsent = $pourcentage['absent'] ?? "0";
        return [
            'apprs' => $apprs,
            'getSeance' => $getSeance,
            'getPresence' => $getPresence,
            'percentPresent' => $percentPresent,
            'percentPartiel' => $percentPartiel,
            'percentAbsent' => $percentAbsent,
        ];
    }

    public function getAllPourcentagesForProjectsFormateur(array $projectIds): array
    {


        if (empty($projectIds)) {
            return [];
        }


        $now = Carbon::now()->toDateString();


        $result = [];

        try {
            // Récupération des statistiques d'émargement groupées par projet
            $statutsByProject = DB::table('emargements as e')
                ->select('e.idProjet', 'e.isPresent', DB::raw('COUNT(*) as count'))
                ->join('projets as p', 'e.idProjet', 'p.idProjet')
                ->whereIn('e.idProjet', $projectIds)
                ->whereIn('e.isPresent', [0, 1, 2, 3])
                ->groupBy('e.idProjet', 'e.isPresent')
                ->get()
                ->groupBy('idProjet');

            // Récupération des séances groupées par projet
            $seancesByProject = DB::table('v_emargement_appr as ve')
                ->select('ve.idProjet', 've.idSeance')
                ->join('projets as p', 'p.idProjet', 've.idProjet')
                ->whereIn('ve.idProjet', $projectIds)
                ->whereDate('ve.dateSeance', '<=', $now)
                ->groupBy('ve.idProjet', 've.idSeance')
                ->get()
                ->groupBy('idProjet');

            // Récupération des apprenants groupées par projet
            $apprsByProject = DB::table('v_emargement_appr as ve')
                ->select('ve.idProjet', 've.idEmploye')
                ->join('projets as p', 'p.idProjet', 've.idProjet')
                ->whereIn('ve.idProjet', $projectIds)
                ->groupBy('ve.idProjet', 've.idEmploye')
                ->get()
                ->groupBy('idProjet');

            // Récupération des statuts par apprenant pour le calcul BE
            $apprStatuts = DB::table('emargements as e')
                ->select('e.idProjet', 'e.idEmploye', 'e.isPresent')
                ->join('projets as p', 'e.idProjet', 'p.idProjet')
                ->whereIn('e.idProjet', $projectIds)
                ->get()
                ->groupBy(['idProjet', 'idEmploye']);

            foreach ($projectIds as $projectId) {
                // Safe array access with null coalescing
                $statuts = $statutsByProject[$projectId] ?? collect();
                $seances = $seancesByProject[$projectId] ?? collect();
                $apprs = $apprsByProject[$projectId] ?? collect();
                $projectApprStatuts = $apprStatuts[$projectId] ?? [];

                $seancesCount = count($seances);
                $apprsCount = count($apprs);

                // Calcul pourcentage standard
                $countPresent = $statuts->where('isPresent', 3)->sum('count') ?? 0;
                $countPartiel = $statuts->where('isPresent', 2)->sum('count') ?? 0;
                $countAbsent = ($statuts->where('isPresent', 1)->sum('count') ?? 0) +
                    ($statuts->where('isPresent', 0)->sum('count') ?? 0);

                $divide = $seancesCount * $apprsCount;

                // Safe division
                $pourcentage = [
                    'present' => $divide > 0 ? number_format(($countPresent / $divide) * 100, 1, ',', ' ') : "0",
                    'partiel' => $divide > 0 ? number_format(($countPartiel / $divide) * 100, 1, ',', ' ') : "0",
                    'absent' => $divide > 0 ? number_format(($countAbsent / $divide) * 100, 1, ',', ' ') : "0",
                ];
                $isCompleted = (
                    floatval(str_replace(',', '.', $pourcentage['present'])) > 0 ||
                    floatval(str_replace(',', '.', $pourcentage['partiel'])) > 0 ||
                    floatval(str_replace(',', '.', $pourcentage['absent'])) > 0
                );

                // Calcul Nbre apprenant
                $NbPresent = 0;
                $NBAbsent = 0;
                $NbASaisir = 0;

                foreach ($apprs as $apprenant) {
                    $id = $apprenant->idEmploye;
                    $statuses = $projectApprStatuts[$id] ?? collect();

                    if ($statuses->isEmpty()) {
                        $NbASaisir++;
                    } else {
                        $statusValues = $statuses->pluck('isPresent')->toArray();
                        if (in_array(3, $statusValues)) {
                            $NbPresent++;
                        } else {
                            $NBAbsent++;
                        }
                    }
                }

                $nbApprenant = [
                    'nb_present' => $NbPresent,
                    'nb_absent' => $NBAbsent,
                    'nb_a_saisir' => $NbASaisir,
                    'total_inscrits' => $apprsCount,
                ];

                $result[$projectId] = [
                    'pourcentage' => $pourcentage,
                    'nbApprenant' => $nbApprenant,
                    'isCompleted' => $isCompleted
                ];
            }
        } catch (\Exception $e) {
            return [];
        }

        return $result;
    }
}
