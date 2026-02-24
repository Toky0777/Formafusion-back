<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\EntrepriseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AnalyticPortefeuilleClientController extends Controller
{

    // Ajoutez cette fonction si elle n'existe pas
private function idCfp()
{
    return Customer::idCustomer();
}
private function getProject($year){
        $projectsNoSubContractor = DB::table('v_union_projets')
                        ->select('idProjet', 'total_ttc', 'module_name', 'dateDebut', 'dateFin', 'project_reference')
                        ->where(function ($query){
                            $query->where('idCfp_intra', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        ->whereNot('module_name', 'Default module')
                        ->whereYear('dateDebut', $year); 

        $idProjectSubContractors = DB::table('project_sub_contracts')
                                        ->where('idSubContractor', Customer::idCustomer())
                                        ->pluck('idProjet');
        
        $projectSubContractors = DB::table('v_union_projets')
                                    ->select('idProjet', 'total_ht_sub_contractor as total_ttc', 'module_name', 'dateDebut', 'dateFin', 'project_reference')
                                    ->whereIn('idProjet', $idProjectSubContractors)
                                    ->whereNot('module_name', 'Default module')
                                    ->whereYear('dateDebut', $year); 

        $projects = $projectsNoSubContractor->union($projectSubContractors)
                                            ->where('project_is_trashed', 0)
                                            ->orderBy('total_ttc', 'desc')
                                            ->get();

        $total_price = $this->getTotalPriceProject($year);

        $results = [];
        foreach($projects as $project){
            $results[] = [
                'id_projet' => $project->idProjet,
                'module_name' => $project->module_name,
                'project_reference' => $project->project_reference,
                'date_debut' => Carbon::parse($project->dateDebut)->format('j.m.y'),
                'date_fin' => Carbon::parse($project->dateFin)->format('j.m.y'),
                'total_ttc' => $project->total_ttc,
                'percentage' => number_format($this->getPercentageProject($project->total_ttc, $total_price), 2)
            ];
        }

        return $results;
    }

    
    private function getPercentageProject($price_project, $total_price)
    {
        return ($total_price == 0) ? 0 : $price_project * 100 / $total_price;
    }

    private function getTotalPriceProject($year){
        $idProjectSubContractors = DB::table('project_sub_contracts')
                                        ->where('idSubContractor', Customer::idCustomer())
                                        ->pluck('idProjet');

        $priceNoSubcontractor = DB::table('v_union_projets')
                        ->where(function ($query){
                            $query->where('idCfp_intra', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        ->whereNot('module_name', 'Default module')
                        ->whereYear('dateDebut', $year)
                        ->value(DB::raw('SUM(total_ttc)'));
        
        $priceWithSubcontractor = DB::table('v_union_projets')
                        ->whereIn('idProjet', $idProjectSubContractors)
                        ->whereNot('module_name', 'Default module')
                        ->whereYear('dateDebut', $year)
                        ->value(DB::raw('SUM(total_ht_sub_contractor)'));
        
        return $priceNoSubcontractor + $priceWithSubcontractor;
    }

    // NOUVELLE FONCTION : Récupérer les projets par client
    private function getProjectsByClient($clientName, $year)
    {
        $projectsNoSubContractor = DB::table('v_union_projets')
            ->select('idProjet', 'total_ttc', 'module_name', 'dateDebut', 'dateFin', 'project_reference')
            ->where(function ($query) {
                $query->where('idCfp_intra', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer());
            })
            ->where('project_reference', $clientName)
            ->whereNot('module_name', 'Default module')
            ->whereYear('dateDebut', $year)
            ->where('project_is_trashed', 0);

        $idProjectSubContractors = DB::table('project_sub_contracts')
            ->where('idSubContractor', Customer::idCustomer())
            ->pluck('idProjet');

        $projectSubContractors = DB::table('v_union_projets')
            ->select('idProjet', 'total_ht_sub_contractor as total_ttc', 'module_name', 'dateDebut', 'dateFin', 'project_reference')
            ->whereIn('idProjet', $idProjectSubContractors)
            ->where('project_reference', $clientName)
            ->whereNot('module_name', 'Default module')
            ->whereYear('dateDebut', $year)
            ->where('project_is_trashed', 0);

        $projects = $projectsNoSubContractor->union($projectSubContractors)
            ->orderBy('total_ttc', 'desc')
            ->get();

        $total_price = $this->getTotalPriceProject($year);

        $results = [];
        foreach ($projects as $project) {
            $results[] = [
                'id_projet' => $project->idProjet,
                'module_name' => $project->module_name,
                'project_reference' => $project->project_reference,
                'date_debut' => Carbon::parse($project->dateDebut)->format('j.m.y'),
                'date_fin' => Carbon::parse($project->dateFin)->format('j.m.y'),
                'total_ttc' => $project->total_ttc,
                'percentage' => number_format($this->getPercentageProject($project->total_ttc, $total_price), 2)
            ];
        }

        return $results;
    }

    private function countProjectAndCaByCustomer($etpId, $year){
        return DB::table('v_union_projets')
            ->select(DB::raw('COUNT(idProjet) as totalProject'), DB::raw('SUM(total_ttc) as totalCA'))
            ->where(function ($query) {
                $query->where('idCfp_intra', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer());
            })
            ->where(function($query) use($etpId){
                $query->where('idEtp_inter', $etpId)
                ->orWhere('idEtp', $etpId);
            })
            ->where('module_name', '<>', 'Default module')
            ->where('headYear', $year)
            ->whereIn('project_status', ['Terminé', 'Cloturé'])
            ->where('project_is_trashed', 0)
            ->groupBy('etp_name')
            ->first();
    }

    private function couProjectByCustomer($etpId, $year){
        return DB::table('v_union_projets')
            ->select(DB::raw('COUNT(idProjet) as totalProject'), DB::raw('SUM(total_ttc) as totalCA'))
            ->where(function ($query) {
                $query->where('idCfp_intra', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer());
            })
            ->where(function($query) use($etpId){
                $query->where('idEtp_inter', $etpId)
                ->orWhere('idEtp', $etpId);
            })
            ->whereNot('module_name', 'Default module')
            ->whereYear('dateDebut', $year)
            ->whereIn('project_status',['Terminé', 'Cloturé'])
            ->where('project_is_trashed', 0)
            ->groupBy('etp_name')
            ->first()
            ;
    }

    // NOUVELLE FONCTION : Calcul des KPIs par client (MODIFIÉE pour gérer les clients sans projets)
private function calculateClientKPIs($currentYearProjects, $previousYearProjects, $currentYear, $clientName)
{
    // Calcul du nombre de projets
    $currentYearCount = count($currentYearProjects);
    $previousYearCount = count($previousYearProjects);
    
    // Évolution du nombre de projets
    $projectEvolution = 0;
    if ($previousYearCount > 0) {
        $projectEvolution = (($currentYearCount - $previousYearCount) / $previousYearCount) * 100;
    } elseif ($currentYearCount > 0) {
        $projectEvolution = 100;
    }
    
    // Calcul du chiffre d'affaire
    $currentYearRevenue = array_sum(array_column($currentYearProjects, 'total_ttc'));
    $previousYearRevenue = array_sum(array_column($previousYearProjects, 'total_ttc'));
    
    // Évolution du CA
    $revenueEvolution = 0;
    if ($previousYearRevenue > 0) {
        $revenueEvolution = (($currentYearRevenue - $previousYearRevenue) / $previousYearRevenue) * 100;
    } elseif ($currentYearRevenue > 0) {
        $revenueEvolution = 100;
    }
    
    // NOUVEAU : Calcul des apprenants
    $currentYearLearners = $this->getLearnersByClientAndYear($clientName, $currentYear);
    $previousYearLearners = $this->getLearnersByClientAndYear($clientName, $currentYear - 1);
    
    $currentYearLearnersCount = count($currentYearLearners);
    $previousYearLearnersCount = count($previousYearLearners);
    
    // Évolution du nombre d'apprenants
    $learnersEvolution = 0;
    if ($previousYearLearnersCount > 0) {
        $learnersEvolution = (($currentYearLearnersCount - $previousYearLearnersCount) / $previousYearLearnersCount) * 100;
    } elseif ($currentYearLearnersCount > 0) {
        $learnersEvolution = 100;
    }
    
    return [
        'project_reference' => $currentYearProjects[0]['project_reference'] ?? $clientName,
        'projects_count' => $currentYearCount,
        'projects_evolution' => number_format($projectEvolution, 1),
        'projects_trend' => $projectEvolution >= 0 ? 'up' : 'down',
        
        'revenue_amount' => $currentYearRevenue,
        'revenue_formatted' => $this->formatRevenue($currentYearRevenue),
        'revenue_evolution' => number_format($revenueEvolution, 1),
        'revenue_trend' => $revenueEvolution >= 0 ? 'up' : 'down',
        
        // NOUVEAUX INDICATEURS APPRENANTS
        'learners_count' => $currentYearLearnersCount,
        'learners_evolution' => number_format($learnersEvolution, 1),
        'learners_trend' => $learnersEvolution >= 0 ? 'up' : 'down',
        
        'year' => $currentYear,
        'last_project_date' => $this->getLastProjectDate($currentYearProjects),
        'days_since_last_project' => $this->getDaysSinceLastProject($currentYearProjects),
        
        // Indicateur pour savoir si le client a des projets
        'has_projects' => $currentYearCount > 0,
        
        // Données détaillées pour les graphiques
        // 'learners_by_project' => $this->getLearnersByProjectDetails($currentYearProjects, $currentYear)
    ];
}

private function getLastProjectDate($etpId)
    {
        return DB::table('v_union_projets')
            ->select(DB::raw('DATEDIFF(NOW(), datefin) as nbjourDernier'))
            ->where(function ($query) {
                $query->where('idCfp_intra', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer());
            })
            ->where(function($query) use($etpId){
                $query->where('idEtp_inter', $etpId)
                ->orWhere('idEtp', $etpId);
            })
            ->whereNot('module_name', 'Default module')
            ->whereIn('project_status',['Terminé', 'Cloturé'])
            ->where('project_is_trashed', 0)
            ->orderByDesc('datefin')
            ->first()
            ;
    }

    // NOUVELLE FONCTION : Récupérer les KPIs pour tous les clients (MODIFIÉE)
public function getClientKPIs(Request $request, $year = null)
{
    $currentYear = $year ?? date('Y');
    $previousYear = $currentYear - 1;

    // 🔍 Récupérer les paramètres de recherche et filtres
    $searchTerm = $request->get('q');
    $location = $request->get('location');
    $type = $request->get('type');
    $minRevenue = $request->get('minRevenue');
    $minLearners = $request->get('minLearners');

    // Récupérer tous les clients pour le ranking (sans pagination) avec filtres
    $allClientsForRanking = DB::table('v_collaboration_cfp_etps as vc')
        ->leftJoin('v_union_projects as vp', function($join) use ($currentYear) {
            $join->on('vc.idEtp', '=', 'vp.id_etp')
                 ->where('vp.module_name', '<>', 'Default module')
                 ->where('vp.headYear', $currentYear)
                 ->whereIn('vp.project_status', ['Terminé', 'Cloturé'])
                 ->where('vp.project_is_trashed', 0);
        })
        ->select(
            'vc.idEtp',
            'vc.etp_name',
            'vc.etp_ville',
            'vc.etp_email',
            'vc.type_etp_desc',
            DB::raw('COALESCE(SUM(vp.total_ttc), 0) AS total')
        )
        ->where('vc.idCfp', Customer::idCustomer())
        // 🔍 APPLICATION DES FILTRES DE RECHERCHE
        ->when($searchTerm, function ($q) use ($searchTerm) {
            return $q->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('vc.etp_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('vc.etp_ville', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('vc.etp_email', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('vc.type_etp_desc', 'LIKE', "%{$searchTerm}%");
            });
        })
        ->when($location, function ($q, $location) {
            return $q->where('vc.etp_ville', $location);
        })
        ->when($type, function ($q, $type) {
            return $q->where('vc.type_etp_desc', $type);
        })
        ->groupBy('vc.idEtp', 'vc.etp_name', 'vc.etp_ville', 'vc.etp_email', 'vc.type_etp_desc')
        ->orderByDesc('total')
        ->get();

    // Calculer le ranking sur les clients filtrés
    $rankingData = $this->calculateClientRanking($allClientsForRanking);

    // Maintenant récupérer les clients paginés avec les mêmes filtres
    $clientsQuery = DB::table('v_collaboration_cfp_etps as vc')
        ->leftJoin('v_union_projects as vp', function($join) use ($currentYear) {
            $join->on('vc.idEtp', '=', 'vp.id_etp')
                 ->where('vp.module_name', '<>', 'Default module')
                 ->where('vp.headYear', $currentYear)
                 ->whereIn('vp.project_status', ['Terminé', 'Cloturé'])
                 ->where('vp.project_is_trashed', 0);
        })
        ->select(
            'vc.idEtp',
            'vc.etp_name',
            'vc.etp_logo',
            'vc.etp_phone',
            'vc.etp_email',
            'vc.etp_ville',
            'vc.type_etp_desc',
            DB::raw('COALESCE(SUM(vp.total_ttc), 0) AS total')
        )
        ->where('vc.idCfp', Customer::idCustomer())
        // 🔍 APPLICATION DES FILTRES DE RECHERCHE (identique)
        ->when($searchTerm, function ($q) use ($searchTerm) {
            return $q->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('vc.etp_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('vc.etp_ville', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('vc.etp_email', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('vc.type_etp_desc', 'LIKE', "%{$searchTerm}%");
            });
        })
        ->when($location, function ($q, $location) {
            return $q->where('vc.etp_ville', $location);
        })
        ->when($type, function ($q, $type) {
            return $q->where('vc.type_etp_desc', $type);
        })
        ->groupBy('vc.idEtp', 'vc.etp_name', 'vc.etp_logo', 'vc.etp_phone', 'vc.etp_email', 'vc.etp_ville', 'vc.type_etp_desc')
        ->orderByDesc('total');

    // 🔍 FILTRE REVENU MINIMUM
    if ($minRevenue) {
        $clientsQuery->having('total', '>=', $minRevenue);
    }

    $clients = $clientsQuery->paginate(9);

    $clients->getCollection()->transform(function ($client) use ($currentYear, $previousYear, $rankingData, $minLearners) {
        $projectByCustomer = $this->countProjectAndCaByCustomer($client->idEtp, $currentYear);
        $learnersPrevious = $this->getLearnersEvolutionData($client->idEtp, $previousYear);
        $learnersCurrent = $this->getLearnersEvolutionData($client->idEtp, $currentYear);
        $revenuePrevious = $this->getRevenueEvolutionData($client->idEtp, $previousYear);
        $revenueCurrent = $this->getRevenueEvolutionData($client->idEtp, $currentYear);
        $nbjourDernier = $this->getLastProjectDate($client->idEtp);
        $daysSinceLastProject = $nbjourDernier->nbjourDernier ?? 0;
        $frequency = $this->getClientFrequency((int) ($projectByCustomer->totalProject ?? 0),(int) $daysSinceLastProject);
        $clientRevenuePerecentage = $this->getClientRevenuePercentage($client->idEtp);
        
        // 🔍 FILTRE APPRENANTS MINIMUM
        $totalLearners = $this->getLearnersByClientAndYear($client->idEtp, $currentYear);
        
        // Si filtre apprenants et client ne correspond pas, retourner null (sera filtré)
        if ($minLearners && $totalLearners < $minLearners) {
            return null;
        }

        return [
            'client_info' => [
                'id_etp' => $client->idEtp,
                'etp_name' => $client->etp_name,
                'etp_logo' => $client->etp_logo,
                'etp_phone' => $client->etp_phone,
                'etp_email' => $client->etp_email,
                'etp_ville' => $client->etp_ville,
                'type_etp_desc' => $client->type_etp_desc, // Ajouté pour les filtres
            ],
            'nb_projects' => $projectByCustomer->totalProject ?? 0,
            'total_ca' => (float) ($client->total),
            'ranking' => $rankingData[$client->idEtp] ?? 'N/A',
            'nbjourDernierProject' => $this->getLastProjectDate($client->idEtp),
            'nbrapprenant' => $totalLearners,
            'frequency' => $frequency,
            'regulation' => $clientRevenuePerecentage,
            'charts_data' => [
                'learners_previous' => $learnersPrevious,
                'learners_current' => $learnersCurrent,
                'revenue_previous' => $revenuePrevious,
                'revenue_current' => $revenueCurrent,
            ],
        ];
    });

    // 🔍 FILTRER LES CLIENTS NULL (ceux qui ne correspondent pas au filtre apprenants)
    $filteredClients = $clients->getCollection()->filter()->values();

    return response()->json([
        'data' => $filteredClients,
        'current_page' => $clients->currentPage(),
        'last_page' => $clients->lastPage(),
        'total' => $filteredClients->count(),
        'search_info' => [
            'search_term' => $searchTerm,
            'filters_applied' => [
                'location' => $location,
                'type' => $type,
                'min_revenue' => $minRevenue,
                'min_learners' => $minLearners
            ]
        ]
    ], 200);
}

    // NOUVELLE FONCTION : Ajouter le ranking des clients
 private function calculateClientRanking($clients)
{
    $rankingData = [];
    $rank = 1;
    $previousTotal = null;
    $skipRank = 0;

    foreach ($clients as $index => $client) {
        // Si le CA est différent du précédent, on met à jour le rank
        if ($previousTotal !== null && $client->total != $previousTotal) {
            $rank += $skipRank + 1;
            $skipRank = 0;
        } 
        // Si même CA que le précédent, on garde le même rank mais on prépare le skip
        elseif ($previousTotal !== null && $client->total == $previousTotal) {
            $skipRank++;
        }
        // Pour le premier élément
        elseif ($previousTotal === null) {
            $rank = 1;
        }

        $rankingData[$client->idEtp] = $rank;
        $previousTotal = $client->total;
    }

    return $rankingData;
}
    // FONCTION UTILITAIRE : Formater le chiffre d'affaire
    private function formatRevenue($amount)
    {
        if ($amount >= 1000000) {
            return number_format($amount / 1000000, 1) . 'M€';
        } elseif ($amount >= 1000) {
            return number_format($amount / 1000, 1) . 'K€';
        } else {
            return number_format($amount, 0) . '€';
        }
    }

    // FONCTION UTILITAIRE : Date du dernier projet
    // private function getLastProjectDate($projects)
    // {
    //     if (empty($projects)) {
    //         return null;
    //     }
        
    //     $lastDate = null;
    //     foreach ($projects as $project) {
    //         $projectDate = Carbon::createFromFormat('j.m.y', $project['date_fin']);
    //         if (!$lastDate || $projectDate->gt($lastDate)) {
    //             $lastDate = $projectDate;
    //         }
    //     }
        
    //     return $lastDate ? $lastDate->format('j.m.y') : null;
    // }

    // FONCTION UTILITAIRE : Jours depuis le dernier projet
    private function getDaysSinceLastProject($projects)
    {
        $lastProjectDate = $this->getLastProjectDate($projects);
        if (!$lastProjectDate) {
            return null;
        }
        
        $lastDate = Carbon::createFromFormat('j.m.y', $lastProjectDate);
        return $lastDate->diffInDays(Carbon::now());
    }

    // FONCTION UTILITAIRE : Déterminer la fréquence du client (MODIFIÉE)
    private function getClientFrequency($projectsCount, $daysSinceLastProject)
    {
        if ($projectsCount == 0) {
            return 'prospect'; // Changé de 'perdu' à 'prospect' pour les clients sans projets
        } elseif ($projectsCount == 1 && $daysSinceLastProject <= 90) {
            return 'nouveau';
        } elseif ($projectsCount >= 5 && $daysSinceLastProject <= 60) {
            return 'regulier';
        } elseif ($projectsCount >= 3 && $daysSinceLastProject <= 90) {
            return 'frequent';
        } elseif ($projectsCount >= 1 && $daysSinceLastProject <= 180) {
            return 'occasionnel';
        } elseif ($daysSinceLastProject > 365) {
            return 'inactif'; // Changé de 'perdu' à 'inactif'
        } else {
            return 'rare';
        }
    }

    // FONCTION PRINCIPALE : Récupérer tous les clients avec leurs KPIs (MODIFIÉE)
    public function getClientsWithKPIs()
    {
        try {
            $clientKPIs = $this->getClientKPIs();
            
            // MODIFICATION : On ne retourne plus d'erreur si aucun client n'a de projets
            // On inclut maintenant tous les clients
            
            // Ajouter la fréquence pour chaque client
            foreach ($clientKPIs as &$client) {
                $client['kpis']['frequency'] = $this->getClientFrequency(
                    $client['kpis']['projects_count'],
                    $client['kpis']['days_since_last_project'] ?? 999
                );
            }
            
            // Calculer les statistiques globales
            $clientsWithProjects = array_filter($clientKPIs, function($client) {
                return $client['kpis']['has_projects'];
            });
            
            return response()->json([
                'status' => 200,
                'clients' => $clientKPIs,
                'summary' => [
                    'total_clients' => count($clientKPIs),
                    'clients_with_projects' => count($clientsWithProjects),
                    'clients_without_projects' => count($clientKPIs) - count($clientsWithProjects),
                    'year' => date('Y'),
                    'total_revenue' => $this->formatRevenue(array_sum(array_column(array_column($clientKPIs, 'kpis'), 'revenue_amount'))),
                    'total_projects' => array_sum(array_column(array_column($clientKPIs, 'kpis'), 'projects_count'))
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la récupération des données: ' . $e->getMessage()
            ], 500);
        }
    }

    // NOUVELLE FONCTION : Récupérer seulement les clients avec projets (pour compatibilité)
    public function getClientsWithProjectsOnly()
    {
        try {
            $clientKPIs = $this->getClientKPIs();
            
            // Filtrer seulement les clients avec projets
            $clientsWithProjects = array_filter($clientKPIs, function($client) {
                return $client['kpis']['has_projects'];
            });
            
            // Réindexer le tableau
            $clientsWithProjects = array_values($clientsWithProjects);
            
            if (empty($clientsWithProjects)) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Aucun client trouvé avec des projets !'
                ], 404);
            }
            
            // Ajouter la fréquence pour chaque client
            foreach ($clientsWithProjects as &$client) {
                $client['kpis']['frequency'] = $this->getClientFrequency(
                    $client['kpis']['projects_count'],
                    $client['kpis']['days_since_last_project'] ?? 999
                );
            }
            
            return response()->json([
                'status' => 200,
                'clients' => $clientsWithProjects,
                'summary' => [
                    'total_clients' => count($clientsWithProjects),
                    'year' => date('Y'),
                    'total_revenue' => $this->formatRevenue(array_sum(array_column(array_column($clientsWithProjects, 'kpis'), 'revenue_amount'))),
                    'total_projects' => array_sum(array_column(array_column($clientsWithProjects, 'kpis'), 'projects_count'))
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la récupération des données: ' . $e->getMessage()
            ], 500);
        }
    }

    // FONCTION ALTERNATIVE : Pour la compatibilité avec l'ancien code
    public function getClientPortefeuilleData()
    {
        $currentYear = date('Y');
        
        // Récupérer les projets détaillés
        $projects = $this->getProject($currentYear);
        
        // Récupérer les KPI globaux (pour compatibilité)
        $kpis = $this->getClientStatsOptimized($currentYear);
        
        return response()->json([
            'success' => true,
            'data' => [
                'projects' => $projects,
                'kpis' => $kpis,
                'summary' => [
                    'total_projects' => $kpis['projects_count'],
                    'total_revenue' => $kpis['revenue_formatted'],
                    'year' => $currentYear
                ]
            ]
        ]);
    }

    // FONCTION EXISTANTE : Pour compatibilité
    private function getClientStatsOptimized($currentYear)
    {
        $previousYear = $currentYear - 1;
        
        // Récupérer les projets des deux années en une seule opération
        $currentYearProjects = $this->getProject($currentYear);
        $previousYearProjects = $this->getProject($previousYear);
        
        // Calculs pour les projets
        $currentYearCount = count($currentYearProjects);
        $previousYearCount = count($previousYearProjects);
        
        $projectEvolution = 0;
        if ($previousYearCount > 0) {
            $projectEvolution = (($currentYearCount - $previousYearCount) / $previousYearCount) * 100;
        } elseif ($currentYearCount > 0) {
            $projectEvolution = 100;
        }
        
        // Calculs pour le CA
        $currentYearRevenue = array_sum(array_column($currentYearProjects, 'total_ttc'));
        $previousYearRevenue = array_sum(array_column($previousYearProjects, 'total_ttc'));
        
        $revenueEvolution = 0;
        if ($previousYearRevenue > 0) {
            $revenueEvolution = (($currentYearRevenue - $previousYearRevenue) / $previousYearRevenue) * 100;
        } elseif ($currentYearRevenue > 0) {
            $revenueEvolution = 100;
        }
        
        return [
            'projects_count' => $currentYearCount,
            'projects_evolution' => number_format($projectEvolution, 1),
            'projects_trend' => $projectEvolution >= 0 ? 'up' : 'down',
            
            'revenue_amount' => $currentYearRevenue,
            'revenue_formatted' => $this->formatRevenue($currentYearRevenue),
            'revenue_evolution' => number_format($revenueEvolution, 1),
            'revenue_trend' => $revenueEvolution >= 0 ? 'up' : 'down',
            
            'year' => $currentYear
        ];
    }

    // Fonction pour récupérer les apprenants par client et année
   private function getLearnersByClientAndYear($etpId, $year)
{
    return DB::table('v_apprenant_union')
        ->select('idEmploye')
        ->where('idCfp', $this->idCfp())
        ->where('idEtp', $etpId)
        ->whereNotNull('idProjet')
        ->whereYear('dateDebut', $year)
        ->whereIn('project_status', ['Terminé', 'Cloturé'])
        ->distinct()
        ->count();
}

  // Fonction pour récupérer les apprenants par projet spécifique
private function getLearnersByProjectId($projectId, $year)
{
    return DB::table('v_apprenant_union')
        ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_email', 'etp_name', 'idProjet', 'module_name')
        ->where('idCfp', $this->idCfp())
        ->where('idProjet', $projectId)
        ->whereYear('dateDebut', $year)
        ->distinct()
        ->get();
}


// Fonction pour récupérer le project_reference d'un projet
private function getProjectReference($projectId)
{
    $project = DB::table('v_union_projets')
        ->select('project_reference')
        ->where('idProjet', $projectId)
        ->first();
    
    return $project ? $project->project_reference : 'N/A';
}

// Fonction pour récupérer les apprenants avec project_reference
private function getLearnersByProjectWithReference($projectReference, $year)
{
    // D'abord trouver les idProjet correspondant au project_reference
    $projectIds = DB::table('v_union_projets')
        ->where('project_reference', $projectReference)
        ->whereYear('dateDebut', $year)
        ->pluck('idProjet');
    
    if ($projectIds->isEmpty()) {
        return collect([]);
    }
    
    return DB::table('v_apprenant_union')
        ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_email', 'etp_name', 'idProjet', 'module_name')
        ->where('idCfp', $this->idCfp())
        ->whereIn('idProjet', $projectIds)
        ->whereYear('dateDebut', $year)
        ->distinct()
        ->get();
}
    // Fonction pour récupérer les apprenants par projet (détaillé)
  
private function getLearnersByProjectDetails($projects, $year)
{
    $learnersByProject = [];
    
    foreach ($projects as $project) {
        // Utiliser l'idProjet pour récupérer les apprenants
        $projectLearners = $this->getLearnersByProjectId($project['id_projet'], $year);
        
        $learnersByProject[] = [
            'project_reference' => $project['project_reference'],
            'project_id' => $project['id_projet'],
            'module_name' => $project['module_name'],
            'learners_count' => count($projectLearners),
            'learners_list' => $projectLearners->take(5), // Premiers 5 apprenants
            'total_learners' => count($projectLearners),
            'date_debut' => $project['date_debut'],
            'date_fin' => $project['date_fin'],
            'revenue' => $project['total_ttc']
        ];
    }
    
    return $learnersByProject;
}

// Fonction pour les données de graphique d'évolution des apprenants
private function getLearnersEvolutionData($etpId, $year)
{
    $learners = DB::table('v_apprenant_union')
            ->select(DB::raw('MONTH(dateDebut) as month'), DB::raw('COUNT(DISTINCT idEmploye) as total_employe'))
            ->where('idCfp', $this->idCfp())
            ->where('idEtp', $etpId)
            ->whereNotNull('idProjet')
            ->whereYear('dateDebut', $year)
            ->whereIn('project_status', ['Terminé', 'Cloturé'])
            ->groupBy(DB::raw('MONTH(dateDebut)'))
            ->orderBy(DB::raw('MONTH(dateDebut)')) 
            ->get();
    return collect(range(1, 12))->map(function($m) use ($learners) {
        $found = $learners->firstWhere('month', $m);
        return [
            'month' => $m,
            'total_employe' => $found ? $found->total_employe : 0,
        ];
    });
}

private function getLearnersDistributionByProject($clientName, $currentYear)
{
    $projects = DB::table('v_apprenant_union')
        ->select('idProjet', 'module_name')
        ->where('idCfp', $this->idCfp())
        ->where('etp_name', $clientName)
        ->whereNotNull('idProjet')
        ->whereYear('dateDebut', $currentYear)
        ->distinct()
        ->get();
    
    $distribution = [];
    
    foreach ($projects as $project) {
        $learnersCount = DB::table('v_apprenant_union')
            ->where('idCfp', $this->idCfp())
            ->where('etp_name', $clientName)
            ->where('idProjet', $project->idProjet)
            ->whereYear('dateDebut', $currentYear)
            ->distinct()
            ->count('idEmploye');
        
        // Récupérer le project_reference depuis v_union_projets
        $projectReference = $this->getProjectReference($project->idProjet);
        
        $distribution[] = [
            'project_id' => $project->idProjet,
            'project_reference' => $projectReference,
            'module_name' => $project->module_name,
            'learners_count' => $learnersCount
        ];
    }
    
    return $distribution;
}

// Fonction pour l'évolution mensuelle du CA
private function getRevenueEvolutionData($etpId, $year)
{
    $ca = DB::table('v_union_projects')
            ->select(DB::raw('SUM(total_ttc) as totalCA'), DB::raw('MONTH(dateDebut) as month'))
            ->where('id_cfp', Customer::idCustomer())
            ->where('id_etp', $etpId)
            ->where('module_name', '<>', 'Default module')
            ->where('headYear', $year)
            ->whereIn('project_status', ['Terminé', 'Cloturé'])
            ->where('project_is_trashed', 0)
            ->groupBy(DB::raw('MONTH(dateDebut)'))
            ->get();

    return collect(range(1, 12))->map(function($m) use ($ca) {
        $found = $ca->firstWhere('month', $m);
        return [
            'month' => $m,
            'totalCA' => $found ? $found->totalCA : 0,
        ];
    });
}


//  Fonction pour la répartition du CA par projet
private function getRevenueDistributionByProject($clientName, $currentYear)
{
    $projects = DB::table('v_union_projets')
        ->select('idProjet', 'project_reference', 'module_name', 'total_ttc', 'dateDebut', 'dateFin')
        ->where(function ($query) {
            $query->where('idCfp_intra', Customer::idCustomer())
                ->orWhere('idCfp_inter', Customer::idCustomer());
        })
        ->where('project_reference', $clientName)
        ->whereNot('module_name', 'Default module')
        ->whereYear('dateDebut', $currentYear)
        ->where('project_is_trashed', 0)
        ->orderBy('total_ttc', 'desc')
        ->get();
    
    $distribution = [];
    $totalRevenue = 0;
    
    foreach ($projects as $project) {
        $distribution[] = [
            'project_id' => $project->idProjet,
            'project_reference' => $project->project_reference,
            'module_name' => $project->module_name,
            'revenue_amount' => (float) $project->total_ttc,
            'revenue_formatted' => $this->formatRevenue($project->total_ttc),
            'date_debut' => Carbon::parse($project->dateDebut)->format('j.m.y'),
            'date_fin' => Carbon::parse($project->dateFin)->format('j.m.y'),
            'percentage' => 0 // Sera calculé après
        ];
        $totalRevenue += (float) $project->total_ttc;
    }
    
    // Calculer les pourcentages
    foreach ($distribution as &$item) {
        $item['percentage'] = $totalRevenue > 0 ? number_format(($item['revenue_amount'] / $totalRevenue) * 100, 1) : 0;
    }
    
    return [
        'distribution' => $distribution,
        'total_revenue' => $totalRevenue,
        'total_revenue_formatted' => $this->formatRevenue($totalRevenue)
    ];
}

// Calcule le pourcentage du CA d'un client par rapport au CA total sur les 5 dernières années

public function getClientRevenuePercentage($clientId)
{
    $currentYear = date('Y');
    $years = range($currentYear - 4, $currentYear); // 5 dernières années
    
    $results = [];
    
    foreach ($years as $year) {
        // CA du client pour l'année
        $clientRevenue = DB::table('v_union_projects as vp')
            ->where('vp.id_etp', $clientId)
            ->where('vp.module_name', '<>', 'Default module')
            ->where('vp.headYear', $year)
            ->whereIn('vp.project_status', ['Terminé', 'Cloturé'])
            ->where('vp.project_is_trashed', 0)
            ->sum('vp.total_ttc');
        
        // CA total de tous les clients pour l'année
        $totalRevenue = DB::table('v_union_projects as vp')
            ->join('v_collaboration_cfp_etps as vc', 'vp.id_etp', '=', 'vc.idEtp')
            ->where('vc.idCfp', Customer::idCustomer())
            ->where('vp.module_name', '<>', 'Default module')
            ->where('vp.headYear', $year)
            ->whereIn('vp.project_status', ['Terminé', 'Cloturé'])
            ->where('vp.project_is_trashed', 0)
            ->sum('vp.total_ttc');
        
        $clientRevenue = (float) $clientRevenue;
        $totalRevenue = (float) $totalRevenue;
        
        // Calcul du pourcentage
        $percentage = 0;
        if ($totalRevenue > 0) {
            $percentage = round(($clientRevenue / $totalRevenue) * 100, 2);
        }
        
        $results[] = [
            'year' => $year,
            'client_revenue' => $clientRevenue,
            'total_revenue' => $totalRevenue,
            'percentage' => $percentage
        ];
    }
    
    return $results;
}

// Fonction pour les données de tendance du CA
private function getRevenueTrendData($clientName, $currentYear)
{
    $years = range($currentYear - 2, $currentYear); // 3 dernières années
    
    $yearlyData = [];
    $monthlyTrend = [];
    
    foreach ($years as $year) {
        // CA annuel
        $yearlyRevenue = DB::table('v_union_projets')
            ->where(function ($query) {
                $query->where('idCfp_intra', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer());
            })
            ->where('project_reference', $clientName)
            ->whereNot('module_name', 'Default module')
            ->whereYear('dateDebut', $year)
            ->where('project_is_trashed', 0)
            ->sum('total_ttc');
        
        $yearlyData[] = [
            'year' => $year,
            'revenue' => (float) $yearlyRevenue,
            'revenue_formatted' => $this->formatRevenue($yearlyRevenue)
        ];
        
        // Données mensuelles pour l'année en cours
        if ($year == $currentYear) {
            $months = range(1, 12);
            foreach ($months as $month) {
                $monthlyRevenue = DB::table('v_union_projets')
                    ->where(function ($query) {
                        $query->where('idCfp_intra', Customer::idCustomer())
                            ->orWhere('idCfp_inter', Customer::idCustomer());
                    })
                    ->where('project_reference', $clientName)
                    ->whereNot('module_name', 'Default module')
                    ->whereYear('dateDebut', $year)
                    ->whereMonth('dateDebut', $month)
                    ->where('project_is_trashed', 0)
                    ->sum('total_ttc');
                
                $monthlyTrend[] = [
                    'month' => $month,
                    'month_name' => Carbon::create()->month($month)->locale('fr')->monthName,
                    'revenue' => (float) $monthlyRevenue,
                    'revenue_formatted' => $this->formatRevenue($monthlyRevenue)
                ];
            }
        }
    }
    
    // Calculer l'évolution entre les années
    $evolution = [];
    for ($i = 1; $i < count($yearlyData); $i++) {
        $current = $yearlyData[$i]['revenue'];
        $previous = $yearlyData[$i - 1]['revenue'];
        $evolutionRate = $previous > 0 ? (($current - $previous) / $previous) * 100 : ($current > 0 ? 100 : 0);
        
        $evolution[] = [
            'from_year' => $yearlyData[$i - 1]['year'],
            'to_year' => $yearlyData[$i]['year'],
            'evolution_rate' => number_format($evolutionRate, 1),
            'trend' => $evolutionRate >= 0 ? 'up' : 'down'
        ];
    }
    
    return [
        'yearly_trend' => $yearlyData,
        'monthly_trend' => $monthlyTrend,
        'evolution' => $evolution,
        'current_year_growth' => $evolution[count($evolution) - 1]['evolution_rate'] ?? 0,
        'current_year_trend' => $evolution[count($evolution) - 1]['trend'] ?? 'stable'
    ];
}

// Fonction pour les indicateurs financiers avancés
private function getFinancialIndicators($clientName, $currentYear)
{
    $previousYear = $currentYear - 1;
    
    // CA des 2 dernières années
    $currentYearRevenue = DB::table('v_union_projets')
        ->where(function ($query) {
            $query->where('idCfp_intra', Customer::idCustomer())
                ->orWhere('idCfp_inter', Customer::idCustomer());
        })
        ->where('project_reference', $clientName)
        ->whereNot('module_name', 'Default module')
        ->whereYear('dateDebut', $currentYear)
        ->where('project_is_trashed', 0)
        ->sum('total_ttc');
    
    $previousYearRevenue = DB::table('v_union_projets')
        ->where(function ($query) {
            $query->where('idCfp_intra', Customer::idCustomer())
                ->orWhere('idCfp_inter', Customer::idCustomer());
        })
        ->where('project_reference', $clientName)
        ->whereNot('module_name', 'Default module')
        ->whereYear('dateDebut', $previousYear)
        ->where('project_is_trashed', 0)
        ->sum('total_ttc');
    
    // Nombre de projets
    $currentYearProjects = DB::table('v_union_projets')
        ->where(function ($query) {
            $query->where('idCfp_intra', Customer::idCustomer())
                ->orWhere('idCfp_inter', Customer::idCustomer());
        })
        ->where('project_reference', $clientName)
        ->whereNot('module_name', 'Default module')
       
        ->whereYear('dateDebut', $currentYear)
        ->where('project_is_trashed', 0)
        ->count();
    
    $previousYearProjects = DB::table('v_union_projets')
        ->where(function ($query) {
            $query->where('idCfp_intra', Customer::idCustomer())
                ->orWhere('idCfp_inter', Customer::idCustomer());
        })
        ->where('project_reference', $clientName)
        ->whereNot('module_name', 'Default module')
       
        ->whereYear('dateDebut', $previousYear)
        ->where('project_is_trashed', 0)
        ->count();
    
    // Calcul des indicateurs
    $revenueEvolution = $previousYearRevenue > 0 ? 
        (($currentYearRevenue - $previousYearRevenue) / $previousYearRevenue) * 100 : 
        ($currentYearRevenue > 0 ? 100 : 0);
    
    $projectEvolution = $previousYearProjects > 0 ? 
        (($currentYearProjects - $previousYearProjects) / $previousYearProjects) * 100 : 
        ($currentYearProjects > 0 ? 100 : 0);
    
    $averageRevenuePerProject = $currentYearProjects > 0 ? 
        $currentYearRevenue / $currentYearProjects : 0;
    
    return [
        'current_year_revenue' => (float) $currentYearRevenue,
        'previous_year_revenue' => (float) $previousYearRevenue,
        'revenue_evolution' => number_format($revenueEvolution, 1),
        'revenue_trend' => $revenueEvolution >= 0 ? 'up' : 'down',
        
        'current_year_projects' => $currentYearProjects,
        'previous_year_projects' => $previousYearProjects,
        'project_evolution' => number_format($projectEvolution, 1),
        'project_trend' => $projectEvolution >= 0 ? 'up' : 'down',
        
        'average_revenue_per_project' => (float) $averageRevenuePerProject,
        'average_revenue_per_project_formatted' => $this->formatRevenue($averageRevenuePerProject),
        
        'total_revenue_formatted' => $this->formatRevenue($currentYearRevenue),
        'previous_revenue_formatted' => $this->formatRevenue($previousYearRevenue)
    ];
}
}