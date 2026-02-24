<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CourseService
{
    // This service can be used to handle course-related logic
    // For example, fetching courses, creating new courses, etc.

    public function getModuleByKey($idCustomer, $key)
    {

        $modules = DB::table('mdls as M')
            ->join('modules as MD', 'MD.idModule', 'M.idModule')
            ->join('module_levels as ML', 'ML.idLevel', 'M.idLevel')
            ->select('M.moduleName', 'M.dureeJ', 'M.dureeH', 'M.idModule', 'MD.prix', 'M.module_image', 'M.module_is_complete', 'ML.module_level_name', 'M.moduleStatut')
            ->where('M.moduleName', 'like', "%$key%")
            ->where('idCustomer', $idCustomer)
            ->where('M.moduleName', '!=', 'Default module')
            ->get();

        $data = [];

        // $endpoint = config('filesystems.disks.do.url_cdn_digital');
        // $bucket = config('filesystems.disks.do.bucket');
        // $digitalOcean = $endpoint . '/' . $bucket;

        // $path = $digitalOcean . '/img/modules/';

        foreach ($modules as $module) {
            $data[] = [
                'title' => $module->moduleName,
                'prix' => $module->prix,
                'id' => $module->idModule,
                'dureeJ' => $module->dureeJ,
                'dureeH' => $module->dureeH,
                'moduleStatut' => $module->moduleStatut,
                'image' => $module->module_image,
                'module_is_complete' => $module->module_is_complete,
                'level' => $module->module_level_name,
                'totalFormed' => $this->countLearnerByModule($module->idModule)
            ];
        }

        return $data;
    }

    public function countCourse($key, $idCustomer)
    {
        return DB::table('mdls')
            ->select('idModule')
            ->where('moduleName', 'like', "%$key%")
            ->where('idCustomer', $idCustomer)
            ->where('moduleName', '!=', 'Default module')
            ->count();
        return $courses;
    }

    public function countLearnerByModule($id)
    {
        $projects = $this->getProjectByModule($id);

        $totalLearnerFormed = 0;

        foreach ($projects as $project) {
            $totalLearnerFormed += count($this->getLearnerByProject($project));
        }

        return $totalLearnerFormed;
    }

    public function getLearnerByProject($id)
    {
        return $this->checkTypesProject($id) == 1 ? $this->getLearnerByProjectIntra($id) : $this->getLearnerByProjectInter($id);
    }

    public function getLearnerByProjectInter($id)
    {
        $learner = DB::table('detail_apprenant_inters as DA')
            ->join('users as U', 'U.id', 'DA.idEmploye')
            ->join('customers as C', 'C.idCustomer', 'DA.idEtp')
            ->select('DA.idEmploye', 'U.name', 'U.firstName', 'U.photo', 'C.idCustomer', 'C.customerName')
            ->where('DA.idProjet', $id)
            ->get();

        return $learner;
    }

    public function getLearnerByProjectIntra($id)
    {
        $learner = DB::table('detail_apprenants as DA')
            ->join('users as U', 'U.id', 'DA.idEmploye')
            ->join('employes as E', 'E.idEmploye', 'DA.idEmploye')
            ->join('customers as C', 'C.idCustomer', 'E.idCustomer')
            ->select('DA.idEmploye', 'U.name', 'U.firstName', 'U.photo', 'C.idCustomer', 'C.customerName')
            ->where('DA.idProjet', $id)
            ->get();

        return $learner;
    }

    public function checkTypesProject($id)
    {
        $typeProject = DB::table('projets')
            ->select('idTypeProjet')
            ->where('idProjet', $id)
            ->first();

        return $typeProject->idTypeProjet;
    }

    public function getProjectByModule($id)
    {
        $projects = DB::table('projets')
            ->where('idModule', $id)
            ->where('dateFin', '<', now())
            ->where('project_is_trashed', 0)
            ->pluck('idProjet');

        return $projects;
    }
}
