<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EvaluationChaudEtpController extends Controller
{


    public function index()
    {
        $projectIntra = DB::table('intras as i')
            ->join('projets as p', 'i.idProjet', 'p.idProjet')
            ->join('mdls as m', 'p.idModule', 'm.idModule')
            ->select('i.idProjet', 'i.idEtp', 'p.dateDebut', 'p.dateFin', 'p.idTypeProjet', 'p.project_reference', 'm.moduleName')
            ->where('i.idEtp', auth()->user()->id)
            ->where('p.project_is_active', 1)
            ->where('p.dateFin', '<', Carbon::now())
            ->get();

        $projectInter = DB::table('inter_entreprises as ie')
            ->join('projets as p', 'ie.idProjet', 'p.idProjet')
            ->join('mdls as m', 'p.idModule', 'm.idModule')
            ->select('ie.idProjet', 'ie.idEtp', 'p.dateDebut', 'p.dateFin', 'p.idTypeProjet', 'p.project_reference', 'm.moduleName')
            ->where('ie.idEtp', auth()->user()->id)
            ->where('p.project_is_active', 1)
            ->where('p.dateFin', '<', Carbon::now())
            ->get();

        $projects = $projectIntra->merge($projectInter);

        foreach ($projects as $p) {
            $p->evaluated_count = $this->getEvaluatedByProject($p->idProjet);

            $p->totalLearners = ($p->idTypeProjet == 1)
                ? $this->getTotalLearnerByProjectIntra($p->idProjet)
                : $this->getTotalLearnerByProjectInter($p->idProjet);
        }

        return response()->json(['projects' => $projects], 200);
    }



    public function getLearnerAdded($projectId)
    {
        $projectType = DB::table('projets')
            ->where('idProjet', $projectId)
            ->value('idTypeProjet');

        $learners = ($projectType == 1) ? $this->getLearnerAddedIntra($projectId) : $this->getLearnerAddedInter($projectId);

        $totalLearners = ($projectType == 1) ? $this->getTotalLearnerByProjectIntra($projectId) : $this->getTotalLearnerByProjectInter($projectId);
        $learnersEvaluated = $this->getEvaluatedByProject($projectId);

        return response()->json([
            'learners' => $learners,
            'percentage_evaluated' => $this->getPercentageEvaluatedByProject($totalLearners, $learnersEvaluated),
        ], 200);
    }



    public function getTotalLearnerByProjectIntra($projectId)
    {
        return DB::table('detail_apprenants')
            ->select('idEmploye')
            ->where('idProjet', $projectId)
            ->count();
    }

    public function getPercentageEvaluatedByProject($totalLearners, $learnersEvaluated)
    {
        if ($totalLearners == 0) {
            return 0;
        }

        return round(($learnersEvaluated / $totalLearners) * 100, 2) ?? 0;
    }
    public function getLearnerAddedIntra($projectId)
    {
        $learners = DB::table('detail_apprenants as D')
            ->join('users as U', 'U.id', 'D.idEmploye')
            ->where('D.idProjet', $projectId)
            ->select('U.id', 'U.name', 'U.firstName', 'U.photo')
            ->get();

        $learners = $learners->map(function ($learner) use ($projectId) {
            $learnerId = $learner->id;

            return [
                'id' => $learnerId,
                'name' => $learner->name,
                'first_name' => $learner->firstName,
                'photo' => $learner->photo,
                'note' => $this->getAverageNoteByLearner($learnerId, $projectId),
            ];
        });

        return $learners;
    }

    public function getLearnerAddedInter($projectId)
    {
        $entreprises = DB::table('inter_entreprises as I')
            ->join('customers as C', 'C.idCustomer', 'I.idEtp')
            ->select('C.idCustomer', 'C.customerName')
            ->where('I.idProjet', $projectId)
            ->get();

        $entreprises = $entreprises->map(function ($entreprise) use ($projectId) {
            $entrepriseId = $entreprise->idCustomer;
            return [
                'id' => $entrepriseId,
                'name' => $entreprise->customerName,
                'learners' => $this->getLearnerWithNoteByEntreprise($entrepriseId, $projectId)
            ];
        });

        return $entreprises;
    }

    public function getLearnerWithNoteByEntreprise($entrepriseId, $projectId)
    {
        $learners = DB::table('detail_apprenant_inters as D')
            ->join('users as U', 'U.id', 'D.idEmploye')
            ->join('employes as E', 'E.idCustomer', $entrepriseId)
            ->where('D.idProjet', $projectId)
            ->where('D.idEtp', $entrepriseId)
            ->select('U.id', 'U.name', 'U.firstName')
            ->get();

        $learners = $learners->map(function ($learner) use ($projectId) {
            $learnerId = $learner->id;

            return [
                'id' => $learnerId,
                'name' => $learner->name,
                'first_name' => $learner->firstName,
                'note' => $this->getAverageNoteByLearner($learnerId, $projectId),
            ];
        });

        return $learners;
    }

    public function getAverageNoteByLearner($learnerId, $projectId)
    {
        $note = DB::table('eval_chauds')
            ->select(DB::raw('AVG(note) as note'))
            ->where('idEmploye', $learnerId)
            ->where('idProjet', $projectId)
            ->first();

        return round($note->note, 1) ?? 0;
    }
    public function getTotalLearnerByProjectInter($projectId)
    {
        return DB::table('detail_apprenants as DA')
            ->join('employes as E', 'E.idEmploye', 'DA.idEmploye')
            ->select('DA.idEmploye')
            ->where('E.idCustomer', auth()->user()->id)
            ->where('DA.idProjet', $projectId)
            ->count();
    }
    public function getEvaluatedByProject($projectId)
    {
        $evaluated = DB::table('eval_chauds as EC')
            ->join('employes as E', 'E.idEmploye', 'EC.idEmploye')
            ->select('EC.idEmploye')
            ->where('EC.idProjet', $projectId)
            ->where('E.idCustomer', auth()->user()->id)
            ->groupBy('E.idEmploye')
            ->get();

        return count($evaluated);
    }
}
