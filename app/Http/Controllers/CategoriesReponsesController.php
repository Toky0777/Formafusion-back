<?php

namespace App\Http\Controllers;

use App\Models\CategoriesReponses;
use App\Services\Qcm\QcmNavigationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CategoriesReponsesController extends Controller
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
     * Fonction menant à l'index des Sections (v2)
     */
    public function index_categories_reponses()
    {
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté
        $all_ct_reponses = CategoriesReponses::all(); # Toutes les catégories de réponses

        return response()->json([
            'extends_containt' => $extends_containt,
            'all_ct_reponses' => $all_ct_reponses,
        ]);
    }

    /**
     * Fonction pour sauvegarder les catégories de réponses ou les sections
     * 
     * @param $request
     */
    public function store_categories_reponses(Request $request)
    {
        try {
            $request->validate([
                'nomCategorie' => 'required|string|max:255',
                'descriptionCategorie' => 'required|string'
            ]);

            CategoriesReponses::create([
                'nomCategorie' => $request->nomCategorie,
                'descriptionCategorie' => $request->descriptionCategorie
            ]);

            return response()->json([
                'status' => 200,
                'message' => "Catégorie créée avec succès"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la création de la catégorie : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fonction pour mettre à jour une catégorie de réponse ou une section
     * 
     * @param $request, $id (id de la catégorie de réponse ou section)
     */
    public function update_categories_reponses($id, Request $request)
    {
        try {
            $request->validate([
                'nomCategorie' => 'required|string|max:255',
                'descriptionCategorie' => 'required|string'
            ]);

            $categorie = CategoriesReponses::findOrFail($id);
            $categorie->update([
                'nomCategorie' => $request->nomCategorie,
                'descriptionCategorie' => $request->descriptionCategorie
            ]);

            return response()->json([
                'status' => 200,
                'message' => "Catégorie mise à jour avec succès"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la mise à jour de la catégorie : ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Fonction pour supprimer une catégorie de réponse ou une section
     * 
     * @param $id (id de la catégorie de réponse ou section)
     */
    public function delete_categories_reponses($id)
    {
        try {
            $categorie = CategoriesReponses::findOrFail($id);
            $categorie->delete();

            return response()->json([
                'status' => 200,
                'message' => "Catégorie supprimée avec succès"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la suppression de la catégorie : ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Function that search category
     * 
     * @param $request
     */
    public function search(Request $request)
    {
        $query = $request->input('query');

        $categories = CategoriesReponses::where('nomCategorie', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json($categories);
    }

    /**
     * Function that store the category if don't exist when searching
     * 
     * @param $request
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'nomCategorie' => 'required|unique:categories_reponses,nomCategorie|max:255'
        ]);

        $category = CategoriesReponses::create([
            'nomCategorie' => $validated['nomCategorie']
        ]);

        return response()->json($category);
    }
}
