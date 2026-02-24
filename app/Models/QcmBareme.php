<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class QcmBareme extends Model
{
    use HasFactory;

    protected $table = "qcm_bareme";
    protected $primaryKey = "idBareme";

    protected $fillable = [
        'idQCM',
        'minPoints',
        'maxPoints',
        'niveau',
    ];

    // Relation avec le modèle Qcm (Inverse of One to One)
    public function qcm()
    {
        return $this->belongsTo(Qcm::class, 'idQCM');
    }
    public function niveau()
    {
        return $this->belongsTo(NiveauQcm::class, 'id_niveau');
    }
    /**
     * Fonction pour générer des couleurs aléatoirement (utilisée dans "getAllApprSpiderChartData")
     * 
     * @param $index (index des sections)
     */
    private function generateColor($index)
    {
        // Liste de couleurs prédéfinies pour assurer un bon contraste
        $colors = [
            '54, 162, 235',   // bleu
            '255, 99, 132',   // rouge
            '75, 192, 192',   // turquoise
            '153, 102, 255',  // violet
            '255, 159, 64',   // orange
            '255, 205, 86',   // jaune
            '201, 203, 207',  // gris
            '0, 150, 136',    // vert
            '233, 30, 99',    // rose
            '103, 58, 183',   // violet foncé
        ];

        if ($index < count($colors)) {
            return $colors[$index];
        }

        // Si on dépasse le nombre de couleurs prédéfinies,
        // on génère une couleur aléatoire
        $h = ($index * 137.508) % 360; // Angle doré pour une bonne distribution
        $s = 70 + rand(-20, 20);       // Saturation entre 50% et 90%
        $l = 60 + rand(-20, 20);       // Luminosité entre 40% et 80%

        // Conversion HSL vers RGB
        $c = (1 - abs(2 * $l / 100 - 1)) * $s / 100;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l / 100 - $c / 2;

        if ($h < 60) {
            $r = $c;
            $g = $x;
            $b = 0;
        } else if ($h < 120) {
            $r = $x;
            $g = $c;
            $b = 0;
        } else if ($h < 180) {
            $r = 0;
            $g = $c;
            $b = $x;
        } else if ($h < 240) {
            $r = 0;
            $g = $x;
            $b = $c;
        } else if ($h < 300) {
            $r = $x;
            $g = 0;
            $b = $c;
        } else {
            $r = $c;
            $g = 0;
            $b = $x;
        }

        $r = round(($r + $m) * 255);
        $g = round(($g + $m) * 255);
        $b = round(($b + $m) * 255);

        return "$r, $g, $b";
    }

    /**
     * Fonction pour avoir les résultats d'un apprenant après un test
     * 
     * @param $id (id du qcm), $idAppr (id de l'apprennant)
     */
    public function getAllResultsOneApprenantPostTest($id, $idAppr)
    {
        $query = DB::table('v_reponses_users_qcm')
            ->join('users', 'v_reponses_users_qcm.idUtilisateur', '=', 'users.id')
            ->select('users.id as idUtilisateur', 'users.name', 'users.firstName', 'v_reponses_users_qcm.idSession', 'v_reponses_users_qcm.idQCM', 'v_reponses_users_qcm.intituleQCM', 'v_reponses_users_qcm.date_session')
            ->selectRaw('SUM(v_reponses_users_qcm.points_obtenus) as total_points')
            ->where('v_reponses_users_qcm.idQCM', $id)
            ->where('v_reponses_users_qcm.idUtilisateur', $idAppr)
            ->groupBy('users.id', 'users.name', 'users.firstName', 'v_reponses_users_qcm.idSession', 'v_reponses_users_qcm.idQCM', 'v_reponses_users_qcm.intituleQCM', 'v_reponses_users_qcm.date_session');

        $results = $query->get();

        // Vérifie si il y a une ou des résultats
        if ($results->isEmpty()) {
            return [
                'status' => 'error',
                'message' => 'Aucun résultat trouvé pour ce QCM.'
            ];
        }

        // Détermine le niveau de chaque apprenants après le test
        foreach ($results as $result) {
            $totalPoints = (int)$result->total_points;
            $idQCM = $result->idQCM;

            $baremeExists = DB::table('qcm_bareme')
                ->where('idQCM', $idQCM)
                ->exists();

            if ($baremeExists) {
                $level = DB::table('qcm_bareme')
                    ->where('idQCM', $idQCM)
                    ->where('minPoints', '<=', $totalPoints)
                    ->where('maxPoints', '>=', $totalPoints)
                    ->value('niveau');

                $result->niveau = $level ?: "Niveau non défini";
            } else {
                $result->niveau = "Pas de barème pour ce QCM";
            }
        }

        return [
            'status' => 'success',
            'data' => $results
        ];
    }

    /**
     * Fonction pour avoir les résultats des apprenants après avoir fait un test sans la liste des apprenants déjà existant du
     * formateur ou Ctf (v1) (non utilisé actuellement)
     * 
     * @param $id (id du qcm)
     */
    public function getAllResultsApprenantsPostTest($id)
    {
        $query = DB::table('v_reponses_users_qcm')
            ->join('users', 'v_reponses_users_qcm.idUtilisateur', '=', 'users.id')
            ->select('users.id as idUtilisateur', 'users.name', 'users.firstName', 'v_reponses_users_qcm.idSession', 'v_reponses_users_qcm.idQCM', 'v_reponses_users_qcm.intituleQCM', 'v_reponses_users_qcm.date_session')
            ->selectRaw('SUM(v_reponses_users_qcm.points_obtenus) as total_points')
            ->where('v_reponses_users_qcm.idQCM', $id)
            ->groupBy('users.id', 'users.name', 'users.firstName', 'v_reponses_users_qcm.idSession', 'v_reponses_users_qcm.idQCM', 'v_reponses_users_qcm.intituleQCM', 'v_reponses_users_qcm.date_session');

        $results = $query->get();

        // Vérifie si il y a une ou des résultats
        if ($results->isEmpty()) {
            return [
                'status' => 'error',
                'message' => 'Aucun résultat trouvé pour ce QCM.'
            ];
        }

        // Détermine le niveau de chaque apprenants après le test
        foreach ($results as $result) {
            $totalPoints = (int)$result->total_points;
            $idQCM = $result->idQCM;

            $baremeExists = DB::table('qcm_bareme')
                ->where('idQCM', $idQCM)
                ->exists();

            if ($baremeExists) {
                $level = DB::table('qcm_bareme')
                    ->where('idQCM', $idQCM)
                    ->where('minPoints', '<=', $totalPoints)
                    ->where('maxPoints', '>=', $totalPoints)
                    ->value('niveau');

                $result->niveau = $level ?: "Niveau non défini";
            } else {
                $result->niveau = "Pas de barème pour ce QCM";
            }
        }

        return [
            'status' => 'success',
            'data' => $results
        ];
    }

    /**
     * Fonction pour avoir les résultats des apprenants après avoir fait un test avec la liste des apprenants déjà existant du
     * formateur ou Ctf (v5 de getAllResultsApprenantsPostTest), version vaovao 11-2-2025
     * Avec classement des utilisateurs dans le Qcm
     * 
     * @param $id (id du qcm), $idCtf (id du Formateur ou du Ctf)
     */
    public function getAllResultsApprPostTestWithAlreadyApprCtf($id, $idCtf)
    {
        $id = intval($id);
        $user = User::find($idCtf);
        if($user->hasRole('Formateur')) {
            $idCtf = DB::table('cfp_formateurs')
                ->where('idFormateur', $user->id)
                ->value('idCfp');
        }
        /*$query =  DB::table('v_all_users_qcm as u')
        ->select(
            'u.idUtilisateur',
            'u.name',
            'u.firstName',
            'u.idEtp',
            'u.etp_name',
            'u.idQCM',
            'u.idSession',
            'u.date_session',
            'u.total_points',
        ) */
        $query = DB::table('sessions_test')
            ->where('idQCM', $id);
        if ($user->hasRole('Formateur')) {
                // on joint la vue des apprenants
            $query->join('v_apprenant_etp_alls as a', function($join) use ($idCtf) {
                $join->on('a.idEmploye', '=', 'idUtilisateur')
                    ->where('a.idCfp', $idCtf);
            });
            $query->select('*')->groupBy('idSession');
        } else if ($user->hasRole('Cfp')){
            // on joint la vue des apprenants                                 
            $query->join('v_apprenant_etp_alls as a', function($join) use ($idCtf) {
                $join->on('a.idEmploye', '=', 'idUtilisateur')
                    ->where('a.idCfp', $idCtf);
            });
            // on ne garde que les sessions de l'utilisateur
            $query->select('*')->groupBy('idSession');
        } else if ($user->hasRole('Employe')) {
            $query->where('idUtilisateur', $user->id);
            $query->join('users', function($join) use ($idCtf) {
                $join->on('id', '=', 'idUtilisateur');
            });
                // on joint la vue des apprenants
            $query->join('v_apprenant_etp', function ($join) use ($idCtf) {
                $join->on('idEmploye','=','idUtilisateur');
            });
        } else {
            $query->where('idUtilisateur', $user->id);
            $query->join('users', function($join) use ($idCtf) {
                $join->on('id', '=', 'idUtilisateur');
            });
        }
        // Union des deux requêtes
        $results = $query->where('idSession', '!=', null)->get();

        // Vérifie si il y a une ou des résultats
        if ($results->isEmpty()) {
            return [
                'status' => 'error',
                'message' => 'Aucun résultat trouvé pour ce QCM.'
            ];
        }

        // Détermine le niveau de chaque apprenant après le test
        foreach ($results as $result) {
            $totalPoints = (int)$result->totalPoints;
            $result->date = $result->dateFin;
            $idQCM = $result->idQCM;
            $baremeExists = DB::table('qcm_bareme')
                ->where('idQCM', $idQCM)
                ->exists();

            if ($baremeExists) {
                $level_id = DB::table('qcm_bareme')
                    ->where('idQCM', $idQCM)
                    ->where('minPoints', '<=', $totalPoints)
                    ->where('maxPoints', '>=', $totalPoints)
                    ->value('id_niveau');                    
                $level = NiveauQcm::where('id', $level_id)->first();
                $result->niveau = $level ? $level->niveau : "Niveau non défini";
            } else {
                $result->niveau = "Pas de barème pour ce QCM";
            }
        }
        // Classement des participants selon "total_points"
        $results = $results->sortByDesc('total_points')->values(); // Trie par points décroissants
        $rank = 1; // Initialisation du rang
        $previousPoints = null; // Stocke les points du précédent participant
        $offset = 0; // Pour gérer les égalités

        foreach ($results as $key => $result) {
            $totalPoints = (int)$result->totalPoints;

            if ($previousPoints !== null && $totalPoints < $previousPoints) {
                // Si les points sont inférieurs à ceux du précédent, passe au rang suivant
                $rank += $offset + 1;
                $offset = 0; // Réinitialise l'offset
            } elseif ($previousPoints !== null && $totalPoints === $previousPoints) {
                // Si les points sont égaux à ceux du précédent, le rang reste le même
                $offset++;
            }

            $result->rang = $rank; // Assigne le rang actuel
            $previousPoints = $totalPoints; // Met à jour les points précédents
        }
        // Compte le nombre d'apprenant
        $count_lines = count($results);

        return [
            'status' => 'success',
            'data' => $results,
            'count' => $count_lines,
        ];
    }

    /**
     * Fonction pour avoir le résultat d'un apprenant après avoir fait un test
     * Une seule session
     * 
     * @param $id (id de l'apprenant), $idQCM, $idSession
     */
    public function getResultApprenantPostTest($id, $idQCM, $idSession)
    {

        $result = DB::table('v_reponses_users_qcm')
            ->select('*')
            ->where('idUtilisateur', $id)
            ->where('idQCM', $idQCM)
            ->where('idSession', $idSession)
            ->groupBy('idUtilisateur', 'idSession', 'idQCM', 'intituleQCM', 'date_session')
            ->first();

        $questions = QcmQuestions::with('reponses_questions')->where('idQCM', $idQCM)->get();

        $listPoints = [];
        $processedQuestions = []; // Pour éviter de traiter plusieurs fois la même question

        foreach ($questions as $question) {
            $maxPointsParCategorie = [];
            
            // Récupérer le maximum de points pour chaque catégorie de cette question
            foreach ($question->reponses_questions as $reponse) {
                $categorieId = $reponse->categorie_id;
                
                if (!isset($maxPointsParCategorie[$categorieId])) {
                    $maxPointsParCategorie[$categorieId] = 0;
                }
                
                // Garder le maximum de points pour cette catégorie dans cette question
                if ($reponse->points > $maxPointsParCategorie[$categorieId]) {
                    $maxPointsParCategorie[$categorieId] = $reponse->points;
                }
            }
            
            // Ajouter les points maximum de chaque catégorie au total
            foreach ($maxPointsParCategorie as $categorieId => $maxPoints) {
                if (!isset($listPoints[$categorieId])) {
                    $listPoints[$categorieId] = 0;
                }
                $listPoints[$categorieId] += $maxPoints;
            }
        }

        /* // Affichage pour debug
        foreach ($listPoints as $categorieId => $totalPoints) {
    echo "Catégorie $categorieId : $totalPoints points<br>";
        } */

        $totalPointsQcm = array_sum($listPoints);

        if ($result) {
            $totalPoints = (int)$result->total_points;
            $bareme = DB::table('qcm_bareme')
                ->where('idQCM', $idQCM)
                ->where('minPoints', '<=', $totalPoints)
                ->where('maxPoints', '>=', $totalPoints)
                ->value('id_niveau');
            $level = NiveauQcm::where('id', $bareme)->first();
            if ($bareme) {
                $result->niveau = $level->niveau;
            } else {
                $result->niveau = DB::table('qcm_bareme')
                    ->where('idQCM', $idQCM)
                    ->exists() ? "Niveau non défini" : "Pas de barème pour ce QCM";
            }
            $result->pourcentage = ($totalPointsQcm != 0) ? (($totalPoints / $totalPointsQcm) * 100) : 0;
            $result->totalPointQcm = $totalPointsQcm;
        }

        return $result;
    }

    /**
     * Fonction pour avoir les résultats d'un apprenant après avoir fait un test plusieurs fois
     * Toutes les sessions
     * 
     * @param $id (id l'apprenant), $idQCM
     */
    public function getAllResultsOfOneApprPostTest($id, $idQCM)
    {
        $results = DB::table('v_reponses_users_qcm')
            ->select('idUtilisateur', 'idSession', 'idQCM', 'intituleQCM', 'date_session')
            ->selectRaw('SUM(points_obtenus) as total_points')
            ->where('idUtilisateur', $id)
            ->where('idQCM', $idQCM)
            ->groupBy('idUtilisateur', 'idSession', 'idQCM', 'intituleQCM', 'date_session')
            ->get();

        $bareme = DB::table('qcm_bareme')
            ->where('idQCM', $idQCM)
            ->get()
            ->keyBy('niveau');

        foreach ($results as $result) {
            $totalPoints = (int)$result->total_points;

            if ($bareme->isNotEmpty()) {
                $level = $bareme->first(function ($item) use ($totalPoints) {
                    return $totalPoints >= $item->minPoints && $totalPoints <= $item->maxPoints;
                });

                $result->niveau = $level ? $level->niveau : "Niveau non défini";
            } else {
                $result->niveau = "Pas de barème pour ce QCM";
            }
        }

        return $results;
    }

    /**
     * Fonction pour avoir les réponses choisies par un apprenant durant un test
     * 
     * @param $id ($id de l'apprenant), $idQCM, $idSession
     */
    public function getChoosenAnswers($id, $idQCM, $idSession)
    {
        $query = DB::table('v_reponses_users_qcm')
            ->select(
                'v_reponses_users_qcm.idUtilisateur',
                'v_reponses_users_qcm.idQCM',
                'v_reponses_users_qcm.idQuestion',
                'v_reponses_users_qcm.enonce_question',
                'v_reponses_users_qcm.idReponse',
                'v_reponses_users_qcm.choosen_responses',
                'v_reponses_users_qcm.idRepCategorie',
                'v_reponses_users_qcm.points_obtenus',
                'categories_reponses.nomCategorie'
            )
            ->join('categories_reponses', 'v_reponses_users_qcm.idRepCategorie', '=', 'categories_reponses.idCategorie')
            ->where('v_reponses_users_qcm.idUtilisateur', $id)
            ->where('v_reponses_users_qcm.idQCM', $idQCM)
            ->where('v_reponses_users_qcm.idSession', $idSession);

        $results = $query->get();

        return $results;
    }

    /**
     * Fonction pour avoir les résultats détaillés d'un apprenant après un test
     * 
     * @param $id (id de l'apprenant), $idQCM, $idSession
     */
    public function getApprDetailsResults($id, $idQCM, $idSession)
    {
        $query = DB::table('v_reponses_users_qcm')
            ->select(
                'v_reponses_users_qcm.idUtilisateur',
                'v_reponses_users_qcm.idQCM',
                'v_reponses_users_qcm.idSession',
                'v_reponses_users_qcm.idRepCategorie',
                'categories_reponses.nomCategorie',
                DB::raw('SUM(v_reponses_users_qcm.points_obtenus) as total_points')
            )
            ->join('categories_reponses', 'v_reponses_users_qcm.idRepCategorie', '=', 'categories_reponses.idCategorie')
            ->where('v_reponses_users_qcm.idUtilisateur', $id)
            ->where('v_reponses_users_qcm.idQCM', $idQCM)
            ->where('v_reponses_users_qcm.idSession', $idSession)
            ->groupBy(
                'v_reponses_users_qcm.idUtilisateur',
                'v_reponses_users_qcm.idQCM',
                'v_reponses_users_qcm.idSession',
                'v_reponses_users_qcm.idRepCategorie',
                'categories_reponses.nomCategorie'
            );

        //Total des points par section
        
        $questions = QcmQuestions::with('reponses_questions')->where('idQCM', $idQCM)->get();
        $listPoints = [];
        $lastCategorie = null;
        foreach ($questions as $question) {
            foreach ($question->reponses_questions as $reponse) {
                if ($listPoints == [] || $lastCategorie !== $reponse->categorie_id) {
                    $lastCategorie = $reponse->categorie_id;
                    $listPoints[$reponse->categorie_id] = $reponse->where('categorie_id', $reponse->categorie_id)->where('idQuestion', $question->idQuestion)->max('points');
                }
                else {
                    $listPoints[$lastCategorie] += $reponse->where('categorie_id', $reponse->categorie_id)->where('idQuestion', $question->idQuestion)->max('points');
                }
            }
        }

        //Fin Total Points par section

        $results = $query->get();
        $groupedResponses = collect(); // Initialise une collection vide

        //Pourcentages des points à chaque section

        foreach ($results as $details) {
            $details->pourcentage = 0;

            // Recherche du total de points correspondant à la catégorie par nom
            $categorieId = null;
            foreach ($listPoints as $catId => $totalpoint) {
                $evaluationCat = QcmCategoryEvaluation::with('categorie')
                    ->where('idQCM', $idQCM)
                    ->whereHas('categorie', function ($query) use ($details) {
                        $query->where('nomCategorie', $details->nomCategorie);
                    })
                    ->where('idCategorie', $catId)
                    ->first();

                if ($evaluationCat && $totalpoint > 0) {
                    // On a trouvé la bonne catégorie
                    $categorieId = $catId;
                    $details->pourcentage = ($details->total_points / $totalpoint) * 100;
                    break; // Plus besoin de continuer
                }
            }

            // Maintenant, on récupère le bon niveau d’évaluation à partir du pourcentage
            $evaluationDetails = QcmCategoryEvaluation::with('categorie')
                ->where('idQCM', $idQCM)
                ->where('min_percentage', '<', $details->pourcentage)
                ->where('max_percentage', '>', $details->pourcentage)
                ->whereHas('categorie', function ($query) use ($categorieId) {
                    $query->where('idCategorie', $categorieId);
                })
                ->first();

            // Affectation
            $details->descriptionNiveau = $evaluationDetails->description ?? "Aucune description précise";
            $details->recommandation = $evaluationDetails->recommendations ?? "Aucune recommandation précise";

            $groupedResponses->push($details);
        }
        
        //Fin Pourcentages

        return $groupedResponses;
    }

    /**
     * Fonction pour avoir les détails des choix d'un apprenant lors d'un test dans une catégorie précise
     * 
     * @param $id (id de l'apprenant), $idQCM, $idCategorie (id de la catégorie de la réponse dans le QCM), $idSession
     */
    public function getDetailsSectionResultForAppr($id, $idQCM, $idCategorie, $idSession)
    {
        $query = DB::table('v_reponses_users_qcm')
            ->select(
                'v_reponses_users_qcm.idUtilisateur',
                'v_reponses_users_qcm.idQCM',
                'v_reponses_users_qcm.idQuestion',
                'v_reponses_users_qcm.enonce_question',
                'v_reponses_users_qcm.idReponse',
                'v_reponses_users_qcm.choosen_responses',
                'v_reponses_users_qcm.idRepCategorie',
                'v_reponses_users_qcm.points_obtenus',
                'categories_reponses.nomCategorie'
            )
            ->join('categories_reponses', 'v_reponses_users_qcm.idRepCategorie', '=', 'categories_reponses.idCategorie')
            ->where('v_reponses_users_qcm.idUtilisateur', $id)
            ->where('v_reponses_users_qcm.idQCM', $idQCM)
            ->where('v_reponses_users_qcm.idRepCategorie', $idCategorie)
            ->where('v_reponses_users_qcm.idSession', $idSession);

        $results = $query->get();

        return $results;
    }

    /**
     * Fonction relatif au diagramme en araigné pour un utilisateur pour un Qcm à une session
     * 
     * @param $id (id de l'user), $idQCM, $idSession
     */
    public function getSpiderChartData($id, $idQCM, $idSession)
    {
        // Récupérer les résultats de l'utilisateur par catégorie
        $userResults = $this->getApprDetailsResults($id, $idQCM, $idSession);

        // Récupérer le total des points obtenus par l'utilisateur
        $totalUserPoints = (float) $this->getResultApprenantPostTest($id, $idQCM, $idSession)->total_points;

        // Récupérer le total des points maximums pour le QCM depuis la vue `v_pts_max_qcm`
        $maxQCMPoints = DB::table('v_pts_max_qcm')
            ->where('idQCM', $idQCM)
            ->value('points_maximum');

        // Initialisation des données pour le diagramme en araignée
        $chartData = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Score (%)',
                    'data' => [],
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];

        // Calculer les pourcentages pour chaque section en fonction du maximum du QCM
        foreach ($userResults as $result) {
            $userPoints = (float) $result->total_points;

            // Calcul du pourcentage par rapport au total maximum du QCM
            // $percentage = $maxQCMPoints > 0 ? round(($userPoints / $maxQCMPoints) * 100, 1) : 0;
            $percentage = $maxQCMPoints > 0 ? ($userPoints / $maxQCMPoints) * 100 : 0;

            $chartData['labels'][] = $result->nomCategorie;
            $chartData['datasets'][0]['data'][] = $percentage;
        }

        // Ajouter des statistiques supplémentaires
        $scores = $chartData['datasets'][0]['data'];
        $chartData['stats'] = [
            'total_user_score' => $totalUserPoints,
            'qcm_max_score' => $maxQCMPoints,
            'average_score' => round(array_sum($scores) / count($scores), 1),
            'max_category' => [
                'name' => $chartData['labels'][array_search(max($scores), $scores)] ?? '',
                'score' => max($scores)
            ],
            'min_category' => [
                'name' => $chartData['labels'][array_search(min($scores), $scores)] ?? '',
                'score' => min($scores)
            ]
        ];

        // Ajouter des informations de debug si nécessaire
        if (config('app.debug')) {
            $chartData['debug'] = [
                'user_results' => $userResults->toArray(),
                'total_user_points' => $totalUserPoints,
                'qcm_max_points' => $maxQCMPoints
            ];
        }

        return $chartData;
    }

    /**
     * Fonction relatif au diagramme global des utilisateurs ayant participé à un Qcm (v3) nouveau 07-01-2025
     * 
     * @param $id (id du qcm), $idCtf
     */
    public function getAllApprSpiderChartData($id, $idCtf)
    {
        // Récupérer tous les utilisateurs pour ce QCM avec leurs sessions
        $users = DB::table('v_all_users_qcm')
            ->select('idUtilisateur', 'name', 'firstName', 'idEtp', 'etp_name', 'idQCM', 'idSession', 'date_session', 'total_points')
            ->where(function ($query) use ($id) {
                $query->where('idQCM', $id)
                    ->orWhereNull('idQCM');
            })
            ->where(function ($query) use ($idCtf) {
                $query->where('qcm_creator_id', $idCtf)
                    ->orWhereNull('qcm_creator_id');
            })
            ->whereNotNull('idSession') // S'assurer que nous avons une session
            ->orderBy('idUtilisateur')
            ->orderBy('date_session', 'desc')
            ->get();

        // Vérifier si nous avons des utilisateurs
        if ($users->isEmpty()) {
            return [
                'labels' => [],
                'datasets' => [],
                'overall_stats' => [
                    'total_users' => 0,
                    'total_sessions' => 0,
                    'qcm_max_score' => 0,
                    'average_total_score' => 0,
                    'best_performing_user' => null,
                    'categories_averages' => [],
                    'sessions_per_user' => []
                ]
            ];
        }

        // Récupérer le total des points maximums pour le QCM
        $maxQCMPoints = DB::table('v_pts_max_qcm')
            ->where('idQCM', $id)
            ->value('points_maximum') ?? 0;

        // Si pas de points maximum définis, retourner données vides
        if ($maxQCMPoints <= 0) {
            return [
                'error' => 'Aucun barème défini pour ce QCM',
                'labels' => [],
                'datasets' => [],
                'overall_stats' => [
                    'total_users' => $users->groupBy('idUtilisateur')->count(),
                    'total_sessions' => $users->count(),
                    'qcm_max_score' => 0,
                    'error_details' => 'Points maximum non définis pour ce QCM'
                ]
            ];
        }

        // Initialisation des données globales
        $globalChartData = [
            'labels' => [],
            'datasets' => [],
            'overall_stats' => [
                'total_users' => $users->groupBy('idUtilisateur')->count(),
                'total_sessions' => $users->count(),
                'qcm_max_score' => $maxQCMPoints,
                'average_total_score' => 0,
                'best_performing_user' => null,
                'categories_averages' => [],
                'sessions_per_user' => [],
                'errors' => [] // Pour tracer les erreurs éventuelles
            ]
        ];

        // Tableau pour stocker les scores par catégorie pour tous les utilisateurs
        $categoryScores = [];
        $colorIndex = 0;
        $validSessionsCount = 0;

        // Pour chaque utilisateur et ses sessions
        foreach ($users->groupBy('idUtilisateur') as $userId => $userSessions) {
            $userValidSessions = 0;

            // Pour chaque session de l'utilisateur
            foreach ($userSessions as $session) {
                try {
                    $userData = $this->getSpiderChartData(
                        $session->idUtilisateur,
                        $id,
                        $session->idSession
                    );

                    // Vérifier si les données sont valides
                    if (!isset($userData['datasets'][0]['data']) || empty($userData['datasets'][0]['data'])) {
                        $globalChartData['overall_stats']['errors'][] = "Données invalides pour l'utilisateur {$session->firstName} {$session->name} (Session {$session->idSession})";
                        continue;
                    }

                    $validSessionsCount++;
                    $userValidSessions++;

                    // Si c'est la première donnée valide, initialiser les labels
                    if (empty($globalChartData['labels']) && isset($userData['labels'])) {
                        $globalChartData['labels'] = $userData['labels'];
                    }

                    // Formater la date pour l'affichage
                    $sessionDate = date('d/m/Y', strtotime($session->date_session));

                    // Créer un dataset pour cette session
                    $datasetColor = $this->generateColor($colorIndex++);
                    $globalChartData['datasets'][] = [
                        'label' => "{$session->firstName} {$session->name} (Session du {$sessionDate})",
                        'data' => $userData['datasets'][0]['data'],
                        'backgroundColor' => "rgba({$datasetColor}, 0.2)",
                        'borderColor' => "rgba({$datasetColor}, 1)",
                        'borderWidth' => 1,
                        'userId' => $session->idUtilisateur,
                        'sessionId' => $session->idSession,
                        'sessionDate' => $session->date_session
                    ];

                    // Stocker les scores par catégorie pour les moyennes
                    foreach ($userData['datasets'][0]['data'] as $catIndex => $score) {
                        if (!isset($categoryScores[$catIndex])) {
                            $categoryScores[$catIndex] = [];
                        }
                        $categoryScores[$catIndex][] = $score;
                    }

                    // Mettre à jour les statistiques globales
                    if (
                        isset($userData['stats']['average_score']) &&
                        (!$globalChartData['overall_stats']['best_performing_user'] ||
                            $userData['stats']['average_score'] > $globalChartData['overall_stats']['best_performing_user']['score'])
                    ) {
                        $globalChartData['overall_stats']['best_performing_user'] = [
                            'name' => "{$session->firstName} {$session->name}",
                            'score' => $userData['stats']['average_score'],
                            'enterprise' => $session->etp_name,
                            'session_date' => $sessionDate
                        ];
                    }
                } catch (\Exception $e) {
                    $globalChartData['overall_stats']['errors'][] = "Erreur pour l'utilisateur {$session->firstName} {$session->name} (Session {$session->idSession}): " . $e->getMessage();
                    continue;
                }
            }

            // N'ajouter les statistiques que si l'utilisateur a des sessions valides
            if ($userValidSessions > 0) {
                $globalChartData['overall_stats']['sessions_per_user'][] = [
                    'name' => $userSessions->first()->firstName . ' ' . $userSessions->first()->name,
                    'sessions_count' => $userValidSessions,
                    'first_session' => date('d/m/Y', strtotime($userSessions->min('date_session'))),
                    'last_session' => date('d/m/Y', strtotime($userSessions->max('date_session')))
                ];
            }
        }

        // Vérifier si nous avons des données valides
        if ($validSessionsCount === 0) {
            return [
                'error' => 'Aucune donnée valide trouvée',
                'labels' => [],
                'datasets' => [],
                'overall_stats' => [
                    'total_users' => $users->groupBy('idUtilisateur')->count(),
                    'total_sessions' => $users->count(),
                    'valid_sessions' => 0,
                    'errors' => $globalChartData['overall_stats']['errors']
                ]
            ];
        }

        // Calculer les moyennes par catégorie (v2) avec prise en compte des nulls
        foreach ($categoryScores as $catIndex => $scores) {
            // Vérifier si l'index existe dans les labels
            if (!empty($scores) && isset($globalChartData['labels'][$catIndex])) {
                $average = round(array_sum($scores) / count($scores), 1);
                $globalChartData['overall_stats']['categories_averages'][$globalChartData['labels'][$catIndex]] = $average;
            } else {
                // Si l'index n'existe pas ou scores vides, on utilise un nom de catégorie par défaut
                $categoryName = isset($globalChartData['labels'][$catIndex]) ?
                    $globalChartData['labels'][$catIndex] :
                    'Catégorie ' . ($catIndex + 1);

                $globalChartData['overall_stats']['categories_averages'][$categoryName] = 0;
            }
        }

        // Calculer la moyenne globale de toutes les sessions valides
        if (!empty($categoryScores)) {
            $allScores = array_merge(...array_values($categoryScores));
            if (!empty($allScores)) {
                $globalChartData['overall_stats']['average_total_score'] = round(array_sum($allScores) / count($allScores), 1);
            }
        }

        // Ajouter un dataset pour la moyenne générale seulement s'il y a des données
        if (!empty($globalChartData['overall_stats']['categories_averages'])) {
            $averageDataset = [
                'label' => 'Moyenne générale',
                'data' => array_values($globalChartData['overall_stats']['categories_averages']),
                'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                'borderColor' => 'rgba(255, 99, 132, 1)',
                'borderWidth' => 2,
                'type' => 'line'
            ];
            $globalChartData['datasets'][] = $averageDataset;
        }

        return $globalChartData;
    }

    /**
     * Fonction pour avoir les résultats des employés d'une entreprise pour un qcm (v2)
     * 
     * @param $id (id du qcm), $idEtp
     */
    public function getAllResultEmpOfEtp($id, $idEtp)
    {
        $participants = DB::table('v_all_users_qcm')
            ->select('idUtilisateur', 'name', 'firstName', 'idEtp', 'etp_name', 'idQCM', 'idSession', 'date_session', 'total_points')
            ->where(function ($query) use ($id) {
                $query->where('idQCM', $id)
                    ->orWhereNull('idQCM');
            })
            ->where('idEtp', $idEtp);

        $invited = DB::table('v_all_users_qcm')
            ->select('idUtilisateur', 'name', 'firstName', 'idEtp', 'etp_name', DB::raw("$id as idQCM"), DB::raw("NULL as idSession"), DB::raw("NULL as date_session"), DB::raw("0 as total_points"))
            ->whereExists(function ($subquery) use ($id, $idEtp) {
                $subquery->select(DB::raw(1))
                    ->from('qcm_invitations')
                    ->whereColumn('qcm_invitations.idEmploye', 'v_all_users_qcm.idUtilisateur')
                    ->where('qcm_invitations.idQCM', $id)
                    ->where('qcm_invitations.status', "pending") # seulement les invitations en attente
                    ->where('v_all_users_qcm.idEtp', $idEtp);
            });

        $query = $participants->union($invited);


        $results = $query->get();

        // Vérifie si il y a une ou des résultats
        if ($results->isEmpty()) {
            return [
                'status' => 'error',
                'message' => 'Aucun résultat trouvé pour ce QCM.'
            ];
        }

        // Détermine le niveau de chaque apprenants après le test
        foreach ($results as $result) {
            $totalPoints = (int)$result->total_points;
            $idQCM = $result->idQCM;

            $baremeExists = DB::table('qcm_bareme')
                ->where('idQCM', $idQCM)
                ->exists();

            if ($baremeExists) {
                $level = DB::table('qcm_bareme')
                    ->where('idQCM', $idQCM)
                    ->where('minPoints', '<=', $totalPoints)
                    ->where('maxPoints', '>=', $totalPoints)
                    ->value('niveau');

                $result->niveau = $level ?: "Niveau non défini";
            } else {
                $result->niveau = "N'ayant pas participé au Test actuellement";
            }
        }

        // Compte le nombre d'apprenant
        $count_lines = count($results);

        return [
            'status' => 'success',
            'data' => $results,
            'count' => $count_lines,
        ];
    }

    /**
     * Fonction relatif au diagramme global des employés d'une entreprise ayant un Qcm
     * 
     * @param $id (id du qcm), $idEtp
     */
    public function getAllApprSpiderChartDataEmpOfEtp($id, $idEtp)
    {
        // Récupérer tous les utilisateurs pour ce QCM avec leurs sessions
        $users = DB::table('v_all_users_qcm')
            ->select('idUtilisateur', 'name', 'firstName', 'idEtp', 'etp_name', 'idQCM', 'idSession', 'date_session', 'total_points')
            ->where(function ($query) use ($id) {
                $query->where('idQCM', $id)
                    ->orWhereNull('idQCM');
            })
            ->where(function ($query) use ($idEtp) {
                $query->where('idEtp', $idEtp);
            })
            ->whereNotNull('idSession') // S'assurer que nous avons une session
            ->orderBy('idUtilisateur')
            ->orderBy('date_session', 'desc')
            ->get();

        // Vérifier si nous avons des utilisateurs
        if ($users->isEmpty()) {
            return [
                'labels' => [],
                'datasets' => [],
                'overall_stats' => [
                    'total_users' => 0,
                    'total_sessions' => 0,
                    'qcm_max_score' => 0,
                    'average_total_score' => 0,
                    'best_performing_user' => null,
                    'categories_averages' => [],
                    'sessions_per_user' => []
                ]
            ];
        }

        // Récupérer le total des points maximums pour le QCM
        $maxQCMPoints = DB::table('v_pts_max_qcm')
            ->where('idQCM', $id)
            ->value('points_maximum') ?? 0;

        // Si pas de points maximum définis, retourner données vides
        if ($maxQCMPoints <= 0) {
            return [
                'error' => 'Aucun barème défini pour ce QCM',
                'labels' => [],
                'datasets' => [],
                'overall_stats' => [
                    'total_users' => $users->groupBy('idUtilisateur')->count(),
                    'total_sessions' => $users->count(),
                    'qcm_max_score' => 0,
                    'error_details' => 'Points maximum non définis pour ce QCM'
                ]
            ];
        }

        // Initialisation des données globales
        $globalChartData = [
            'labels' => [],
            'datasets' => [],
            'overall_stats' => [
                'total_users' => $users->groupBy('idUtilisateur')->count(),
                'total_sessions' => $users->count(),
                'qcm_max_score' => $maxQCMPoints,
                'average_total_score' => 0,
                'best_performing_user' => null,
                'categories_averages' => [],
                'sessions_per_user' => [],
                'errors' => [] // Pour tracer les erreurs éventuelles
            ]
        ];

        // Tableau pour stocker les scores par catégorie pour tous les utilisateurs
        $categoryScores = [];
        $colorIndex = 0;
        $validSessionsCount = 0;

        // Pour chaque utilisateur et ses sessions
        foreach ($users->groupBy('idUtilisateur') as $userId => $userSessions) {
            $userValidSessions = 0;

            // Pour chaque session de l'utilisateur
            foreach ($userSessions as $session) {
                try {
                    $userData = $this->getSpiderChartData(
                        $session->idUtilisateur,
                        $id,
                        $session->idSession
                    );

                    // Vérifier si les données sont valides
                    if (!isset($userData['datasets'][0]['data']) || empty($userData['datasets'][0]['data'])) {
                        $globalChartData['overall_stats']['errors'][] = "Données invalides pour l'utilisateur {$session->firstName} {$session->name} (Session {$session->idSession})";
                        continue;
                    }

                    $validSessionsCount++;
                    $userValidSessions++;

                    // Si c'est la première donnée valide, initialiser les labels
                    if (empty($globalChartData['labels']) && isset($userData['labels'])) {
                        $globalChartData['labels'] = $userData['labels'];
                    }

                    // Formater la date pour l'affichage
                    $sessionDate = date('d/m/Y', strtotime($session->date_session));

                    // Créer un dataset pour cette session
                    $datasetColor = $this->generateColor($colorIndex++);
                    $globalChartData['datasets'][] = [
                        'label' => "{$session->firstName} {$session->name} (Session du {$sessionDate})",
                        'data' => $userData['datasets'][0]['data'],
                        'backgroundColor' => "rgba({$datasetColor}, 0.2)",
                        'borderColor' => "rgba({$datasetColor}, 1)",
                        'borderWidth' => 1,
                        'userId' => $session->idUtilisateur,
                        'sessionId' => $session->idSession,
                        'sessionDate' => $session->date_session
                    ];

                    // Stocker les scores par catégorie pour les moyennes
                    foreach ($userData['datasets'][0]['data'] as $catIndex => $score) {
                        if (!isset($categoryScores[$catIndex])) {
                            $categoryScores[$catIndex] = [];
                        }
                        $categoryScores[$catIndex][] = $score;
                    }

                    // Mettre à jour les statistiques globales
                    if (
                        isset($userData['stats']['average_score']) &&
                        (!$globalChartData['overall_stats']['best_performing_user'] ||
                            $userData['stats']['average_score'] > $globalChartData['overall_stats']['best_performing_user']['score'])
                    ) {
                        $globalChartData['overall_stats']['best_performing_user'] = [
                            'name' => "{$session->firstName} {$session->name}",
                            'score' => $userData['stats']['average_score'],
                            'enterprise' => $session->etp_name,
                            'session_date' => $sessionDate
                        ];
                    }
                } catch (\Exception $e) {
                    $globalChartData['overall_stats']['errors'][] = "Erreur pour l'utilisateur {$session->firstName} {$session->name} (Session {$session->idSession}): " . $e->getMessage();
                    continue;
                }
            }

            // N'ajouter les statistiques que si l'utilisateur a des sessions valides
            if ($userValidSessions > 0) {
                $globalChartData['overall_stats']['sessions_per_user'][] = [
                    'name' => $userSessions->first()->firstName . ' ' . $userSessions->first()->name,
                    'sessions_count' => $userValidSessions,
                    'first_session' => date('d/m/Y', strtotime($userSessions->min('date_session'))),
                    'last_session' => date('d/m/Y', strtotime($userSessions->max('date_session')))
                ];
            }
        }

        // Vérifier si nous avons des données valides
        if ($validSessionsCount === 0) {
            return [
                'error' => 'Aucune donnée valide trouvée',
                'labels' => [],
                'datasets' => [],
                'overall_stats' => [
                    'total_users' => $users->groupBy('idUtilisateur')->count(),
                    'total_sessions' => $users->count(),
                    'valid_sessions' => 0,
                    'errors' => $globalChartData['overall_stats']['errors']
                ]
            ];
        }

        // Calculer les moyennes par catégorie
        foreach ($categoryScores as $catIndex => $scores) {
            if (!empty($scores)) {
                $average = round(array_sum($scores) / count($scores), 1);
                $globalChartData['overall_stats']['categories_averages'][$globalChartData['labels'][$catIndex]] = $average;
            }
        }

        // Calculer la moyenne globale de toutes les sessions valides
        if (!empty($categoryScores)) {
            $allScores = array_merge(...array_values($categoryScores));
            if (!empty($allScores)) {
                $globalChartData['overall_stats']['average_total_score'] = round(array_sum($allScores) / count($allScores), 1);
            }
        }

        // Ajouter un dataset pour la moyenne générale seulement s'il y a des données
        if (!empty($globalChartData['overall_stats']['categories_averages'])) {
            $averageDataset = [
                'label' => 'Moyenne générale',
                'data' => array_values($globalChartData['overall_stats']['categories_averages']),
                'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                'borderColor' => 'rgba(255, 99, 132, 1)',
                'borderWidth' => 2,
                'type' => 'line'
            ];
            $globalChartData['datasets'][] = $averageDataset;
        }

        return $globalChartData;
    }

    /**
     * Fonction pour avoir le maximum de points dans un QCM
     * 
     * @param $idQcm
     */
    public function getMaxPointsInQcm($idQcm)
    {
        $query = DB::table('v_pts_max_qcm')->where('idQcm', $idQcm)->get();
        $result = 0;

        // Vérifiez si la collection est vide
        if ($query->isEmpty()) {
            $result; // Retourne 0 si aucun résultat n'est trouvé
        } else {
            $result = $query;
        }

        // Si des résultats sont trouvés, vous pouvez retourner le maximum ou le premier résultat
        // Supposons que vous voulez le maximum des points dans la collection
        return $result; // Remplacez 'points' par le nom de la colonne qui contient les points
    }

    /**
     * Method for getting all results of all Qcm created CTF
     * 
     * @param $idCTF
     */
    public function fetchGlobalResultsQcmCtf($idCTF)
    {
        $query = DB::table('v_all_users_qcm')
            ->select('idUtilisateur', 'name', 'firstName', 'idEtp', 'etp_name', 'idQCM', 'intituleQCM', 'idSession', 'date_session', 'total_points', 'invitationId', 'campId')
            ->where(function ($query) use ($idCTF) {
                $query->where('qcm_creator_id', $idCTF);
            })
            ->where('idSession', '!=', null);

            $user = User::find($idCTF);
            /* $idQcms = Qcm::where('user_id', $idCTF)->pluck('idQCM');
            if ($user->hasRole('Formateur')) {
                $idCTF = DB::table('cfp_formateurs')
                    ->where('idFormateur', $user->id)
                    ->value('idCfp');
            }
            
            $baseQuery = SessionsQcm::with('rel_qcm_session')
                ->whereIn('idQCM', $idQcms);
            // ->where('u.qcm_creator_id', $idCTF);

            if ($user->hasRole('Formateur')) {
                $idCfp = DB::table('cfp_formateurs')
                    ->where('idFormateur', $user->id)
                    ->value('idCfp');
                
                $query = $baseQuery->join('v_apprenant_etp_alls as a', function($join) use ($idCfp) {
                    $join->on('a.idEmploye', '=', 'idUtilisateur')
                        ->where('a.idCfp', $idCfp);
                });
            } else if ($user->hasRole('Cfp')) {
                $query = $baseQuery->join('v_apprenant_etp_alls as a', function($join) use ($idCTF) {
                    $join->on('a.idEmploye', '=', 'idUtilisateur')
                        ->where('a.idCfp', $idCTF);
                });
                $query->join('users', function($join){
                    $join->on('a.idEmploye', '=', 'users.id');
                });
            } else {
                $query = $baseQuery;
            } */
            $results = $query->groupBy('idSession')->get();
            
        // Vérifie si il y a une ou des résultats
        if ($results->isEmpty()) {
            return [
                'status' => 'error',
                'message' => 'Aucun résultat trouvé pour ce QCM.'
            ];
        }   

        // Détermine le niveau de chaque apprenant après le test
        foreach ($results as $result) {
            $totalPoints = (int)$result->total_points ?? (int)$result->totalPoints;
            $idQCM = $result->idQCM;
            // $result->intituleQcm = $result->rel_qcm_session->intituleQCM || '';
            $baremeExists = DB::table('qcm_bareme')
                ->where('idQCM', $idQCM)
                ->exists();
            //Test hiverenena amin'ilay vue v_all_users_qcm
            /* if($user->hasRole('Cfp') || $user->hasRole('Formateur')) {
                $result->name = $result->emp_name;
                $result->firstname = $result->emp_firstname;
            } */
            if ($baremeExists) {
                $level_id = DB::table('qcm_bareme')
                    ->where('idQCM', $idQCM)
                    ->where('minPoints', '<=', $totalPoints)
                    ->where('maxPoints', '>=', $totalPoints)
                    ->value('id_niveau');                    
                $level = NiveauQcm::where('id', $level_id)->first();
                $result->niveau = $level ? $level->niveau : "Niveau non défini";
            } else {
                $result->niveau = "Niveau non défini";
            }
        }
        // Classement des participants selon "total_points"
        $results = $results->sortByDesc('total_points')->values(); // Trie par points décroissants

        $rank = 1; // Initialisation du rang
        $previousPoints = null; // Stocke les points du précédent participant
        $offset = 0; // Pour gérer les égalités

        foreach ($results as $key => $result) {
            $totalPoints = (int)$result->total_points;

            if ($previousPoints !== null && $totalPoints < $previousPoints) {
                // Si les points sont inférieurs à ceux du précédent, passe au rang suivant
                $rank += $offset + 1;
                $offset = 0; // Réinitialise l'offset
            } elseif ($previousPoints !== null && $totalPoints === $previousPoints) {
                // Si les points sont égaux à ceux du précédent, le rang reste le même
                $offset++;
            }

            $result->rang = $rank; // Assigne le rang actuel
            $previousPoints = $totalPoints; // Met à jour les points précédents
        }

        // Compte le nombre d'apprenant
        $count_lines = count($results);

        return [
            'status' => 'success',
            'data' => $results,
            'count' => $count_lines,
        ];
    }
    /**
     * Fonction relatif au diagramme en baton pour un utilisateur pour un Qcm à une session
     * 
     * @param $id (id de l'user), $idQCM, $idSession
     */
    public function getBarChartData($id, $idQCM, $idSession)
    {
        // Récupérer les résultats de l'utilisateur par catégorie
        $userResults = $this->getApprDetailsResults($id, $idQCM, $idSession);

        // Récupérer le total des points obtenus par l'utilisateur
        $totalUserPoints = (float) $this->getResultApprenantPostTest($id, $idQCM, $idSession)->total_points;

        // Récupérer le total des points maximums pour le QCM depuis la vue `v_pts_max_qcm`
        $maxQCMPoints = DB::table('v_pts_max_qcm')
            ->where('idQCM', $idQCM)
            ->value('points_maximum');

        // Initialisation des données pour le diagramme à barres
        $chartData = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Points obtenus',
                    'data' => [],
                    'backgroundColor' => [], // Couleurs dynamiques pour chaque barre
                    'borderColor' => [], // Bordures des barres
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Points maximaux',
                    'data' => [],
                    'backgroundColor' => [], // Couleurs pour les points maximaux
                    'borderColor' => [],
                    'borderWidth' => 1,
                    'type' => 'line' // Ligne de référence
                ]
            ]
        ];

        // Générer une palette de couleurs
        $colors = [
            ['bg' => 'rgba(54, 162, 235, 0.6)', 'border' => 'rgba(54, 162, 235, 1)'],
            ['bg' => 'rgba(255, 99, 132, 0.6)', 'border' => 'rgba(255, 99, 132, 1)'],
            ['bg' => 'rgba(75, 192, 192, 0.6)', 'border' => 'rgba(75, 192, 192, 1)'],
            ['bg' => 'rgba(255, 206, 86, 0.6)', 'border' => 'rgba(255, 206, 86, 1)'],
            ['bg' => 'rgba(153, 102, 255, 0.6)', 'border' => 'rgba(153, 102, 255, 1)']
        ];

        // Variables pour les statistiques
        $categoryScores = [];
        $categoryMaxScores = [];

        // Calculer les scores par catégorie
        foreach ($userResults as $index => $result) {
            $userPoints = (float) $result->total_points;
            $categoryMaxPoints = $maxQCMPoints > 0 ? round($userPoints, 2) : 0;

            $chartData['labels'][] = $result->nomCategorie;

            // Choisir une couleur cyclique
            $colorIndex = $index % count($colors);

            $chartData['datasets'][0]['data'][] = $userPoints;
            $chartData['datasets'][0]['backgroundColor'][] = $colors[$colorIndex]['bg'];
            $chartData['datasets'][0]['borderColor'][] = $colors[$colorIndex]['border'];

            // Ligne de référence (points maximaux)
            $chartData['datasets'][1]['data'][] = $categoryMaxPoints;
            $chartData['datasets'][1]['backgroundColor'][] = 'rgba(0,0,0,0.1)';
            $chartData['datasets'][1]['borderColor'][] = 'rgba(0,0,0,0.3)';

            $categoryScores[] = $userPoints;
            $categoryMaxScores[] = $categoryMaxPoints;
        }

        // Calculer les statistiques
        $chartData['stats'] = [
            'total_user_score' => $totalUserPoints,
            'qcm_max_score' => $maxQCMPoints,
            'average_score' => count($categoryScores) > 0 ? round(array_sum($categoryScores) / count($categoryScores), 1) : 0,
            'max_category' => [
                'name' => $chartData['labels'][array_search(max($categoryScores), $categoryScores)] ?? '',
                'score' => max($categoryScores)
            ],
            'min_category' => [
                'name' => $chartData['labels'][array_search(min($categoryScores), $categoryScores)] ?? '',
                'score' => min($categoryScores)
            ]
        ];

        // Informations de debug
        if (config('app.debug')) {
            $chartData['debug'] = [
                'user_results' => $userResults->toArray(),
                'total_user_points' => $totalUserPoints,
                'qcm_max_points' => $maxQCMPoints
            ];
        }

        return $chartData;
    }

    /**
     * Method related to global data for bar chart of all qcm of an user
     * 
     * @param $idCTF
     */
    public function getAllApprBarChartAllQcmOfCtf($idCTF)
    {
        // Récupérer tous les utilisateurs pour ce QCM avec leurs sessions
        $users = DB::table('v_all_users_qcm')
            ->select('idUtilisateur', 'name', 'firstName', 'idEtp', 'etp_name', 'idQCM', 'intituleQCM', 'idSession', 'date_session', 'total_points')
            ->where(function ($query) use ($idCTF) {
                $query->where('qcm_creator_id', $idCTF)
                    ->orWhereNull('qcm_creator_id');
            })
            ->whereNotNull('idSession') // S'assurer que nous avons une session
            ->orderBy('idUtilisateur')
            ->orderBy('date_session', 'desc')
            ->get();

        // Vérifier si nous avons des utilisateurs
        if ($users->isEmpty()) {
            return [
                'labels' => [],
                'datasets' => [],
                'overall_stats' => [
                    'total_users' => 0,
                    'total_sessions' => 0,
                    'qcm_max_score' => 0,
                    'average_total_score' => 0,
                    'categories_averages' => []
                ]
            ];
        }

        // Initialisation des données globales pour le bar chart
        $globalBarChartData = [
            'labels' => [], // Catégories uniques
            'datasets' => [],
            'overall_stats' => [
                'total_users' => $users->groupBy('idUtilisateur')->count(),
                'total_sessions' => $users->count(),
                'qcm_max_score' => 0,
                'average_total_score' => 0,
                'categories_averages' => [],
                'sessions_details' => [],
                'errors' => []
            ]
        ];

        // Tableau pour stocker les scores par catégorie pour tous les utilisateurs
        $globalCategoryScores = [];
        $uniqueCategories = [];

        // Pour chaque utilisateur et ses sessions
        foreach ($users->groupBy('idUtilisateur') as $userId => $userSessions) {
            $userSessionCount = 0;

            foreach ($userSessions as $session) {
                try {
                    // Utiliser la nouvelle fonction getBarChartData
                    $userData = $this->getBarChartData(
                        $session->idUtilisateur,
                        $session->idQCM,
                        $session->idSession
                    );

                    // Vérifier si les données sont valides
                    if (empty($userData['labels']) || empty($userData['datasets'][0]['data'])) {
                        $globalBarChartData['overall_stats']['errors'][] = "Données invalides pour l'utilisateur {$session->firstName} {$session->name} (Session {$session->idSession})";
                        continue;
                    }

                    // Initialiser les labels si pas encore fait
                    if (empty($globalBarChartData['labels'])) {
                        $globalBarChartData['labels'] = $userData['labels'];
                    }

                    // Stocker les scores par catégorie
                    foreach ($userData['labels'] as $index => $category) {
                        if (!in_array($category, $uniqueCategories)) {
                            $uniqueCategories[] = $category;
                        }

                        if (!isset($globalCategoryScores[$category])) {
                            $globalCategoryScores[$category] = [];
                        }
                        $globalCategoryScores[$category][] = $userData['datasets'][0]['data'][$index];
                    }

                    // Collecter les détails de session
                    $userSessionCount++;
                    $globalBarChartData['overall_stats']['sessions_details'][] = [
                        'user' => "{$session->firstName} {$session->name}",
                        'qcm' => $session->intituleQCM,
                        'date' => date('d/m/Y', strtotime($session->date_session)),
                        'total_score' => $userData['stats']['total_user_score'] ?? 0
                    ];
                } catch (\Exception $e) {
                    $globalBarChartData['overall_stats']['errors'][] = "Erreur pour l'utilisateur {$session->firstName} {$session->name} (Session {$session->idSession}): " . $e->getMessage();
                    continue;
                }
            }
        }

        // Préparer les datasets
        $datasets = [
            // Dataset des points obtenus
            [
                'label' => 'Moyenne points obtenus',
                'data' => [],
                'backgroundColor' => [],
                'borderColor' => [],
                'borderWidth' => 1
            ],
            // Dataset des points maximaux
            [
                'label' => 'Points maximaux',
                'data' => [],
                'backgroundColor' => [],
                'borderColor' => [],
                'borderWidth' => 2,
                'type' => 'line'
            ]
        ];

        // Couleurs prédéfinies
        $colors = [
            ['bg' => 'rgba(54, 162, 235, 0.6)', 'border' => 'rgba(54, 162, 235, 1)'],
            ['bg' => 'rgba(255, 99, 132, 0.6)', 'border' => 'rgba(255, 99, 132, 1)'],
            ['bg' => 'rgba(75, 192, 192, 0.6)', 'border' => 'rgba(75, 192, 192, 1)']
        ];

        // Calculer les moyennes par catégorie
        foreach ($uniqueCategories as $index => $category) {
            if (isset($globalCategoryScores[$category]) && !empty($globalCategoryScores[$category])) {
                $averageScore = round(array_sum($globalCategoryScores[$category]) / count($globalCategoryScores[$category]), 2);

                $colorIndex = $index % count($colors);

                $datasets[0]['data'][] = $averageScore;
                $datasets[0]['backgroundColor'][] = $colors[$colorIndex]['bg'];
                $datasets[0]['borderColor'][] = $colors[$colorIndex]['border'];

                // Points maximaux (à ajuster selon votre logique)
                $datasets[1]['data'][] = $averageScore;
                $datasets[1]['backgroundColor'][] = 'rgba(0,0,0,0.1)';
                $datasets[1]['borderColor'][] = 'rgba(0,0,0,0.3)';

                // Statistiques globales
                $globalBarChartData['overall_stats']['categories_averages'][$category] = $averageScore;
            }
        }

        // Mise à jour des labels si nécessaire
        $globalBarChartData['labels'] = $uniqueCategories;
        $globalBarChartData['datasets'] = $datasets;

        // Calculer la moyenne globale
        $allScores = array_merge(...array_values($globalCategoryScores));
        if (!empty($allScores)) {
            $globalBarChartData['overall_stats']['average_total_score'] = round(array_sum($allScores) / count($allScores), 2);
        }

        return $globalBarChartData;
    }
}
