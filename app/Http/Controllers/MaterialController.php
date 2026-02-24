<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HandlesMaterial;
use Exception;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    use HandlesMaterial;

    public function index()
    {
        $materials = $this->getMaterials();

        if (count($materials->get()) <= 0) {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }

        return response()->json([
            'status' => 200,
            'materials' => [
                'material_count' => count($materials->get()),
                'material_items' => $materials->paginate(15)
            ]
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:2|max:200',
            'stock_number' => 'required|numeric|min:0|max:10000',
            'description' => 'nullable',
            'material_type_id' => 'required|exists:material_types,id'
        ]);

        $materialId = $this->storeMaterial(
            $request->name,
            $request->description,
            $request->stock_number,
            $request->material_type_id
        );

        return response()->json([
            'status' => 200,
            'message' => 'Ajouté avec succès',
            'material_id' => $materialId
        ], 200);
    }

    public function show($materialId)
    {
        $material = $this->showMaterial($materialId);

        if (!$material->exists()) {
            return response()->json([
                'status' => 204,
                'message' => 'Introuvable !'
            ], 204);
        }

        return response()->json([
            'status' => 200,
            'material' => $material->first()
        ], 200);
    }

    public function update(Request $request, $materialId)
    {
        $request->validate([
            'name' => 'required|min:2|max:200',
            'stock_number' => 'required|numeric|min:0|max:10000',
            'description' => 'nullable',
            'material_type_id' => 'required|exists:material_types,id'
        ]);

        if (!$this->showMaterial($materialId)->exists()) {
            return response()->json([
                'status' => 204,
                'message' => 'Introuvable !'
            ], 204);
        }

        try {
            $this->updateMaterial(
                $materialId,
                $request->name,
                $request->description,
                $request->stock_number,
                $request->material_type_id
            );

            return response()->json([
                'status' => 200,
                'message' => 'Modifié avec succès'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Modification impossible !',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($materialId)
    {
        $material = $this->showMaterial($materialId);

        if (!$material->exists()) {
            return response()->json([
                'status' => 204,
                'message' => 'Introuvable !'
            ], 204);
        }

        try {
            $material->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Supprimé'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Suppréssion impossible !',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
