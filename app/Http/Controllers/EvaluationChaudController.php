<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EvaluationChaudController extends Controller
{
    public function getProjectForEvaluation(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $status = $request->status;
        $entreprise = $request->entreprise;
        $city = $request->lieu;
        $course = $request->module;
        $key = $request->search;

        $projects = DB::table('v_projet_cfps')
            ->select(
                'idProjet',
                'dateDebut',
                'dateFin',
                'idEtp',
                'module_name',
                'etp_name',
                'project_status',
                'project_reference',
                'project_type',
                'ville',
                DB::raw('MONTH(dateDebut) as month, YEAR(dateDebut) as year')
            )
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                });
            })
            ->where('module_name', '!=', 'Default module')
            ->whereIn('project_status', ["Terminé", "Cloturé"]);

        if ($key) {
            $projects->where(function ($query) use ($key) {
                $query->where('module_name', 'like', "%$key%")
                    ->orWhere('li_name', 'like', "%$key%")
                    ->orWhere('etp_name', 'like', "%$key%");
            });
        }

        if ($entreprise) {
            $projects->where('etp_name', $entreprise);
        }

        if ($city) {
            $projects->where('ville', $city);
        }

        if ($course) {
            $projects->where('module_name', $course);
        }

        $paginated = $projects->orderByDesc('dateDebut')
            ->groupBy('idProjet')
            ->paginate($perPage);

        $paginated->getCollection()->transform(function ($project) use ($status) {
            $projectId = $project->idProjet;

            $totalLearners = ($project->project_type == "Intra")
                ? $this->getTotalLearnerByProjectIntra($projectId)
                : $this->getTotalLearnerByProjectInter($projectId);

            $learnersEvaluated = $this->getEvaluatedByProject($projectId);
            $percentageEvaluated = $this->getPercentageEvaluatedByProject($totalLearners, $learnersEvaluated);

            $beginDate = $project->dateDebut;

            $dateBegin = Carbon::parse($beginDate)->locale('fr')->translatedFormat('d F Y');
            $dateEnd = Carbon::parse($project->dateFin)->locale('fr')->translatedFormat('d F Y');

            if ($status === "Terminé" && $percentageEvaluated != 100) {
                return null;
            }

            if ($status === "En cours" && $percentageEvaluated == 100) {
                return null;
            }

            return [
                'id' => $projectId,
                'begin_date' => $beginDate,
                'idEtp' => $project->idEtp,
                'begin_date_converted' => ucfirst($dateBegin),
                'end_date_converted' => ucfirst($dateEnd),
                'city' => $project->ville,
                'entreprise' => $project->etp_name,
                'module' => $project->module_name,
                'total_learners' => $totalLearners,
                'learners_evaluated' => $learnersEvaluated,
                'project_status' => $project->project_status,
                'project_reference' => $project->project_reference,
                'percentage_evaluated' => $percentageEvaluated,
                'note' => $this->getNoteByProject($projectId),
                'month' => $project->month,
                'year' => $project->year,
            ];
        });

        // On retire les projets filtrés à null
        $paginated->setCollection(
            $paginated->getCollection()->filter(fn($p) => $p !== null)->values()
        );

        return response()->json($paginated, 200);
    }


    public function getFilters()
    {
        $customerId = Customer::idCustomer();

        $modules = DB::table('v_projet_cfps')
            ->select('module_name as nom')
            ->where('module_name', '!=', 'Default module')
            ->where(function ($query) use ($customerId) {
                $query->where('idCfp', $customerId)
                    ->orWhere('idCfp_inter', $customerId)
                    ->orWhere('idSubContractor', $customerId);
            })
            ->distinct()
            ->orderBy('module_name')
            ->whereIn('project_status', ['Terminé', 'Cloturé'])
            ->get();

        $entreprises = DB::table('v_projet_cfps')
            ->select('etp_name as nom')
            ->whereNotNull('etp_name')
            ->where(function ($query) use ($customerId) {
                $query->where('idCfp', $customerId)
                    ->orWhere('idCfp_inter', $customerId)
                    ->orWhere('idSubContractor', $customerId);
            })
            ->distinct()
            ->orderBy('etp_name')
            ->whereIn('project_status', ['Terminé', 'Cloturé'])
            ->get();

        $lieux = DB::table('v_projet_cfps')
            ->select('ville as nom')
            ->whereNotNull('ville')
            ->where(function ($query) use ($customerId) {
                $query->where('idCfp', $customerId)
                    ->orWhere('idCfp_inter', $customerId)
                    ->orWhere('idSubContractor', $customerId);
            })
            ->distinct()
            ->whereIn('project_status', ['Terminé', 'Cloturé'])
            ->orderBy('ville')
            ->get();

        return response()->json([
            'modules' => $modules,
            'entreprises' => $entreprises,
            'lieux' => $lieux,
        ]);
    }


    public function getNoteByProject($projectId)
    {
        $query = DB::table('eval_chauds')
            ->select(
                DB::raw('SUM(firstNotes.generalApreciate) as sumFirstNotes'),
                DB::raw('COUNT(DISTINCT firstNotes.idEmploye) as totalEmployees')
            )
            ->fromSub(function ($query) use ($projectId) {
                $query->select('idEmploye', 'idProjet', 'generalApreciate')
                    ->from('eval_chauds')
                    ->where('idProjet', $projectId)
                    ->whereNotNull('generalApreciate')
                    ->groupBy('idEmploye', 'idProjet');
            }, 'firstNotes')
            ->first();

        $average = $query->totalEmployees > 0 ? $query->sumFirstNotes / $query->totalEmployees : 0;

        return round($average, 1);
    }

    public function getPercentageEvaluatedByProject($totalLearners, $learnersEvaluated)
    {
        if ($totalLearners == 0) {
            return 0;
        }

        return round(($learnersEvaluated / $totalLearners) * 100, 2) ?? 0;
    }

    public function getEvaluatedByProject($projectId)
    {
        $evaluated = DB::table('eval_chauds')
            ->select('idEmploye')
            ->where('idProjet', $projectId)
            ->groupBy('idEmploye')
            ->get();

        return count($evaluated);
    }

    public function getTotalLearnerByProjectIntra($projectId)
    {
        return DB::table('detail_apprenants')
            ->select('idEmploye')
            ->where('idProjet', $projectId)
            ->count();
    }

    public function getTotalLearnerByProjectInter($projectId)
    {
        return DB::table('detail_apprenants')
            ->select('idEmploye')
            ->where('idProjet', $projectId)
            ->count();
    }

    public function getLearnerAdded($projectId)
    {
        $projectType = DB::table('projets')
            ->where('idProjet', $projectId)
            ->value('idTypeProjet');

        $entreprises = ($projectType == 1) ? $this->getLearnerByEntrepriseProjectIntra($projectId) : $this->getLearnerAddedInter($projectId);

        $totalLearners = ($projectType == 1) ? $this->getTotalLearnerByProjectIntra($projectId) : $this->getTotalLearnerByProjectInter($projectId);
        $learnersEvaluated = $this->getEvaluatedByProject($projectId);

        return response()->json([
            'entreprises' => $entreprises,
            'percentage_evaluated' => $this->getPercentageEvaluatedByProject($totalLearners, $learnersEvaluated),
        ], 200);
    }

    public function getLearnerByEntrepriseProjectIntra($projectId)
    {
        $entreprise = DB::table('customers as C')
            ->join('intras as I', 'I.idEtp', '=', 'C.idCustomer')
            ->select('C.customerName', 'C.idCustomer')
            ->where('I.idProjet', $projectId)
            ->first();

        if (!$entreprise) {
            return [];
        }

        return [[
            'id' => $entreprise->idCustomer,
            'name' => $entreprise->customerName,
            'learners' => $this->getLearnerAddedIntra($projectId)
        ]];
    }

    public function getLearnerAddedIntra($projectId)
    {
        $learners = DB::table('detail_apprenants as D')
            ->join('users as U', 'U.id', 'D.idEmploye')
            ->where('D.idProjet', $projectId)
            ->select('U.id', 'U.name', 'U.firstName')
            ->get();

        $learners = $learners->map(function ($learner) {
            $learnerId = $learner->id;

            return [
                'id' => $learnerId,
                'name' => $learner->name,
                'first_name' => $learner->firstName,
                'note' => $this->getAverageNoteByLearner($learnerId),
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
            ->where('D.idProjet', $projectId)
            ->where('D.idEtp', $entrepriseId)
            ->select('U.id', 'U.name', 'U.firstName')
            ->get();

        $learners = $learners->map(function ($learner) {
            $learnerId = $learner->id;

            return [
                'id' => $learnerId,
                'name' => $learner->name,
                'first_name' => $learner->firstName,
                'note' => $this->getAverageNoteByLearner($learnerId),
            ];
        });

        return $learners;
    }

    public function getAverageNoteByLearner($learnerId)
    {
        $note = DB::table('eval_chauds')
            ->select(DB::raw('AVG(note) as note'))
            ->where('idEmploye', $learnerId)
            ->first();

        return round($note->note, 1) ?? 0;
    }
}
