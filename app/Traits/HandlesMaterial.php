<?php

namespace App\Traits;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait HandlesMaterial
{
    public function getMaterials()
    {
        $materials = DB::table('materials')
            ->select('id', 'name', 'stock_number', 'description', 'customer_id', 'material_type_id', 'created_at')
            ->where('customer_id', Customer::idCustomer());

        return $materials;
    }

    public function storeMaterial(
        $name,
        $description = null,
        $stockNumber,
        $type
    ) {
        return DB::table('materials')->insertGetId([
            'name' => $name,
            'stock_number' => $stockNumber,
            'description' => $description,
            'material_type_id' => $type,
            'customer_id' => Customer::idCustomer(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }

    public function showMaterial($materialId)
    {
        $material = $this->getMaterials()->where('id', $materialId);

        return $material;
    }

    public function updateMaterial(
        $materialId,
        $name,
        $description = null,
        $stockNumber,
        $type
    ) {
        DB::table('materials')->where('id', $materialId)->update([
            'name' => $name,
            'stock_number' => $stockNumber,
            'description' => $description,
            'material_type_id' => $type,
            'updated_at' => Carbon::now()
        ]);
    }
}
