<?php

namespace App\Traits;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

trait GoalQuery
{
    public function getGoalByMonth($year)
    {
        $total = DB::table('financial_goals')
            ->select(DB::raw('SUM(value) as value'), DB::raw('MONTH(date) as month'))
            ->whereYear('date', $year)
            ->where('id_customer', Customer::idCustomer())
            ->groupBy(DB::raw('MONTH(date)'))
            ->get()
            ->keyBy('month');

        $data = array_fill(0, 12, 0);

        foreach ($total as $month => $goal) {
            $data[$month - 1] = $goal->value;
        }

        return $data;
    }

    public function goalByYear($year)
    {
        $modules = DB::table('mdls')
            ->select(
                'mdls.moduleName',
                'mdls.module_image',
                'mdls.idModule',
                'dom.nomDomaine as domaineName' 
            )
            ->join('domaine_formations as dom', 'mdls.idDomaine', '=', 'dom.idDomaine')
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleStatut', 1)
            ->whereNot('moduleName', 'Default module')
            ->get();


        $data = [];

        foreach ($modules as $module) {
            $data[] = [
                'idModule' => $module->idModule,
                'domaineName' => $module->domaineName,
                'moduleName' => $module->moduleName,
                'moduleImage' => config('filesystems.disks.do.url_cdn_digital') . '/' . config('filesystems.disks.do.bucket') . '/img/modules/' . $module->module_image,
                'moduleGoal' => $this->getGoalByModule($module->idModule, $year)
            ];
        }

        return $data;
    }

    public function getGoalByModule($idModule, $year)
    {
        $moduleGoal = DB::table('financial_goals')
            ->select('value', DB::raw('MONTH(date) as month'))
            ->where('id_module', $idModule)
            ->whereYear('date', $year)
            ->get()
            ->keyBy('month');

        $data = array_fill(1, 12, 0);

        foreach ($moduleGoal as $month => $goal) {
            $data[$month] = $goal->value;
        }

        return $data;
    }

    public function getYear()
    {
        $year = DB::table('financial_goals')
            ->select(DB::raw('YEAR(date) as year'))
            ->where('id_customer', Customer::idCustomer())
            ->groupBy('year')
            ->orderBy('year', 'ASC')
            ->first();

        return range($year->year ?? date('Y'), date('Y') + 5);
    }

    public function getTotalByYear($year)
    {
        $total = DB::table('financial_goals')
            ->select('value')
            ->whereYear('date', $year)
            ->where('id_customer', Customer::idCustomer())
            ->sum('value');

        return $total;
    }
}
