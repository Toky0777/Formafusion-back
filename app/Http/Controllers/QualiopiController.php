<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use App\Traits\StudentQuery;
use App\Traits\ProjectQuery;

class QualiopiController extends Controller
{
    use StudentQuery, ProjectQuery;
    protected $EvaluationChaud;
    public function __construct(EvaluationChaudController $evaluation)
    {
        $this->middleware('auth');
        $this->EvaluationChaud = $evaluation;
    }

    public function qualiopiCfp()
    {
        $idCfp = Customer::idCustomer();
        $CustomerName = DB::table('customers')->where('idCustomer', $idCfp)->value('customerName');
        $getCountNotFinished = $this->getCountNotFinished();
        return response()->json([
            'status' => 200,
            'customer_name' => $CustomerName,
            'nonCloture' => $this->countDossier(),
            'count_not_finished' => $getCountNotFinished
        ]);
    }

    private function countDossier()
    {
        $idCfp = Customer::idCustomer();
        $year = date('Y');

        // On compte les dossiers qui n'ont pas tous leurs projets "Cloturé"
        $dossiers = DB::table('dossiers')
            ->leftJoin('v_projet_cfps', 'dossiers.idDossier', '=', 'v_projet_cfps.idDossier')
            ->where('dossiers.idCfp', $idCfp)
            ->whereYear('dossiers.created_at', $year)
            ->where(function ($query) {
                $query->whereNull('v_projet_cfps.project_status')
                    ->orWhere('v_projet_cfps.project_status', '!=', 'Cloturé');
            })
            ->distinct('dossiers.idDossier')
            ->count('dossiers.idDossier');

        return $dossiers;
    }

    private function getProjectById($idCustomer)
    {
        $results = DB::table('projets as P')
            ->select([
                'P.idProjet',
                'P.dateDebut',
                'P.dateFin',
                'P.idCustomer as cfp_id',
                'C.customerName as cfp_name',
                'P.idTypeProjet',
                'TP.type as project_type',
                'PSC.idSubContractor as subcontractor_id',
                'CS.customerName as subcontractor_name',
                'mdls.moduleName as module_name'
            ])
            ->selectRaw("
                CASE
                    WHEN (P.project_is_archived = 1) THEN 'Archivé'
                    WHEN (P.project_is_trashed = 1) THEN 'Supprimé'
                    WHEN (P.project_is_closed = 1) THEN 'Cloturé'
                    WHEN (P.project_is_active = 1 AND P.dateFin < CURRENT_DATE) THEN 'Terminé'
                    WHEN (P.project_is_active = 0
                        AND P.project_is_cancelled = 0
                        AND P.project_is_repported = 0
                        AND P.project_is_reserved = 0
                        AND P.project_is_archived = 0
                        AND P.project_is_trashed = 0) THEN 'En préparation'
                    WHEN (P.project_is_cancelled = 1) THEN 'Annulé'
                    WHEN (P.project_is_repported = 1) THEN 'Reporté'
                    WHEN (P.project_is_reserved = 1) THEN 'Réservé'
                    WHEN (P.project_is_active = 1 AND P.dateDebut > CURRENT_DATE) THEN 'Planifié'
                    ELSE 'En cours'
                END as project_status
            ")
            ->join('type_projets as TP', 'P.idTypeProjet', '=', 'TP.idTypeProjet')
            ->join('mdls', 'P.idModule', '=', 'mdls.idModule')
            ->join('customers as C', 'P.idCustomer', '=', 'C.idCustomer')
            ->leftJoin('project_sub_contracts as PSC', 'P.idProjet', '=', 'PSC.idProjet')
            ->leftJoin('customers as CS', 'PSC.idSubContractor', '=', 'CS.idCustomer')
            ->where('P.project_is_trashed', '!=', 1)
            ->where(function ($query) use ($idCustomer) {
                $query->where('P.idCustomer', $idCustomer)
                    ->orWhere('PSC.idSubContractor', $idCustomer);
            })
            ->whereNotNull('P.dateDebut')
            ->where('mdls.moduleName', '!=', 'Default module')
            ->having('project_status', '=', 'Terminé')
            ->orderByDesc('PSC.idSubContractor')
            ->get();

        return $results;
    }

    private function getCountNotFinished()
    {
        $idCustomer = Customer::idCustomer();
        $projectsFinished = $this->getProjectById($idCustomer);


        $projectIds = $projectsFinished->pluck('idProjet');

        $seancesCounts = DB::table('seances')
            ->whereIn('idProjet', $projectIds)
            ->select('idProjet', DB::raw('COUNT(*) as count'))
            ->groupBy('idProjet')
            ->pluck('count', 'idProjet');
        $apprenantsIntras = DB::table('v_list_apprenants as ve')
            ->join('projets as p', 'p.idProjet', 've.idProjet')
            ->whereIn('ve.idProjet', $projectIds)
            ->select('ve.idProjet', DB::raw('COUNT(DISTINCT ve.idEmploye) as count'))
            ->groupBy('ve.idProjet')
            ->pluck('count', 'idProjet');

        $apprenantsInter = DB::table('v_list_apprenant_inter_added as ve')
            ->join('projets as p', 'p.idProjet', 've.idProjet')
            ->whereIn('ve.idProjet', $projectIds)
            ->select('ve.idProjet', DB::raw('COUNT(DISTINCT ve.idEmploye) as count'))
            ->groupBy('ve.idProjet')
            ->pluck('count', 'idProjet');

        $apprenantsCounts = $apprenantsIntras->union($apprenantsInter);
        $emargementCounts = DB::table('emargements as e')
            ->join('projets as p', 'e.idProjet', 'p.idProjet')
            ->whereIn('e.idProjet', $projectIds)
            ->where('p.idCustomer', $idCustomer)
            ->whereIn('e.isPresent', [0, 1, 2, 3])
            ->select('e.idProjet', DB::raw('COUNT(*) as count'))
            ->groupBy('e.idProjet')
            ->pluck('count', 'idProjet');
        $data = [];
        $tab = [];
        $countAttendanceNotFinished = 0;
        $countEvaluationChaudNotFinished = 0;

        foreach ($projectsFinished as $pf) {
            $projectId = $pf->idProjet;

            $totalLearners = ($pf->project_type == "Intra")
                ? $this->EvaluationChaud->getTotalLearnerByProjectIntra($projectId)
                : $this->EvaluationChaud->getTotalLearnerByProjectInter($projectId);

            $learnersEvaluated = $this->EvaluationChaud->getEvaluatedByProject($projectId);
            $percentageEvaluated = $this->EvaluationChaud->getPercentageEvaluatedByProject($totalLearners, $learnersEvaluated);

            if ($percentageEvaluated != 100) {
                $countEvaluationChaudNotFinished++;
            }

            $seancesCount = $seancesCounts[$projectId] ?? 0;
            $apprenantsCount = $apprenantsCounts[$projectId] ?? 0;
            $emargementCount = $emargementCounts[$projectId] ?? 0;

            if ((($apprenantsCount * $seancesCount) > $emargementCount) && ($apprenantsCount || $seancesCount) > 0) {
                $countAttendanceNotFinished++;
                $tab[] = $projectId;
            }
        }
        $data['attendanceNotFinished'] = $countAttendanceNotFinished;
        $data['evaluationChaudNotFinished'] = $countEvaluationChaudNotFinished;
        $data['idProject'] = $tab;

        return $data;
    }
}
