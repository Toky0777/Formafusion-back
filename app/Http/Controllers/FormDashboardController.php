<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FormDashboardController extends Controller
{
    public function index()
    {
        $idFormateur = Auth::user()->id;
        $headYear = now()->format('Y');
        $projects = DB::table('v_projet_form')
            ->select([
                'v_projet_form.*',
                'customers.customerName'
            ])
            ->join('customers', 'v_projet_form.idCfp', '=', 'customers.idCustomer')
            ->where('idFormateur', $idFormateur)
            ->where('headYear', $headYear)
            ->orderBy('dateDebut', 'asc')
            ->get();

        $projectsAverage = DB::table('v_projet_form')
            ->select([
                'v_projet_form.*',
                'customers.customerName'
            ])
            ->join('customers', 'v_projet_form.idCfp', '=', 'customers.idCustomer')
            ->where('idFormateur', $idFormateur)
            ->orderBy('dateDebut', 'asc')
            ->get();

        $current_year_projects = DB::table('v_union_projets')
            ->select('v_union_projets.*')
            ->whereIn('idProjet', $projects->pluck('idProjet'))
            ->whereRaw("project_status COLLATE utf8mb4_unicode_ci = 'Terminé'")
            ->get();


        $project_by_month = [];
        $project = [];
        foreach ($current_year_projects as $project) {
            if (Projet::getProjectMonth($project->dateFin) == '01') {
                $project_by_month[0][] = $project;
            } elseif (Projet::getProjectMonth($project->dateFin) == '02') {
                $project_by_month[1][] = $project;
            } elseif (Projet::getProjectMonth($project->dateFin) == '03') {
                $project_by_month[2][] = $project;
            } elseif (Projet::getProjectMonth($project->dateFin) == '04') {
                $project_by_month[3][] = $project;
            } elseif (Projet::getProjectMonth($project->dateFin) == '05') {
                $project_by_month[4][] = $project;
            } elseif (Projet::getProjectMonth($project->dateFin) == '06') {
                $project_by_month[5][] = $project;
            } elseif (Projet::getProjectMonth($project->dateFin) == '07') {
                $project_by_month[6][] = $project;
            } elseif (Projet::getProjectMonth($project->dateFin) == '08') {
                $project_by_month[7][] = $project;
            } elseif (Projet::getProjectMonth($project->dateFin) == '09') {
                $project_by_month[8][] = $project;
            } elseif (Projet::getProjectMonth($project->dateFin) == '10') {
                $project_by_month[9][] = $project;
            } elseif (Projet::getProjectMonth($project->dateFin) == '11') {
                $project_by_month[10][] = $project;
            } elseif (Projet::getProjectMonth($project->dateFin) == '12') {
                $project_by_month[11][] = $project;
            }
        }

        $students = [];
        $hours_by_month = [];

        // Parcours des projets par mois pour calculer les heures et les étudiants
        foreach ($project_by_month as $key => $ps) {
            $total_hours = 0;
            $total_students = 0;

            foreach ($ps as $project) {
                $total_hours += strtotime($project->dateFin) - strtotime($project->dateDebut); // Exemple de calcul
                $total_students += $project->numberOfStudents ?? 0; // Ajoutez un champ dans la requête pour le nombre d'étudiants
            }

            $students[$key] = $this->getStudents(collect($projects)->pluck('idProjet'))->count();
            $hours_by_month[$key] = $this->getSeances(collect($current_year_projects)->pluck('idProjet'));
        }

        // Remplir les mois manquants avec des valeurs par défaut
        for ($i = 0; $i < 12; $i++) {
            if (!isset($hours_by_month[$i])) {
                $hours_by_month[$i] = 0;
            }
            if (!isset($students[$i])) {
                $students[$i] = 0;
            }
        }

        ksort($students);
        ksort($hours_by_month);


        // dd($current_year_projects);


        $chart_data = [];
        $histogram_data = [];

        foreach ($hours_by_month as $key => $d) {
            $chart_data[] = $d;
        }
        $total_students = 0;
        foreach ($students as $key => $d) {
            $total_students += $d;
            $histogram_data[] = $d;
        }

        $trainer_evaluated_projects = DB::table('eval_chauds')
            ->select('eval_chauds.*', 'type_questions.idTypeQuestion')
            ->join('questions', 'eval_chauds.idQuestion', '=', 'questions.idQuestion')
            ->join('type_questions', 'questions.idTypeQuestion', '=', 'type_questions.idTypeQuestion')
            ->whereIn('idProjet', $projectsAverage->pluck('idProjet'))
            ->where('type_questions.idTypeQuestion', 2)
            ->get();

        $prjts =  $trainer_evaluated_projects->groupBy('idProjet');
        $count_review = 0;
        foreach ($prjts as $key => $prjt) {
            $count_review += $prjt->pluck('idEmploye')->unique()->count();
        }


        $trainer_note = $trainer_evaluated_projects->sum('note');

        if ($count_review === 0) {
            $trainer_average = 0;
        } else {
            $trainer_average = floor($trainer_note / $count_review / 5 * 2) / 2;
        }


        $trainer_hours = DB::table('v_seances')
            ->join('v_union_projets', 'v_seances.idProjet', '=', 'v_union_projets.idProjet')
            ->select(DB::raw('FORMAT(SUM(TIME_TO_SEC(intervalle_raw)) / 3600, 2) AS sumHourSession'))
            ->whereIn('v_seances.idProjet', $projects->pluck('idProjet'))
            ->whereRaw("v_union_projets.project_status COLLATE utf8mb4_unicode_ci = 'Terminé'")
            ->first()->sumHourSession;

        $enpoint = "https://formafusionmg.ams3.cdn.digitaloceanspaces.com/formafusionmg";

        $projetFormCount = DB::table('v_projet_form')
            ->select('idProjet', 'dateDebut', 'idEtp', 'idFormateur', 'idParticulier', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin', 'dateFin', 'module_name', 'etp_name', 'ville', 'project_status', 'project_description', 'project_type', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name')
            ->where('idFormateur', auth()->user()->id)->count();

        $cfpformCount = DB::table('v_cfp_forms')
            ->select('idFormateur', 'idCustomer', 'initialNameCustomer', 'customerName as name', 'customerEmail', 'description', 'logo', 'idTypeCustomer', 'isActiveFormateur', 'isActiveCfp')
            ->where('idFormateur', $idFormateur)
            ->count();

        return response()->json([
            'average' => $trainer_average,
            'hours' => $trainer_hours,
            'students' => $students,
            'hours_by_month' => $hours_by_month,
            'chart_data' => $chart_data,
            'histogram_data' => $histogram_data,
            'total_students' => $total_students,
            'count_review' => $count_review,
            'enpoint' => $enpoint,
            'projetFormCount' => $projetFormCount,
            'cfpformCount' => $cfpformCount
        ]);
    }

    public function getStudents(mixed $idProjets)
    {
        if (is_countable($idProjets)) {
            $students = DB::table('v_apprenant_etp_alls')
                ->select('idEmploye')
                ->whereIn('idProjet', $idProjets)
                ->get();
        } else {
            $students = DB::table('v_apprenant_etp_alls')
                ->select('idEmploye')
                ->where('idProjet', $idProjets)
                ->get();
        }
        return $students;
    }

    public function getSeances(mixed $idProjets)
    {
        $query = DB::table('v_seances_form')
            ->select(DB::raw('FORMAT(SUM(TIME_TO_SEC(intervalle_raw)) / 3600, 2) AS sumHourSession'));

        if (is_countable($idProjets)) {
            $query->whereIn('idProjet', $idProjets);
        } else {
            $query->where('idProjet', $idProjets);
        }

        // Retourner la première valeur ou 0 si aucune séance
        return $query->pluck('sumHourSession')->first() ?? 0;
    }
}
