<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FormateurSideBarComposer
{
    public function compose(View $view)
    {
        $role = 'formateur';
        $idFormateur = Auth::user()->id;
        $headYear = now()->format('Y');
        $projects = DB::table('v_projet_form')
            ->select([
                'v_projet_form.*',
                'customers.customerName'
            ])
            ->join('customers','v_projet_form.idCfp','=','customers.idCustomer')
            ->where('idFormateur', $idFormateur)
            ->where('headYear', $headYear)
            ->orderBy('dateDebut', 'asc')
            ->get();

        $total_number_projects = count($projects);
        $count = [];
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
            }
        }

        $projects_array = [];
        foreach ($count as $status => $value) {
            $projects_array[$status] = collect($count[$status])->groupBy('customerName')->toArray();
            foreach ($projects_array[$status] as $cfp => $module) {
                $projects_array[$status][$cfp] = collect($module)->groupBy('module_name')->toArray();
            }
        }


        return $view->with(['projects' => $projects_array, 'total_number_projects' => $total_number_projects, 'count' => $count, 'role' => $role]);
    }
}
