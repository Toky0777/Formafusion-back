<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\NiveauQcm;
use App\Models\Qcm;
use App\Models\QcmBareme;
use App\Models\QcmInvitCamp;
use App\Models\User;
use App\Services\Qcm\QcmNavigationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QcmBaremeController extends Controller
{
    # Services part added 18-02-2025
    private QcmNavigationService $navigationService;

    public function __construct(
        QcmNavigationService $navigationService
    ) {
        $this->navigationService = $navigationService;
    }
    # Services part added 18-02-2025

    /**
     * Fonction menant vers le formulaire pour insérer un barème pour un qcm (v2)
     *  
     * @param $id (id du qcm)
     */
    public function create_qcm_bareme($id)
    {
        $user = Auth::user();
        $id_auth_user = $user->id;
        $extends_containt = $this->navigationService->determineLayout();
        $qcm_id = $id;
        $niveaux = NiveauQcm::all();
        // Get the specific QCM
        $qcm = Qcm::findOrFail($id);

        // Check authorization
        if ($user->hasRole('Formateur') || $user->hasRole('Cfp') || $user->hasRole('EmployeCfp')) {
            if ($qcm->user_id !== $id_auth_user) {
                return response()->json([
                    'status' => 403,
                    'message' => 'hello !'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 403,
                'message' => 'Forbidden !'
            ], 403);
        }

        return response()->json([
            'user' => $user,
            'qcm' => $qcm,
            'niveaux' => $niveaux,
            'extends_containt' => $extends_containt,
            'qcm_id' => $qcm_id
        ]);
    }

    /**
     * Get all baremes for a specific QCM
     * 
     * @param $id (id du qcm)
     */
    public function getBaremes($id)
    {
        $QcmBareme = QcmBareme::with('niveau')
            ->where('idQCM', $id)
            ->orderBy('minPoints', 'asc')
            ->get();

        return response()->json([
            'QcmBareme' => $QcmBareme
        ]);
    }

    /**
     * Get a specific bareme
     * 
     * @param $id (id du barème)
     */
    public function getBareme($id)
    {
        $QcmBareme = QcmBareme::findOrFail($id);

        return response()->json([
            'QcmBareme' => $QcmBareme
        ]);
    }

    /**
     * Fonction pour sauvegarder les barèmes d'un qcm
     * 
     * @param $request, $id -> id du qcm
     */
    public function storeQcmBareme(Request $request, $id)
    {
        // Validate the request
        $validated = $request->validate([
            'minPoints' => 'required|numeric',
            'maxPoints' => 'required|numeric|gte:minPoints',
            'niveau' => 'required|string|max:255',
        ]);

        try {
            $bareme = new QcmBareme();
            $bareme->idQCM = $id;
            $bareme->minPoints = $request->minPoints;
            $bareme->maxPoints = $request->maxPoints;
            $bareme->id_niveau = $request->niveau;
            $bareme->save();

            return response()->json([
                'success' => true,
                'message' => 'Barème créé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du barème'
            ], 500);
        }
    }

    /**
     * Update an existing bareme
     * 
     * @param $id (id du barème)
     */
    public function updateQcmBareme(Request $request, $id)
    {
        // Validate the request
        $validated = $request->validate([
            'minPoints' => 'required|numeric',
            'maxPoints' => 'required|numeric|gte:minPoints',
            'niveau' => 'required|numeric|max:255',
        ]);

        try {
            $bareme = QcmBareme::findOrFail($id);
            $bareme->minPoints = $request->minPoints;
            $bareme->maxPoints = $request->maxPoints;
            $bareme->id_niveau = $request->niveau;
            $bareme->save();

            return response()->json([
                'success' => true,
                'message' => 'Barème mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du barème'
            ], 500);
        }
    }

    /**
     * Delete a bareme
     * 
     * @param $id (id du barème)
     */
    public function deleteQcmBareme($id)
    {
        try {
            $bareme = QcmBareme::findOrFail($id);
            $bareme->delete();

            return response()->json([
                'success' => true,
                'message' => 'Barème supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du barème'
            ], 500);
        }
    }

    /**
     * Fonction pour avoir la liste des apprenants lors d'un test avec leur résultats (v5) avec filtre
     * avec la fonction "getAllResultsApprPostTestWithAlreadyApprCtf"
     * 
     * @param $request, $id (id du qcm)
     */
    public function list_apprenants_test($id, Request $request)
    {
        $user = Auth::user();
        $id_auth_user = $user->id;
        $qcmId = $id;

        // Qcm details to display on top
        $detailsQcm = Qcm::where('idQCM', $id)->first();

        // Initialision de QcmBareme
        $QcmResults = new QcmBareme();
        $results = $QcmResults->getAllResultsApprPostTestWithAlreadyApprCtf($id, $id_auth_user); # $id_auth_user -> idCtf
        // Avoir les détails du centre de formation
        $user = User::findOrFail($id_auth_user);
        if ($results['status'] === 'error') {
            return response()->json([
                'status' => 500,
                'message' => $results['message'],
                'apprenants' => [],
                'user' => $user,
                'detailsQcm' => $detailsQcm
            ]);
        }

        $apprenants = $results['data'];
        $message = '';
        // Si il n'y a aucun apprenant(s) ayant fait le test
        if (count($apprenants) === 0) {
            $message = 'Aucun apprenant n\'a encore passé ce test.';
        }

        // Formattage des données (optionnel)
        if ($user->hasRole('Particulier'))
        {
            $formattedData = $apprenants->map(function ($apprenant) {
            return [
                'id' => $apprenant->idUtilisateur,
                'name' => $apprenant->name ?? $apprenant->emp_name,
                'firstname' => $apprenant->firstName ?? $apprenant->emp_firstname,
                'session' => $apprenant->idSession,
                'idqcm' => $apprenant->idQCM,
                'date' => $apprenant->date ?? $apprenant->created_at,
                'points' => $apprenant->totalPoints,
                'niveau' => $apprenant->niveau,
                'rang' => $apprenant->rang
            ];
        });
        } else {
            $formattedData = $apprenants->map(function ($apprenant) {
                return [
                    'id' => $apprenant->idUtilisateur,
                    'name' => $apprenant->name ?? $apprenant->emp_name,
                    'firstname' => $apprenant->firstName ?? $apprenant->emp_firstname,
                    'session' => $apprenant->idSession,
                    'id_etp' => $apprenant->idEtp,
                    'etp' => $apprenant->etp_name ,
                    'idqcm' => $apprenant->idQCM,
                    'date' => $apprenant->date ?? $apprenant->created_at,
                    'points' => $apprenant->totalPoints,
                    'niveau' => $apprenant->niveau,
                    'invitationId' => $apprenant->invitationId,
                    'campId' => $apprenant->campId,
                    'rang' => $apprenant->rang
                ];
            });
        }
        $level = NiveauQcm::all();
        
        return response()->json([
            'status' => 200,
            'apprenants' => $formattedData,
            'message' => $message,
            'user' => $user,
            'uniqueLevels' => $level,
            'cfpId' => $id_auth_user,
            'qcmId' => $qcmId,
            'detailsQcm' => $detailsQcm
        ]);
    }

    /**
     * Fonction pour le résultat d'un apprenant lors d'un test (v2)
     * 
     * @param $id (id du qcm), $idAppr (id de l'apprenant)
     */
    public function results_one_appr_test($id, $idAppr)
    {
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté

        // Initialision de QcmBareme
        $QcmResults = new QcmBareme();
        $results = $QcmResults->getAllResultsOneApprenantPostTest($id, $idAppr);

        // Avoir les détails de l'apprenant
        $user = User::findOrFail($idAppr);

        if ($results['status'] === 'error') {
            return response()->json([
                'status' => 500,
                'message' => $results['message'],
                'apprenants' => [],
                'extends_containt' => $extends_containt,
                'user' => $user,
            ]);
        }
        $apprenants = $results['data'];
        $message = '';

        // Si il n'y a aucun apprenant(s) ayant fait le test
        if (count($apprenants) === 0) {
            $message = 'Vous n\'avez pas encore passé ce test.';
        }

        // Formattage des données (optionnel)
        $formattedData = $apprenants->map(function ($apprenant) {
            return [
                'id' => $apprenant->idUtilisateur,
                'name' => $apprenant->name,
                'firstname' => $apprenant->firstName,
                'session' => $apprenant->idSession,
                'idqcm' => $apprenant->idQCM,
                'qcm' => $apprenant->intituleQCM,
                'date' => $apprenant->date_session,
                'points' => $apprenant->total_points,
                'niveau' => $apprenant->niveau
            ];
        });

        $niveaux = NiveauQcm::all();

        return response()->json([
            'status' => 200,
            'apprenants' => $formattedData,
            'message' => $message,
            'extends_containt' => $extends_containt,
            'niveaux' => $niveaux,
            'user' => $user,
        ]);
    }

    /**
     * Fonction pour avoir le résultat d'un apprenant lors d'un test spécifique (une session spécifique) (v4)
     * 
     * @param $id -> id du qcm, $idAppr -> id de l'apprenant, $idSession
     */
    public function result_appr_qcm_one_session($id, $idAppr, $idSession)
    {
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté

        $user_auth = Auth::user();

        // Initialision de QcmBareme
        $QcmResult = new QcmBareme();
        $detailsResultsPerSection = $QcmResult->getApprDetailsResults($idAppr, $id, $idSession); # Avoir les résultats par catégorie (total points par catégorie)
        $resultAppr = $QcmResult->getResultApprenantPostTest($idAppr, $id, $idSession); # Avoir son résultat après le test (niveau total de points)

        // Get user details
        $userAppr = User::findOrFail($idAppr);

        // Group chosen answers by category
        $groupedResponses = $detailsResultsPerSection->groupBy('nomCategorie'); # (v2)

        return response()->json([
            'stataus' => 200,
            'extends_containt' => $extends_containt,
            'userAppr' => $userAppr,
            'result' => $resultAppr,
            'groupedResponses' => $groupedResponses,
            'user_auth' => $user_auth,
        ]);
    }

    /**
     * Fonction pour avoir les détails du résultat d'un apprenant dans une section précise (v2)
     * 
     * @param $id -> id du qcm, $idAppr -> id de l'apprenant, $idSection, $idSession
     */
    public function result_appr_sectiondetails_one_session($id, $idAppr, $idSection, $idSession)
    {
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté

        // Initialision de QcmBareme
        $QcmResult = new QcmBareme();
        $detailsApprSection = $QcmResult->getDetailsSectionResultForAppr($idAppr, $id, $idSection, $idSession);

        $sectionName = DB::table('categories_reponses')
            ->select('nomCategorie')
            ->where('idCategorie', $idSection)
            ->first(); // Nom d'une section

        $section = $sectionName ? $sectionName->nomCategorie : 'Unknown Section';

        // Group by questions
        $groupedDetailsInSection = $detailsApprSection->groupBy('enonce_question');

        return response()->json([
            'extends_containt' => $extends_containt,
            'sectionName' => $section,
            'groupedDetailsInSection' => $groupedDetailsInSection,
        ]);
    }

    /**
     * Fonction pour le diagramme en araigné d'un utilisateur après avoir fait un test de qcm
     * pour la vue (v2)
     * 
     * @param $id (id de l'utilisateur), $idQCM, $idSession
     */
    public function showSpiderChart($id, $idQCM, $idSession)
    {
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté

        $qcmBareme = new QcmBareme();
        $chartData = $qcmBareme->getSpiderChartData($id, $idQCM, $idSession);

        return response()->json([
            'chartData' => $chartData,
            'idUtilisateur' => $id,
            'idQCM' => $idQCM,
            'idSession' => $idSession,
            'extends_containt' => $extends_containt,
        ]);
    }

    /**
     * Fonction pour le diagramme en araigné d'un utilisateur après avoir fait un test de qcm
     * pour le modal (v2)
     * 
     * @param $id (id de l'utilisateur), $idQCM, $idSession
     */
    public function getSpiderChartData($id, $idQCM, $idSession)
    {
        $qcmBareme = new QcmBareme();
        $chartData = $qcmBareme->getSpiderChartData($id, $idQCM, $idSession);
        $pointsMaxQcm = $qcmBareme->getMaxPointsInQcm($idQCM);

        // Créez un tableau associatif pour les données à retourner
        $responseData = [
            'chartData' => $chartData,
            'pointsMaxQcm' => $pointsMaxQcm,
        ];

        return response()->json($responseData);
    }

    /**
     * Fonction pour le diagramme en araigné de tous les apprenants et les utilisateurs ayant participés au qcm
     * pour le modal (v2)
     * 
     * @param $id (id du qcm), $idCtf
     */
    public function getGlobalSpiderChartData($id, $idCtf)
    {
        $qcmBareme = new QcmBareme();
        $chartData = $qcmBareme->getAllApprSpiderChartData($id, $idCtf);
        $pointsMaxQcm = $qcmBareme->getMaxPointsInQcm($id);

        // Créez un tableau associatif pour les données à retourner
        $responseData = [
            'chartData' => $chartData,
            'pointsMaxQcm' => $pointsMaxQcm,
        ];

        // return $responseData; # Pour le test sur laravel tinker
        return response()->json($responseData);
    }

    /**
     * Fonction pour avoir les résultats des employés d'une entreprise pour un qcm (liste) (v2)
     * 
     * @param $id (id du qcm), $idEtp
     */
    public function resultsOfEtpEmp($id, $idEtp)
    {
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté

        $etp_id = $idEtp; // Id de l'entreprise
        $id_qcm = $id; // Id du qcm

        // Initialision de QcmBareme
        $QcmResults = new QcmBareme();
        $results = $QcmResults->getAllResultEmpOfEtp($id, $idEtp);

        // Avoir les détails de l'entreprise
        $user = User::findOrFail($idEtp); # Entreprise in users table
        $etpDetails = Customer::findOrFail($idEtp); # Entreprise in customers table

        if ($results['status'] === 'error') {
            return response()->json([
                'message' => $results['message'],
                'apprenants' => [],
                'extends_containt' => $extends_containt,
                'user' => $user,
                'etpDetails' => $etpDetails,
            ]);
        }

        $apprenants = $results['data'];
        $message = '';

        // Si il n'y a aucun apprenant(s) ayant fait le test
        if (count($apprenants) === 0) {
            $message = 'Aucun employé n\'a encore fait ce test';
        }

        // Formattage des données (optionnel)
        $formattedData = $apprenants->map(function ($apprenant) {
            return [
                'id' => $apprenant->idUtilisateur,
                'name' => $apprenant->name,
                'firstname' => $apprenant->firstName,
                'session' => $apprenant->idSession,
                'idqcm' => $apprenant->idQCM,
                'date' => $apprenant->date_session,
                'points' => $apprenant->total_points,
                'niveau' => $apprenant->niveau
            ];
        });

        return response()->json([
            'status' => 200,
            'apprenants' => $formattedData,
            'message' => $message,
            'extends_containt' => $extends_containt,
            'user' => $user,
            'etp_id' => $etp_id,
            'id_qcm' => $id_qcm,
            'etpDetails' => $etpDetails,
        ]);
    }

    /**
     * Fonction pour le diagramme en araigné des tous les employés d'une entreprise ayant participés à un qcm pour le modal (v2)
     * 
     * @param $id (id du qcm), $idEtp
     */
    public function getGlobalSpiderChartDataForEmpOfEtp($id, $idEtp)
    {
        $qcmBareme = new QcmBareme();
        $chartData = $qcmBareme->getAllApprSpiderChartDataEmpOfEtp($id, $idEtp);
        $pointsMaxQcm = $qcmBareme->getMaxPointsInQcm($id);

        // Créez un tableau associatif pour les données à retourner
        $responseData = [
            'chartData' => $chartData,
            'pointsMaxQcm' => $pointsMaxQcm,
        ];

        return response()->json($responseData);
    }

    /**
     * Method for the global results of all qcm created by an user (v2)
     * 
     * @param $request
     */
    public function index_global_results_allQcmOfUser(Request $request)
    {
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté

        $user = Auth::user();
        $id_auth_user = $user->id;
        // Initialision de QcmBareme
        $QcmResults = new QcmBareme();
        $results = $QcmResults->fetchGlobalResultsQcmCtf($id_auth_user); # $id_auth_user -> idCtf
        // Avoir les détails du centre de formation
        $user = User::findOrFail($id_auth_user);
        
        if ($results['status'] === 'error') {
            return response()->json([
                'status' => 500,
                'message' => $results['message'],
                'apprenants' => [],
                'extends_containt' => $extends_containt,
                'user' => $user,
                'id_auth_user' => $id_auth_user,
            ]);
        }

        $apprenants = $results['data'];
        $message = '';

        // Si il n'y a aucun apprenant(s) ayant fait le test
        if (count($apprenants) === 0) {
            $message = 'Aucun apprenant n\'a encore passé ce test.';
        }

        // Formattage des données (optionnel)
        $formattedData = $apprenants->map(function ($apprenant) {
            return [
                'id' => $apprenant->idUtilisateur,
                'name' => $apprenant->name,
                'firstname' => $apprenant->firstName,
                'session' => $apprenant->idSession,
                'id_etp' => $apprenant->idEtp,
                'etp' => $apprenant->etp_name,
                'idqcm' => $apprenant->idQCM,
                'intituleqcm' => $apprenant->intituleQCM,
                'date' => $apprenant->date_session,
                'points' => $apprenant->total_points,
                'niveau' => $apprenant->niveau,
                'invitationId' => $apprenant->invitationId,
                'campId' => $apprenant->campId,
                'rang' => $apprenant->rang
            ];
        });
        // Récupération des filtres depuis la requête
        $nameFilter = $request->get('name_filter', '');
        $etpFilter = $request->get('etp_filter', '');
        $intituleQcm = $request->get('qcm_filter', '');
        $dateFilter = $request->get('date_filter', '');
        $pointsMin = $request->get('points_min', '');
        $pointsMax = $request->get('points_max', '');
        $levelFilter = $request->get('level_filter', '');
        $campaignFilter = $request->get('campaign_filter', '');
        $campagnes = QcmInvitCamp::with('invitations')->where('created_by', $user->id)->get();
        // Application des filtres
        if ($formattedData->isNotEmpty()) {
            $filteredData = $formattedData->filter(function ($apprenant) use ($nameFilter, $etpFilter, $intituleQcm, $dateFilter, $pointsMin, $pointsMax, $levelFilter, $campaignFilter) {
                $nameMatch = empty($nameFilter) || str_contains(
                    strtolower($apprenant['name'] . ' ' . $apprenant['firstname']),
                    strtolower($nameFilter)
                );

                $etpMatch = empty($etpFilter) || str_contains(strtolower($apprenant['etp']), strtolower($etpFilter));

                $qcmMatch = empty($intituleQcm) || str_contains(strtolower($apprenant['intituleqcm']), strtolower($intituleQcm));

                $dateMatch = empty($dateFilter) || str_contains($apprenant['date'], $dateFilter);

                $pointsMatch = (empty($pointsMin) || $apprenant['points'] >= $pointsMin) &&
                    (empty($pointsMax) || $apprenant['points'] <= $pointsMax);

                $levelMatch = empty($levelFilter) || $apprenant['niveau'] === $levelFilter;
                
                $campaignMatch = empty($campaignFilter) || $apprenant['campId'] == $campaignFilter;

                return $nameMatch && $etpMatch && $qcmMatch && $dateMatch && $pointsMatch && $levelMatch && $campaignMatch;
            });

            // Récupération des niveaux uniques pour le filtre
            $uniqueLevels = $formattedData->pluck('niveau')->unique();
            
        } else {
            $filteredData = collect([]);
            $uniqueLevels = collect([]);
        }

        return response()->json([
            'status' => 200,
            'apprenants' => $filteredData,
            'message' => $message,
            'extends_containt' => $extends_containt,
            'user' => $user,
            'filters' => [
                'name' => $nameFilter,
                'date' => $dateFilter,
                'points_min' => $pointsMin,
                'points_max' => $pointsMax,
                'level' => $levelFilter,
            ],
            'uniqueLevels' => $uniqueLevels,
            'campagnes'=> $campagnes,
            'cfpId' => $id_auth_user,
            'id_auth_user' => $id_auth_user,
        ]);
    }

    /**
     * Method for the global chart of all Qcm of the CTF
     * 
     * @param $id (id of CTF)
     */
    public function getGlobalChartAllQcmOfCfp($id)
    {
        try {
            $qcmBareme = new QcmBareme();
            $chartData = $qcmBareme->getAllApprBarChartAllQcmOfCtf($id);

            // Récupérer les points maximums pour tous les QCM du centre
            $maxPoints = DB::table('v_pts_max_qcm')
                ->whereIn('idQCM', array_unique(array_column($chartData['datasets'], 'qcmId')))
                ->select('idQCM', 'points_maximum')
                ->get();

            return response()->json([
                'chartData' => $chartData,
                'pointsMaxQcm' => $maxPoints
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des données : ' . $e->getMessage()
            ], 500);
        }
    }
}
