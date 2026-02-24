<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmargementEmployeController extends Controller
{
    public function getProjects()
    {
        $projects = DB::table('emargements as emg')
            ->select('li.li_name','li.idLieu','emg.idProjet', 'emg.idEmploye', 'p.dateDebut as start_date', 'p.dateFin as end_date', 'p.idCustomer as idCfp', 'cst.customerName as cfp_name', 'cst.customerEmail as cfp_email', 'cst.logo as cfp_logo', 'p.idModule', 'mdls.moduleName as module_name')
            ->join('projets as p', 'emg.idProjet', 'p.idProjet')
            ->join('customers as cst', 'p.idCustomer', 'cst.idCustomer')
            ->join('mdls', 'p.idModule', 'mdls.idModule')
            ->leftJoin('salles as s', 's.idSalle', '=', 'p.idSalle')
            ->leftJoin('lieux as li', 's.idLieu', '=', 'li.idLieu')
            ->where('emg.idEmploye', auth()->user()->id)
            ->groupBy('emg.idProjet', 'emg.idEmploye', 'p.dateDebut', 'p.dateFin', 'p.idCustomer', 'cst.customerName', 'cst.customerEmail', 'cst.logo', 'p.idModule', 'mdls.moduleName')
            ->orderBy('mdls.moduleName', 'asc')
            ->get();

        if (count($projects) <= 0) {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }
        $results = [];
        foreach ($projects as $project) {

            $results[] = [
                'idProjet' => $project->idProjet,
                'idEmploye' => $project->idEmploye,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'idCfp' => $project->idCfp,
                'cfp_name' => $project->cfp_name,
                'cfp_email' => $project->cfp_email,
                'cfp_logo' => $project->cfp_logo,
                'idModule' => $project->idModule,
                'module_name' => $project->module_name,
                'li_name' => $project->li_name,
                'idLieu' => $project->idLieu,
                'pourcentage' => $this->pourcentage($project->idProjet)
                // 'percentage' => 
            ];
        }

        return response()->json([
            'status' => 200,
            'projects' => [
                'project_count' => count($projects),
                "project_items" => $results
            ]
        ], 200);
    }

    public function show($idProjet)
    {
        $project = DB::table('emargements')->where('idProjet', $idProjet)->where('idEmploye', auth()->user()->id)->first();

        if (!$project) {
            return response()->json([
                'status' => 204,
                'message' => 'projet introuvable !'
            ], 204);
        }

        $projects = DB::table('v_emargement_appr')
            ->select('idSeance', 'heureDebut as start_hour', 'heureFin as end_hour', 'idProjet', 'idEmploye', 'dateSeance as session_date', 'isPresent')
            ->where('idEmploye', auth()->user()->id)
            ->where('idProjet', $idProjet)
            ->get();

        if (count($projects) <= 0) {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }

        $results = [];
        $pourcentange =  $this->pourcentage($idProjet);
        foreach ($projects as $project) {


            $results[] = [
                'idSeance' => $project->idSeance,
                'start_hour' => $project->start_hour,
                'end_hour' => $project->end_hour,
                'idProjet' => $project->idProjet,
                'idEmploye' => $project->idEmploye,
                'session_date' => $project->session_date,
                'isPresent' => $project->isPresent,
                // 'percentage' => 
            ];
        }

        return response()->json([
            'status' => 200,
            'projects' => [
                'project_count' => count($projects),
                "project_items" => $results,
                "pourcentage" => $pourcentange
            ]
        ], 200);
    }
    public function pourcentage($idProjet)
    {
        $tab = [];
        $statuts = DB::table('emargements')
            ->where('idProjet', $idProjet)
            ->whereIn('isPresent', [0, 1, 2, 3])
            ->select('isPresent', DB::raw('COUNT(*) as count'))
            ->groupBy('isPresent')
            ->where('idEmploye', auth()->user()->id)
            ->pluck('count', 'isPresent');
        $countSeances = DB::table('seances')
            ->where('idProjet', $idProjet)
            ->count();
        $countPresent = $statuts[3] ?? 0;
        $countPartiel = $statuts[2] ?? 0;
        $countAbsent = ($statuts[1] ?? 0) + ($statuts[0] ?? 0);

        // Calcul des pourcentages
        $divide = $countSeances;
        $tab['present'] = $divide > 0 ? number_format(($countPresent / $divide) * 100, 1, ',', ' ') : 0;
        $tab['partiel'] = $divide > 0 ? number_format(($countPartiel / $divide) * 100, 1, ',', ' ') : 0;
        $tab['absent'] = $divide > 0 ? number_format(($countAbsent / $divide) * 100, 1, ',', ' ') : 0;

        return $tab;
    }
}
