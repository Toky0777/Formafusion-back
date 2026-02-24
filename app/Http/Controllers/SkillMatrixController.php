<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SkillMatrixController extends Controller
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
        $projects = DB::table('v_projet_cfps')
            ->select(
                'idProjet',
                'idModule',
                'module_name',
                'module_description',
                'module_image',
                'dateDebut as start_date',
                'dateFin as end_date',
                'project_status',
                'etp_name',
                'etp_logo'
            )
            ->where(function ($query) {
                $query->where('idCustomer', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            ->whereIn('project_status', ["Terminé", "Cloturé", "En cours"])
            ->where('module_name', '!=', 'Default module')
            ->orderBy('dateDebut', 'asc')
            ->get();

        if ($projects->isEmpty()) {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }

        $groupedProjects = [];

        foreach ($projects as $project) {
            $year = date('Y', strtotime($project->start_date));
            $month = date('m', strtotime($project->start_date));

            $groupedProjects[$year][$month][] = [
                'idProjet' => $project->idProjet,
                'etp_name' => $project->etp_name,
                'etp_logo' => $project->etp_logo,
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
        $project = DB::table('v_projet_cfps')
            ->select("idProjet", "project_reference", "dateDebut", "dateFin", "idModule", "idCustomer", "module_name", "project_status", "etp_name", "dateDebut as start_date", "dateFin as end_date")
            ->where('idCustomer', Customer::idCustomer())
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
            ->orderBy('users.name', 'asc');

        $learners = DB::table('detail_apprenant_inters as da')
            ->select('da.idProjet', 'da.idEmploye', 'users.name as emp_name', 'users.firstName as emp_firstname', 'users.email as emp_email', 'users.photo as emp_photo', 'emp.idCustomer as etp_id', 'cst.customerName as etp_name', 'p.idModule')
            ->join('employes as emp', 'da.idEmploye', 'emp.idEmploye')
            ->join('users', 'emp.idEmploye', 'users.id')
            ->join('customers as cst', 'emp.idCustomer', 'cst.idCustomer')
            ->join('projets as p', 'da.idProjet', 'p.idProjet')
            ->where('da.idProjet', $idProjet)
            ->orderBy('users.name', 'asc')
            ->union($learnerIntras)
            ->get();

        $skills = DB::table('projets as p')
            ->select('ms.id as skill_id', 'ms.name as skill_name', 'ms.idModule', 'p.idProjet')
            ->join('module_skills as ms', 'p.idModule', 'ms.idModule')
            ->where('p.idProjet', $idProjet)
            ->orderBy('ms.name', 'asc')
            ->get();

        if (count($learners) <= 0) {
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

    public function store(Request $req, $idProjet, $idEmploye, $skillId)
    {
        $req->validate(['skill_score_before' => 'required|numeric|min:0|max:10']);
        $skillScoreBefore = $req->skill_score_before;

        if ($req->has('skill_score_after')) {
            $req->validate(['skill_score_after' => 'required|numeric|min:0|max:10']);
            $skillScoreAfter = $req->skill_score_after;
        }

        DB::table('skill_matrix')->insert([
            'skill_score_before' => $skillScoreBefore ?? 0,
            'skill_score_after' => $skillScoreAfter ?? 0,
            'id_module_skill' => $skillId,
            'idEmploye' => $idEmploye,
            'idProjet' => $idProjet
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Succès',
        ], 200);
    }


    public function update(Request $req, $idProjet, $idEmploye, $skillId)
    {
        $req->validate(['skill_score_before' => 'required|numeric|min:0|max:10']);
        $skillScoreBefore = $req->skill_score_before;

        if ($req->has('skill_score_after')) {
            $req->validate(['skill_score_after' => 'required|numeric|min:0|max:10']);
            $skillScoreAfter = $req->skill_score_after;
        }

        DB::table('skill_matrix')
            ->where('idProjet', $idProjet)
            ->where('idEmploye', $idEmploye)
            ->where('id_module_skill', $skillId)
            ->update([
                'skill_score_before' => $skillScoreBefore ?? 0,
                'skill_score_after' => $skillScoreAfter ?? 0,
            ]);

        return response()->json([
            'status' => 200,
            'message' => 'Modifier avec succès',

        ], 200);
    }

    public function getSkillLevel($idProjet)
    {
        $skillLevels = DB::table('skill_matrix as skm')
            ->select('skm.id as skill_matrix_id', 'skm.idProjet', 'skm.skill_score_before', 'skm.skill_score_after', 'skm.id_module_skill', 'ms.name as module_skill_name', 'skm.idEmploye', 'users.name as emp_name', 'users.firstName as emp_firstname', 'users.photo as emp_photo')
            ->join('users', 'skm.idEmploye', 'users.id')
            ->join('module_skills as ms', 'skm.id_module_skill', 'ms.id')
            ->where('skm.idProjet', $idProjet)
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
