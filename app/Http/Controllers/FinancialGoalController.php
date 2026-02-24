<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinancialGoalRequest;
use App\Models\Customer;
use App\Traits\GoalQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\Project;

class FinancialGoalController extends Controller
{
    use Project;
    use GoalQuery;

    public function index()
    {
        $currentYear = date('Y');

        $data = $this->goalByYear($currentYear);

        $year = $this->getYear();

        $totalGoal = number_format($this->getTotalByYear($currentYear), 2, ',', ' ');

        $goalByMonth = $this->getGoalByMonth(date('Y'));

        return response()->json([
            'data' => $data,
            'currentYear' => $currentYear,
            'totalGoal' => $totalGoal,
            'goalByMonth' => $goalByMonth
        ]);
    }

    public function convertGoalByMonth($goals)
    {
        $results = [];

        foreach ($goals as $goal) {
            $results[] = $this->formatPrice($goal);
        }

        return $results;
    }

    public function getGoalByYear(Request $request)
    {
        $data = $this->goalByYear($request->year);

        $totalGoal = $this->getTotalByYear($request->year);

        return response()->json([
            'data' => $data,
            'totalGoal' => number_format($totalGoal, 2, ',', ' ')
        ]);
    }

    public function getTotalYear(Request $request)
    {
        $total = $this->getTotalByYear($request->year);
        return response()->json(number_format($total, 2, ',', ' '));
    }

    public function store($amount, $date, $moduleId)
    {
        DB::table('financial_goals')
            ->insert(
                [
                    'id_customer' => Customer::idCustomer(),
                    'value' => $amount,
                    'date' => $date,
                    'id_module' => $moduleId
                ]
            );

        return response()->json('Objectif ajouté avec succes', 200);
    }

    public function update($amount, $date, $moduleId)
    {
        DB::table('financial_goals')
            ->where('id_customer', Customer::idCustomer())
            ->where('date', $date)
            ->where('id_module', $moduleId)
            ->update([
                'value' => $amount
            ]);
        return response()->json('Objectif modifié avec succes', 200);
    }

    public function goalByMonth(Request $request)
    {
        $goals = $this->getGoalByMonth($request->year);

        $results = $this->convertGoalByMonth($goals);

        return response()->json($results);
    }

    public function save(FinancialGoalRequest $request)
    {
        if (isset($request->year)) {
            $date = $request->year . '/' . $request->month . '/01';

            return $this->isExist($date, $request->idModule) ? $this->update($request->amount, $date, $request->idModule) : $this->store($request->amount, $date, $request->idModule);
        } else {
            return response()->json('Veullez remplir le champ', 401);
        }
    }

    public function isExist($date, $moduleId)
    {
        $goal = DB::table('financial_goals')
            ->where('id_customer', Customer::idCustomer())
            ->where('date', $date)
            ->where('id_module', $moduleId)
            ->exists();
        return $goal;
    }
}
