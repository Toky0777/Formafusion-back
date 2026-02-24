<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AtteandanceController extends Controller
{
    protected $attendance;

    public function __construct(AttendanceService $Attendance)
    {
        $this->attendance = $Attendance;
    }

    public function index(string $status, Request $request)
    {
        $validStatuses = ['Cloturé', 'En cours', 'Terminé'];

        if (!in_array($status, $validStatuses)) {
            return response()->json([
                'status' => 200,
                'projets' => [],
                'filtre' => [
                    'type_projets' => [],
                    'lieux' => [],
                    'entreprises' => [],
                    'modules' => [],
                    'formateurs' => [],
                    'mois' => [],
                ],
            ]);
        }

        $filters = $request->all();
        $projets = $this->getStatus($status, $filters);

        return response()->json([
            'status' => 200,
            'projets' => $projets['projets'],
            'pagination' => $projets['pagination'],
        ]);
    }

    private function getStatus(string $status, array $filters = [])
    {
        $userId = Auth::id();
        $projects = $this->attendance->index(null, Customer::idCustomer(), $status, $filters);
        $projectIds = $projects->pluck('idProjet')->unique()->toArray();

        if (empty($projectIds)) {
            return [
                'projets' => [],
                'pagination' => method_exists($projects, 'links') ? $projects->toArray() : null,
            ];
        }

        $allApprs = $this->getAllApprsForProjects($projectIds);
        $allFormateurs = $this->getAllFormateursForProjects($projectIds);
        $allEtps = $this->getAllEtpsForProjects($projectIds);
        $allPourcentages = $this->getAllPourcentagesForProjects($projectIds);

        $projets = [];
        foreach ($projects as $project) {
            $idProjet = $project->idProjet;
            $idCfpInter = $project->idCfp_inter;

            $projets[] = [
                'formateurs' => $allFormateurs[$idProjet] ?? [],
                'idProjet' => $idProjet,
                'idCfp_inter' => $idCfpInter,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'etp_name' => $allEtps[$idProjet] ?? [],
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'modalite' => $project->modalite,
                'project_description' => $project->project_description,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'etp_initial_name' => $project->etp_initial_name,
                'idModule' => $project->idModule,
                'apprs' => $allApprs[$idProjet] ?? [],
                'li_name' => $project->li_name,
                'pourcentage' => $allPourcentages[$idProjet]['pourcentage'] ?? [],
                'nbApprenant' => $allPourcentages[$idProjet]['nbApprenant'] ?? [],

            ];
            DB::table('attendance_count')->updateOrInsert(
                ['idProjet' => $idProjet],
                [
                    'nb_present' => $allPourcentages[$idProjet]['nbApprenant']['nb_present'],
                    'nb_absent' => $allPourcentages[$idProjet]['nbApprenant']['nb_absent'],
                    'nb_total_inscrit' => $allPourcentages[$idProjet]['nbApprenant']['total_inscrits'], // Correction: nb_total au lieu de total_inscrits
                    'nb_a_saisir' => $allPourcentages[$idProjet]['nbApprenant']['nb_a_saisir']
                ]
            );
        }

        return [
            'projets' => $projets,
            'pagination' => method_exists($projects, 'links') ? $projects->toArray() : null,
        ];
    }

    /**
     * Récupère tous les apprenants pour une liste de projets
     */
    private function getAllApprsForProjects(array $projectIds): array
    {
        $apprIntras = DB::table('v_list_apprenants')
            ->select('idProjet', 'idEmploye', 'emp_name', 'emp_firstname', 'emp_photo', 'etp_name', 'emp_initial_name')
            ->whereIn('idProjet', $projectIds)
            ->orderBy('emp_name', 'asc')
            ->get()
            ->groupBy('idProjet')
            ->map(function ($group) {
                return $group->toArray();
            })
            ->toArray();

        $apprenantInters = DB::table('v_list_apprenant_inter_added')
            ->select('idProjet', 'idEmploye', 'emp_name', 'emp_firstname', 'emp_photo', 'etp_name')
            ->whereIn('idProjet', $projectIds)
            ->orderBy('emp_name', 'asc')
            ->get()
            ->groupBy('idProjet')
            ->map(function ($group) {
                return $group->toArray();
            })
            ->toArray();

        // Fusion des apprenants intra et inter par projet
        $result = [];
        foreach ($projectIds as $projectId) {
            $intra = $apprIntras[$projectId] ?? [];
            $inter = $apprenantInters[$projectId] ?? [];
            $result[$projectId] = array_merge($intra, $inter);
        }

        return $result;
    }

    /**
     * Récupère tous les formateurs pour une liste de projets
     */
    private function getAllFormateursForProjects(array $projectIds): array
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


    private function getAllEtpsForProjects(array $projectIds): array
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

    /**
     * Calcul pourcentage ny projet sy maka isany inscrits rehetra(abs,present,a saisir)
     */
    private function getAllPourcentagesForProjects(array $projectIds): array
    {
        // Early return if no project IDs
        if (empty($projectIds)) {
            return [];
        }

        $now = Carbon::now()->toDateString();

        // Get CFP ID safely
        $idCfp = DB::table('projets')
            ->whereIn('idProjet', $projectIds)
            ->value('idCustomer'); // Use value() instead of first() for single value

        // Handle customer ID safely

        $idCustomer = Customer::idCustomer();


        // If no customer ID found, return empty result
        if (!$idCustomer) {
            return [];
        }

        $result = [];

        try {
            // Récupération des statistiques d'émargement groupées par projet
            $statutsByProject = DB::table('emargements as e')
                ->select('e.idProjet', 'e.isPresent', DB::raw('COUNT(*) as count'))
                ->join('projets as p', 'e.idProjet', 'p.idProjet')
                ->whereIn('e.idProjet', $projectIds)
                ->where('p.idCustomer', $idCustomer)
                ->whereIn('e.isPresent', [0, 1, 2, 3])
                ->groupBy('e.idProjet', 'e.isPresent')
                ->get()
                ->groupBy('idProjet');

            // Récupération des séances groupées par projet
            $seancesByProject = DB::table('v_emargement_appr as ve')
                ->select('ve.idProjet', 've.idSeance')
                ->join('projets as p', 'p.idProjet', 've.idProjet')
                ->whereIn('ve.idProjet', $projectIds)
                ->where('p.idCustomer', $idCustomer)
                ->whereDate('ve.dateSeance', '<=', $now)
                ->groupBy('ve.idProjet', 've.idSeance')
                ->get()
                ->groupBy('idProjet');

            // Récupération des apprenants groupées par projet
            $apprsIntras = DB::table('v_list_apprenants as ve')
                ->select('ve.idProjet', 've.idEmploye')
                ->join('projets as p', 'p.idProjet', 've.idProjet')
                ->whereIn('ve.idProjet', $projectIds)
                ->groupBy('ve.idProjet', 've.idEmploye')
                ->get()
                ->groupBy('idProjet');
            $apprsInter = DB::table('v_list_apprenant_inter_added as ve')
                ->select('ve.idProjet', 've.idEmploye')
                ->join('projets as p', 'p.idProjet', 've.idProjet')
                ->whereIn('ve.idProjet', $projectIds)
                ->groupBy('ve.idProjet', 've.idEmploye')
                ->get()
                ->groupBy('idProjet');
            $apprsByProject = $apprsIntras->union($apprsInter);

            // Récupération des statuts par apprenant pour le calcul BE
            $apprStatuts = DB::table('emargements as e')
                ->select('e.idProjet', 'e.idEmploye', 'e.isPresent')
                ->join('projets as p', 'e.idProjet', 'p.idProjet')
                ->whereIn('e.idProjet', $projectIds)
                ->where('p.idCustomer', $idCustomer)
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
                ];
            }
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error in getAllPourcentagesForProjects: ' . $e->getMessage());
            // Return empty result or re-throw based on your needs
            return [];
        }

        return $result;
    }

    public static function getEtpProjectInter($idProjet, $idCfp_inter)
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

    public static function getEtpProjectInterByFormateur($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null || $idCfp_inter == 'null') {
            $etp = DB::table('v_projet_cfps')
                ->select('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
                ->where('idProjet', $idProjet)
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

    public static function getApprListProjet($idProjet)
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

    public function getFormProject($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
            ->groupBy('idFormateur', 'name', 'firstName', 'photoForm', 'initialNameForm')
            ->where('idProjet', $idProjet)->get();

        return $forms->toArray();
    }

    //SHOW ETP
    public function showEtpDrawer(Request $request)
    {
        $referents = DB::table('users')
            ->select('users.*', 'employes.idCustomer', 'customers.customerPhone')
            ->join('employes', 'users.id', '=', 'employes.idEmploye')
            ->join('role_users', 'users.id', '=', 'role_users.user_id')
            ->join('customers', 'customers.idCustomer', '=', 'users.id')
            ->where('employes.idCustomer', $request->idEtp)
            ->whereIn('role_users.role_id', [3, 6, 8, 9])
            ->get();

        $customer = DB::table('customers')
            ->select('customers.*')
            ->where('idCustomer', $request->idEtp)
            ->first();

        return response()->json([
            'customer' => $customer,
            'referents' => $referents,
        ]);
    }

    // //SHOWfORMATEUR
    // public function getMiniCV($idFormateur)
    // {
    //     try {
    //         if (!Auth::check()) {
    //             throw new Exception('User is not authenticated.');
    //         }

    //         // Vérifier que l'utilisateur a accès aux informations demandées
    //         $userId = Auth::user()->id;

    //         $form = DB::table('users')
    //             ->select('id', 'name', 'email', 'firstName', 'phone', 'photo')
    //             ->where('id', $idFormateur)
    //             ->first();

    //         // Expériences
    //         $exp = DB::table('experiences')
    //             ->select('id', 'idFormateur', 'Lieu_de_stage', 'Fonction', 'Date_debut', 'Date_fin', 'Lieu')
    //             ->where('idFormateur', $idFormateur)
    //             ->get();

    //         // Diplômes
    //         $dp = DB::table('diplomes')
    //             ->select('id', 'idFormateur', 'Ecole', 'Diplome', 'Domaine', 'Date_debut', 'Date_fin')
    //             ->where('idFormateur', $idFormateur)
    //             ->get();

    //         // Compétences
    //         $cpc = DB::table('competences')
    //             ->select('id', 'idFormateur', 'Competence', 'note')
    //             ->where('idFormateur', $idFormateur)
    //             ->get();

    //         // Langues
    //         $lg = DB::table('langues')
    //             ->select('id', 'idFormateur', 'Langue', 'note')
    //             ->where('idFormateur', $idFormateur)
    //             ->get();

    //         $speciality = DB::table('formateurs')->select('form_titre')->where('idFormateur', $idFormateur)->first();
    //     } catch (Exception $e) {
    //         Log::error($e->getMessage());
    //         return response()->json(['error' => ['message' => $e->getMessage()]], 500);
    //     }

    //     // Retourner les données au format JSON
    //     return response()->json([
    //         'form' => $form,
    //         'experiences' => $exp,
    //         'diplomes' => $dp,
    //         'competences' => $cpc,
    //         'langues' => $lg,
    //         'speciality' => $speciality
    //     ]);
    // }

    //getDATA presence
    public function getAttendanceByProject($idProjet)
    {
        $allPourcentages = $this->getAllPourcentagesForProjects([$idProjet]);

        $now = Carbon::now()->toDateString();

        // Récupération des séances et présences
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

        // Nombre total de dates de séance
        $countDate = DB::table('v_seances')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', $now)
            ->groupBy('dateSeance')
            ->select('idProjet', 'idSeance', DB::raw('COUNT(*) as count'), 'dateSeance')
            ->get();

        // Récupération des apprenants avec leurs évaluations
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

        // CORRECTION : Accès correct aux données de pourcentage
        $projectData = $allPourcentages[$idProjet] ?? [];
        $pourcentage = $projectData['pourcentage'] ?? [];

        $percentPresent = $pourcentage['present'] ?? "0";
        $percentPartiel = $pourcentage['partiel'] ?? "0";
        $percentAbsent = $pourcentage['absent'] ?? "0";

        // DEBUG: Vérifier la structure des données
        // \Log::info('Attendance data', [
        //     'project_id' => $idProjet,
        //     'allPourcentages' => $allPourcentages,
        //     'projectData' => $projectData,
        //     'pourcentage' => $pourcentage
        // ]);

        return response()->json([
            'apprs' => $apprs,
            'getSeance' => $getSeance,
            'getPresence' => $getPresence,
            'countDate' => $countDate,
            'percentPresent' => $percentPresent,
            'percentPartiel' => $percentPartiel,
            'percentAbsent' => $percentAbsent,
        ]);
    }

    public function getAttendanceByProjectInter($idProjet)
    {
        $allPourcentages = $this->getAllPourcentagesForProjects([$idProjet]);
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
        return response()->json([
            'apprs' => $apprs,
            'getSeance' => $getSeance,
            'getPresence' => $getPresence,
            'percentPresent' => $percentPresent,
            'percentPartiel' => $percentPartiel,
            'percentAbsent' => $percentAbsent,
        ]);
    }

    public function getCountProject()
    {
        $req = DB::table('role_users')
            ->select('role_id', 'user_id')
            ->where('user_id', Auth::user()->id)
            ->first();

        [$projetEnCours, $projetTermines, $projetClotures] = match ($req->role_id) {
            3 => [
                $this->attendance->countByStatus(Customer::idCustomer(), "En cours"),
                $this->attendance->countByStatus(Customer::idCustomer(), "Terminé"),
                $this->attendance->countByStatus(Customer::idCustomer(), "Cloturé"),
            ],
            5 => [
                $this->attendance->countByStatusByFormateur("En cours", Auth::user()->id),
                $this->attendance->countByStatusByFormateur("Terminé", Auth::user()->id),
                $this->attendance->countByStatusByFormateur("Cloturé", Auth::user()->id),
            ],
            default => throw new \Exception('Rôle non reconnu'),
        };

        return response()->json([
            'status' => 200,
            'projet_counts' => [
                'en_cours' => $projetEnCours,
                'termines' => $projetTermines,
                'clotures' => $projetClotures,
            ]
        ]);
    }

    public function getFiltre(string $status)
    {
        $validStatuses = ['Cloturé', 'En cours', 'Terminé'];

        if (!in_array($status, $validStatuses)) {
            // Retourne structure vide si status invalide
            return response()->json([
                'status' => 200,
                'filtre' => [
                    'type_projets' => [],
                    'lieux' => [],
                    'entreprises' => [],
                    'modules' => [],
                    'formateurs' => [],
                    'mois' => [],
                ],
            ]);
        }

        $projets = $this->getFilterByStatus($status);

        return response()->json([
            'status' => 200,
            'filtre' => [
                'type_projets' => $projets['type_projets'],
                'lieux' => $projets['lieux'],
                'entreprises' => $projets['entreprises'],
                'modules' => $projets['modules'],
                'formateurs' => $projets['formateurs'],
                'mois' => $projets['mois'],
            ],
        ]);
    }

    private function getFilterByStatus(string $status): array
    {
        $lieux = collect();
        $entreprises = collect();
        $modules = collect();
        $formateursUniques = collect();
        $mois = collect();

        $type_projets = DB::table('type_projets')->get();

        $roleId = DB::table('role_users')
            ->where('user_id', Auth::id())
            ->value('role_id');
        $projets = match ($roleId) {
            3 => $this->attendance->indexFilter(Customer::idCustomer(), $status),
            5 => $this->attendance->indexFilterByFormateur($status, Auth::id()),
            default => [],
        };

        // Récupération de tous les IDs de projets
        $projectIds = collect($projets)->pluck('idProjet')->unique()->toArray();

        if (!empty($projectIds)) {
            // Chargement eager des données
            $allEtps = $this->getAllEtpsForProjects($projectIds, $roleId);
            $allFormateurs = $this->getAllFormateursForProjects($projectIds);

            foreach ($projets as $pj) {
                $idProjet = $pj->idProjet;

                $lieux->push($pj->li_name);

                // Entreprises
                if (isset($allEtps[$idProjet])) {
                    foreach ($allEtps[$idProjet] as $etp) {
                        $entreprises->push((object)[
                            'idEtp' => $etp->idEtp,
                            'etp_name' => $etp->etp_name
                        ]);
                    }
                }

                // Modules uniques
                $modules->push([
                    'idModule' => $pj->idModule,
                    'module_name' => $pj->module_name,
                    'module_image' => $pj->module_image,
                ]);

                // Formateurs uniques
                if (isset($allFormateurs[$idProjet])) {
                    foreach ($allFormateurs[$idProjet] as $form) {
                        $formateursUniques->push((object)$form);
                    }
                }

                // Mois uniques basés sur dateDebut
                $date = Carbon::parse($pj->dateDebut);
                $mois->push([
                    'id' => $date->format('Y-m'),
                    'label' => $date->format('F Y')
                ]);
            }
        }

        return [
            'type_projets' => $type_projets,
            'lieux' => $lieux->unique()->values()->all(),
            'entreprises' => $entreprises->unique('idEtp')->values()->all(),
            'modules' => $modules->unique('idModule')->values()->all(),
            'formateurs' => $formateursUniques->unique('idFormateur')->values()->all(),
            'mois' => $mois->unique('id')->values()->all(),
        ];
    }


    public function getDataPresence($idProjet)
    {
        // ✅ Récupération des sessions et leur durée en une seule requête
        $seances = DB::table('v_seances')
            ->where('idProjet', $idProjet)
            ->orderBy('dateSeance', 'asc')
            ->get([
                'idSeance',
                'dateSeance',
                'heureDebut',
                'heureFin',
                'idProjet',
                'idModule',
                DB::raw("TIME_FORMAT(SEC_TO_TIME(TIME_TO_SEC(intervalle_raw)), '%H:%i') AS intervalle_raw")
            ]);
        return response()->json([

            'seances' => $seances,

        ]);
    }
}
