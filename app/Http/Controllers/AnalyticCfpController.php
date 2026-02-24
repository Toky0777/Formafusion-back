<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Traits\AnalyticQuery;
use App\Traits\EvaluationQuery;
use App\Traits\GoalQuery;
use App\Traits\ProjectQuery;
use App\Traits\StudentQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticCfpController extends Controller
{
    use ProjectQuery;
    use StudentQuery;
    use AnalyticQuery;
    use GoalQuery;
    use EvaluationQuery;



    public function index(Request $req)
    {
        $idCfp = Customer::idCustomer();
        $current_month = date('m');

        // Bon de commande
        $current_year_bc = DB::table('v_bon_commande')
            ->where('idCfp', $idCfp)
            ->whereYear('date_bc', $req->year)
            ->count();
        $last_year_bc = DB::table('v_bon_commande')
            ->where('idCfp', $idCfp)
            ->whereYear('date_bc', $req->year - 1)
            ->count();

        // heure moyenne par projet

        $seances =  DB::table('v_seances')
            ->select(
                'idProjet',
                DB::raw('SUM(HOUR(TIMEDIFF(heureFin, heureDebut))) as total_heures')
            )
            ->whereYear('dateSeance', $req->year)
            ->groupBy('idProjet')
            ->get();

        $average_hours = $seances->avg('total_heures');

        $heures = floor($average_hours);
        $minutes = round(($average_hours - $heures) * 60);

        $formatted_average_project = sprintf('%02dh%02d', $heures, $minutes);


        // projet par année
        $current_year_project = $this->getCfpProjectsByYear(['Terminé', 'Cloturé'], $idCfp, $req->year);
        $last_year_project = $this->getCfpProjectsByYear(['Terminé', 'Cloturé'], $idCfp, $req->year - 1);

        //ca moyen par prohet
        $current_year_ca_project = round($current_year_project->avg('total_ttc'));
        $last_year_ca_project = round($last_year_project->avg('total_ttc'));


        // nombre projet du mois  
        $current_month_project = $this->getCfpProjects($current_month, ['Terminé', 'Cloturé'], $idCfp, $req->year);
        $last_month_project = $this->getCfpProjects($current_month - 1, ['Terminé', 'Cloturé'], $idCfp, $req->year);

        // leaners 
        $current_year_leaners = ($this->getStudents($current_year_project->pluck('idProjet')));
        $last_year_leaners = ($this->getStudents($last_year_project->pluck('idProjet')));

        // total YTD 
        $current_year_total_YTD = $current_year_project->sum('total_ttc');
        $last_year_total_YTD = $last_year_project->sum('total_ttc');

        // Nombre de projet
        $current_year_project_count = count($current_year_project);
        $last_year_project_count = count($last_year_project);

        // projet per customer average
        $current_projectsCustomer = $this->getProjectPerCustomer(['Terminé', 'Cloturé'], $idCfp, $req->year);
        $last_projectsCustomer = $this->getProjectPerCustomer(['Terminé', 'Cloturé'], $idCfp, $req->year - 1);

        // leaners per projet average
        $idProjet_last_year = $current_year_project->pluck('idProjet')->toArray();
        $idProjet_current_year = $last_year_project->pluck('idProjet')->toArray();

        $current_year_leaners_average = $this->getAverageLeanerByProject($idProjet_last_year);
        $last_year_leaners_average = $this->getAverageLeanerByProject($idProjet_current_year);

        $remain_months = array_slice([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12], intval($current_month) - 1);

        $prepared_and_in_progress_projects = $this->getCfpProjects($remain_months, ['En cours', 'Planifié'], $idCfp, $req->year);
        $prepared_and_in_progress_projects_by_month = $this->groupProjectsByMonth($prepared_and_in_progress_projects);

        $prices = array_fill(0, 12, 0);
        $learners = [];
        $evaluations_by_months = [];
        foreach ($prepared_and_in_progress_projects_by_month as $month => $projects) {
            $forecast_prices[$month] = collect($projects)->sum('total_ttc');
        }
        for ($i = 0; $i < 12; $i++) {
            if ($i <= $current_month - 1) {
                if (!isset($forecast_prices[$i])) {
                    $forecast_prices[$i] = 0;
                }
            } else {
                if (!isset($forecast_prices[$i])) {
                    $forecast_prices[$i] = 0;
                }
            }
            if (!isset($evaluations_by_months[$i])) {
                $evaluations_by_months[$i] = 0;
            }
            if (!isset($evaluations_labels[$i])) {
                $evaluations_labels[$i] = ['countProjects' => 0, 'countEvaluations' => 0];
            }
        }


        // currentGoal
        $current_goal = $this->getGoalByMonth($req->year);

        $project_by_month = $this->groupProjectsByMonth($current_year_project);


        foreach ($project_by_month as $month => $projects) {
            $prices[$month] = collect($projects)->sum('total_ttc');
            $learners[$month] = count($this->getStudents(collect($projects)->pluck('idProjet')));
            $evals = $this->getEvaluations(collect($projects)->pluck('idProjet'));
            $countEvaluatedProjects = count($evals);
            $array = [];
            $total = 0;
            $countEvaluations = 0;
            $average = 0;
            foreach ($evals as $key => $eval) { //by projects
                $array[$key] = $eval->groupBy('idEmploye');
                $countEvaluations += count($array[$key]);
                foreach ($array[$key] as $key => $value) { //by employee
                    $total += $value[0]->generalApreciate;
                }
                $average += $countEvaluations > 0 && $countEvaluatedProjects > 0 ? $total / $countEvaluations / $countEvaluatedProjects : 0;
            }
            $evaluations_by_months[$month] = round($average, 1);
            $evaluations_labels[$month] = ['countProjects' => $countEvaluatedProjects, 'countEvaluations' => $countEvaluations];
        }

        $group_by_idPaiement = [];
        foreach ($current_year_project as $key => $project) {
            $group_by_idPaiement[$project->idPaiement][] = $project;
        }

        $pie_data = [];
        foreach ($group_by_idPaiement as $key => $projects) {
            if ($key == 1) {
                $pie_data['Fonds Propres'] = collect($projects)->sum('total_ttc');
            } elseif ($key == 2) {
                $pie_data['FMFP'] = collect($projects)->sum('total_ttc');
            } elseif ($key == 3) {
                $pie_data['Autres'] = collect($projects)->sum('total_ttc');
            }
        }

        $pie_total = collect($pie_data)->sum();

        $pie_data_in_percentage = [];
        foreach ($pie_data as $key => $value) {
            $pie_data_in_percentage[$key] = $pie_total > 0 ? number_format(($value * 100 / collect($pie_data)->sum()), 2) : [];
        }


        ksort($learners);
        ksort($prices);
        ksort($forecast_prices);
        ksort($evaluations_by_months);
        ksort($evaluations_labels);


        $last_year_total_cost = $last_year_project->sum('total_ttc');
        $current_year_total_cost = $current_year_project->sum('total_ttc');


        $total_month_cost = $current_month_project->sum('total_ttc');
        $last_total_month_cost = $last_month_project->sum('total_ttc');

        $unique_trained = collect($current_year_leaners)->unique()->count();
        $last_unique_trained = collect($last_year_leaners)->unique()->count();

        if ($unique_trained != 0) {
            $cost_by_employee = $current_year_total_YTD / $unique_trained;
        } else {
            $cost_by_employee = 0;
        }
        if ($last_unique_trained != 0) {
            $last_cost_by_employee = $last_year_total_cost / $last_unique_trained;
        } else {
            $last_cost_by_employee = 0;
        }

        [$total_paye, $restantDuCurrentYear] = $this->calculateInvoiceTotals($req->year);
        [$total_paye, $restantDuLastYear] = $this->calculateInvoiceTotals($req->year - 1);


        return response()->json([
            'status' => 200,
            'current_year_project_count' => $current_year_project_count,
            'last_year_project_count' => $last_year_project_count,
            'learners' => $learners,
            'finished_data' => $prices,
            'current_month_project' => count($current_month_project),
            'last_month_project' => count($last_month_project),
            'current_goal' => $current_goal,
            'current_year_leaners' => count($current_year_leaners),
            'last_year_leaners' => count($last_year_leaners),
            'current_projectsCustomer' => $current_projectsCustomer,
            'last_projectsCustomer' => $last_projectsCustomer,
            'current_year_leaners_average' => $current_year_leaners_average,
            'last_year_leaners_average' => $last_year_leaners_average,
            'total_month_cost' => $total_month_cost,
            'last_total_month_cost' => $last_total_month_cost,
            'last_year_total_cost' => $last_year_total_cost,
            'current_year_total_cost' => $current_year_total_cost,
            'cost_by_employee' => round($cost_by_employee, 2),
            'last_cost_by_employee' => round($last_cost_by_employee, 2),
            'pie_data' => $pie_data_in_percentage,
            'forecast_data' => $forecast_prices,
            'current_year_bc' => $current_year_bc,
            'last_year_bc' => $last_year_bc,
            'restantDuCurrentYear' => $restantDuCurrentYear,
            'restantDuLastYear' => $restantDuLastYear,
            'current_year_ca_project' => $current_year_ca_project,
            'last_year_ca_project' => $last_year_ca_project,
            'formatted_average_project' => $formatted_average_project,
            'customers' => $this->getCfpCustomers($req->year, ['Terminé', 'Cloturé', 'Planifié'])->count(),
            'bon_commandes' => $this->getBonCommande($req->year)['totaux_bc_month'],
            'nombre_commandes_month' => $this->getBonCommande($req->year)['nombre_commandes_month'],
            'evaluations_by_months' => $evaluations_by_months,
            'evaluations_labels' => $evaluations_labels

        ]);
    }



    public function getProject($month, $year, $type)
    {
        $idCfp = Customer::idCustomer();
        $projects = [];
        $type = (int) $type;
        if ($type === 1) {
            $projects = $this->getCfpProjectInLineChart($month, ['Terminé', 'Cloturé'], $idCfp, $year);
        } else {
            $projects = $this->getProjectPrev($month, $year, 'Planifié');
        }

        return response()->json($projects, 200);
    }


    public function getLearner($month, $year)
    {

        $data = $this->getLearnerByMonth($month, $year);
        $results = [];

        foreach ($data as $key => $learner) {
            $results[$learner->customerName][$key] = $learner;
        }

        return response()->json([
            'results' => $results,
        ]);
    }

    public function getDetailsBc($month, $year)
    {
        $idCfp = Customer::idCustomer();

        $details_bc = DB::table('v_bon_commande')
            ->select('idBC', 'numero_bc', 'montant_bc', 'date_bc', 'etp_name')
            ->whereMonth('date_bc', $month)
            ->whereYear('date_bc', $year)
            ->where('idCfp', $idCfp)
            ->get();

        $total = 0;
        foreach ($details_bc as $bc) {
            $total += (float) $bc->montant_bc;
        }

        return response()->json([
            'details_bc' => $details_bc,
            'total' => $total
        ]);
    }
}
