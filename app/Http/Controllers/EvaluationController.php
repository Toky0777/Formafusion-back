<?php

namespace App\Http\Controllers;

use App\Mail\EvaluationFroid;
use App\Models\Customer;
use App\Services\BrevoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\returnSelf;

class EvaluationController extends Controller
{

    public function getProjectEvaluations($idProjet)
    {
        try {
            \Log::info("Début getProjectEvaluations pour le projet: " . $idProjet);

            // Récupérer le projet avec les apprenants
            $projet = DB::table('v_projet_cfps')
                ->where('idProjet', $idProjet)
                ->first();

            if (!$projet) {
                \Log::warning("Projet non trouvé: " . $idProjet);
                return response()->json(['evaluations' => []]);
            }

            \Log::info("Projet trouvé, apprs: " . ($projet->apprs ?? 'null'));

            // Vérifier si apprs existe et n'est pas null
            if (!$projet->apprs || $projet->apprs === 'null' || $projet->apprs === '[]') {
                \Log::warning("Aucun apprenant trouvé pour le projet: " . $idProjet);
                return response()->json(['evaluations' => []]);
            }

            $apprenantsArray = json_decode($projet->apprs, true);

            // Vérifier le décodage JSON
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error("Erreur décodage JSON apprs: " . json_last_error_msg());
                return response()->json([
                    'error' => 'Erreur format données apprenants',
                    'message' => json_last_error_msg()
                ], 500);
            }

            \Log::info("Nombre d'apprenants décodés: " . count($apprenantsArray));

            $evaluations = [];

            foreach ($apprenantsArray as $apprenant) {
                // Vérifier la structure de l'apprenant
                if (!isset($apprenant['idEmploye'])) {
                    \Log::warning("Apprenant sans idEmploye: " . json_encode($apprenant));
                    continue;
                }

                $idEmploye = $apprenant['idEmploye'];

                \Log::info("Traitement apprenant: " . $idEmploye);

                // Vérifier si une évaluation existe
                $hasEvaluation = DB::table('eval_chauds')
                    ->where('idProjet', $idProjet)
                    ->where('idEmploye', $idEmploye)
                    ->exists();

                if ($hasEvaluation) {
                    \Log::info("Évaluation trouvée pour: " . $idEmploye);

                    // Évaluation globale
                    $evaluation = DB::table('v_evaluation_alls')
                        ->where('idProjet', $idProjet)
                        ->where('idEmploye', $idEmploye)
                        ->first(['com1', 'com2', 'temoignage', 'generalApreciate', 'idValComment']);

                    // Notes individuelles
                    $notes = DB::table('v_evaluation_alls')
                        ->where('idProjet', $idProjet)
                        ->where('idEmploye', $idEmploye)
                        ->get(['note', 'idQuestion']);

                    // Calcul de la moyenne
                    $validNotes = $notes->filter(function ($note) {
                        return is_numeric($note->note) && $note->note > 0;
                    });

                    $average = $validNotes->count() > 0
                        ? round($validNotes->avg('note'), 2)
                        : ($evaluation && is_numeric($evaluation->generalApreciate)
                            ? round($evaluation->generalApreciate, 2)
                            : 0);

                    $evaluations[$idEmploye] = [
                        'checkEval' => 1,
                        'one' => $evaluation,
                        'notes' => $notes,
                        'averageNote' => $average,
                        'apprenant' => $apprenant
                    ];
                } else {
                    \Log::info("Aucune évaluation pour: " . $idEmploye);
                    $evaluations[$idEmploye] = [
                        'checkEval' => 0,
                        'one' => null,
                        'notes' => [],
                        'averageNote' => 0,
                        'apprenant' => $apprenant
                    ];
                }
            }

            \Log::info("GetProjectEvaluations terminé - Évaluations: " . count($evaluations));

            return response()->json([
                'evaluations' => $evaluations,
                'total' => count($evaluations),
                'completed' => count(array_filter($evaluations, function ($eval) {
                    return $eval['checkEval'] === 1;
                }))
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur dans getProjectEvaluations:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'idProjet' => $idProjet
            ]);

            return response()->json([
                'error' => 'Erreur serveur',
                'message' => $e->getMessage(),
                'trace' => env('APP_DEBUG') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    public function checkEval($idProjet, $idEmploye)
    {
        try {
            // Vérifie si une évaluation existe
            $hasEvaluation = DB::table('eval_chauds')
                ->where('idProjet', $idProjet)
                ->where('idEmploye', $idEmploye)
                ->exists();

            // Récupération des types de questions (sauf idTypeQuestion = 5)
            $typeQuestions = DB::table('type_questions')
                ->where('idTypeQuestion', '<>', 5)
                ->get(['idTypeQuestion', 'typeQuestion']);

            // Récupération de toutes les questions
            $questions = DB::table('questions')
                ->get(['idQuestion', 'question', 'idTypeQuestion']);

            if ($hasEvaluation) {
                // Évaluation globale
                $evaluation = DB::table('v_evaluation_alls')
                    ->where('idProjet', $idProjet)
                    ->where('idEmploye', $idEmploye)
                    ->first(['com1', 'com2', 'temoignage', 'generalApreciate', 'idValComment']);

                // Notes individuelles
                $notes = DB::table('v_evaluation_alls')
                    ->where('idProjet', $idProjet)
                    ->where('idEmploye', $idEmploye)
                    ->get(['note', 'idQuestion']);

                // Calcul de la moyenne
                $validNotes = $notes->pluck('note')->filter(function ($n) {
                    return is_numeric($n) && $n > 0;
                });

                $average = $validNotes->count() > 0
                    ? round($validNotes->avg(), 2)
                    : ($evaluation && is_numeric($evaluation->generalApreciate) ? round($evaluation->generalApreciate, 2) : 0);

                // Examinateur(s)
                $examiner = DB::table('v_evaluation_alls')
                    ->where('idProjet', $idProjet)
                    ->where('idEmploye', $idEmploye)
                    ->distinct()
                    ->get(['idEmploye', 'name_examiner', 'firstname_examiner']);

                // Projet
                $projet = DB::table('v_projet_cfps')
                    ->where('idProjet', $idProjet)
                    ->first(['idProjet', 'project_title']);

                return response()->json([
                    'checkEval' => 1,
                    'typeQuestions' => $typeQuestions,
                    'questions' => $questions,
                    'notes' => $notes,
                    'one' => $evaluation,
                    'examiner' => $examiner,
                    'projet' => $projet,
                    'averageNote' => $average
                ]);
            }

            return response()->json([
                'checkEval' => 0,
                'typeQuestions' => $typeQuestions,
                'questions' => $questions
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur dans checkEval:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'idProjet' => $idProjet,
                'idEmploye' => $idEmploye
            ]);
            return response()->json([
                'error' => 'Erreur serveur',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function satisfaction()
    {
        return view('employes.evaluations.components');
    }

    public function store(Request $req)
    {
        $req->validate([
            'idProjet' => 'required|integer|exists:projets,idProjet',
            'com1' => 'max:255',
            'com2' => 'max:255',
            'temoignage' => 'max:255',
            'idValComment' => 'max:255',
            'generalApreciate' => 'required|integer',
            'idQuestion' => 'required',
            'eval_note' => 'required'
        ]);
        $check = DB::select("SELECT idProjet, idEmploye FROM eval_chauds WHERE idProjet = ? AND idEmploye = ? GROUP BY idProjet, idEmploye", [$req->idProjet, $req->idEmploye]);
        $checkEval = count($check);

        if ($checkEval <= 0) {
            foreach ($req->idQuestion as $key => $value) {
                $insert = DB::table('eval_chauds')->insert([
                    'idProjet' => $req->idProjet,
                    'idEmploye' => $req->idEmploye,
                    'idExaminer' => Auth::user()->id,
                    'idValComment' => $req->idValComment ? $req->idValComment : 'Pas de réponse',
                    'idQuestion' => $req->idQuestion[$key],
                    'note' => $req->eval_note[$key + 1],
                    'com1' => $req->com1 ? $req->com1 : 'Pas de réponse',
                    'com2' => $req->com2 ? $req->com2 : 'Pas de réponse',
                    'temoignage' => $req->temoignage ? $req->temoignage : 'Pas de commentaire',
                    'generalApreciate' => $req->generalApreciate,
                ]);
            }
            if ($insert) {
                return response()->json(['success' => "Succès"]);
                // return back()->with('message', 'Operation Successful !');
            } else {
                return response()->json(['error' => "Erreur lors de l'insertion des données !"]);
            }
        } else {
            $typeQuestions = DB::select("SELECT idTypeQuestion, typeQuestion FROM type_questions WHERE idTypeQuestion <> 4");
            $questions = DB::select("SELECT idQuestion, question, idTypeQuestion FROM questions");
            $notes = DB::select('select idQuestion, note from v_evaluation_alls where idProjet = ? AND idEmploye = ?', [$req->idProjet, Auth::user()->id]);
            $one = DB::select("SELECT idEmploye, com1, com2, temoignage, generalApreciate FROM v_evaluation_alls WHERE idProjet = ? AND idEmploye = ? GROUP BY com1, com2, temoignage, generalApreciate", [$req->idProjet, Auth::user()->id]);

            $project = DB::table('projets')->select('idProjet')->where('idProjet', $req->idProjet)->first();

            return response()->json([
                'typeQuestions' => $typeQuestions,
                'questions' => $questions,
                'notes' => $notes,
                'one' => $one,
                'project' => $project
            ]);
        }
    }

    // CFP
    public function evalCfp($idProjet, $idEmploye)
    {
        $evaluation = DB::table('v_evaluation_alls')
            ->select('idEmploye', 'idProjet', 'typeQuestion', 'idQuestion', 'question', 'note', 'generalApreciate')
            ->where('idProjet', $idProjet)
            ->where('idEmploye', $idEmploye)
            ->get();

        $countEvaluation = DB::table('v_evaluation_alls')
            ->select('idEmploye', 'idProjet', 'typeQuestion', 'idQuestion', 'question', 'note', 'generalApreciate')
            ->where('idProjet', $idProjet)
            ->where('idEmploye', $idEmploye)
            ->count();

        $generalAppreciate = DB::table('v_evaluation_alls')
            ->select('idEmploye', 'idProjet', 'generalApreciate')
            ->groupBy('idEmploye', 'idProjet', 'generalApreciate')
            ->where('idProjet', $idProjet)
            ->where('idEmploye', $idEmploye)
            ->first();

        $typeQuestions = DB::select("SELECT idTypeQuestion, typeQuestion FROM type_questions WHERE idTypeQuestion <> 4");
        $questions = DB::select("SELECT idQuestion, question, idTypeQuestion FROM questions");

        return response()->json([
            'evaluation' => $evaluation,
            'countEvaluation' => $countEvaluation,
            'generalAppreciate' => $generalAppreciate,
            'typeQuestions' => $typeQuestions,
            'questions' => $questions,
        ]);
    }

    // ETP
    public function evalEtp($idProjet, $idSession, $idEmploye)
    {
        $typeQuestions = DB::select("SELECT idTypeQuestion, typeQuestion FROM type_questions");
        $questions = DB::select("SELECT idQuestion, question, idTypeQuestion FROM questions");
        $checkEval = DB::select('SELECT eval_chauds.idSession, sessions.idProjet FROM eval_chauds 
            INNER JOIN sessions ON eval_chauds.idSession = sessions.idSession
            WHERE eval_chauds.idSession = ? AND sessions.idProjet = ? AND idEmploye = ? GROUP BY eval_chauds.idSession, sessions.idProjet', [$idSession, $idProjet, $idEmploye]);
        $evalChecked = count($checkEval);

        if ($evalChecked <= 0) {
            return back()->with('errorEvaluate', 'Evaluation indisponible');
        } else {
            $notes = DB::select('select idQuestion, note from v_evaluation_alls where idSession = ? and idProjet = ? AND idEmploye = ?', [$idSession, $idProjet, $idEmploye]);
            $one = DB::select("SELECT idEmploye, com1, com2, generalApreciate FROM v_evaluation_alls WHERE idSession = ? AND idProjet = ? AND idEmploye = ? GROUP BY com1, com2, generalApreciate", [$idSession, $idProjet, $idEmploye]);

            $project = DB::table('v_union_sessions')
                ->select('idProjet', 'idSession')
                ->where('idSession', $idSession)
                ->where('idProjet', $idProjet)
                ->first();

            return view('ETP.evaluations.index', compact(['questions', 'typeQuestions', 'evalChecked', 'notes', 'one', 'project']));
        }
    }

    // Formateur
    // EvaluationChaud
    public function evalForm($idProjet, $idEmploye)
    {
        $checkEval = DB::table('eval_chauds')
            ->select('idProjet', 'idEmploye')
            ->where('idProjet', $idProjet)
            ->where('idEmploye', $idEmploye)
            ->groupBy('idProjet', 'idEmploye')
            ->count();

        if ($checkEval <= 0) {
            return response()->json(['error' => 'Erreur inconnue !']);
        } else {
            $typeQuestions = DB::select("SELECT idTypeQuestion, typeQuestion FROM type_questions WHERE idTypeQuestion <> 4");
            $questions = DB::select("SELECT idQuestion, question, idTypeQuestion FROM questions");
            $evaluation = DB::select('select idQuestion, note from v_evaluation_alls where idProjet = ? AND idEmploye = ?', [$idProjet, $idEmploye]);
            $generalAppreciate = DB::select("SELECT idEmploye, com1, com2, generalApreciate FROM v_evaluation_alls WHERE idProjet = ? AND idEmploye = ? GROUP BY com1, com2, generalApreciate", [$idProjet, $idEmploye]);

            $project = DB::table('projets')->select('idProjet')->where('idProjet', $idProjet)->first();

            return response()->json([
                'typeQuestions' => $typeQuestions,
                'questions' => $questions,
                'evaluation' => $evaluation,
                'generalAppreciate' => $generalAppreciate[0],
                'project' => $project
            ]);
        }
    }

    // Eval chaud
    public function pdfForm($idProjet, $idEmploye)
    {
        $projet = DB::table('v_head_evals')
            ->select('idProjet', 'dateDebut', 'dateFin', 'ville', 'nomSalle', 'customerName', 'moduleName', 'formName', 'formFirstName', 'empName', 'empFirstName')
            ->where('idProjet', $idProjet)
            ->where('idEmploye', $idEmploye)
            ->first();

        $typeQuestions = DB::select("SELECT idTypeQuestion, typeQuestion FROM type_questions WHERE idTypeQuestion <> 4");
        $questions = DB::select("SELECT idQuestion, question, idTypeQuestion FROM questions");

        $notes = DB::select('select idQuestion, note from v_evaluation_alls where idProjet = ? AND idEmploye = ?', [$idProjet, $idEmploye]);
        $one = DB::select("SELECT idEmploye, com1, com2, generalApreciate FROM v_evaluation_alls WHERE idProjet = ? AND idEmploye = ? GROUP BY com1, com2, generalApreciate", [$idProjet, $idEmploye]);

        if (count($notes) <= 0 || count($one) <= 0) {
            return back()->with("error", "Evaluation indisponible !");
        } else {
            $pdf = Pdf::loadView('CFP.evaluations.pdf', compact(['projet', 'questions', 'typeQuestions', 'notes', 'one']))->setPaper('a4', 'portrait');

            return $pdf->download('Fiche_evaluation_chaud.pdf');
        }
    }

    public function editEval(Request $req)
    {
        // Validation
        $req->validate([
            'idProjet' => 'required|integer|exists:projets,idProjet',
            'idEmploye' => 'required|integer|exists:users,id',
            'com1' => 'nullable|string|max:255',
            'com2' => 'nullable|string|max:255',
            'temoignage' => 'nullable|string|max:500', // max 500 caractères
            'idValComment' => 'nullable|string|max:255',
            'generalApreciate' => 'required|integer',
            'idQuestion' => 'required|array',
            'idQuestion.*' => 'integer|exists:questions,idQuestion',
            'eval_note' => 'required|array',
            'eval_note.*' => 'integer|min:0|max:5'
        ]);

        try {
            // Boucle pour chaque question: mise à jour ou insertion
            foreach ($req->idQuestion as $key => $idQuestion) {
                DB::table('eval_chauds')->updateOrInsert(
                    [
                        'idProjet' => $req->idProjet,
                        'idEmploye' => $req->idEmploye,
                        'idQuestion' => $idQuestion,
                    ],
                    [
                        'idExaminer' => Auth::user()->id,
                        'idValComment' => $req->idValComment ?? 'Pas de réponse',
                        'note' => $req->eval_note[$key],
                        'com1' => $req->com1 ?? 'Pas de réponse',
                        'com2' => $req->com2 ?? 'Pas de réponse',
                        'generalApreciate' => $req->generalApreciate,
                    ]
                );
            }

            // Mise à jour du temoignage séparément pour l'ensemble du projet
            DB::table('eval_chauds')
                ->where('idProjet', $req->idProjet)
                ->where('idEmploye', $req->idEmploye)
                ->update([
                    'temoignage' => $req->temoignage ?? 'Pas de commentaire'
                ]);

            return response()->json([
                'status' => 200,
                'message' => 'Succès'
            ]);
        } catch (Exception $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }





    public function save(Request $request)
    {
        $apprenant = DB::table('eval_apprenant')->where('idEmploye', $request->idEmploye)->where('idProjet', $request->idProjet)->first();
        if ($apprenant) {
            $this->update($apprenant->id, $request->before, $request->after);
        } else {
            $this->storeEval($request->idEmploye, $request->idProjet, $request->before, $request->after);
        }
        return response()->json(['success' => 'Apprenant evalué avec succes'], 200);
    }

    private function storeEval($idEmploye, $idProjet, $before, $after)
    {
        DB::table('eval_apprenant')->insert([
            'idEmploye' => $idEmploye,
            'idProjet' => $idProjet,
            'avant' => $before,
            'apres' => $after
        ]);
    }

    private function update($id_eval, $before, $after)
    {
        DB::table('eval_apprenant')->where('id', $id_eval)->update([
            'avant' => $before,
            'apres' => $after
        ]);
    }

    public function get($idEmploye, $idProjet)
    {
        $ratings = DB::table('eval_apprenant')
            ->where('idEmploye', $idEmploye)
            ->where('idProjet', $idProjet)
            ->first(['avant', 'apres']);

        return response()->json($ratings);
    }

    // FROIDS
    public function getAllSelect($table)
    {
        $data = DB::table($table)->select('*')->get();
        return $data;
    }

    // check evaluation à froids if she is already evaluated
    public function checkEvalFroid()
    {
        $check = DB::table('eval_froids')
            ->select('idProjet', 'idEmploye')
            ->where('idEmploye', Auth::user()->id)
            ->groupBy('idProjet', 'idEmploye')
            ->pluck('idProjet');

        return $check;
    }

    // check evaluation à froids if she is already sent()
    public function checkEvalFroidSent()
    {
        $check = DB::table('eval_froid_sents')->pluck('idProjet');

        return $check;
    }

    public function index()
    {
        $typeQuestions = $this->getAllSelect('quizz_types');
        $questions = $this->getAllSelect('quizz_colds');
        $notes = $this->getAllSelect('quizz_levels');

        $projetsCollection = DB::table('v_projet_emps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'cfp_name', 'module_name', 'etp_name', 'ville', 'project_status', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'salle_name', 'salle_quartier', 'salle_code_postal', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name')
            ->where('idEmploye', Auth::user()->id)
            ->where('project_status', "Terminé")
            ->where(function ($query) {
                $query->whereIn('idProjet', $this->checkEvalFroidSent())
                    ->whereNotIn('idProjet', $this->checkEvalFroid());
            })
            ->orderBy('dateDebut', 'asc')
            ->get();

        // Transformer les opportunités en un format approprié
        $projets = $projetsCollection->map(function ($p) {
            return [
                'idProjet' => $p->idProjet,
                'dateDebut' => $this->formatDate($p->dateDebut),
                'dateFin' => $this->formatDate($p->dateFin),
                'idEtp' => $p->idEtp,
                'cfp_name' => $p->cfp_name,
                'module_name' => $p->module_name,
                'etp_name' => $p->etp_name,
                'ville' => $p->ville,
                'project_status' => $p->project_status,
                'project_description' => $p->project_description,
                'project_type' => $p->project_type,
                'paiement' => $p->paiement,
                'headDate' => $p->headDate,
                'module_image' => $p->module_image,
                'etp_logo' => $p->etp_logo,
                'etp_initial_name' => $p->etp_initial_name,
                'idCfp_inter' => $p->idCfp_inter,
                'modalite' => $p->modalite,
                'idModule' => $p->idModule,
                'project_inter_privacy' => $p->project_inter_privacy,
                'sub_name' => $p->sub_name,
                'idSubContractor' => $p->idSubContractor,
                'idCfp' => $p->idCfp,
                'cfp_name' => $p->cfp_name,
            ];
        });

        $user = DB::table('users')
            ->select('name', 'firstName')
            ->where('id', Auth::user()->id)
            ->first();

        return response()->json([
            'typeQuestions' => $typeQuestions,
            'questions' => $questions,
            'notes' => $notes,
            'projets' => $projets,
            'user' => $user
        ]);
    }

    public function storeColdEvaluation(Request $req, $idProjet)
    {
        $validate = Validator::make($req->all(), [
            'quizz_level' => 'required',
            'quizz_aspect' => 'required|min:2',
            'quizz_suggestion' => 'required|min:2',
            'global_satisfaction' => 'required|integer',
            'general_recomand' => 'required'
        ]);

        // dd($validate->validated());

        if ($validate->fails()) {
            return back()->with('error', $validate->messages());
        }

        $check = DB::table('eval_froids')
            ->select('idProjet', 'idEmploye')
            ->where('idProjet', $idProjet)
            ->where('idEmploye', Auth::user()->id)
            ->groupBy('idProjet', 'idEmploye')
            ->get();

        if (count($check) >= 1) {
            return back()->with('error', "Ré-évaluation impossible !");
        }

        try {
            DB::transaction(function () use ($req, $idProjet) {
                for ($i = 0; $i < count($req->quizz_level); $i++) {
                    DB::table('eval_froids')->insert([
                        'idProjet' => $idProjet,
                        'idEmploye' => Auth::user()->id,
                        'idQuizzCold' => $req->idQuizzCold[$i],
                        'note' => $req->quizz_level[$i],
                        'date_added' => Carbon::now(),
                        'general_aspect' => $req->quizz_aspect,
                        'general_suggestion' => $req->quizz_suggestion,
                        'general_satisfaction' => $req->global_satisfaction,
                        'general_recomand' => $req->general_recomand
                    ]);
                }

                // Notification / email ho an'ilay referent hoe "vita sady lasa ilay Evaluation"

                // Mail::to("referent-forma-fusion@test.test")->send();
            });

            return response()->json([
                'status' => 200,
                'message' => 'Succès'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }
    // Fin evaluation FROID Employes

    // Evaluation à Froid CFP
    public function indexFroid()
    {
        $projets = DB::table('v_projet_cfps')
            ->select('idProjet', 'project_reference', 'dateDebut', 'dateFin', 'project_title', 'project_status', 'ville', 'li_name', 'module_name')
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            ->distinct()
            ->where('project_status', "Terminé")
            ->where('module_name', '!=', 'Default module')
            ->orderBy('dateDebut', 'desc')
            ->get();
        $idProjet = $projets->pluck('idProjet')->toArray();
        $allEtps = $this->getAllEtpsForProjects($idProjet);

        $projectsIsSent = $this->checkEvalFroidSent()->toArray();

        $result = [];

        foreach ($projets as $projet) {
            $result[] = [
                'idProjet' => $projet->idProjet,
                'module_name' => $projet->module_name,
                'ville' => $projet->ville,
                'project_reference' => $projet->project_reference,
                'dateDebut' => $projet->dateDebut,
                'dateFin' => $projet->dateFin,
                'project_title' => $projet->project_title,
                'project_status' => $projet->project_status,
                'etps' => $allEtps[$projet->idProjet] ?? [],
                'is_sent' => in_array($projet->idProjet, $projectsIsSent)
            ];
        }

        return response()->json([
            'projects' => $result
        ]);
    }

    // entreprises

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

    public function getApprenants($idProjet)
    {
        $typeProjet = DB::table('projets')->select('idTypeProjet')->where('idProjet', $idProjet)->first();

        if (!$typeProjet) {
            return response([
                'status' => 404,
                'message' => "Projet introuvable"
            ]);
        }

        switch ($typeProjet->idTypeProjet) {
            case 1:
                $apprenants = DB::table("detail_apprenants as d")
                    ->select('idProjet', 'photo as emp_photo', 'idEmploye', 'name as emp_name', 'firstName as emp_firstname', 'email as emp_email')
                    ->join('users as u', 'u.id', 'd.idEmploye')
                    ->where('idProjet', $idProjet)
                    ->get();
                break;
            case 2:
                $apprenants = DB::table("detail_apprenant_inters as d")
                    ->select('idProjet', 'idEmploye', 'name as emp_name', 'firstName as emp_firstname', 'email as emp_email')
                    ->join('users as u', 'u.id', 'd.idEmploye')
                    ->where('idProjet', $idProjet)
                    ->get();
                break;
        }

        if (count($apprenants) <= 0) {
            return response([
                'status' => 404,
                'message' => "Aucun apprenant trouvé"
            ]);
        }

        return response([
            'status' => 200,
            'apprenants' => $apprenants
        ]);
    }



    public function sendEvaluation($idProjet)
    {
        $typeProjet = DB::table('projets')->select('idTypeProjet')->where('idProjet', $idProjet)->first();

        $entreprise = DB::table('projets as p')
            ->join('intras as itr', 'p.idProjet', 'itr.idProjet')
            ->select('itr.idEtp')
            ->where('p.idProjet', $idProjet)
            ->first();

        if (!$entreprise) {
            return response([
                'status' => 404,
                'message' => "Entreprise introuvable"
            ]);
        }


        if (!$typeProjet) {
            return response([
                'status' => 404,
                'message' => "Projet introuvable"
            ]);
        }

        switch ($typeProjet->idTypeProjet) {
            case 1:
                $apprenants = DB::table("detail_apprenants as d")
                    ->select('idProjet', 'd.idEmploye', 'name as emp_name', 'firstName as emp_firstname', 'email as emp_email', 'customerEmail as etp_email')
                    ->join('users as u', 'u.id', 'd.idEmploye')
                    ->join('employes as e', 'u.id', 'e.idEmploye')
                    ->join('customers as c', 'e.idCustomer', 'c.idCustomer')
                    ->where('idProjet', $idProjet)
                    ->get();
                break;
            case 2:
                $apprenants = DB::table("detail_apprenant_inters as d")
                    ->select('idProjet', 'd.idEmploye', 'name as emp_name', 'firstName as emp_firstname', 'email as emp_email', 'customerEmail as etp_email')
                    ->join('users as u', 'u.id', 'd.idEmploye')
                    ->join('employes as e', 'u.id', 'e.idEmploye')
                    ->join('customers as c', 'e.idCustomer', 'c.idCustomer')
                    ->where('idProjet', $idProjet)
                    ->get();
                break;
        }

        $apprenantsSansEmail = $apprenants->filter(function ($a) {
            return empty($a->emp_email);
        });

        if ($apprenantsSansEmail->count() > 0) {
            $liste = $apprenantsSansEmail->map(function ($a) {
                return $a->emp_firstname . ' ' . $a->emp_name;
            })->implode(', ');

            return response([
                'status' => 422,
                'message' => "Impossible d’envoyer l’évaluation. Il y a des apprenants qui n’ont pas d’adresse email"
            ]);
        }

        try {
            DB::transaction(function () use ($apprenants, $idProjet, $entreprise) {
                $check = DB::table('eval_froid_sents')
                    ->where('idProjet', $idProjet)
                    ->where('idEtp', $entreprise->idEtp)
                    ->count();

                if ($check <= 0) {
                    DB::table('eval_froid_sents')
                        ->insert([
                            'idProjet' => $idProjet,
                            'idEtp' => $entreprise->idEtp,
                            'date_sent' => Carbon::now(),
                            'eval_is_sent' => 1
                        ]);
                }


                // recuperer customer_name
                $idCfp = DB::table('cfp_formateurs')->where('idFormateur', Auth::user()->id)->value('idCfp');
                $customerName = DB::table('customers')->where('idCustomer', $idCfp ?: Auth::user()->id)->value('customerName');
                foreach ($apprenants as $apprenant) {
                    $htmlContent = (new EvaluationFroid($customerName))->render();

                    app(BrevoService::class)->sendEmail(
                        $apprenant->emp_email,
                        "Évaluation à froid", // Sujet
                        $htmlContent
                    );
                }

                if (isset($apprenant->etp_email)) {
                    $htmlContent = (new EvaluationFroid($customerName))->render();

                    app(BrevoService::class)->sendEmail(
                        $apprenant->etp_email,
                        "Évaluation à froid", // Sujet
                        $htmlContent
                    );
                }
            });

            return response([
                'status' => 200,
                'message' => 'Evaluation envoyée avec succès'
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 422,
                'message' => $e->getMessage()
            ]);
        }
    }

    // listes des apprenants pour chaque résultat par projets
    public function getApprenantByProjectResult($idProjet)
    {
        $apprs = DB::table('v_result_evaluation_froids')
            ->select('idProjet', 'idEmploye', 'emp_name', 'emp_firstname', 'emp_email')
            ->where('idProjet', $idProjet)
            ->groupBy('idProjet', 'idEmploye', 'emp_name', 'emp_firstname', 'emp_email')
            ->get();

        if (count($apprs) <= 0) {
            return response([
                'status' => 404,
                'message' => "Aucun apprenant trouvé !"
            ]);
        }

        return response([
            'status' => 200,
            'apprenants' => $apprs
        ]);
    }

    public function getTest($idProjet, $idEmploye)
    {
        $query = DB::table('v_result_evaluation_froids')
            ->select('idQuizzType', 'quiz_type_name', 'quizz_cold_name', 'module_name', 'projet_date_debut', 'projet_date_fin', 'emp_name', 'emp_firstname', 'etp_name', 'general_suggestion', 'general_aspect', 'general_satisfaction', 'general_recomand_libelle', 'note_libelle')
            ->where('idProjet', $idProjet)
            ->where('idEmploye', $idEmploye);

        return $query;
    }

    // listes des evaluations par apprenants
    public function apprenantEvaluationResult($idProjet, $idEmploye)
    {
        $heading = $this->getTest($idProjet, $idEmploye)->first();
        $notes = $this->getTest($idProjet, $idEmploye)->get();

        $pdf = Pdf::loadView('pdf.eval-froid-pdf', compact('heading', 'notes'))->setPaper('a4', 'portrait');

        return $pdf->download('evaluation_froid.pdf');
    }

    private function formatDate($date, $type = 'j M Y')
    {
        return Carbon::parse($date)->locale('fr')->translatedFormat($type);
    }

    public function indexAvis()
    {
        $evals = DB::table('eval_chauds')->select('idProjet', 'idEmploye');
        $idCustomer = DB::table('employes')->whereIn('idEmploye', $evals->pluck('idEmploye'))->groupBy('idCustomer')->pluck('idCustomer');

        $modules = DB::table('v_projet_cfps')
            ->select('idProjet', 'idModule', 'module_name', 'idCfp')
            ->whereIn('idProjet', $evals->pluck('idProjet'))
            ->where('idCfp', Customer::idCustomer())
            ->groupBy('idModule')
            ->orderBy('module_name', 'asc')
            ->get();

        $etps =  DB::table('v_projet_cfps')
            ->select('idEtp', 'etp_name', 'idModule')
            ->whereIn('idEtp', $idCustomer)
            ->where('idCfp', Customer::idCustomer())
            ->whereIn('idProjet', $evals->pluck('idProjet'))
            ->groupBy('idEtp', 'idModule')
            ->orderBy('etp_name', 'asc')
            ->get();

        $apprs = DB::table('users as u')
            ->select('ev.idEmploye', 'u.name', 'u.firstName', 'vpc.idModule', 'vpc.idEtp', 'ev.generalApreciate', 'ev.temoignage')
            ->leftJoin('eval_chauds as ev', 'u.id', 'ev.idEmploye')
            ->leftJoin('v_projet_cfps as vpc', 'ev.idProjet', 'vpc.idProjet')
            ->whereIn('ev.idEmploye', $evals->pluck('idEmploye'))
            ->where('vpc.idCfp', Customer::idCustomer())
            ->groupBy('ev.idEmploye', 'ev.idProjet')
            ->get();

        return response()->json([
            'modules' => $modules,
            'etps' => $etps,
            'apprs' => $apprs
        ]);
    }

    // Froid Formateur
    public function indexFroidForm()
    {
        $idFormateurs = Auth::user()->id;
        $projetCollections = DB::table('v_projet_form')
            ->select('idProjet', 'dateDebut', 'dateFin', 'project_title', 'project_status', 'etp_name', 'ville', 'module_name')
            ->where('idFormateur', $idFormateurs)
            ->where('project_status', "Terminé")
            ->where('module_name', '!=', 'Default module')
            ->orderBy('module_name', 'asc')
            ->get();

        $projets = $projetCollections->map(function ($p) {
            return [
                'idProjet' => $p->idProjet,
                'dateDebut' => $this->formatDate($p->dateDebut),
                'dateFin' => $this->formatDate($p->dateFin),
                'etp_name' => $p->etp_name,
                'module_name' => $p->module_name,
            ];
        });

        // Récupérer les ids des projets
        $projetIds = $projets->pluck('idProjet')->toArray();

        $projectResults = DB::table('v_result_evaluation_froids')
            ->select('idProjet', 'module_name', 'module_image', 'projet_date_debut', 'projet_date_fin', 'sub_name', 'etp_name')
            ->whereIn('idProjet', $projetIds)
            ->groupBy('idProjet', 'module_name', 'module_image', 'projet_date_debut', 'projet_date_fin', 'sub_name', 'etp_name')
            ->get();

        return response()->json([
            'projets' => $projets,
            'projectResults' => $projectResults
        ]);
    }

    public function indexFroidEtp()
    {
        $idEtps = Auth::user()->id;

        $projectResults = DB::table('v_result_evaluation_froids')
            ->select('idProjet', 'module_name', 'module_image', 'projet_date_debut', 'projet_date_fin', 'sub_name', 'cfp_name')
            ->where('idEtp', $idEtps)
            ->groupBy('idProjet', 'module_name', 'module_image', 'projet_date_debut', 'projet_date_fin', 'sub_name', 'cfp_name')
            ->get();

        return response()->json([
            'status' => 200,
            'projectResults' => $projectResults
        ]);
    }
}
