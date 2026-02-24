<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmargementEtpController extends Controller
{

    public function idEtp()
    {

        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idCustomer;
    }
    public function getProjects()
    {
        $idEtp = $this->idEtp();
        $projects = DB::table('projets as p')
            ->select(
                'p.idProjet',
                'p.dateDebut as start_date',
                'p.dateFin as end_date',
                'p.idCustomer as idCfp',
                'p.idModule',
                'mdls.moduleName as module_name',
                'intras.idEtp as etp_id_intra',
                'ie.idEtp as etp_id_inter',
                'c.customerName as cfp_name',
                'li.li_name',
                'li.idLieu',
                DB::raw('COALESCE(ie.idEtp, intras.idEtp) as etp_id'),
                'cst.customerName as etp_name',
                'cst.customerEmail as etp_email',
                DB::raw("CASE 
                            WHEN p.project_is_archived = 1 THEN 'Archivé'
                            WHEN p.project_is_trashed = 1 THEN 'Supprimé'
                            WHEN p.project_is_closed = 1 THEN 'Cloturé'
                            WHEN p.project_is_active = 1 AND p.dateFin < CURRENT_DATE THEN 'Terminé'
                            WHEN p.project_is_active = 0 AND p.project_is_cancelled = 0 AND p.project_is_repported = 0 
                                AND p.project_is_reserved = 0 AND p.project_is_archived = 0 AND p.project_is_trashed = 0 
                                THEN 'En préparation'
                            WHEN p.project_is_cancelled = 1 THEN 'Annulé'
                            WHEN p.project_is_repported = 1 THEN 'Reporté'
                            WHEN p.project_is_reserved = 1 THEN 'Réservé'
                            WHEN p.project_is_active = 1 AND p.dateDebut > CURRENT_DATE THEN 'Planifié'
                            ELSE 'En cours'
                        END as project_status")
            )
            ->leftJoin('intras', 'intras.idProjet', '=', 'p.idProjet')
            ->leftJoin('customers as cst', 'intras.idEtp', '=', 'cst.idCustomer')
            ->leftJoin('inter_entreprises as ie', 'ie.idProjet', '=', 'p.idProjet')
            ->leftJoin('mdls', 'p.idModule', '=', 'mdls.idModule')
            ->leftJoin('customers as c', 'c.idCustomer', '=', 'p.idCustomer')
            ->leftJoin('salles as s', 's.idSalle', '=', 'p.idSalle')
            ->leftJoin('lieux as li', 's.idLieu', '=', 'li.idLieu')
            ->whereNotNull('p.dateDebut')
            ->where('p.project_is_closed', 0)   // <-- Exclut Cloturé
            ->where('p.project_is_trashed', 0)  // <-- Exclut Supprimé
            ->where(function ($query) use ($idEtp) {
                $query->where('intras.idEtp', $idEtp)
                    ->orWhere('ie.idEtp', $idEtp);
            })
            ->get();



        if ($projects->isEmpty()) {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }
        $projectIds = $projects->pluck('idProjet')->unique()->toArray();
        $allPourcentages = $this->getAllPourcentagesForProjects($projectIds);
        DB::table('attendance_count')->updateOrInsert(
            ['idProjet' => $idProjet],
            [
                'nb_present' => $allPourcentages[$idProjet]['nbApprenant']['nb_present'],
                'nb_absent' => $allPourcentages[$idProjet]['nbApprenant']['nb_absent'],
                'nb_total_inscrit' => $allPourcentages[$idProjet]['nbApprenant']['total_inscrits'], // Correction: nb_total au lieu de total_inscrits
                'nb_a_saisir' => $allPourcentages[$idProjet]['nbApprenant']['nb_a_saisir']
            ]
        );
        $results = $projects->map(function ($project) use ($allPourcentages) {
            return [
                'idProjet'=> $project->idProjet,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'idCfp' => $project->idCfp,
                'idModule' => $project->idModule,
                'module_name' => $project->module_name ?? null,
                'etp_id_intra' => $project->etp_id_intra,
                'etp_id_inter' => $project->etp_id_inter,
                'etp_id' => $project->etp_id,
                'project_status' => $project->project_status,
                'cfp_name' => $project->cfp_name,
                'idLieu' =>$project->idLieu,
                'li_name' =>$project->li_name,
                'pourcentage' => $allPourcentages[$project->idProjet]['pourcentage'] ?? [],
                'nbApprenant' => $allPourcentages[$project->idProjet]['nbApprenant'] ?? [],
            ];
        });
        return response()->json([
            'status' => 200,
            'projects' => [
                'project_count' => $projects->count(),
                'project_items' => $results
            ]
        ], 200);
    }

    private function getAllPourcentagesForProjects(array $projectIds): array
    {
        $now = Carbon::now()->toDateString();
        $result = [];
        $idEtp = $this->idEtp();
        
        // Récupérer les idCustomer pour chaque projet
        $idCustomers = DB::table('projets')
            ->whereIn('idProjet', $projectIds)
            ->pluck('idCustomer', 'idProjet');

        // 1. Récupérer tous les apprenants de l'entreprise pour ces projets
        $apprenantsByProject = DB::table('v_apprenant_etp_alls as va')
            ->select('va.idProjet', 'va.idEmploye', 'va.idCfp')
            ->whereIn('va.idProjet', $projectIds)
            ->where('va.idEtp', $idEtp)
            ->whereIn('va.idCfp', $idCustomers->values()->unique()->toArray())
            ->groupBy('va.idProjet', 'va.idEmploye', 'va.idCfp')
            ->get()
            ->groupBy('idProjet');

        // 2. Récupérer toutes les séances passées pour ces projets
        $seancesByProject = DB::table('seances as s')
            ->select('s.idProjet', 's.idSeance')
            ->whereIn('s.idProjet', $projectIds)
            ->whereDate('s.dateSeance', '<=', $now)
            ->groupBy('s.idProjet', 's.idSeance')
            ->get()
            ->groupBy('idProjet');

        // 3. Récupérer tous les émargements pour ces projets et cette entreprise - CORRIGÉ
        $emargementsByProject = DB::table('emargements as e')
            ->select('e.idProjet', 'e.idEmploye', 'e.idSeance', 'e.isPresent')
            ->join('v_apprenant_etp_alls as va', function($join) use ($idEtp, $idCustomers) {
                $join->on('va.idEmploye', '=', 'e.idEmploye')
                    ->on('va.idProjet', '=', 'e.idProjet')
                    ->where('va.idEtp', $idEtp)
                    ->whereIn('va.idCfp', $idCustomers->values()->unique()->toArray());
            })
            ->whereIn('e.idProjet', $projectIds)
            ->whereIn('e.isPresent', [0, 1, 2, 3])
            ->get()
            ->groupBy('idProjet');

        foreach ($projectIds as $projectId) {
            $apprenants = $apprenantsByProject[$projectId] ?? collect();
            $seances = $seancesByProject[$projectId] ?? collect();
            $emargements = $emargementsByProject[$projectId] ?? collect();
            
            $apprsCount = count($apprenants);
            $seancesCount = count($seances);
            
            // Initialiser les compteurs
            $totalEmargementsAttendus = $seancesCount * $apprsCount;
            $countPresent = 0;
            $countPartiel = 0;
            $countAbsent = 0;

            // Calculer les émargements réels - COMPTAGE CORRECT
            $countPresent = $emargements->where('isPresent', 3)->count();
            $countPartiel = $emargements->where('isPresent', 2)->count();
            $countAbsent = $emargements->whereIn('isPresent', [0, 1])->count();



            // Calculer les pourcentages
            $pourcentage = [
                'present' => $totalEmargementsAttendus > 0 ? 
                    number_format(($countPresent / $totalEmargementsAttendus) * 100, 1, ',', ' ') : "0",
                'partiel' => $totalEmargementsAttendus > 0 ? 
                    number_format(($countPartiel / $totalEmargementsAttendus) * 100, 1, ',', ' ') : "0",
                'absent' => $totalEmargementsAttendus > 0 ? 
                    number_format(($countAbsent / $totalEmargementsAttendus) * 100, 1, ',', ' ') : "0",
            ];

            // Calculer le statut par apprenant
            $NbPresent = 0;
            $NBAbsent = 0;
            $NbASaisir = 0;

            foreach ($apprenants as $apprenant) {
                $idEmploye = $apprenant->idEmploye;
                
                // Récupérer tous les émargements de cet apprenant
                $emargementsApprenant = $emargements->where('idEmploye', $idEmploye);
                
                if ($emargementsApprenant->isEmpty()) {
                    $NbASaisir++;
                } else {
                    // Vérifier s'il a au moins un "présent"
                    $hasPresent = $emargementsApprenant->contains('isPresent', 3);
                    $hasAbsent = $emargementsApprenant->contains('isPresent', 0) || 
                                $emargementsApprenant->contains('isPresent', 1);
                    
                    if ($hasPresent) {
                        $NbPresent++;
                    } else if ($hasAbsent) {
                        $NBAbsent++;
                    } else {
                        $NbASaisir++;
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
            ];
        }

        return $result;
    }
    public function show($idProjet)
    {
        $apprenants = DB::table('v_apprenant_etp_alls')
            ->select('idEmploye', 'idEtp', 'idCfp', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_photo')
            ->where('idEtp', $this->idEtp())
            ->where('idProjet', $idProjet)
            ->groupBy('idEmploye')
            ->get();

        $seances = DB::table('seances')
            ->where('idProjet', $idProjet)
            ->get();

        $results = [];

        foreach ($seances as $seance) {
            $row = [
                'idSeance'    => $seance->idSeance,
                'date'        => $seance->dateSeance,
                'start_hour'  => $seance->heureDebut,
                'end_hour'    => $seance->heureFin,
            ];

            foreach ($apprenants as $appr) {
                $presence = DB::table('emargements')
                    ->where('idProjet', $idProjet)
                    ->where('idSeance', $seance->idSeance)
                    ->where('idEmploye', $appr->idEmploye)
                    ->value('isPresent');
                $row[$appr->idEmploye] = $presence ?? 0;
            }

            $results[] = $row;
        }

        // Récupérer seulement les pourcentages
        $pourcentageData = $this->getAllPourcentagesForProjects([$idProjet]);
        $pourcentage = $pourcentageData[$idProjet]['pourcentage'] ?? [
            'present' => '0', 
            'partiel' => '0', 
            'absent' => '0'
        ];

        return response()->json([
            'status' => 200,
            'projects' => [
                'project_count' => count($seances),
                "matrix"        => $results,
                "pourcentage"   => $pourcentage, // Directement les pourcentages
                "apprenants"    => $apprenants,
                "seances"       => $seances
            ]
        ], 200);
    }


}
