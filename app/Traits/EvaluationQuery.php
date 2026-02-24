<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;


trait EvaluationQuery
{

    public function getEvaluations(mixed $idProjet)
    {
        if (!is_countable($idProjet)) {
            $idProjet = [$idProjet];
        }
        $evaluation = DB::table('eval_chauds')
            ->select('generalApreciate', 'idEmploye', 'idProjet')
            ->whereIn('idProjet', $idProjet)
            ->get()
            ->groupBy('idProjet');
        return $evaluation;
    }

    public function getEval($idModule)
    {
        $projectIds = $this->getProjectByModuleEval($idModule);

        $result = DB::table('eval_chauds')
            ->select(
                DB::raw('SUM(firstNotes.generalApreciate) as sumFirstNotes'),
                DB::raw('COUNT(DISTINCT firstNotes.idEmploye) as totalEmployees')
            )
            ->fromSub(function ($query) use ($projectIds) {
                $query->select('idEmploye', 'idProjet', 'generalApreciate')
                    ->from('eval_chauds')
                    ->whereIn('idProjet', $projectIds)
                    ->whereNotNull('generalApreciate')
                    ->groupBy('idEmploye', 'idProjet');
            }, 'firstNotes')
            ->first();

        $average = $result->totalEmployees > 0 ? $result->sumFirstNotes / $result->totalEmployees : 0;

        return [
            'totalEmployees' => $result->totalEmployees,
            'average' => round($average, 1)
        ];
    }

    public function getProjectByModuleEval($idModule)
    {
        $projects = DB::table('v_projet_cfps')
            ->select('idProjet')
            ->where('idModule', $idModule)
            ->pluck('idProjet');
        return $projects;
    }
}
