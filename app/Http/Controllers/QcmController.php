<?php

namespace App\Http\Controllers;

use App\Models\CategoriesReponses;
use App\Models\CreditsWallet;
use App\Models\DomainesFormation;
use App\Models\NiveauQcm;
use App\Models\Qcm;
use App\Models\QcmBareme;
use App\Models\QcmImages;
use App\Models\QcmInvitation;
use App\Models\QcmQuestions;
use App\Models\QcmReponses;
use App\Models\ReponsesQcmUsers;
use App\Models\SessionsQcm;
use App\Models\TypeQcmQuestion;
use App\Models\User;
use App\Services\Qcm\QcmCreditService;
use App\Services\Qcm\QcmNavigationService;
use App\Services\Qcm\QcmSessionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class QcmController extends Controller
{
    # Services part added 18-02-2025
    private QcmSessionService $sessionService;
    private QcmCreditService $creditService;
    private QcmNavigationService $navigationService;

    public function __construct(
        QcmSessionService $sessionService,
        QcmCreditService $creditService,
        QcmNavigationService $navigationService
    ) {
        $this->sessionService = $sessionService;
        $this->creditService = $creditService;
        $this->navigationService = $navigationService;
    }
    # Services part added 18-02-2025

    /**
     * Fonction menant à l'index de l'utilisateur connecté (v5)
     * 
     * @param $request
     */
    public function index_qcm(Request $request)
    {
        $all_qcm = null; // Initialize the variable to hold the QCMs
        $user = null;    // Initialize the user as null for non-authenticated users
        $roleNames = collect(); // Empty collection for roles if no user is authenticated
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté

        // Récupère l'id de l'entreprise de l'employé si c'est un employé
        $idEtpOfEmp = new CreditsWallet();
        $idEtp = $idEtpOfEmp->getIdEtpByidEmp(Auth::user()->id);
        $walletAuthUser = $idEtpOfEmp->user_credit_walletBasedOnRole(Auth::user()); # Pour voir le portefeuille de l'utilisateur connecté

        // Récupérer le domaine sélectionné depuis la requête
        $selectedDomaine = $request->input('domaine');

        // Récupérer tous les domaines pour le filtre
        $domaines = DomainesFormation::orderBy('nomDomaine')->get();

        // Check if the user is authenticated
        if (Auth::check()) {
            // User is authenticated, retrieve the authenticated user's data
            $user = Auth::user();
            $id_auth_user = $user->id;
            $roleNames = $user->roles->pluck('roleName');

            // Initialiser la requête de base
            $query = Qcm::query();

            // Appliquer le filtre par domaine si sélectionné
            if ($selectedDomaine) {
                $query->whereHas('domaineFormation', function ($q) use ($selectedDomaine) {
                    $q->where('idDomaine', $selectedDomaine);
                });
            }

            // Filtrer selon le rôle
            if ($user->hasRole('Formateur') || $user->hasRole('Cfp') || $user->hasRole('EmployeCfp')) {
                $query->where('user_id', $id_auth_user);
            }else if ($user->hasRole('EmployeEtp') || $user->hasRole('Employe') || $user->hasRole('Referent')) {
                $idCfp = DB::table('cfp_etps')->where('idEtp', $user->idEtp())->pluck('idCfp');
                $idFormateur = DB::table('cfp_formateurs')
                    ->where(function ($subQuery) use ($idCfp) {
                        foreach ($idCfp as $id) {
                            $subQuery->orWhere('idCfp', $id);
                        }
                    })->pluck('idFormateur');
                $query->where(function ($subQuery) use ($idCfp, $idFormateur) {
                    foreach ($idCfp as $id) {
                        $subQuery->orWhere('user_id', $id);
                    }
                    foreach ($idFormateur as $id) {
                        $subQuery->orWhere('user_id', $id);
                    }
                })->where('statut', 1); 
            } else {
                $query->where('statut', 1);
            }

            // Exécuter la requête
            $all_qcm = $query->get();
        } else {
            // Si non authentifié, montrer tous les QCMs actifs
            $query = Qcm::query();
            if ($selectedDomaine) {
                $query->whereHas('domaineFormation', function ($q) use ($selectedDomaine) {
                    $q->where('idDomaine', $selectedDomaine);
                });
            }
            $all_qcm = $query->where('statut', 1)->get();
        }

        return response()->json([
            'all_qcm' => $all_qcm,
            'user' => $user,
            'roleNames' => $roleNames,
            'extends_containt' => $extends_containt,
            'id_auth_user' => $id_auth_user,
            'idEtp' => $idEtp,
            'walletAuthUser' => $walletAuthUser,
            'domaines' => $domaines,
            'selectedDomaine' => $selectedDomaine
        ]);
    }

    /**
     * Fonction pour le toggle button
     * 
     * @param $request, $id
     */
    public function updateStatus(Request $request, $id)
    {
        $qcm = Qcm::find($id);
        if (!$qcm) {
            return response()->json(['success' => false, 'message' => 'QCM introuvable.']);
        }

        $qcm->statut = $request->statut;
        $qcm->save();

        return response()->json(['success' => true, 'message' => 'Statut mis à jour avec succès.']);
    }

    /**
     * Fonction menant à l'index de l'utilisateur non connecté (v1)
     * 
     * @param $request
     */
    public function index_qcm_not_auth(Request $request)
    {
        // Récupérer le domaine sélectionné depuis la requête
        $selectedDomaine = $request->input('domaine');

        // Récupérer tous les domaines pour le filtre
        $domaines = DomainesFormation::orderBy('nomDomaine')->get();

        // Récupérer les QCMs selon le filtre
        if ($selectedDomaine) {
            $all_qcm = Qcm::getQcmsByDomaine($selectedDomaine);
        } else {
            $all_qcm = Qcm::getAllPublicQcms();
        }

        return response()->json([
            'all_qcm' => $all_qcm,
            'domaines' => $domaines,
            'selectedDomaine' => $selectedDomaine
        ]);
    }

    /**
     * Vue du formulaire de création de Qcm (v2)
     */
    public function create_qcm()
    {
        $typeQcms = TypeQcmQuestion::all();
        $all_domaines = DomainesFormation::all();
        $all_responses_cat = CategoriesReponses::all(); # toutes les catégories de réponses / sections
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté
        $user = Auth::user();

        return response()->json([
            'all_domaines' => $all_domaines,
            'all_responses_cat' => $all_responses_cat,
            'extends_containt' => $extends_containt,
            'user' => $user,
            'typeQcms' => $typeQcms
        ]);
    }

    /**
     * Stocker le Qcm créer (v4)
     * With image upload on each question
     * 
     * @param $request
     * 
     * @return redirect
     */
    public function storeQcm(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'intituleQCM' => 'required|string|max:255',
            'descriptionQCM' => 'required|string',
            'duree' => 'required|integer',
            'prixUnitaire' => 'required|numeric',
            'idDomaine' => 'required|exists:domaine_formations,idDomaine',
            'sections' => 'required|array',
            'sections.*.categorie_id' => 'required|exists:categories_reponses,idCategorie',
            'sections.*.questions' => 'required|array',
            'sections.*.questions.*.texteQuestion' => 'required|string',
            'sections.*.questions.*.image' => 'nullable|image|max:5120',
            'sections.*.questions.*.removeImage' => 'nullable|string',
            'sections.*.questions.*.reponses' => 'required|array|min:1',
            'sections.*.questions.*.idTypeQcmQuestion' => 'required|numeric',
            'sections.*.questions.*.reponses.*.texteReponse' => 'required|string',
            'sections.*.questions.*.reponses.*.explicationReponse' => 'nullable|string',
            'sections.*.questions.*.reponses.*.points' => 'required|integer',
            'sections.*.questions.*.reponses.*.categorie_id' => 'required|exists:categories_reponses,idCategorie',
        ], [
            'required' => 'Le champ :attribute est requis.',
            'exists' => 'Le champ :attribute n\'existe pas dans la base de données.',
            'image' => 'Le fichier doit être une image.',
            'max' => 'La taille de l\'image ne doit pas dépasser 5 Mo.',
        ]);

        // Use a transaction to ensure all operations succeed or none
        DB::transaction(function () use ($validatedData, $request) {
            $qcm = Qcm::create([
                'intituleQCM' => $validatedData['intituleQCM'],
                'descriptionQCM' => $validatedData['descriptionQCM'],
                'duree' => $validatedData['duree'] * 60, # Convertir les minutes en secondes
                'prixUnitaire' => $validatedData['prixUnitaire'],
                'idDomaine' => $validatedData['idDomaine'],
                'user_id' => Auth::id(),
            ]);

            // Loop through the sections and create questions with responses
            foreach ($validatedData['sections'] as $sectionIndex => $sectionData) {
                foreach ($sectionData['questions'] as $questionIndex => $questionData) {
                    $question = QcmQuestions::create([
                        'idQCM' => $qcm->idQCM,
                        'idTypeQcmQuestion' => intval($questionData['idTypeQcmQuestion']),
                        'texteQuestion' => $questionData['texteQuestion'],
                    ]);

                    // Gestion de l'image - MODIFICATION ICI
                    // Utiliser la structure correcte générée par la fonction récursive
                    $imageFieldName = "sections[{$sectionIndex}][questions][{$questionIndex}][image]";
                    $removeImageFieldName = "sections[{$sectionIndex}][questions][{$questionIndex}][removeImage]";
                    
                    // Vérifier si removeImage est défini
                    $removeImage = $request->has($removeImageFieldName) && $request->input($removeImageFieldName) == "1";

                    // Traitement de l'image
                    if ($request->hasFile($imageFieldName) && !$removeImage) {
                        $file = $request->file($imageFieldName);
                        $originalName = $file->getClientOriginalName();
                        $filename = pathinfo($originalName, PATHINFO_FILENAME);

                        // Initialiser le gestionnaire d'images et le driver
                        $driver = new Driver();
                        $manager = new ImageManager($driver);

                        // Lire l'image et convertir en WebP avec qualité 25
                        $image = $manager->read($file)->toWebp(25);

                        // Créer un fichier temporaire pour stocker l'image WebP
                        $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.webp';
                        $image->save($tempPath);

                        // Définir le nom du fichier final avec extension WebP
                        $webpFilename = $filename . '.webp';

                        // Stocker le fichier WebP converti sur DigitalOcean
                        $path = Storage::disk('do')->putFileAs(
                            'questionImg/' . $question->idQuestion,
                            new File($tempPath),
                            $webpFilename
                        );

                        // Obtenir l'URL de l'image
                        $url = Storage::disk('do')->url($path);

                        // Supprimer le fichier temporaire
                        @unlink($tempPath);

                        // Création de l'image dans la base de données
                        $qcmImage = QcmImages::create([
                            'idTypeImage' => 1,
                            'url' => $url,
                            'nomImage' => $webpFilename,
                            'path' => $path,
                        ]);

                        // Mise à jour de la question avec l'ID de l'image
                        $question->update(['idImageQ' => $qcmImage->idImageQ]);
                    }

                    // Préparer les réponses pour insertion batch
                    $reponses = [];
                    foreach ($questionData['reponses'] as $reponseData) {
                        $reponses[] = [
                            'idQuestion' => $question->idQuestion,
                            'texteReponse' => $reponseData['texteReponse'],
                            'explicationReponse' => isset($reponseData['explicationReponse']) ? $reponseData['explicationReponse'] : null,
                            'points' => $reponseData['points'],
                            'categorie_id' => $reponseData['categorie_id'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    // Batch insert responses for the current question
                    QcmReponses::insert($reponses);
                }
            }
        });

        return response()->json([
            'status' => 200,
            'message' => 'QCM créé avec succès.'
        ]);
    }

    /**
     * Fonction menant aux détails du qcm en utilisant l'id du qcm concerné (v2)
     * 
     * @param $id
     */
    public function show_qcm_details($id)
    {
        $one_qcm = Qcm::with('domaineFormation')->find($id);

        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté

        return response()->json([
            'one_qcm' => $one_qcm,
            'extends_containt' => $extends_containt
        ]);
    }

    public function start_test(Request $request)
    {
        // Validate the request data
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'qcm_id' => 'required|exists:qcm,idQCM',
            'duration' => 'required|integer|min:1', // Durée en secondes
        ]);
        // Stocker le propos des timer du test (début, durée, etc.)
        DB::table('testing_timer')->insert([
            'user_id' => $request->input('user_id'),
            'qcm_id' => $request->input('qcm_id'),
            'start_time' => now()->toDateTimeString(), 
            'duration' => $request->input('duration'), // Durée en secondes
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function get_start_test($id)
    {
        $timer = DB::table('testing_timer')
            ->where('user_id', Auth::id())
            ->where('qcm_id', $id)
            ->where('start_time', '<=', now())
            ->latest('id')->first();
        if (!$timer) {
            return response()->json(['error' => 'Timer not found'], 404);
        }

        return response()->json($timer);
    }

    public function finished_test(Request $req)
    {
        // Logic to handle the end of the test
        $timer = DB::table('testing_timer')
            ->where('user_id', Auth::id())
            ->where('start_time', '<=', now())
            ->latest('id')->first();
        if ($timer) {
            DB::table('testing_timer')
                ->where('id', $timer->id)
                ->update(['finished_test' => $req->input('finished_test')]);
        } 
    }

    public function get_finished_test($id)
    {
        $timer = DB::table('testing_timer')
            ->where('user_id', Auth::id())
            ->where('qcm_id', $id)
            ->where('start_time', '<=', now())
            ->latest('id')->first();
        if (!$timer) {
            return response()->json(['error' => 'Timer not found'], 404);
        }

        return response()->json($timer);
    }

    /**
     * Fonction pour afficher le formulaire pour répondre aux questions avec l'index de la question
     * pour les afffichées un par un (v9)
     * $id -> id du qcm
     * avec débit de crédit, pour pouvoir faire le test qcm
     * avec partie changer en services
     * get the image related to each question
     * 
     * @param $id, $questionIndex = 0, $invitationId = null
     * @return qcm 
     */
    public function show_qcm_to_respond($id, $questionIndex = 0, $invitationId = null)
    {
        try {
            # Determine the layout based on the user's role
            $extends_containt = $this->navigationService->determineLayout();

            // Check invitation validity if invitationId is provided
            if ($invitationId !== null) {
                $invitation = QcmInvitation::findOrFail($invitationId);
                $campagne = $invitation->campaigns;
                if (isset($campagne[0])) {
                    $campId = $campagne[0]->idInvitCamp;
                }
                $validationResult = $invitation->validateInvitation($id, Auth::user()->id);
                # Return the redirect if the validation is not valid
                if (!$validationResult['valid']) {
                    return $validationResult['redirect'];
                }
            }

            // Get QCM with questions, responses, images, and type
            $qcm = Qcm::with([
                'questions_qcm.reponses_questions',
                'questions_qcm.image', // Eager load the image relation
                'questions_qcm.type_qcm', // Add type relation for questions
            ])->findOrFail($id);

            // Get enterprise ID for employee
            $idEtpOfEmp = new CreditsWallet();
            $idEtp = $idEtpOfEmp->getIdEtpByidEmp(Auth::user()->id);

            // Handle credits for employees and individuals
            if (Auth::user()->hasRole('Employe') || Auth::user()->hasRole('EmployeEtp') || Auth::user()->hasRole('Particulier')) {
                $creditResult = $this->creditService->validateAndProcessCredits(
                    Auth::id(),
                    $idEtp,
                    $qcm->prixUnitaire
                );

                if (!$creditResult['success']) {
                    return redirect()->back()->with('error', $creditResult['message']);
                }
            } else {
                // For other roles, set a default credit result
                $creditResult = [
                    'success' => true,
                    'hasEnoughCredits' => true,
                    'wallet' => null
                ];
            }
            
            // Initialize timer if not exists
            if (!session()->has('qcm_timer')) {
                $timer = $this->sessionService->initializeTimer($qcm->duree);
            }

            // Check if time has expired
            if ($this->sessionService->validateTimer()) {
                session(['time_expired' => true]);
                return redirect()->route('qcm.review', ['id' => $id]);
            }

            // Initialize progress if not exists
            if (!session()->has('qcm_progress')) {
                $progress = $this->sessionService->initializeProgress();
            }

            // Get current progress from session
            $progress = $this->sessionService->getCurrentProgress();

            // Validate and adjust question index
            $questions = $qcm->questions_qcm;
            $totalQuestions = count($questions);
            if ($totalQuestions === 0) {
                return redirect()->back()->with('error', "Ce QCM ne contient aucune question.");
            }
            
            // Ensure questionIndex is within valid range
            $questionIndex = max(0, min($questionIndex, $totalQuestions - 1));
            
            // Get current question
            $question = $questions[$questionIndex];
            // Check if the current question has an image
            $hasImage = false;
            $imagePath = null;

            if ($question->image) {
                $hasImage = true;
                $imagePath = $question->image->url; // Get the image path from the related image model
            }

            // Get question type information
            $questionType = $question->type_question ?? null;

            // Update progress with current index only (preserve existing responses)
            $progress['current_index'] = $questionIndex;
            session(['qcm_progress' => $progress]);
            
            if (session('invitationId') === null && $invitationId !== null) {
                session()->put('invitationId', $invitationId);
                if (isset($campId)) {
                    session()->put('campId', $campId);
                }
            }
            
            // Calculate remaining time
            $timer = session('qcm_timer');
            $endTime = Carbon::parse($timer['end_time']);
            $timeLeft = $endTime->diffInSeconds(now());

            // Get user wallet if applicable
            $hasEnoughCredits = $creditResult['success'] ?? false;
            $userWallet = $creditResult['wallet'] ?? null;

            // Get QCM type information
            $qcmType = $qcm->type_qcm ?? null;

            return response()-> json([
                'qcm' => $qcm,
                'question' => $question,
                'questionIndex'=> $questionIndex,
                'progress' => $progress,
                'extends_containt' => $extends_containt,
                'timeLeft' => $timeLeft,
                'hasEnoughCredits' => $hasEnoughCredits,
                'userWallet' => $userWallet,
                'invitationId' => $invitationId,
                'totalQuestions' => $totalQuestions,
                'hasImage' => $hasImage,      // Add this to indicate if the question has an image
                'imagePath' => $imagePath,     // Add the image path if available
                'questionType' => $questionType,  // Add question type information
                'qcmType' => $qcmType
            ]);
        } catch (\Exception $e) {
            Log::error('Error in show_qcm_to_respond: ' . $e->getMessage());
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Save each choosen responses that the user choose in session (v5)
     * $id -> id du qcm
     * avec partie changer en services
     * 
     * @param $request, $id
     */
    public function save_qcm_response(Request $request, $id)
    {
        # Check if time has expired
        if ($this->sessionService->validateTimer()) {
            session(['time_expired' => true]);
            return $request->ajax()
                ? response()->json(['redirect' => route('qcm.review', ['id' => $id])])
                : redirect()->route('qcm.review', ['id' => $id]);
        }
        # Validate response
        $request->validate([
            'idQuestion' => 'required|exists:qcm_questions,idQuestion',
            'idReponse' => 'nullable|exists:qcm_reponses,idReponse',
            'reponse' => 'nullable|string|min:1',
            'invitationId' => 'nullable|exists:qcm_invitations,idInvitation'
        ]);
        // Get the QCM and current progress
        $qcm = Qcm::with('questions_qcm')->findOrFail($id);
        $progress = $this->sessionService->getCurrentProgress();
        //si la question est un qcm alors idReponse = reponse
        if ($request->idReponse != null) {
            $reponse = $request->idReponse;
        }
        //Sinon, on cherche l'id de la reponse tapez
        else {
            $searchReponse = QcmReponses::where('texteReponse', $request->reponse)->where('idQuestion', $request->idQuestion)->get();
            $reponse = $searchReponse[0]->idReponse ?? null;
            $reponse_courte = $request->reponse;
        }

        // Save the response first
        $this->sessionService->saveResponse($request->idQuestion, $reponse);

        # Handle AJAX requests
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        // Handle navigation
        if ($request->has('finish')) {
            return response()->json([
                'status' => 200,
                'id' => $id
            ]);
        }

        # Handle next and previous navigation
        $newIndex = $progress['current_index'];
        if ($request->has('next')) {
            $newIndex = min($progress['current_index'] + 1, $qcm->questions_qcm->count() - 1);
        } elseif ($request->has('previous')) {
            $newIndex = max($progress['current_index'] - 1, 0);
        }
        // Update the progress with new index
        $this->sessionService->updateProgress(
        $request->idQuestion,
        $reponse,
        isset($reponse_courte) ? $reponse_courte : null,
        $newIndex,
            $request->invitationId // Add this parameter
        );

        $invitationId = session()->get('invitationId');
        # Redirect to the new question
        /* if ($route) {
            return redirect()->route('qcm.show_respond_with_invitation', [
                'id' => $id,
                'questionIndex' => $newIndex,
                'invitationId' => $invitationId,
            ]);
        } */
        // else {
            return response()->json([
                'id' => $id,
                'questionIndex' => $newIndex
            ]);
        // }
    }

    /**
     * Fonction pour faire une revue des réponses choisies par l'utilisateur  (v3)
     * $id -> id du qcm
     * 
     * @param $id
     */
    public function review_qcm($id)
    {
        // Charger le QCM avec ses questions et réponses
        $qcm = Qcm::with(['questions_qcm.reponses_questions'])->findOrFail($id);
        
        // Récupérer la progression et l'état du timer
        $progress = session('qcm_progress');
        $timer = session('qcm_timer');
        $timeExpired = session('time_expired', false);
        // Calculer le temps restant si le temps n'est pas expiré
        $timeLeft = null;
        if (!$timeExpired && $timer) {
            $now = now();
            $endTime = Carbon::parse($timer['end_time']);
            // Si le temps n'est pas écoulé, calculer le temps restant
            $timeLeft = $now->gt($endTime) ? 0 : $endTime->diffInSeconds($now);
        }
        $invitationId = session()->get('invitationId');
        // Définir le layout selon le rôle de l'utilisateur
        $extends_containt = $this->navigationService->determineLayout();
        // Renvoyer la vue avec toutes les données nécessaires
        return response()->json([
            'qcm' => $qcm,
            'progress' => $progress,
            'extends_containt' => $extends_containt,
            'invitationId' => $invitationId,
            'timeExpired' => $timeExpired,
            'timeLeft' => $timeLeft
        ]);
    }

    /**
     * Fonction pour soumettre (ou valider) les réponses choisies par l'utilisateur (v2)
     * $id -> id du qcm
     * 
     * @param $id 
     */
    public function submit_qcm(Request $request, $id)
    {
        // Vérifier l'état du timer
        $timer = $request->input('qcm_timer');
        $now = now();
        /* $endTime = Carbon::parse($timer['end_time']);
        // Marquer si le temps est expiré
        if ($now->gt($endTime)) {
            session(['time_expired' => true]);
        } */
        // Récupérer la progression
        $progress = $request->input('progress');
        if (empty($progress)) {
            return response()->json([
                'status' => 500,
                'id' => $id,
                'message' => 'No responses found in session.'
            ]);
        }
        // Créer une nouvelle session de QCM
        $qcm = Qcm::with('questions_qcm')->findOrFail($id);
        try {
            $session = new SessionsQcm();
            $session->idUtilisateur = Auth::id();
            $session->idQCM = $qcm->idQCM;
            $session->invitationId = $request->input('invitationId');
            $session->campId = $request->input('campId');
            $session->dateDebut = Carbon::createFromTimestamp($request->input('dateDebut'));
            $session->save();
            // Calculer les points totaux
            $totalPoints = 0;
            
            // Sauvegarder les réponses et calculer les points
            foreach ($qcm->questions_qcm as $question) {
                // Récupérer la réponse de l'utilisateur
                $responseId = $progress['responses'][$question->idQuestion] ?? null;
                if ($responseId) {
                    $reponse = QcmReponses::where('idReponse', $responseId)->where('idQuestion', $question->idQuestion)->first(); // check si la réponse est correct(existe dans la base)
                    if ($reponse) {
                        $totalPoints += intval($reponse->points);
                        // debug
                        /* echo "idQuestion : ".$question->idQuestion. ' - Points obtenus : '.intval($reponse->points);
                        echo "<br>"; */
                    } else {
                        $reponse = QcmReponses::where('texteReponse', '=', $responseId)
                            ->where('idQuestion', $question->idQuestion)
                            ->first(); // check si la réponse est correct(existe dans la base)
                        $totalPoints = $reponse ? $totalPoints + intval($reponse->points) : $totalPoints;
                        // debug
                        /* echo "idQuestion : ".$question->idQuestion. ' - Points obtenus : '.($reponse ? intval($reponse->points) : 0);
                        echo "<br>"; */
                    }
                }
                $userResponse = new ReponsesQcmUsers();
                $userResponse->idSession = $session->idSession;
                $userResponse->idQuestion = $question->idQuestion;
                $userResponse->save();
            }
            /* dd('ok'); */
            // Finaliser la session
            $session->dateFin = now();
            $session->totalPoints = $totalPoints;
            $session->save();
            return response()->json([
                'status' => 200,
                'id' => $id,
                'progress' => $progress
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 400,
                'error' => $th
            ]);
        } 

        // Rediriger vers les résultats
        
    }

    /**
     * Fonction pour afficher les points final de l'utilisateur après avoir
     * soumis ses réponses après avoir résolut le qcm pour les apprenants
     * $id -> id du qcm (v2)
     * 
     * @param $id
     */
    public function show_qcm_results_after_test($id)
    {
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté

        // Retrieve the session or result data
        $session = SessionsQcm::with('rel_qcm_session')
            ->where('idQCM', $id)
            ->where('idUtilisateur', Auth::id())
            ->latest('dateFin')
            ->firstOrFail();
        // Retrieve the user's responses and the total points scored
        $responses = ReponsesQcmUsers::with('questionOfResponse', 'userChoosenReponse')
            ->where('idSession', $session->idSession)
            ->get();

        $niveauAppr = 'Niveau inconnu';
        // Déterminer le niveau de l'apprenant après le QCM
        // Vérifier si un barème existe pour ce QCM
        $baremeExists = QcmBareme::where('idQCM', $id)->exists();

        if ($baremeExists) {
            // Trouver le barème correspondant aux points obtenus
            $bareme = QcmBareme::where('idQCM', $id)
                ->where('minPoints', '<=', $session->totalPoints)
                ->where('maxPoints', '>=', $session->totalPoints)
                ->first();
            
            // Vérifier si un barème a été trouvé pour ce niveau de points
            if ($bareme) {
                $niveauAppr = $bareme->niveau->niveau;
            } else {
                $niveauAppr = 'Niveau inconnu';
            }
        } else {
            // Aucun barème défini pour ce QCM
            $niveauAppr = 'Barème non défini';
        }
        $niveaux = NiveauQcm::all();

        // Pass the session, responses, total points, and niveau to the view
        return response()->json([
            'session' => $session,
            'responses' => $responses,
            'niveaux' => $niveaux,
            'niveauAppr' => $niveauAppr,
            'totalPoints' => $session->totalPoints,
            'extends_containt' => $extends_containt
        ]);
    }

    /**
     * Fonction menant au formulaire pour mettre à jour un qcm (v2)
     * $id -> id du qcm
     * 
     * @param $id
     */
    public function edit_qcm($id)
    {
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté

        // Retrieve the QCM along with its questions and responses
        $qcm = Qcm::with(['questions_qcm.reponses_questions'])->findOrFail($id);
        $typeQcms = TypeQcmQuestion::all();
        // Fetch all available domaines and Response's categories
        $all_domaines = DomainesFormation::all();
        $all_categories_reponses = CategoriesReponses::all();

        // Return the view with the QCM data and domaines
        return response()->json([
            'qcm' => $qcm,
            'all_domaines' => $all_domaines,
            'typeQcms' => $typeQcms,
            'all_categories_reponses' => $all_categories_reponses,
            'extends_containt' => $extends_containt
        ]);
    }

    /**
     * Fonction pour mettre à jour un qcm suivit de ses questions et réponses (v4)
     * $id -> id du qcm
     * with update images
     * 
     * @param $request, $id
     */
    public function update_qcm(Request $request, $id)
    {
        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the QCM
            $qcm = Qcm::findOrFail(intval($id));
            $qcm->update($request->only(['intituleQCM', 'descriptionQCM', 'idDomaine', 'prixUnitaire', 'duree']));

            // Get all current question IDs for this QCM
            $currentQuestionIds = $qcm->questions_qcm->pluck('idQuestion')->toArray();

            // Process questions
            foreach ($request->input('questions', []) as $questionData) {
                if (strpos($questionData['idQuestion'], 'new_') === 0) {
                    // This is a new question
                    $question = new QcmQuestions([
                        'texteQuestion' => $questionData['texteQuestion'],
                        'idTypeQcmQuestion' => $questionData['idTypeQcmQuestion'],
                        'idQCM' => $id
                    ]);
                    $question->save();
                } else {
                    // This is an existing question
                    $question = QcmQuestions::findOrFail($questionData['idQuestion']);
                    $question->update([
                        'texteQuestion' => $questionData['texteQuestion'],
                        'idTypeQcmQuestion' => $questionData['idTypeQcmQuestion']
                    ]);

                    // Remove this ID from the current questions array
                    $currentQuestionIds = array_diff($currentQuestionIds, [$questionData['idQuestion']]);
                }
                // Process responses for this question
                $currentResponseIds = $question->reponses_questions->pluck('idReponse')->toArray();
                foreach ($questionData['reponses_questions'] ?? [] as $responseData) {
                    // Handle the case where no category is selected
                    $categorie_id = $responseData['categorie_id'] ?: null;

                    if (strpos($responseData['idReponse'], 'new_') === 0) {
                        $response = new QcmReponses([
                            'texteReponse' => $responseData['texteReponse'],
                            'points' => $responseData['points'],
                            'categorie_id' => $categorie_id,
                            'explicationReponse' => $responseData['explicationReponse'] ?? null,
                            'idQuestion' => $question->idQuestion
                        ]);
                        $response->save();
                    } else {
                        // This is an existing response
                        $response = QcmReponses::findOrFail($responseData['idReponse']);
                        $response->update([
                            'texteReponse' => $responseData['texteReponse'],
                            'points' => $responseData['points'],
                            'categorie_id' => $categorie_id,
                            'explicationReponse' => $responseData['explicationReponse'] ?? null
                        ]);

                        // Remove this ID from the current responses array
                        $currentResponseIds = array_diff($currentResponseIds, [$responseData['idReponse']]);
                    }
                }

                // Delete responses that were removed
                QcmReponses::destroy($currentResponseIds);
            }

            // Delete questions that were removed along with their responses
            foreach ($currentQuestionIds as $questionIdToDelete) {
                $questionToDelete = QcmQuestions::findOrFail($questionIdToDelete);

                // v2
                // Si la question avait une image, supprimer l'image en ligne en utilisant la fonction existante
                if ($questionToDelete->idImageQ) {
                    $imageId = $questionToDelete->idImageQ;

                    // Utiliser la fonction existante pour supprimer l'image
                    $imageModel = new QcmImages();
                    $imageModel->deleteQuestionPhoto($imageId);

                    // Remarque: La fonction deleteQuestionPhoto met déjà à jour la relation dans la base de données,
                    // donc pas besoin de faire questionToDelete->idImageQ = null et save() ici
                }
                // v2

                // Delete all responses associated with this question
                $questionToDelete->reponses_questions()->delete();
                // Now delete the question itself
                $questionToDelete->delete();
            }

            // Commit the transaction
            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'QCM updated successfully!'
            ]);
            } catch (\Exception $e) {
            // Something went wrong, rollback the transaction
            DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => 'Error update: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Fonction pour supprimer un qcm avec ses questions et réponses en utilisant l'id
     * du qcm (v1)
     * @param $id
     */
    // public function destroy_qcm($id)
    // {
    //     try {
    //         Qcm::deleteQcmWithRelated($id);
    //         // return response()->json(['message' => 'QCM avec ses questions et réponses supprimé'], 200);
    //         return redirect()->route('index.qcm')->with('success', 'QCM avec ses questions et réponses supprimé');
    //     } catch (\Exception $e) {
    //         // return response()->json(['message' => 'Erreur lors de la suppression du QCM: ' . $e->getMessage()], 500);
    //         return redirect()->route('index.qcm')->with('error', 'Erreur lors de la suppression du QCM');
    //     }
    // }

    /**
     * Fonction pour supprimer un qcm avec ses questions et réponses en utilisant l'id
     * du qcm (v2)
     * 
     * with deleting all photos related to it
     * @param $id
     */
    public function destroy_qcm($id)
    {
        try {
           // $qcmImagesToDelete = new QcmImages();
            // $qcmImagesToDelete->deleteAllQcmPhotos($id); // Delete all photos related to this QCM
            Qcm::deleteQcmWithRelated($id);

            return response()->json([
                'status' => 200,
                'message' => 'QCM avec ses questions et réponses supprimé'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la suppression du QCM'
            ], 500);
        }
    }

    /**
     * Method for listing all the Cfp for their qcm's results (superadmin side) (v3)
     * 
     * @param $request 
     */
    public function indexCfpListForQCM(Request $request)
    {
        $extends_containt = $this->navigationService->determineLayout();

        // Application des filtres
        $filterName = $request->get('filterName');

        $CfpQcm = new Qcm();
        $query = DB::table('v_cfp_all')
            ->select('idCfp', 'customerName', 'description', 'customerPhone', 'customerEmail', 'customer_addr_lot');

        if ($filterName) {
            $query->where('customerName', 'LIKE', '%' . $filterName . '%');
        }

        $CfpList = $query->paginate(10); // Show 10 items per page

        return response()->json([
            'extends_containt' => $extends_containt,
            'CfpList' => $CfpList
        ]);
    }

    /**
     * Method for showing all Cfp created by the Cfp (superadmin side) (v2)
     * 
     * @param $id (id Cfp), $request
     */
    public function showCfpQcm(Request $request, $id)
    {
        $extends_containt = $this->navigationService->determineLayout();

        // Fetch Qcm's creator datas
        $qcm = new Qcm();
        $customerDatas = $qcm->fetchQcmCreatorDatas($id);

        // Récupération des filtres
        $filterQcmName = $request->get('filterQcmName');
        $filterDomain = $request->get('filterDomain');

        // Récupération des domaines
        $domains = DomainesFormation::select('idDomaine', 'nomDomaine')->get();

        // Récupération des QCM avec filtres
        $query = DB::table('qcm')
            ->join('users', 'qcm.user_id', '=', 'users.id')
            ->join('domaine_formations', 'qcm.idDomaine', '=', 'domaine_formations.idDomaine')
            ->select(
                'qcm.idQCM',
                'qcm.user_id',
                'qcm.intituleQCM',
                'qcm.descriptionQCM',
                'qcm.idDomaine',
                'qcm.prixUnitaire',
                'users.name as creatorName',
                'users.email as creatorEmail',
                'users.phone as creatorPhone',
                'domaine_formations.nomDomaine'
            )
            ->where('qcm.user_id', '=', $id);

        if ($filterQcmName) {
            $query->where('qcm.intituleQCM', 'LIKE', '%' . $filterQcmName . '%');
        }

        if ($filterDomain) {
            $query->where('qcm.idDomaine', '=', $filterDomain);
        }

        $qcmList = $query->get();

        return response()->json([
            'extends_containt' => $extends_containt,
            'qcmList' => $qcmList,
            'domains' => $domains,
            'id' => $id,
            'customerDatas' => $customerDatas
        ]);
    }

    /**
     * Method for getting the results of a qcm (superadmin side) (v2)
     * 
     * @param $id (id qcm), Request $request
     */
    public function showQcmResults($id, Request $request)
    {
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour définir la mise en page selon l'utilisateur connecté

        // Récupérer l'utilisateur connecté
        $user = Auth::user();
        $id_auth_user = $user->id;
        $qcm_id = $id;

        // Récupérer les informations du QCM pour obtenir l'ID du créateur
        $qcm = Qcm::findOrFail($id); // Assurez-vous que le modèle Qcm existe
        $creatorId = $qcm->user_id; // Remplacez `creator_id` par le champ réel indiquant le créateur

        // Récupérer les résultats du QCM
        $QcmResults = new QcmBareme();
        $results = $QcmResults->getAllResultsApprPostTestWithAlreadyApprCtf($id, $creatorId);

        if ($results['status'] === 'error') {
            return response()->json([
                'status' => 400,
                'message' => $results['message'],
                'apprenants' => [],
                'extends_containt' => $extends_containt,
                'filters' => [],
                'qcmId' => $id,
            ]);
        }

        // Formater les données des apprenants
        $apprenants = collect($results['data'])->map(function ($apprenant) {
            return [
                'id' => $apprenant->idUtilisateur,
                'name' => $apprenant->name,
                'firstname' => $apprenant->firstName,
                'session' => $apprenant->idSession,
                'etp' => $apprenant->etp_name,
                'date' => $apprenant->date_session,
                'points' => $apprenant->total_points,
                'niveau' => $apprenant->niveau,
                'invitationId' => $apprenant->invitationId,
                'campId' => $apprenant->campId,
                'rang' => $apprenant->rang
            ];
        });

        // Appliquer les filtres
        $filters = $request->only(['name', 'etp', 'date', 'points_min', 'points_max', 'level', 'camp']);
        $filteredData = $apprenants->filter(function ($apprenant) use ($filters) {
            return (empty($filters['name']) || stripos($apprenant['name'], $filters['name']) !== false) &&
                (empty($filters['etp']) || stripos($apprenant['etp'], $filters['etp']) !== false) &&
                (empty($filters['date']) || stripos($apprenant['date'], $filters['date']) !== false) &&
                (empty($filters['points_min']) || $apprenant['points'] >= $filters['points_min']) &&
                (empty($filters['points_max']) || $apprenant['points'] <= $filters['points_max']) &&
                (empty($filters['level']) || $apprenant['niveau'] == $filters['level']) &&
                (empty($filters['campId']) || $apprenant['campId'] == $filters['campId']);
        });

        return response()->json([
            'status' => 200,
            'apprenants' => $filteredData,
            'filters' => $filters,
            'extends_containt' => $extends_containt,
            'qcmId' => $id,
        ]);
    }

    /**
     * Method for the global results of all qcm created by an user, superadmin part (v2)
     * 
     * @param $id (id ctf), $request
     */
    public function index_global_results_allQcmOfUserSA($id, Request $request)
    {
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté

        $user = Auth::user();
        $id_auth_user = $user->id;

        // Initialision de QcmBareme
        $QcmResults = new QcmBareme();
        $results = $QcmResults->fetchGlobalResultsQcmCtf($id); # $id_auth_user -> idCtf
        // Avoir les détails du centre de formation
        $user = User::findOrFail($id_auth_user);

        if ($results['status'] === 'error') {
            return response()->json([
                'status' => 400,
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

        // Application des filtres
        if ($formattedData->isNotEmpty()) {
            $filteredData = $formattedData->filter(function ($apprenant) use ($nameFilter, $etpFilter, $intituleQcm, $dateFilter, $pointsMin, $pointsMax, $levelFilter) {
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

                return $nameMatch && $etpMatch && $qcmMatch && $dateMatch && $pointsMatch && $levelMatch;
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
                'level' => $levelFilter
            ],
            'uniqueLevels' => $uniqueLevels,
            'cfpId' => $id_auth_user,
            'id_auth_user' => $id_auth_user,
        ]);
    }

    /**
     * Method for displaying the dashboard of results of a qcm (v2)
     * 
     * @param $id (id qcm)
     */
    public function dashboardQcmResults($id)
    {
        $extends_containt = $this->navigationService->determineLayout();

        // Récupérer les données du dashboard
        $qcm = new Qcm();
        if (Auth::user()->hasRole('Referent')) {
            $dashboardData = $qcm->fetchQcmDashboardDatasForEtp($id, Auth::user()->id);
        } else {
            $dashboardData = $qcm->fetchQcmDashboardDatas($id);
        }

        $niveaux = NiveauQcm::all(); 

        // Récupérer les informations du QCM
        $qcmInfo = Qcm::find($id);

        // Préparer les données pour le graphique de participation
        if (isset($dashboardData['status']) && $dashboardData['status'] === 'error') {
            return response()->json([
                'status' => 500,
                'message' => $dashboardData['message'] ?? 'Une erreur est survenue lors de la récupération des données.',
                'extends_containt' => $extends_containt
            ]); 
        } elseif (isset($dashboardData['data']['participation_by_month'])) {
            $participationData = [
                'labels' => array_keys($dashboardData['data']['participation_by_month']),
                'data' => array_values($dashboardData['data']['participation_by_month']),
            ];
        }

        return response()->json([
            'extends_containt' => $extends_containt,
            'dashboardData' => $dashboardData,
            'qcmInfo' => $qcmInfo,
            'participationData' => $participationData,
            'niveaux' => $niveaux
        ]);
    }

    /**
     * Method for displaying abilities's report of a student after a qcm (v3)
     * 
     * @param int $id QCM ID
     * @param int $idApprenant Student ID
     * @param int $idSession Session ID
     * @return \Illuminate\View\View
     */
    public function getAbilitiesReport($id, $idApprenant, $idSession)
    {
        $extends_containt = $this->navigationService->determineLayout();

        // Récupérer le QCM
        $qcm = Qcm::findOrFail($id);

        // Récupérer l'utilisateur (apprenant)
        $apprenant = User::findOrFail($idApprenant);

        // Récupérer les résultats détaillés
        $results = $qcm->fetchUserPointsInCategories($id, $idApprenant, $idSession);

        // Calculer la moyenne des pourcentages
        $averagePercentage = 0;
        $categoryCount = count($results['categories']);

        if ($categoryCount > 0) {
            $totalPercentage = 0;
            foreach ($results['categories'] as $cat) {
                $totalPercentage += $cat['pourcentage'];
            }
            $averagePercentage = round($totalPercentage / $categoryCount, 2);
        }

        // Récupérer la session
        $session = SessionsQcm::findOrFail($idSession);

        return response()->json([
            'extends_containt' => $extends_containt,
            'qcm' => $qcm,
            'apprenant' => $apprenant,
            'results' => $results,
            'session' => $session,
            'averagePercentage' => $averagePercentage
        ]);
    }

    /**
     * Export abilities report as PDF (v2)
     * 
     * @param $id, $idApprenant, $idSession
     */
    public function exportAbilitiesReportPDF($id, $idApprenant, $idSession)
    {
        $qcm = Qcm::findOrFail($id);
        $apprenant = User::findOrFail($idApprenant);
        $results = $qcm->fetchUserPointsInCategories($id, $idApprenant, $idSession);
        $session = SessionsQcm::findOrFail($idSession);

        // Calculer la moyenne des pourcentages
        $averagePercentage = 0;
        $categoryCount = count($results['categories']);

        if ($categoryCount > 0) {
            $totalPercentage = 0;
            foreach ($results['categories'] as $cat) {
                $totalPercentage += $cat['pourcentage'];
            }
            $averagePercentage = round($totalPercentage / $categoryCount, 2);
        }

        $pdf = Pdf::loadView('TestingCenter.abilitiesReportPDF', compact(
            'qcm',
            'apprenant',
            'results',
            'session',
            'averagePercentage'
        ));

        return $pdf->download('rapport-competences-' . $apprenant->name . '_' . $apprenant->firstName . '.pdf');
    }

    /**
     * Handle the QCM invitation start, checking authentication
     * 
     * @param int $qcmId
     * @param int $invitationId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleInvitationStart($qcmId, $invitationId)
    {
        if (!Auth::check()) {
            // Store the intended URL in the session
            session(['url.intended' => url("/qcm/solve/{$qcmId}/respond/0/invitation/{$invitationId}")]);

            // Redirect to login
            return response()->json([
                'status' => 400,
                'message' => 'Please log in to access your QCM invitation.'
            ]);
        }

        // If authenticated, redirect to the QCM
        return redirect("/qcm/solve/{$qcmId}/respond/0/invitation/{$invitationId}");
    }
}
