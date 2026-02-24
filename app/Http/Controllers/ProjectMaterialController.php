<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Traits\HasProjectMaterial;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectMaterialController extends Controller
{
    use HasProjectMaterial;

    public function getMaterialsByProject($projectId)
    {
        if (!$this->getProject($projectId)->exists()) {
            return response()->json([
                'status' => 204,
                'message' => 'Projet introuvable !'
            ], 204);
        }

        $materials = $this->getProjectMaterials($projectId);

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
                'material_items' => $materials->get()
            ]
        ], 200);
    }


    public function materialDrawer($idProjet)
    {
        $materials = DB::table('project_materials as PM')
            ->join('projets as P', 'PM.project_id', 'P.idProjet')
            ->join('mdls', 'P.idModule', 'mdls.idModule')
            ->join('materials as MTL', 'PM.material_id', 'MTL.id')
            ->select('PM.project_id', 'PM.material_id', 'MTL.name as material_name', 'MTL.stock_number', 'MTL.customer_id as cfp_id', 'PM.number', 'PM.created_at', 'P.dateDebut as project_start_date', 'P.dateFin as project_end_date', 'P.idModule as module_id', 'mdls.moduleName as module_name', 'mdls.description as module_description', 'mdls.module_image')
            ->where('P.idCustomer', Customer::idCustomer())
            ->where('PM.project_id', $idProjet);

        return response()->json([
            'status' => 200,
            'materials' => [
                'material_count' => count($materials->get()),
                'material_items' => $materials->get()
            ]
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projets,idProjet',
            'material_id' => 'required|exists:materials,id',
            'number' => 'required|numeric|min:0|max:10000'
        ]);

        $this->storeProjectMaterial($request->project_id, $request->material_id, $request->number);

        return response()->json([
            'status' => 200,
            'message' => 'Succès'
        ], 200);
    }

    public function updateNumber(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projets,idProjet',
            'material_id' => 'required|exists:materials,id',
            'number' => 'required|numeric|min:0|max:10000'
        ]);

        // Vérifie si l'association existe
        $existing = DB::table('project_materials')
            ->where('project_id', $request->project_id)
            ->where('material_id', $request->material_id)
            ->first();

        if (!$existing) {
            return response()->json([
                'status' => 404,
                'message' => 'Ce matériel n’est pas encore associé à ce projet.'
            ], 404);
        }

        DB::table('project_materials')
            ->where('project_id', $request->project_id)
            ->where('material_id', $request->material_id)
            ->update([
                'number' => $request->number,
                'updated_at' => now(),
            ]);

        return response()->json([
            'status' => 200,
            'message' => 'Quantité mise à jour avec succès.',
        ]);
    }


    public function destroy(Request $request)
    {
        $material = $this->showProjectMaterial($request->projectId, $request->materialId);

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
                'message' => 'Succès'
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
