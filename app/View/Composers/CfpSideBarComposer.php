<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CfpSideBarComposer
{

    public function compose(View $view)
    {
        $role = 'cfp';
        $idCfp = Auth::user()->id;
        $headYear = now()->format('Y');
        $projects = DB::table('v_projet_cfps')
            ->select('idProjet', 'project_reference', 'dateDebut', 'dateFin', 'module_name', 'etp_name', 'ville', 'project_status', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville')
            ->where(function ($query) use ($idCfp) {
                $query->where('idCfp', $idCfp)
                    ->orWhere('idCfp_inter', $idCfp);
            })
            ->where('headYear', $headYear)
            ->orderBy('dateDebut', 'asc')
            ->get();

        $total_number_projects = count($projects);
        $count = [];
        $projects_array = [];

        foreach ($projects as $key => $project) {
            if ($project->project_status == 'En cours') {

                $count['in_progress'][] = $project;
            } else if ($project->project_status == 'Terminé') {
                $count['finished'][] = $project;
            } else if ($project->project_status == 'Annulé') {
                $count['trashed'][] = $project;
            } else if ($project->project_status == 'Réservé') {
                $count['reserved'][] = $project;
            } else if ($project->project_status == 'Reporté') {
                $count['reported'][] = $project;
            } else if ($project->project_status == 'En préparation') {
                $count['in_preparation'][] = $project;
            } else if ($project->project_status == 'Planifié') {
                $count['planed'][] = $project;
            } else if ($project->project_status == 'Cloturé') {
                $count['closed'][] = $project; 
            }
        }
        foreach ($count as $status => $value) {
            $projects_array[$status] = collect($count[$status])->groupBy('etp_name')->toArray();
            foreach ($projects_array[$status] as $etp => $module) {
                $projects_array[$status][$etp] = collect($module)->groupBy('module_name')->toArray();
            }
        }

        return $view->with(['projects' => $projects_array, 'total_number_projects' => $total_number_projects, 'count' => $count, 'role' => $role]);
    }
}
