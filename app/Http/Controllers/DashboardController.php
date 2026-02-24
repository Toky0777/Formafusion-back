<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use App\Traits\ProjectQuery;
use App\Traits\StudentQuery;

class DashboardController extends Controller
{
    use ProjectQuery;
    use StudentQuery;

    public function dashboardEtp()
    {
        $idEtp = Auth::user()->id;
        $current_month =  date('m');
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $remain_months = array_slice([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12], intval($current_month) - 1);

        $current_month_projects = $this->getEtpProjects($current_month, ['Terminé', 'Cloturé'], $idEtp);
        $last_year_current_month_projects = $this->getEtpProjects($current_month, ['Terminé', 'Cloturé'], $idEtp, date('Y') - 1);

        $total_cost = $current_month_projects->sum('total_ttc');
        $last_year_total_cost = $last_year_current_month_projects->sum('total_ttc');

        $current_year_projects = $this->getEtpProjectsByYear(['Terminé', 'Cloturé'], $idEtp);
        $last_year_projects = $this->getEtpProjectsByYear(['Terminé', 'Cloturé'], $idEtp, date('Y') - 1);


        $apprenants = $this->getStudents($current_year_projects->pluck('idProjet')->toArray());
        $last_year_apprenants = $this->getStudents($last_year_projects->pluck('idProjet'));

        $total_trained = count($apprenants);
        $last_total_trained = count($last_year_apprenants);

        $unique_trained = collect($apprenants)->unique()->count();
        $last_unique_trained = collect($last_year_apprenants)->unique()->count();


        $prepared_and_in_progress_projects = $this->getEtpProjects($remain_months, ['En cours', 'Planifié'], $idEtp);
        $total_cost_year_to_date = ($current_year_projects->sum('total_ttc'));

        if ($unique_trained != 0) {
            $cost_by_employee = $total_cost_year_to_date / $unique_trained;
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

        $prices = [];
        $students = [];
        foreach ($project_by_month as $month => $projects) {
            $prices[$month] = collect($projects)->sum('total_ttc');
            $students[$month] = count($this->getStudents(collect($projects)->pluck('idProjet')));
        }
        foreach ($last_year_projects_by_month as $month => $projects) {
            $last_year_prices[$month] = collect($projects)->sum('total_ttc');
        }
        foreach ($prepared_and_in_progress_projects as $month => $projects) {
            $forecast_prices[$month] = collect($projects)->sum('total_ttc');
        }
        for ($i = 0; $i < 12; $i++) {
            if ($i <= $current_month - 1) {
                if (!isset($prices[$i])) {
                    $prices[$i] = 0;
                }
                if (!isset($forecast_prices[$i])) {
                    $forecast_prices[$i] = 'null';
                }
            } else {
                if (!isset($forecast_prices[$i])) {
                    $forecast_prices[$i] = 0;
                }
            }
            if (!isset($students[$i])) {
                $students[$i] = 0;
            }
            if (!isset($last_year_prices[$i])) {
                $last_year_prices[$i] = 0;
            }
        }
        ksort($prices);
        ksort($students);
        ksort($forecast_prices);
        ksort($last_year_prices);

        $last_year_total_YTD = collect(array_slice($last_year_prices, (12 - intval($current_month))))->sum();

        $user = Auth::user();
        $notifications = $user->unreadNotifications;

        $authenticatedUser = Customer::idCustomer();
        $userNow = Customer::findOrFail($authenticatedUser);
        $mysubscriptions = $userNow->planSubscriptions()->first();
        if ($mysubscriptions && $mysubscriptions->ended()) {
            $nextSubscription = $userNow->planSubscriptions()->where('starts_at', '>', $mysubscriptions->ends_at)->first();
            if ($nextSubscription) {
                $mysubscriptions->delete();
            }
        }

        return response()->json([
            'months' => $months,
            'project_by_month' => $project_by_month,
            'finished_data' => $prices,
            'forecast_data' => $forecast_prices,
            'total_trained' => $total_trained,
            'unique_trained' => $unique_trained,
            'total_cost' => $total_cost,
            'total_YTD' => $total_cost_year_to_date,
            'cost_by_employee' => $cost_by_employee,
            'current_year_projects' => $current_year_projects,
            'histogram_data' => $students,
            'last_total_trained' => $last_total_trained,
            'last_unique_trained' => $last_unique_trained,
            'last_total_cost' => $last_year_total_cost,
            'last_cost_by_employee' => $last_cost_by_employee,
            'last_year_YTD' => $last_year_total_YTD,
            'last_year_prices' => $last_year_prices,
            'notifications' => $notifications
        ]);
    }
}
