<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SkillMatrixEmployeController extends Controller
{
    private function getLearnerCounts($idProjet)
    {
        $learnerIntras = DB::table('detail_apprenants')->where('idProjet', $idProjet)->count();
        $learnerInters = DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->count();
        $learners = $learnerIntras + $learnerInters;

        return $learners;
    }

    public function index()
    {
        $projects = DB::table('v_projet_emps')
            ->select('idProjet', 'idModule', 'module_name', 'module_description', 'module_image', 'dateDebut as start_date', 'dateFin as end_date', 'project_status')
            ->where('idEmploye', auth()->user()->id)
            ->whereIn('project_status', ["Terminé", "Cloturé", "En cours"])
            ->orderBy('project_name', 'asc')
            ->get();

        if (count($projects) <= 0) {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }

        $groupedProjects = [];

        foreach ($projects as $project) {
            $year = date('Y', strtotime($project->start_date));
            $month = date('m', strtotime(($project->start_date)));

            $groupedProjects[$year][$month][] = [
                'idProjet' => $project->idProjet,
                'idModule' => $project->idModule,
                'module_name' => $project->module_name,
                'module_description' => $project->module_description,
                'module_image' => $project->module_image,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'project_status' => $project->project_status,
                'learner_count' => $this->getLearnerCounts($project->idProjet),
            ];
        }

        return response()->json([
            'status' => 200,
            'projects' => $groupedProjects
        ], 200);
    }

    public function show($idProjet)
    {
        $project = DB::table('v_projet_emps')
            ->select('idProjet', 'idModule', 'module_name', 'module_description', 'module_image', 'dateDebut as start_date', 'dateFin as end_date', 'project_status', 'cfp_name')
            ->where('idEmploye', auth()->user()->id)
            ->where('idProjet', $idProjet)
            ->first();

        if (!$project) {
            return response()->json([
                'status' => 404,
                'message' => 'Projet introuvable !'
            ], 404);
        }

        $learnerIntras = DB::table('detail_apprenants as da')
            ->select('da.idProjet', 'da.idEmploye', 'users.name as emp_name', 'users.firstName as emp_firstname', 'users.email as emp_email', 'users.photo as emp_photo', 'emp.idCustomer as etp_id', 'cst.customerName as etp_name', 'p.idModule')
            ->join('employes as emp', 'da.idEmploye', 'emp.idEmploye')
            ->join('users', 'emp.idEmploye', 'users.id')
            ->join('customers as cst', 'emp.idCustomer', 'cst.idCustomer')
            ->join('projets as p', 'da.idProjet', 'p.idProjet')
            ->where('da.idProjet', $idProjet)
            ->where('da.idEmploye', auth()->user()->id)
            ->orderBy('users.name', 'asc');

        $learners = DB::table('detail_apprenant_inters as da')
            ->select('da.idProjet', 'da.idEmploye', 'users.name as emp_name', 'users.firstName as emp_firstname', 'users.email as emp_email', 'users.photo as emp_photo', 'emp.idCustomer as etp_id', 'cst.customerName as etp_name', 'p.idModule')
            ->join('employes as emp', 'da.idEmploye', 'emp.idEmploye')
            ->join('users', 'emp.idEmploye', 'users.id')
            ->join('customers as cst', 'emp.idCustomer', 'cst.idCustomer')
            ->join('projets as p', 'da.idProjet', 'p.idProjet')
            ->where('da.idProjet', $idProjet)
            ->where('da.idEmploye', auth()->user()->id)
            ->orderBy('users.name', 'asc')
            ->union($learnerIntras)
            ->get();

        $skills = DB::table('projets as p')
            ->select('ms.id as skill_id', 'ms.name as skill_name', 'ms.idModule', 'p.idProjet')
            ->join('module_skills as ms', 'p.idModule', 'ms.idModule')
            ->where('p.idProjet', $idProjet)
            ->orderBy('ms.name', 'asc')
            ->get();

        if (count($learners) <= 0 && count($skills) <= 0) {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }

        return response()->json([
            'status' => 200,
            'project' => $project,
            'learners' => [
                'learner_count' => count($learners),
                'learner_items' => $learners
            ],
            'skills' => [
                'skill_count' => count($skills),
                'skill_items' => $skills
            ]
        ], 200);
    }

    public function getSkillLevel($idProjet)
    {
        $skillLevels = DB::table('skill_matrix as skm')
            ->select('skm.id as skill_matrix_id', 'skm.idProjet', 'skm.skill_score_before', 'skm.skill_score_after', 'skm.id_module_skill', 'ms.name as module_skill_name', 'skm.idEmploye', 'users.name as emp_name', 'users.firstName as emp_firstname', 'users.photo as emp_photo')
            ->join('users', 'skm.idEmploye', 'users.id')
            ->join('module_skills as ms', 'skm.id_module_skill', 'ms.id')
            ->where('skm.idProjet', $idProjet)
            ->where('skm.idEmploye', auth()->user()->id)
            ->orderBy('skm.idEmploye', 'asc')
            ->get();

        if (count($skillLevels) <= 0) {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }

        return response()->json([
            'status' => 200,
            'skill_levels' => $skillLevels
        ], 200);
    }
}
