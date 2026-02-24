<?php

namespace App\Http\Controllers;

use App\Models\CategoriesReponses;
use App\Models\NiveauQcm;
use App\Models\Qcm;
use App\Models\QcmCategoryEvaluation;
use App\Services\Qcm\QcmNavigationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QcmCategoryEvaluationController extends Controller
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
     * Show the evaluation form for categories in a QCM (v3)
     * 
     * retrait du pourcentage globale
     * 
     * @param int $id
     * 
     * @return \Illuminate\Contracts\View\View
     */
    public function showEvaluationForm($id)
    {
        $user = auth()->user();
        $id_auth_user = $user->id;
        $extends_containt = $this->navigationService->determineLayout();
        $qcm = Qcm::findOrFail($id);
        $niveaux = NiveauQcm::all();

        // Authorization check
        if ($user->hasRole('Formateur') || $user->hasRole('Cfp') || $user->hasRole('EmployeCfp')) {
            if ($qcm->user_id !== $id_auth_user) {
                return response()->json([
                    'status' => 403,
                    'message' => 'Erreur !'
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 403,
                'message' => 'Erreur !'
            ], 403);
        }

        // Get all categories used in this QCM
        $categoriesInQcm = $this->getCategoriesUsedInQcm($id);

        // Get existing evaluations
        $evaluations = QcmCategoryEvaluation::with('niveau')->where('idQCM', $id)->get();

        // Group evaluations by category
        $groupedEvaluations = [];
        foreach ($categoriesInQcm as $category) {
            $categoryEvals = $evaluations->where('idCategorie', $category->idCategorie)->values();

            $groupedEvaluations[] = [
                'category' => $category,
                'evaluations' => $categoryEvals
            ];
        }

        return response()->json([
            'status' => 200,
            'user' => $user,
            'qcm' => $qcm,
            'niveaux' => $niveaux,
            'extends_containt' => $extends_containt,
            'groupedEvaluations' => $groupedEvaluations
        ]);
    }

    /**
     * Display evaluations for a QCM
     * 
     * @param int $idQCM
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($idQCM)
    {
        $qcm = Qcm::findOrFail($idQCM);

        // Authorization check
        if (auth()->id() !== $qcm->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get all categories used in this QCM
        $categoriesInQcm = $this->getCategoriesUsedInQcm($idQCM);

        // Get existing evaluations
        $evaluations = QcmCategoryEvaluation::where('idQCM', $idQCM)->get();

        // Group evaluations by category
        $groupedEvaluations = [];
        foreach ($categoriesInQcm as $category) {
            $categoryEvals = $evaluations->where('idCategorie', $category->idCategorie);

            $groupedEvaluations[] = [
                'category' => $category,
                'evaluations' => $categoryEvals
            ];
        }

        // Also include global evaluations (category ID = 0)
        $globalEvals = $evaluations->where('idCategorie', 0);
        $groupedEvaluations[] = [
            'category' => [
                'idCategorie' => 0,
                'nomCategorie' => 'Ensemble du QCM',
                'descriptionCategorie' => 'Évaluation globale pour tous les aspects du QCM'
            ],
            'evaluations' => $globalEvals
        ];

        return response()->json([
            'qcm' => $qcm,
            'evaluations' => $groupedEvaluations
        ]);
    }

    /**
     * Store a new evaluation
     * 
     * @param Request $request
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idQCM' => 'required|exists:qcm,idQCM',
            'idCategorie' => 'required|integer',
            'min_percentage' => 'required|numeric|min:0|max:100',
            'max_percentage' => 'required|numeric|min:0|max:100|gte:min_percentage',
            'id_niveau' => 'required|integer',
            'description' => 'required|string', 
            'recommendations' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Authorization check
        $qcm = Qcm::findOrFail($request->idQCM);
        if (auth()->id() !== $qcm->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // If it's not a global evaluation (idCategorie != 0), verify that the category exists
        if ($request->idCategorie != 0) {
            $categoryExists = CategoriesReponses::where('idCategorie', $request->idCategorie)->exists();
            if (!$categoryExists) {
                return response()->json(['error' => 'Category not found'], 404);
            }

            // Also verify this category is used in the QCM
            $categoryUsedInQcm = $this->isCategoryUsedInQcm($request->idQCM, $request->idCategorie);
            if (!$categoryUsedInQcm) {
                return response()->json(['error' => 'This category is not used in this QCM'], 422);
            }
        }

        // Check for overlapping ranges for the same category
        $overlapping = QcmCategoryEvaluation::where('idQCM', $request->idQCM)
            ->where('idCategorie', $request->idCategorie)
            ->where(function ($query) use ($request) {
                $query->whereBetween('min_percentage', [$request->min_percentage, $request->max_percentage])
                    ->orWhereBetween('max_percentage', [$request->min_percentage, $request->max_percentage])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('min_percentage', '<=', $request->min_percentage)
                            ->where('max_percentage', '>=', $request->max_percentage);
                    });
            })
            ->exists();

        if ($overlapping) {
            return response()->json(['error' => 'This percentage range overlaps with an existing evaluation'], 422);
        }

        $evaluation = QcmCategoryEvaluation::create($request->all());

        return response()->json([
            'message' => 'Evaluation created successfully',
            'evaluation' => $evaluation
        ], 201);
    }

    /**
     * Update an existing evaluation
     * 
     * @param Request $request
     * @param int $id
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'min_percentage' => 'numeric|min:0|max:100',
            'max_percentage' => 'numeric|min:0|max:100|gte:min_percentage',
            'id_niveau' => 'integer',
            'description' => 'string',
            'recommendations' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $evaluation = QcmCategoryEvaluation::findOrFail($id);

        // Authorization check
        $qcm = Qcm::findOrFail($evaluation->idQCM);
        if (auth()->id() !== $qcm->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check for overlapping ranges if percentage values are being updated
        if ($request->has('min_percentage') || $request->has('max_percentage')) {
            $min = $request->min_percentage ?? $evaluation->min_percentage;
            $max = $request->max_percentage ?? $evaluation->max_percentage;

            $overlapping = QcmCategoryEvaluation::where('idQCM', $evaluation->idQCM)
                ->where('idCategorie', $evaluation->idCategorie)
                ->where('id', '!=', $id)
                ->where(function ($query) use ($min, $max) {
                    $query->whereBetween('min_percentage', [$min, $max])
                        ->orWhereBetween('max_percentage', [$min, $max])
                        ->orWhere(function ($q) use ($min, $max) {
                            $q->where('min_percentage', '<=', $min)
                                ->where('max_percentage', '>=', $max);
                        });
                })
                ->exists();

            if ($overlapping) {
                return response()->json(['error' => 'This percentage range overlaps with an existing evaluation'], 422);
            }
        }

        $evaluation->update($request->all());

        return response()->json([
            'message' => 'Evaluation updated successfully',
            'evaluation' => $evaluation
        ]);
    }

    /**
     * Delete an evaluation
     * 
     * @param int $id
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $evaluation = QcmCategoryEvaluation::findOrFail($id);

        // Authorization check
        $qcm = Qcm::findOrFail($evaluation->idQCM);
        if (auth()->id() !== $qcm->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $evaluation->delete();

        return response()->json(['message' => 'Evaluation deleted successfully']);
    }

    /**
     * Get a specific evaluation
     * 
     * @param int $id
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $evaluation = QcmCategoryEvaluation::findOrFail($id);

        // Authorization check
        $qcm = Qcm::findOrFail($evaluation->idQCM);
        if (auth()->id() !== $qcm->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($evaluation);
    }

    /**
     * Get all categories used in a QCM's questions
     * 
     * @param int $idQCM
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getCategoriesUsedInQcm($idQCM)
    {
        return CategoriesReponses::select('categories_reponses.*')
            ->join('qcm_reponses', 'categories_reponses.idCategorie', '=', 'qcm_reponses.categorie_id')
            ->join('qcm_questions', 'qcm_reponses.idQuestion', '=', 'qcm_questions.idQuestion')
            ->where('qcm_questions.idQCM', $idQCM)
            ->distinct()
            ->get();
    }

    /**
     * Check if a category is used in a QCM
     * 
     * @param int $idQCM
     * @param int $idCategorie
     * 
     * @return bool
     */
    private function isCategoryUsedInQcm($idQCM, $idCategorie)
    {
        return CategoriesReponses::select('categories_reponses.idCategorie')
            ->join('qcm_reponses', 'categories_reponses.idCategorie', '=', 'qcm_reponses.categorie_id')
            ->join('qcm_questions', 'qcm_reponses.idQuestion', '=', 'qcm_questions.idQuestion')
            ->where('qcm_questions.idQCM', $idQCM)
            ->where('categories_reponses.idCategorie', $idCategorie)
            ->exists();
    }
}
