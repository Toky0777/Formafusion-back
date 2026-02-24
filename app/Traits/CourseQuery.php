<?php

namespace App\Traits;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

trait CourseQuery
{
    public function getCourse($key)
    {
        $courses = DB::table('mdls as M')
            ->join('modules as MD', 'MD.idModule', 'M.idModule')
            ->join('module_levels as ML', 'ML.idLevel', 'M.idLevel')
            ->select('M.moduleName', 'M.dureeJ', 'M.dureeH', 'M.idModule', 'MD.prix', 'M.module_image', 'M.module_is_complete', 'ML.module_level_name', 'M.moduleStatut')
            ->where('M.moduleName', 'like', "%$key%")
            ->where('idCustomer', Customer::idCustomer())
            ->get();
        return $courses;
    }
}