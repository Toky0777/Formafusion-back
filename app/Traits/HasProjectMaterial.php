<?php

namespace App\Traits;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait HasProjectMaterial
{
    public function getProject($projectId)
    {
        $project = DB::table('projets')
            ->where('idCustomer', Customer::idCustomer())
            ->where('idProjet', $projectId);

        return $project;
    }

    public function getProjectMaterials($projectId)
    {
        $materials = DB::table('project_materials as PM')
            ->join('projets as P', 'PM.project_id', 'P.idProjet')
            ->join('mdls', 'P.idModule', 'mdls.idModule')
            ->join('materials as MTL', 'PM.material_id', 'MTL.id')
            ->select('PM.project_id', 'PM.material_id', 'MTL.name as material_name', 'MTL.stock_number', 'MTL.customer_id as cfp_id', 'PM.number', 'PM.created_at', 'P.dateDebut as project_start_date', 'P.dateFin as project_end_date', 'P.idModule as module_id', 'mdls.moduleName as module_name', 'mdls.description as module_description', 'mdls.module_image')
            ->where('P.idCustomer', Customer::idCustomer())
            ->where('PM.project_id', $projectId);

        return $materials;
    }

    public function storeProjectMaterial($projectId, $materialId, $number)
    {
        DB::table('project_materials')->insert([
            'project_id' => $projectId,
            'material_id' => $materialId,
            'number' => $number,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }

    public function showProjectMaterial($projectId, $materialId)
    {
        $material = DB::table('project_materials as PM')
            ->join('projets as P', 'PM.project_id', 'P.idProjet')
            ->where('P.idCustomer', Customer::idCustomer())
            ->where('PM.project_id', $projectId)
            ->where('PM.material_id', $materialId);

        return $material;
    }

    public function isMaterialInStock($materialId): bool
    {
        $check = DB::table('materials')
            ->select('id', 'name', 'stock_number')
            ->where('customer_id', Customer::idCustomer())
            ->where('id', $materialId)
            ->first();

        // manisa stock

        // mampitovy stock sy ilay ho ampidirina any anaty projet
        return true;
    }
}
