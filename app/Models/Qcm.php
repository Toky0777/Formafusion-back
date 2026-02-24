<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Qcm extends Model
{
    use HasFactory;

    protected $table = "qcm";
    protected $primaryKey = "idQCM";

    protected $fillable = [
        'user_id',
        'intituleQCM',
        'descriptionQCM',
        'idDomaine',
        'prixUnitaire',
        'statut',
        'duree',
    ];

    // Relation avec le modèle DomainesFormation
    public function domaineFormation()
    {
        return $this->belongsTo(DomainesFormation::class, 'idDomaine', 'idDomaine');
    }

    // Relation avec le modèle QcmQuestions
    public function questions_qcm()
    {
        return $this->hasMany(QcmQuestions::class, 'idQCM');
    }

    // Relation avec le modèle QcmBareme (One to One)
    public function bareme()
    {
        return $this->hasOne(QcmBareme::class, 'idQCM');
    }

    // Add this to your existing Qcm model
    public function categoryEvaluations()
    {
        return $this->hasMany(QcmCategoryEvaluation::class, 'idQCM', 'idQCM');
    }

    /**
     * Fonction pour supprimer un QCM entrainant la suppression de ses questions avec les réponses
     * 
     * @param $qcmId
     */
    public static function deleteQcmWithRelated($qcmId)
    {
        DB::transaction(function () use ($qcmId) {
            // Trouver le QCM
            $qcm = self::findOrFail($qcmId);

            // Supprimer les réponses liées au QCM
            QcmReponses::whereIn('idQuestion', function ($query) use ($qcmId) {
                $query->select('idQuestion')
                    ->from('qcm_questions')
                    ->where('idQCM', $qcmId);
            })->delete();
            $invitations = QcmInvitation::where('idQCM', $qcmId)->get();
            foreach ($invitations as $invitation) {
                DB::table('qcm_invit_camp_invitations')
                    ->where('invitation_id', $invitation->idInvitation)
                    ->delete();
            }
            QcmInvitation::where('idQCM', $qcmId)->delete();

            // Supprimer les questions liées au QCM
            $qcm->questions_qcm()->delete();

            // Supprimer le QCM
            $qcm->delete();
        });
    }

    /**
     * Fonction pour avoir tous les Qcm avec un domaine de formation et qui sont disponible
     */
    public static function getAllPublicQcms()
    {
        return self::with('domaineFormation')
            ->where('statut', 1) # Le Qcm est disponible
            ->orderBy('intituleQCM')
            ->get();
    }

    /**
     * Fonction pour avoir tous les domaines
     */
    public static function getAllDomaines()
    {
        return DomainesFormation::orderBy('nomDomaine')->get();
    }

    /**
     * Fonction pour avoir les Qcm selon leur domaines
     * 
     * @param $domaineId
     */
    public static function getQcmsByDomaine($domaineId)
    {
        return self::where('idDomaine', $domaineId)
            ->with('domaineFormation')
            ->where('statut', 1) # Le Qcm est disponible
            ->orderBy('intituleQCM')
            ->get();
    }

    /**
     * Method for getting the list of all CFP used for their qcm results
     */
    public function fetchAllCfp()
    {
        $query = DB::table('v_cfp_all')
            ->select('idCfp', 'customerName', 'description', 'customerPhone', 'customerEmail', 'customer_addr_lot')
            ->get();

        $allCfp = $query;

        if ($allCfp->isEmpty()) {
            return response()->json(['message' => 'Aucun Cfp trouvé'], 404);
        }

        return response()->json($allCfp);
    }

    /**
     * Method for getting all the qcm created by a Cfp
     * 
     * @param $idCfp
     */
    public function fetchAllCfpQcm($idCfp)
    {
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
            ->where('qcm.user_id', '=', $idCfp)
            ->get();

        $allCfpQcm = $query;

        if ($allCfpQcm->isEmpty()) {
            return response()->json(['message' => 'Aucun QCM trouvé'], 404);
        }

        return response()->json($allCfpQcm);
    }

    /**
     * Method for fetching the qcm's creator datas
     * 
     * @param $idCfp
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchQcmCreatorDatas($idCfp)
    {
        $query = Customer::where('idCustomer', '=', $idCfp)->first();

        // Check if the customer exists
        if ($query) {
            // Return the customer data as JSON
            return response()->json($query);
        } else {
            // Return an error message if no customer is found
            return response()->json(['error' => 'Customer not found'], 404);
        }
    }

    /**
     * Method for fetching the data for the dashboard of a QCM, handling multiple attempts per user
     * for SuperAdmin, Ctf
     * 
     * @param $idQcm
     * @return array
     */
    public function fetchQcmDashboardDatas($idQcm)
    {
        // Get all results for this QCM
        $results = DB::table('v_all_users_qcm')->select('idUtilisateur', 'name', 'firstName', 'total_points', 'date_session')->where('idQCM', $idQcm)->whereNotNull('total_points')->get();

        // Check if QCM exists and if there are results
        if (!self::find($idQcm)) {
            return [
                'status' => 'error',
                'message' => 'Ce QCM n\'existe pas.'
            ];
        } elseif (self::find($idQcm) && $results->isEmpty()) {
            return [
                'status' => 'error',
                'message' => 'Aucun résultat trouvé pour ce QCM.'
            ];
        }

        // Initialize statistics array
        $statistics = [
            'total_attempts' => $results->count(),
            'unique_participants' => 0,
            'average_score' => 0,
            'highest_score' => 0,
            'lowest_score' => PHP_INT_MAX,
            'participation_by_month' => [],
            'level_distribution' => [],
            'latest_attempts' => [],
            'score_ranges' => [
                '0-25' => 0,
                '26-50' => 0,
                '51-75' => 0,
                '76-100' => 0
            ],
            'multiple_attempts' => [
                'users_count' => 0,
                'highest_attempts' => 0,
                'average_attempts' => 0
            ],
            'best_scores_by_user' => []
        ];

        // Group results by user
        $userAttempts = $results->groupBy('idUtilisateur');
        $statistics['unique_participants'] = $userAttempts->count();

        // Calculate attempts statistics
        $totalAttemptCounts = 0;
        $usersWithMultipleAttempts = 0;
        $maxAttempts = 0;

        foreach ($userAttempts as $userId => $attempts) {
            $attemptCount = $attempts->count();
            $totalAttemptCounts += $attemptCount;
            $maxAttempts = max($maxAttempts, $attemptCount);

            if ($attemptCount > 1) {
                $usersWithMultipleAttempts++;
            }

            // Store best score for this user
            $bestAttempt = $attempts->sortByDesc('total_points')->first();
            $statistics['best_scores_by_user'][] = [
                'user_id' => $userId,
                'name' => $bestAttempt->firstName . ' ' . $bestAttempt->name,
                'best_score' => $bestAttempt->total_points,
                'attempts_count' => $attemptCount,
                'last_attempt_date' => Carbon::parse($attempts->sortByDesc('date_session')->first()->date_session)->format('d/m/Y')
            ];
        }

        $statistics['multiple_attempts'] = [
            'users_count' => $usersWithMultipleAttempts,
            'highest_attempts' => $maxAttempts,
            'average_attempts' => round($totalAttemptCounts / $statistics['unique_participants'], 2)
        ];

        // Calculate total statistics using all attempts
        $totalPoints = 0;
        foreach ($results as $result) {
            $points = (int)$result->total_points;
            $totalPoints += $points;

            // Update highest and lowest scores
            $statistics['highest_score'] = max($statistics['highest_score'], $points);
            $statistics['lowest_score'] = min($statistics['lowest_score'], $points);

            // Count score ranges
            if ($points <= 25) $statistics['score_ranges']['0-25']++;
            elseif ($points <= 50) $statistics['score_ranges']['26-50']++;
            elseif ($points <= 75) $statistics['score_ranges']['51-75']++;
            else $statistics['score_ranges']['76-100']++;

            // Group by month for participation trend
            $month = Carbon::parse($result->date_session)->format('Y-m');
            if (!isset($statistics['participation_by_month'][$month])) {
                $statistics['participation_by_month'][$month] = 0;
            }
            $statistics['participation_by_month'][$month]++;
        }

        // Calculate average of all attempts
        $statistics['average_score'] = round($totalPoints / $statistics['total_attempts'], 2);

        // Get level distribution if barème exists
        $bareme = DB::table('qcm_bareme')
            ->where('idQCM', $idQcm)
            ->get();

        if ($bareme->isNotEmpty()) {
            // Use best scores for level distribution
            foreach ($statistics['best_scores_by_user'] as $userScore) {
                $points = (int)$userScore['best_score'];
                $level = DB::table('qcm_bareme')
                    ->where('idQCM', $idQcm)
                    ->where('minPoints', '<=', $points)
                    ->where('maxPoints', '>=', $points)
                    ->value('niveau');

                if ($level) {
                    if (!isset($statistics['level_distribution'][$level])) {
                        $statistics['level_distribution'][$level] = 0;
                    }
                    $statistics['level_distribution'][$level]++;
                }
            }
        }

        // Get latest 5 attempts
        $statistics['latest_attempts'] = $results
            ->sortByDesc('date_session')
            ->take(5)
            ->map(function ($result) {
                return [
                    'name' => $result->firstName . ' ' . $result->name,
                    'score' => $result->total_points,
                    'date' => Carbon::parse($result->date_session)->format('d/m/Y')
                ];
            })
            ->values();

        // Sort participation by month chronologically
        ksort($statistics['participation_by_month']);

        // Sort best scores by score descending
        $statistics['best_scores_by_user'] = collect($statistics['best_scores_by_user'])
            ->sortByDesc('best_score')
            ->values()
            ->all();

        return [
            'status' => 'success',
            'data' => $statistics
        ];
    }

    /**
     * Method for fetching the data for the dashboard of a QCM, handling multiple attempts per user
     * for Etp
     * 
     * @param $idQcm, $idEtp
     */
    public function fetchQcmDashboardDatasForEtp($idQcm, $idEtp)
    {
        // Get all results for this QCM
        $results = DB::table('v_all_users_qcm')->select('idUtilisateur', 'name', 'firstName', 'total_points', 'date_session')->where('idQCM', $idQcm)->where('idEtp', $idEtp)->whereNotNull('total_points')->get();

        // Check if QCM exists and if there are results
        if (!self::find($idQcm)) {
            return [
                'status' => 'error',
                'message' => 'Ce QCM n\'existe pas.'
            ];
        } elseif (self::find($idQcm) && $results->isEmpty()) {
            return [
                'status' => 'error',
                'message' => 'Aucun résultat trouvé pour ce QCM.'
            ];
        }

        // Initialize statistics array
        $statistics = [
            'total_attempts' => $results->count(),
            'unique_participants' => 0,
            'average_score' => 0,
            'highest_score' => 0,
            'lowest_score' => PHP_INT_MAX,
            'participation_by_month' => [],
            'level_distribution' => [],
            'latest_attempts' => [],
            'score_ranges' => [
                '0-25' => 0,
                '26-50' => 0,
                '51-75' => 0,
                '76-100' => 0
            ],
            'multiple_attempts' => [
                'users_count' => 0,
                'highest_attempts' => 0,
                'average_attempts' => 0
            ],
            'best_scores_by_user' => []
        ];

        // Group results by user
        $userAttempts = $results->groupBy('idUtilisateur');
        $statistics['unique_participants'] = $userAttempts->count();

        // Calculate attempts statistics
        $totalAttemptCounts = 0;
        $usersWithMultipleAttempts = 0;
        $maxAttempts = 0;

        foreach ($userAttempts as $userId => $attempts) {
            $attemptCount = $attempts->count();
            $totalAttemptCounts += $attemptCount;
            $maxAttempts = max($maxAttempts, $attemptCount);

            if ($attemptCount > 1) {
                $usersWithMultipleAttempts++;
            }

            // Store best score for this user
            $bestAttempt = $attempts->sortByDesc('total_points')->first();
            $statistics['best_scores_by_user'][] = [
                'user_id' => $userId,
                'name' => $bestAttempt->firstName . ' ' . $bestAttempt->name,
                'best_score' => $bestAttempt->total_points,
                'attempts_count' => $attemptCount,
                'last_attempt_date' => Carbon::parse($attempts->sortByDesc('date_session')->first()->date_session)->format('d/m/Y')
            ];
        }

        $statistics['multiple_attempts'] = [
            'users_count' => $usersWithMultipleAttempts,
            'highest_attempts' => $maxAttempts,
            'average_attempts' => round($totalAttemptCounts / $statistics['unique_participants'], 2)
        ];

        // Calculate total statistics using all attempts
        $totalPoints = 0;
        foreach ($results as $result) {
            $points = (int)$result->total_points;
            $totalPoints += $points;

            // Update highest and lowest scores
            $statistics['highest_score'] = max($statistics['highest_score'], $points);
            $statistics['lowest_score'] = min($statistics['lowest_score'], $points);

            // Count score ranges
            if ($points <= 25) $statistics['score_ranges']['0-25']++;
            elseif ($points <= 50) $statistics['score_ranges']['26-50']++;
            elseif ($points <= 75) $statistics['score_ranges']['51-75']++;
            else $statistics['score_ranges']['76-100']++;

            // Group by month for participation trend
            $month = Carbon::parse($result->date_session)->format('Y-m');
            if (!isset($statistics['participation_by_month'][$month])) {
                $statistics['participation_by_month'][$month] = 0;
            }
            $statistics['participation_by_month'][$month]++;
        }

        // Calculate average of all attempts
        $statistics['average_score'] = round($totalPoints / $statistics['total_attempts'], 2);

        // Get level distribution if barème exists
        $bareme = DB::table('qcm_bareme')
            ->where('idQCM', $idQcm)
            ->get();

        if ($bareme->isNotEmpty()) {
            // Use best scores for level distribution
            foreach ($statistics['best_scores_by_user'] as $userScore) {
                $points = (int)$userScore['best_score'];
                $level = DB::table('qcm_bareme')
                    ->where('idQCM', $idQcm)
                    ->where('minPoints', '<=', $points)
                    ->where('maxPoints', '>=', $points)
                    ->value('niveau');

                if ($level) {
                    if (!isset($statistics['level_distribution'][$level])) {
                        $statistics['level_distribution'][$level] = 0;
                    }
                    $statistics['level_distribution'][$level]++;
                }
            }
        }

        // Get latest 5 attempts
        $statistics['latest_attempts'] = $results
            ->sortByDesc('date_session')
            ->take(5)
            ->map(function ($result) {
                return [
                    'name' => $result->firstName . ' ' . $result->name,
                    'score' => $result->total_points,
                    'date' => Carbon::parse($result->date_session)->format('d/m/Y')
                ];
            })
            ->values();

        // Sort participation by month chronologically
        ksort($statistics['participation_by_month']);

        // Sort best scores by score descending
        $statistics['best_scores_by_user'] = collect($statistics['best_scores_by_user'])
            ->sortByDesc('best_score')
            ->values()
            ->all();

        return [
            'status' => 'success',
            'data' => $statistics
        ];
    }

    /**
     * Method for getting all max points in each categories of a qcm
     * 
     * @param int $idQcm
     * @return array Array containing max points for each category and total max points
     */
    public function fetchQcmMaxPointsInEachCategories($idQcm)
    {
        // 1. Récupérer toutes les réponses liées à ce QCM via les relations
        $reponses = QcmReponses::whereHas('reponseToquestion', function ($query) use ($idQcm) {
            $query->where('idQCM', $idQcm);
        })
            ->with(['categorie_reponse', 'reponseToquestion'])
            ->get();

        // 2. Initialiser le tableau des résultats
        $results = [
            'categories' => [],
            'total_max_points' => 0
        ];

        // 3. Grouper les réponses par question et trouver le maximum pour chaque catégorie
        $questionsGrouped = $reponses->groupBy('reponseToquestion.idQuestion');

        foreach ($questionsGrouped as $questionId => $questionReponses) {
            // Pour chaque question, grouper les réponses par catégorie
            $categoriesGrouped = $questionReponses->groupBy('categorie_id');

            foreach ($categoriesGrouped as $categorieId => $categorieReponses) {
                // Trouver la réponse avec le plus de points dans cette catégorie pour cette question
                $maxPoints = $categorieReponses->max('points');

                // Récupérer le nom de la catégorie (on suppose qu'il est le même pour toutes les réponses de cette catégorie)
                $categorieNom = $categorieReponses->first()->categorie_reponse->nomCategorie ?? "Catégorie {$categorieId}";

                // Initialiser la catégorie si elle n'existe pas encore
                if (!isset($results['categories'][$categorieId])) {
                    $results['categories'][$categorieId] = [
                        'nom' => $categorieNom,
                        'points_max' => 0
                    ];
                }

                // Ajouter les points maximum pour cette question à cette catégorie
                $results['categories'][$categorieId]['points_max'] += $maxPoints;
            }
        }

        // 4. Calculer le total des points maximum
        $results['total_max_points'] = array_sum(array_column($results['categories'], 'points_max'));

        return $results;
    }

    /**
     * Generate a performance analysis description based on score percentage (v1)
     * 
     * not stocked in database
     * 
     * @param float $percentage Score percentage
     * @param string $categorieName Category name
     * @return array Analysis with description and level
     */
    // public function generateAnalysis($percentage, $categorieName)
    // {
    //     // Définir le niveau de performance
    //     $level = match (true) {
    //         $percentage >= 90 => 'excellent',
    //         $percentage >= 75 => 'bon',
    //         $percentage >= 60 => 'moyen',
    //         $percentage >= 40 => 'insuffisant',
    //         default => 'faible'
    //     };

    //     // Générer la description en fonction du niveau
    //     $description = match ($level) {
    //         'excellent' => "Excellente maîtrise de la catégorie '$categorieName'. Vous avez démontré une compréhension approfondie des concepts.",
    //         'bon' => "Bonne performance dans la catégorie '$categorieName'. Quelques points mineurs peuvent être améliorés.",
    //         'moyen' => "Niveau acceptable dans la catégorie '$categorieName'. Des révisions ciblées seraient bénéfiques.",
    //         'insuffisant' => "Des lacunes importantes dans la catégorie '$categorieName'. Un renforcement des connaissances est nécessaire.",
    //         'faible' => "Difficultés significatives dans la catégorie '$categorieName'. Un apprentissage approfondi est recommandé."
    //     };

    //     // Générer des recommandations
    //     $recommendations = match ($level) {
    //         'excellent' => "Continuez à approfondir vos connaissances et envisagez d'explorer des concepts plus avancés.",
    //         'bon' => "Concentrez-vous sur les points manqués pour atteindre l'excellence.",
    //         'moyen' => "Identifiez les concepts mal compris et renforcez votre pratique dans ces domaines.",
    //         'insuffisant' => "Reprenez les bases et pratiquez régulièrement.",
    //         'faible' => "Un retour aux fondamentaux est conseillé. Envisagez de suivre des formations complémentaires."
    //     };

    //     return [
    //         'level' => $level,
    //         'description' => $description,
    //         'recommendations' => $recommendations
    //     ];
    // }

    /**
     * Generate a performance analysis description based on score percentage (v2)
     * First checks for custom evaluations by QCM creator, then falls back to default
     * 
     * @param float $percentage Score percentage
     * @param string $categorieName Category name
     * @param int|null $idQCM The QCM ID
     * @param int|null $idCategorie The category ID
     * @return array Analysis with description and level
     */
    public function generateAnalysis($percentage, $categorieName, $idQCM = null, $idCategorie = null)
    {
        // If QCM ID and category ID are provided, look for custom evaluations
        if ($idQCM && $idCategorie) {
            $customEvaluation = QcmCategoryEvaluation::where('idQCM', $idQCM)
                ->where('idCategorie', $idCategorie)
                ->where('min_percentage', '<=', $percentage)
                ->where('max_percentage', '>=', $percentage)
                ->first();

            if ($customEvaluation) {
                return [
                    'level' => $customEvaluation->level,
                    'description' => $customEvaluation->description,
                    'recommendations' => $customEvaluation->recommendations,
                    'is_custom' => true
                ];
            }
        }

        // Fall back to default analysis if no custom evaluation exists
        // Définir le niveau de performance
        $level = match (true) {
            $percentage >= 90 => 'excellent',
            $percentage >= 75 => 'bon',
            $percentage >= 60 => 'moyen',
            $percentage >= 40 => 'insuffisant',
            default => 'faible'
        };

        // Générer la description en fonction du niveau
        $description = match ($level) {
            'excellent' => "Excellente maîtrise de la catégorie '$categorieName'. Vous avez démontré une compréhension approfondie des concepts.",
            'bon' => "Bonne performance dans la catégorie '$categorieName'. Quelques points mineurs peuvent être améliorés.",
            'moyen' => "Niveau acceptable dans la catégorie '$categorieName'. Des révisions ciblées seraient bénéfiques.",
            'insuffisant' => "Des lacunes importantes dans la catégorie '$categorieName'. Un renforcement des connaissances est nécessaire.",
            'faible' => "Difficultés significatives dans la catégorie '$categorieName'. Un apprentissage approfondi est recommandé."
        };

        // Générer des recommandations
        $recommendations = match ($level) {
            'excellent' => "Continuez à approfondir vos connaissances et envisagez d'explorer des concepts plus avancés.",
            'bon' => "Concentrez-vous sur les points manqués pour atteindre l'excellence.",
            'moyen' => "Identifiez les concepts mal compris et renforcez votre pratique dans ces domaines.",
            'insuffisant' => "Reprenez les bases et pratiquez régulièrement.",
            'faible' => "Un retour aux fondamentaux est conseillé. Envisagez de suivre des formations complémentaires."
        };

        return [
            'level' => $level,
            'description' => $description,
            'recommendations' => $recommendations,
            'is_custom' => false
        ];
    }

    /**
     * Method for getting user points in each category for a specific QCM session (v1)
     * 
     * @param int $idQcm
     * @param int $idUser
     * @param int $idSession
     * @return array Array containing user points and analysis for each category
     */
    // public function fetchUserPointsInCategories($idQcm, $idUser, $idSession)
    // {
    //     // 1. Récupérer d'abord les points maximum possibles
    //     $maxPoints = $this->fetchQcmMaxPointsInEachCategories($idQcm);

    //     // 2. Récupérer la session avec toutes les réponses de l'utilisateur
    //     $session = SessionsQcm::where('idSession', $idSession)
    //         ->where('idUtilisateur', $idUser)
    //         ->where('idQCM', $idQcm)
    //         ->with(['reponsesUser.userChoosenReponse.categorie_reponse'])
    //         ->first();

    //     if (!$session) {
    //         return [
    //             'error' => 'Session non trouvée ou invalide'
    //         ];
    //     }

    //     // 3. Initialiser le tableau des résultats
    //     $results = [
    //         'categories' => [],
    //         'total_points' => 0,
    //         'max_points' => $maxPoints['total_max_points'],
    //         'session_info' => [
    //             'date_debut' => $session->dateDebut,
    //             'date_fin' => $session->dateFin,
    //             'duree' => strtotime($session->dateFin) - strtotime($session->dateDebut)
    //         ]
    //     ];

    //     // Initialiser les catégories avec les mêmes que dans maxPoints
    //     foreach ($maxPoints['categories'] as $catId => $catInfo) {
    //         $results['categories'][$catId] = [
    //             'nom' => $catInfo['nom'],
    //             'points_obtenus' => 0,
    //             'points_max' => $catInfo['points_max']
    //         ];
    //     }

    //     // 4. Calculer les points obtenus pour chaque catégorie
    //     foreach ($session->reponsesUser as $reponseUser) {
    //         if ($reponseUser->userChoosenReponse) {
    //             $categorieId = $reponseUser->userChoosenReponse->categorie_id;
    //             $points = $reponseUser->userChoosenReponse->points;

    //             if (isset($results['categories'][$categorieId])) {
    //                 $results['categories'][$categorieId]['points_obtenus'] += $points;
    //             }
    //         }
    //     }

    //     // 5. Calculer le total des points obtenus
    //     $results['total_points'] = array_sum(array_column($results['categories'], 'points_obtenus'));

    //     // 6. Calculer le pourcentage et générer l'analyse pour chaque catégorie
    //     foreach ($results['categories'] as &$categorie) {
    //         $categorie['pourcentage'] = $categorie['points_max'] > 0
    //             ? round(($categorie['points_obtenus'] / $categorie['points_max']) * 100, 2)
    //             : 0;

    //         // Ajouter l'analyse pour cette catégorie
    //         $categorie['analyse'] = $this->generateAnalysis(
    //             $categorie['pourcentage'],
    //             $categorie['nom']
    //         );
    //     }

    //     // 7. Calculer le pourcentage global et générer une analyse globale
    //     $results['pourcentage_global'] = $results['max_points'] > 0
    //         ? round(($results['total_points'] / $results['max_points']) * 100, 2)
    //         : 0;

    //     $results['analyse_globale'] = $this->generateAnalysis(
    //         $results['pourcentage_global'],
    //         'l\'ensemble du QCM'
    //     );

    //     return $results;
    // }

    /**
     * Method for getting user points in each category for a specific QCM session (v2)
     * 
     * @param int $idQcm
     * @param int $idUser
     * @param int $idSession
     * @return array Array containing user points and analysis for each category
     */
    public function fetchUserPointsInCategories($idQcm, $idUser, $idSession)
    {
        // 1. Récupérer d'abord les points maximum possibles
        $maxPoints = $this->fetchQcmMaxPointsInEachCategories($idQcm);

        // 2. Récupérer la session avec toutes les réponses de l'utilisateur
        $session = SessionsQcm::where('idSession', $idSession)
            ->where('idUtilisateur', $idUser)
            ->where('idQCM', $idQcm)
            ->with(['reponsesUser.userChoosenReponse.categorie_reponse'])
            ->first();

        if (!$session) {
            return [
                'error' => 'Session non trouvée ou invalide'
            ];
        }

        // 3. Initialiser le tableau des résultats
        $results = [
            'categories' => [],
            'total_points' => 0,
            'max_points' => $maxPoints['total_max_points'],
            'session_info' => [
                'date_debut' => $session->dateDebut,
                'date_fin' => $session->dateFin,
                'duree' => strtotime($session->dateFin) - strtotime($session->dateDebut)
            ]
        ];

        // Initialiser les catégories avec les mêmes que dans maxPoints
        foreach ($maxPoints['categories'] as $catId => $catInfo) {
            $results['categories'][$catId] = [
                'nom' => $catInfo['nom'],
                'points_obtenus' => 0,
                'points_max' => $catInfo['points_max']
            ];
        }

        // 4. Calculer les points obtenus pour chaque catégorie
        foreach ($session->reponsesUser as $reponseUser) {
            if ($reponseUser->userChoosenReponse) {
                $categorieId = $reponseUser->userChoosenReponse->categorie_id;
                $points = $reponseUser->userChoosenReponse->points;

                if (isset($results['categories'][$categorieId])) {
                    $results['categories'][$categorieId]['points_obtenus'] += $points;
                }
            }
        }

        // 5. Calculer le total des points obtenus
        $results['total_points'] = array_sum(array_column($results['categories'], 'points_obtenus'));

        // 6. Calculer le pourcentage et générer l'analyse pour chaque catégorie
        foreach ($results['categories'] as $categorieId => &$categorie) {
            $categorie['pourcentage'] = $categorie['points_max'] > 0
                ? round(($categorie['points_obtenus'] / $categorie['points_max']) * 100, 2)
                : 0;

            // Vérifier si cette catégorie a des évaluations personnalisées
            $hasCustomEvaluation = QcmCategoryEvaluation::where('idQCM', $idQcm)
                ->where('idCategorie', $categorieId)
                ->exists();

            // Only add analysis if custom evaluations exist for this category
            if ($hasCustomEvaluation) {
                // Ajouter l'analyse pour cette catégorie
                $categorie['analyse'] = $this->generateAnalysis(
                    $categorie['pourcentage'],
                    $categorie['nom'],
                    $idQcm,
                    $categorieId
                );
            }
        }

        // 7. Calculer le pourcentage global et générer une analyse globale
        $results['pourcentage_global'] = $results['max_points'] > 0
            ? round(($results['total_points'] / $results['max_points']) * 100, 2)
            : 0;

        // For global analysis, we use a special category ID of 0 to potentially find global evaluations
        $hasGlobalCustomEvaluation = QcmCategoryEvaluation::where('idQCM', $idQcm)
            ->where('idCategorie', 0) // Special ID for global evaluations
            ->exists();

        if ($hasGlobalCustomEvaluation) {
            $results['analyse_globale'] = $this->generateAnalysis(
                $results['pourcentage_global'],
                'l\'ensemble du QCM',
                $idQcm,
                0 // Use 0 as special ID for global evaluations
            );
        }

        return $results;
    }

    /**
     * Accessor (getter) for formatted duration
     * Get the formatted duration in minutes:seconds
     *
     * @return string
     */
    public function getFormattedDurationAttribute()
    {
        $minutes = floor($this->duree / 60);
        $seconds = $this->duree % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
