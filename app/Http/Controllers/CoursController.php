<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CoursController extends Controller
{
    public function idCfp()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idCustomer;
    }
    public function cours(Request $request)
    {
        $idCfp = Auth::user()->id;

        // Récupération des formations
        $moduleCfp = DB::table('mdls')
            ->select('idModule', 'moduleName')
            ->where('idCustomer', $idCfp)
            ->whereNotNull('moduleName')
            ->whereNot('moduleName', 'Default module');

        $moduleSponsor = DB::table('project_sub_contracts as PSC')
            ->join('projets as P', 'P.idProjet', 'PSC.idProjet')
            ->join('mdls as M', 'M.idModule', 'P.idModule')
            ->select('M.idModule', 'M.moduleName')
            ->where('PSC.idSubContractor', $idCfp)
            ->whereNotNull('moduleName');

        $modules = $moduleCfp->union($moduleSponsor)->get();

        return response()->json([
            'cours' => $modules
        ]);
    }

    private function moduleByYearCfp($idModule, $year)
    {
        $modules = DB::table('v_union_projets')
            ->select('idProjet', 'etp_name', 'project_type', 'dateDebut', 'dateFin', 'total_ttc', 'project_status', 'ville')
            ->where(function ($query) {
                $query->where('idCfp_intra', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer());
            })
            ->whereIn('project_status', ['Terminé', 'En cours'])
            ->where('idModule', $idModule)
            ->whereYear('dateDebut', $year)
            ->get();

        return $modules;
    }

    private function moduleByYearSponsor($idModule, $year)
    {
        $idProjectSubContractors = DB::table('project_sub_contracts')
            ->where('idSubContractor', Customer::idCustomer())
            ->pluck('idProjet');

        $modules = DB::table('v_union_projets')
            ->select('idProjet', 'etp_name', 'project_type', 'dateDebut', 'dateFin', 'total_ht_sub_contractor as total_ttc', 'project_status', 'ville')
            ->whereIn('idProjet', $idProjectSubContractors)
            ->whereIn('project_status', ['Terminé', 'En cours'])
            ->where('idModule', $idModule)
            ->whereYear('dateDebut', $year)
            ->get();

        return $modules;
    }

    private function moduleByYear($idModule, $year, $index)
    {
        return ($index == 1) ? $this->moduleByYearCfp($idModule, $year) : $this->moduleByYearSponsor($idModule, $year);
    }

    public function searchByModule($idModule)
    {
        $module = DB::table('mdls as M')
            ->join('module_levels as L', 'L.idLevel', 'M.idLevel')
            ->select('M.moduleName', 'L.module_level_name')
            ->where('M.idModule', $idModule);

        if ($module->exists()) {
            $projects = $this->getProject($idModule);


            $projectsArray = is_array($projects) ? $projects : [];

            return response()->json([
                'status' => 200,
                'projects' => $projectsArray,
                'module' => $module->first()
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ], 404);
        }
    }
    private function getProject($idModule)
    {
        $module = DB::table('mdls')
            ->where('idModule', $idModule)
            ->where('idCustomer', Customer::idCustomer())
            ->exists();

        return $module ? $this->getProjectByModuleCfp($idModule) : $this->getProjectByModuleSponsor($idModule);
    }

    private function getProjectByModuleCfp($idModule)
    {
        $project_years = DB::table('v_union_projets')
            ->select(DB::raw('YEAR(dateDebut) as year'), DB::raw('1 as `index`'))
            ->where(function ($query) {
                $query->where('idCfp_intra', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer());
            })
            ->whereIn('project_status', ['Terminé', 'En cours'])
            ->where('idModule', $idModule)
            ->groupBy(DB::raw('YEAR(dateDebut)'))
            ->orderBy(DB::raw('YEAR(dateDebut)'), 'desc')
            ->get();



        $modules = [];
        foreach ($project_years as $project) {
            $modules[] = [
                'year' => $project->year,
                'modules' => $this->moduleByYear($idModule, $project->year, $project->index),
                'count_project' => count($this->moduleByYear($idModule, $project->year, $project->index))
            ];
        }

        return $modules;
    }

    private function getProjectByModuleSponsor($idModule)
    {
        $idProjectSubContractors = DB::table('project_sub_contracts')
            ->where('idSubContractor', Customer::idCustomer())
            ->pluck('idProjet');

        $project_years = DB::table('v_union_projets')
            ->select(DB::raw('YEAR(dateDebut) as year'), DB::raw('2 as `index`'))
            ->whereIn('idProjet', $idProjectSubContractors)
            ->whereIn('project_status', ['Terminé', 'En cours'])
            ->where('idModule', $idModule)
            ->groupBy(DB::raw('YEAR(dateDebut)'))
            ->orderBy(DB::raw('YEAR(dateDebut)'), 'desc')
            ->get();

        $modules = [];
        foreach ($project_years as $project) {
            $modules[] = [
                'year' => $project->year,
                'modules' => $this->moduleByYear($idModule, $project->year, $project->index),
                'count_project' => count($this->moduleByYear($idModule, $project->year, $project->index))
            ];
        }

        return $modules;
    }
}
