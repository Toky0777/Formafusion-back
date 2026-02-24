<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Traits\EvaluationQuery;
use App\Traits\GoalQuery;
use App\Traits\ProjectQuery;
use App\Traits\StudentQuery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CfpDashboardController
{
    use ProjectQuery;
    use StudentQuery;
    use EvaluationQuery;
    use GoalQuery;


    private function getAllFactureProfo()
    {
        $invoicesProfoAll = DB::table('invoices')
            ->select('idInvoice', 'invoice_status', 'invoice_total_amount')
            ->where('idTypeFacture', 2)
            ->get();

        $invoicesProfoConvertis = DB::table('invoices')
            ->select('idInvoice', 'invoice_status', 'invoice_total_amount')
            ->where('idTypeFacture', 2)
            ->where('invoice_status', 7)
            ->get();

        $total_invoiceProfoAll = $invoicesProfoAll->sum('invoice_total_amount');
        $total_invoiceProfoConvertis = $invoicesProfoConvertis->sum('invoice_total_amount');

        $difference = $total_invoiceProfoAll - $total_invoiceProfoConvertis;

        $converted_rate = $total_invoiceProfoAll > 0
            ? ($total_invoiceProfoConvertis / $total_invoiceProfoAll) * 100
            : 0;

        $remaining_rate = $total_invoiceProfoAll > 0
            ? ($difference / $total_invoiceProfoAll) * 100
            : 0;

        return [
            'total_invoiceProfoAll' => $total_invoiceProfoAll,
            'total_invoiceProfoConvertis' => $total_invoiceProfoConvertis,
            'difference' => $difference,
            "converted_rate" => round($converted_rate, 2),
            "remaining_rate" => round($remaining_rate, 2)
        ];
    }



    public function index()
    {
        $idCfp = Auth::user()->id;
        $current_month = date('m');

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $months_b = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        $remain_months = array_slice([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12], intval($current_month) - 1);

        $current_month_projects = $this->getCfpProjects($current_month, ['Terminé', 'Cloturé'], $idCfp);

        if ($current_month == "01") {
            $last_year_current_month_projects = $this->getCfpProjects(12, ['Terminé', 'Cloturé'], $idCfp, date('Y') - 1);
        } else {
            $last_year_current_month_projects = $this->getCfpProjects($current_month - 1, ['Terminé', 'Cloturé'], $idCfp, date('Y'));
        }


        $total_cost = $current_month_projects->sum('total_ttc');
        $last_year_total_cost = $last_year_current_month_projects->sum('total_ttc');


        $current_year_projects = $this->getCfpProjectsByYear(['Terminé', 'Cloturé'], $idCfp);

        $last_year_projects = $this->getCfpProjectsByYear(['Terminé', 'Cloturé'], $idCfp, date('Y') - 1);

        $group_by_idPaiement = [];
        foreach ($current_year_projects as $key => $project) {
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
        //  dd($pie_data);
        $pie_total = collect($pie_data)->sum();

        $pie_data_in_percentage = [];
        foreach ($pie_data as $key => $value) {
            $pie_data_in_percentage[$key] = $pie_total > 0 ? number_format(($value * 100 / collect($pie_data)->sum()), 2) : [];
        }


        $prepared_and_in_progress_projects = $this->getCfpProjects($remain_months, ['En cours', 'Planifié'], $idCfp);
        $total_YTD = $current_year_projects->sum('total_ttc');

        $apprenants = $this->getStudents($current_year_projects->pluck('idProjet'));

        $last_year_apprenants = $this->getStudents($last_year_projects->pluck('idProjet'));

        $total_trained = count($apprenants);
        $last_total_trained = count($last_year_apprenants);

        $unique_trained = collect($apprenants)->unique()->count();
        $last_unique_trained = collect($last_year_apprenants)->unique()->count();

        if ($unique_trained != 0) {
            $cost_by_employee = $total_YTD / $unique_trained;
        } else {
            $cost_by_employee = 0;
        }
        if ($last_unique_trained != 0) {
            $last_cost_by_employee = $last_year_total_cost / $last_unique_trained;
        } else {
            $last_cost_by_employee = 0;
        }

        $project_by_month = $this->groupProjectsByMonth($current_year_projects);
        $last_year_projects_by_month = $this->groupProjectsByMonth($last_year_projects);

        $prepared_and_in_progress_projects_by_month = $this->groupProjectsByMonth($prepared_and_in_progress_projects);

        $prices = [];
        $students = [];
        $evaluations_by_months = [];
        //$forcast_prices = [];

        foreach ($project_by_month as $month => $projects) {
            $prices[$month] = collect($projects)->sum('total_ttc');
            $students[$month] = count($this->getStudents(collect($projects)->pluck('idProjet')));
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
            $evaluations_by_months[$month] =  $average;
            $evaluations_labels[$month] = ['countProjects' => $countEvaluatedProjects, 'countEvaluations' => $countEvaluations];
        }

        foreach ($last_year_projects_by_month as $month => $projects) {
            $last_year_prices[$month] = collect($projects)->sum('total_ttc');
            $lastStudents[$month] = count($this->getStudents(collect($projects)->pluck('idProjet'))) ?? 0;
        }

        foreach ($prepared_and_in_progress_projects_by_month as $month => $projects) {
            $forecast_prices[$month] = collect($projects)->sum('total_ttc');
        }
        for ($i = 0; $i < 12; $i++) {
            if ($i <= $current_month - 1) {
                if (!isset($prices[$i])) {
                    $prices[$i] = 0;
                }
                if (!isset($forecast_prices[$i])) {
                    $forecast_prices[$i] = 0;
                }
            } else {
                if (!isset($forecast_prices[$i])) {
                    $forecast_prices[$i] = 0;
                }
            }
            if (!isset($students[$i])) {
                $students[$i] = 0;
            }
            if (!isset($lastStudents[$i])) {
                $lastStudents[$i] = 0;
            }
            if (!isset($last_year_prices[$i])) {
                $last_year_prices[$i] = 0;
            }
            if (!isset($evaluations_by_months[$i])) {
                $evaluations_by_months[$i] = 0;
            }
            if (!isset($evaluations_labels[$i])) {
                $evaluations_labels[$i] = ['countProjects' => 0, 'countEvaluations' => 0];
            }
        }

        ksort($prices);
        ksort($students);
        ksort($lastStudents);
        ksort($forecast_prices);
        ksort($last_year_prices);
        ksort($evaluations_by_months);
        ksort($evaluations_labels);

        $last_year_total_YTD = $this->getCfpProjects(range(1, 12), ['Terminé', 'Cloturé'], $idCfp, date('Y') - 1)->sum('total_ttc');

        $user = Auth::user();
        $notifications = $user->unreadNotifications;

        $authenticatedUser = 2;
        $userNow = Customer::findOrFail($authenticatedUser);
        $mysubscriptions = $userNow->planSubscriptions()->first();
        if ($mysubscriptions && $mysubscriptions->ended()) {
            $nextSubscription = $userNow->planSubscriptions()->where('starts_at', '>', $mysubscriptions->ends_at)->first();
            if ($nextSubscription) {
                $mysubscriptions->delete();
            }
        }

        $countProjet = DB::table('projets')->where('idCustomer', 2)->count();

        $currentGoalByMonth = $this->getGoalByMonth(date('Y'));
        [$total_paye, $restantDu] = $this->calculateInvoiceTotals();

        return response()->json([
            'invoiceProfo' => $this->getAllFactureProfo(),
            'total_trained' => $total_trained,
            'impayees' => $restantDu,
            'encaissees' => $total_paye,
            'unique_trained' => $unique_trained,
            'total_cost' => $total_cost,
            'total_YTD' => $this->getTotalPriceProject(2, ['Terminé', 'Cloturé'])->sum('total_ttc'),
            'last_year_YTD' => $last_year_total_YTD,
            'cost_by_employee' => $cost_by_employee,
            'project_by_month' => $project_by_month,
            'months' => $months,
            'months_b' => $months_b,
            'current_year_projects' => $current_year_projects,
            'finished_data' => $prices,
            'forecast_data' => $forecast_prices,
            'last_year_prices' => $last_year_prices,
            'student_trained' => $students,
            'last_students_trained' => $lastStudents,
            'last_unique_trained' => $last_unique_trained,
            'last_cost_by_employee' => $last_cost_by_employee,
            'last_total_trained' => $last_total_trained,
            'last_total_cost' => $last_year_total_cost,
            'pie_total' => $pie_total,
            'pie_data' => $pie_data_in_percentage,
            'evaluations_data' => $evaluations_by_months,
            'evaluations_labels' => $evaluations_labels,
            'notifications' => $notifications,
            'countProjet' => $countProjet,
            'sumOpportunity' => $this->getOpportunity()->sum('prix'),
            'countOpportunity' => $this->getOpportunity()->count(),
            'currentGoal' => $currentGoalByMonth,
            'current_year' => date('Y')
        ]);
    }

    private function calculateInvoiceTotals()
    {
        // Récupération des factures
        $invoicesQuery = Invoice::with(['entrepriseFromVcollaboration', 'particulier', 'status'])
            ->where('idCustomer', Customer::idCustomer())
            ->standard()
            ->doesntHave('deletedInvoices')
            ->whereNotIn('invoice_status', [1, 4, 9])
            ->orderBy('idInvoice', 'desc');

        $invoices = $invoicesQuery->get();

        // Calcul des totaux
        $total_montant = $invoices->sum('invoice_total_amount');
        $total_paye = $invoices->pluck('payments')->flatten()->sum('amount');
        $restantDu = $total_montant - $total_paye;

        return [$total_paye, $restantDu];
    }


    public function currentCa($month, $type)
    {
        $now = date('Y');
        $monthValue = $this->convertToMonth($month);
        $projectsFinished = [];
        $projectsFenced = [];
        $projectsInProgress = [];
        $projectsFuture = [];

        if ($type == 3) {
            $year = $now - 1;
            $projectsFinished = $this->getCaByMonth($monthValue, $year, "Terminé");

            $projectsFenced = $this->getCaByMonth($monthValue, $year, "Cloturé");
        } else {
            $year = $now;
            if ($type == 1) {
                $projectsFinished = $this->getCaByMonth($monthValue, $year, "Terminé");

                $projectsFenced = $this->getCaByMonth($monthValue, $year, "Cloturé");
            } else {
                $projectsInProgress = $this->getCaByMonth($monthValue, $year, "En cours");

                $projectsFuture = $this->getCaByMonth($monthValue, $year, "Planifié");
            }
        }

        return response()->json(
            [
                'title' => $this->convertToFullMonth($month) . " " . $year,
                'in_progress' => $projectsInProgress,
                'future' => $projectsFuture,
                'finished' => $projectsFinished,
                'fenced' => $projectsFenced
            ]
        );
    }

    public function getLearner($month, $type)
    {
        $currentYear = date('Y');
        $data = null;

        if ($type == 1) {
            $data = $this->getLearnerByMonth($month, $currentYear);
        } else {
            $data = $this->getLearnerByMonth($month, $currentYear - 1);
        }


        $results = [];

        foreach ($data as $key => $learner) {
            $results[$learner->customerName][$key] = $learner;
        }

        return response()->json([
            'results' => $results,
        ]);
    }

    public function accueil()
    {
        $projetAvecImages = DB::table('v_projet_cfps')
            ->join('images', 'v_projet_cfps.idProjet', '=', 'images.idProjet')
            ->select('v_projet_cfps.idProjet')
            ->where(function ($query) {
                $query->where('v_projet_cfps.idCfp', Customer::idCustomer())
                    ->orWhere('v_projet_cfps.idCfp_inter', Customer::idCustomer())
                    ->orWhere('v_projet_cfps.idSubContractor', Customer::idCustomer());
            })
            ->where('v_projet_cfps.project_status', 'Terminé')
            ->orderBy('v_projet_cfps.dateFin', 'desc')
            ->limit(1)
            ->first();

        $images = [];
        $idProjet = null;

        if ($projetAvecImages) {
            $idProjet = $projetAvecImages->idProjet;

            $images = DB::table('images')
                ->select('nomImage', 'idProjet', 'idImages')
                ->where('idProjet', $idProjet)
                ->get();
        }

        return view('CFP.dashboards.accueil', compact('images', 'idProjet'));
    }

    public function getConfigApi()
    {
        return response()->json([
            'googleConfig' => [
                'CLIENT_ID' => config('services.google.client_id'),
                'API_KEY' => config('services.google.api_key'),
                'DISCOVERY_DOC' => config('services.google.discovery_doc'),
                'SCOPES' => config('services.google.scopes'),
            ]
        ]);
    }
}
