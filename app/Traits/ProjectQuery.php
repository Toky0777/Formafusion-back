<?php

namespace App\Traits;

use App\Models\Customer;
use App\Models\Projet;
use App\Services\UtilService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


trait ProjectQuery
{
    protected $utilService;

    public function __construct(UtilService $utilService)
    {
        $this->utilService = $utilService;
    }
    public function groupProjectsByMonth(Collection $current_year_projects)
    {

        $project_by_month = [];
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

        return $project_by_month;
    }


    public function getProjectPerCustomer(mixed $status, int $idCustomer, $year = null)
    {
        $projects = DB::table('v_union_projets')
            ->select('idEtp', DB::raw('COUNT(idProjet) as total_projects'))
            ->where(function ($query) use ($idCustomer) {
                $query->where('idCfp_intra', $idCustomer)
                    ->orWhere('idCfp_inter', $idCustomer);
            })
            ->where('idEtp', '!=', 'null')
            ->when($year, function ($query, $year) {
                $query->whereYear('dateDebut', $year);
            })
            ->whereIn(DB::raw("project_status COLLATE utf8mb4_unicode_ci"), $status)
            ->groupBy('idEtp')
            ->get();

        $average = ceil($projects->avg('total_projects'));

        return $average;
    }

    public function getEtpProjects(mixed $months, mixed $status, int $idCustomer, mixed $year = null)
    {
        if (!is_countable($months)) {
            $months = [$months];
        }
        if (!is_countable($status)) {
            $status = [$status];
        }
        if (is_null($year)) {
            $year = date('Y');
        }

        $d = $months[array_key_first($months)];
        $e = $months[array_key_last($months)];
        $projects = DB::table('v_union_projets')
            ->select('v_union_projets.idProjet', 'v_union_projets.project_status', 'v_union_projets.total_ttc', 'v_union_projets.total_ht', 'v_union_projets.total_ht_etp', 'v_union_projets.total_ttc_etp', 'idPaiement', 'v_union_projets.module_name', 'v_union_projets.dateFin', 'v_union_projets.idCfp_intra', 'v_union_projets.idCfp_inter', 'v_union_projets.etp_name', 'v_union_projets.etp_logo', 'v_union_projets.etp_initial_name', 'v_union_projets.idEtp')
            ->where('v_union_projets.idEtp', $idCustomer)
            ->whereIn(DB::raw("project_status COLLATE utf8mb4_unicode_ci"), $status)
            ->where('headYear', $year)
            ->whereMonth('dateFin', '>=', $d)
            ->whereMonth('dateFin', '<=', $e)
            ->orderBy('total_ttc', 'desc')
            ->get();

        return $projects;
    }
    public function  getEtpProjectsByYear(mixed $status, int $idCustomer, mixed $year = null)
    {
        return $this->getEtpProjects([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12], $status, $idCustomer, $year);
    }

    public function  getCfpProjectsByYear(mixed $status, int $idCustomer, mixed $year = null)
    {
        return $this->getCfpProjects([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12], $status, $idCustomer, $year);
    }


    public function getTotalPriceProject($idCustomer, $status)
    {
        $projects = DB::table('v_union_projets')
            ->select('total_ttc')
            ->where(function ($query) use ($idCustomer) {
                $query->where('idCfp_intra', $idCustomer)
                    ->orWhere('idCfp_inter', $idCustomer);
            })
            ->whereIn(DB::raw("project_status COLLATE utf8mb4_unicode_ci"), $status)
            ->whereYear('dateDebut', date('Y'))
            ->where('project_is_trashed', 0)
            ->groupBy('idProjet')
            ->get();

        return $projects;
    }

    public function getCfpProjects(mixed $months, mixed $status, int $idCustomer, mixed $year = null)
    {
        if (!is_countable($months)) {
            $months = [$months];
        }
        if (!is_countable($status)) {
            $status = [$status];
        }
        if (is_null($year)) {
            $year = date('Y');
        }
        $d = $months[array_key_first($months)];
        $e = $months[array_key_last($months)];
        $projects = DB::table('v_union_projets')
            ->select('v_union_projets.idProjet', 'project_status', 'total_ttc', 'total_ht', 'total_ht_etp', 'total_ttc_etp', 'idPaiement', 'module_name', 'dateFin', 'idCfp_intra', 'idCfp_inter', 'etp_name', 'etp_logo', 'etp_initial_name', 'v_union_projets.idEtp')
            ->where(function ($query) use ($idCustomer) {
                $query->where('idCfp_intra', $idCustomer)
                    ->orWhere('idCfp_inter', $idCustomer);
            })
            ->whereIn(DB::raw("project_status COLLATE utf8mb4_unicode_ci"), $status)
            ->where('headYear', $year)
            ->whereMonth('dateFin', '>=', $d)
            ->whereMonth('dateFin', '<=', $e)
            ->orderBy('total_ttc', 'desc')
            ->get()->unique('idProjet');

        return $projects;
    }

    public function convertToMonth($month)
    {
        if ($month == "Jan") {
            return '01';
        } elseif ($month == "Fev") {
            return '02';
        } elseif ($month == "Mars") {
            return '03';
        } elseif ($month == "Avr") {
            return '04';
        } elseif ($month == "Mai") {
            return '05';
        } elseif ($month == "Jui") {
            return '06';
        } elseif ($month == "Juil") {
            return '07';
        } elseif ($month == "Aout") {
            return '08';
        } elseif ($month == "Sept") {
            return '09';
        } elseif ($month == "Oct") {
            return '10';
        } elseif ($month == "Nov") {
            return '11';
        } elseif ($month == "Dec") {
            return '12';
        }
    }

    public function convertToFullMonth($month)
    {
        if ($month == "Jan") {
            return "Janvier";
        } elseif ($month == "Fev") {
            return "Février";
        } elseif ($month == "Mars") {
            return "Mars";
        } elseif ($month == "Avr") {
            return "Avril";
        } elseif ($month == "Mai") {
            return "Mai";
        } elseif ($month == "Jui") {
            return "Juin";
        } elseif ($month == "Juil") {
            return "Juillet";
        } elseif ($month == "Aout") {
            return "Aout";
        } elseif ($month == "Sept") {
            return "Septembre";
        } elseif ($month == "Oct") {
            return "Octobre";
        } elseif ($month == "Nov") {
            return "Novembre";
        } elseif ($month == "Dec") {
            return "Décembre";
        }
    }

    public function getLearnerByMonth($month, $year)
    {
        $projects = $this->getProjectByMonthAndYear($year, $month);


        $learners = array();
        foreach ($projects as $project) {
            $learners =  array_merge($learners, $this->getLearnerByProject($project)->toArray());
        }

        return $learners;
    }

    public function getProjectByMonthAndYear($year, $month)
    {
        $monthValue = $this->convertToMonth($month);
        $projects = DB::table('v_union_projets')
            ->whereMonth('dateDebut', $monthValue)
            ->whereYear('dateDebut', $year)
            ->where(function ($query) {
                $query->where('idCfp_intra', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer());
            })
            ->whereIn('project_status', ['Terminé', 'Cloturé'])
            ->where('project_is_trashed', 0)
            ->groupBy('idProjet')
            ->pluck('idProjet');

        return $projects;
    }

    public function getCaByMonth($month, $year, $status)
    {
        $idProjectSubContractors = DB::table('project_sub_contracts')
            ->where('idSubContractor', Customer::idCustomer())
            ->pluck('idProjet');

        $projectByMonth = DB::table('v_union_projets')
            ->select('total_ttc', 'module_name', 'project_reference as reference', 'etp_name', 'dateDebut as date_debut', 'dateFin as date_fin', 'idProjet as id_projet', 'project_status')
            ->where(function ($query) {
                $query->where('idCfp_intra', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer());
            })
            ->whereNot('module_name', 'Default module')
            ->where('project_status', $status)
            ->where('project_is_trashed', 0)
            ->whereYear('dateDebut', $year)
            ->whereMonth('dateDebut', $month)
            ->groupBy('idProjet')
            ->get();
        $projectByMonthSubContractor = DB::table('v_union_projets')
            ->select('total_ttc', 'module_name', 'project_reference as reference', 'etp_name', 'dateDebut as date_debut', 'dateFin as date_fin', 'idProjet as id_projet', 'project_status')
            ->whereIn('idProjet', $idProjectSubContractors)
            ->whereNot('module_name', 'Default module')
            ->where('project_status', $status)
            ->where('project_is_trashed', 0)
            ->whereYear('dateDebut', $year)
            ->whereMonth('dateDebut', $month)
            ->groupBy('idProjet')
            ->get();
        $projects = array_merge($projectByMonthSubContractor->toArray(), $projectByMonth->toArray());

        $data = [];

        foreach ($projects as $project) {
            $data[] = [
                'total_ttc' => $project->total_ttc,
                'module_name' => $project->module_name,
                'etp_name' => $project->etp_name,
                'date_debut' => Carbon::parse($project->date_debut)->format('j.m.y'),
                'date_fin' => Carbon::parse($project->date_fin)->format('j.m.y'),
                'id_projet' => $project->id_projet,
                'project_status' => $project->project_status,
                'learner' => count($this->getLearnerByProject($project->id_projet))
            ];
        }
        return $data;
    }

    public function getAverageLeanerByProject(array $ids)
    {
        if (empty($ids)) {
            return 0;
        }

        $totalLearners = 0;
        $projectCount = count($ids);

        foreach ($ids as $projectId) {
            $learners = $this->getLearnerByProject($projectId);
            $totalLearners += $learners->count();
        }

        return ceil($totalLearners / $projectCount);
    }


    public function getLearnerByProject($id)
    {
        return $this->checkTypesProject($id) == 1 ? $this->getLearnerByProjectIntra($id) : $this->getLearnerByProjectInter($id);
    }

    public function getLearnerByProjectInter($id)
    {
        $learner = DB::table('detail_apprenant_inters as DA')
            ->join('users as U', 'U.id', 'DA.idEmploye')
            ->join('customers as C', 'C.idCustomer', 'DA.idEtp')
            ->select('DA.idEmploye', 'U.name', 'U.firstName', 'U.photo', 'C.idCustomer', 'C.customerName')
            ->where('DA.idProjet', $id)
            ->get();

        return $learner;
    }

    public function getLearnerByProjectIntra($id)
    {
        $learner = DB::table('detail_apprenants as DA')
            ->join('users as U', 'U.id', 'DA.idEmploye')
            ->join('employes as E', 'E.idEmploye', 'DA.idEmploye')
            ->join('customers as C', 'C.idCustomer', 'E.idCustomer')
            ->select('DA.idEmploye', 'U.name', 'U.firstName', 'U.photo', 'C.idCustomer', 'C.customerName')
            ->where('DA.idProjet', $id)
            ->get();

        return $learner;
    }

    public function checkTypesProject($id)
    {
        $typeProject = DB::table('projets')
            ->select('idTypeProjet')
            ->where('idProjet', $id)
            ->first();

        return $typeProject->idTypeProjet;
    }

    // public function getOpportunity(){
    //     $opportunity = DB::table('opportunites')
    //                         ->select(DB::raw('MONTH(dateDeb) as dateDebut'), DB::raw('SUM(prix) as prix'))
    //                         ->where('idCustomer', Customer::idCustomer())
    //                         ->where('opportunitie_is_win', 0)
    //                         ->where('opportunitie_is_lost', 0)
    //                         ->where('opportunitie_is_standBy', 0)
    //                         ->whereYear('dateDeb', now())
    //                         ->groupBy(DB::raw('MONTH(dateDeb)'))
    //                         ->get();

    //     return $opportunity;
    // }

    public function getOpportunity()
    {
        $opportunity = DB::table('opportunites')
            ->select('prix')
            ->where('idCustomer', Customer::idCustomer())
            ->where('opportunitie_is_win', 0)
            ->where('opportunitie_is_lost', 0)
            ->where('opportunitie_is_standBy', 0)
            ->whereYear('dateDeb', now())
            ->get();

        return $opportunity;
    }

    public function countProjectByStatus($status)
    {
        return DB::table('v_projet_cfps')
            ->select('idProjet')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                });
            })
            ->where('module_name', '!=', 'Default module')
            ->where('project_status', $status)
            ->count();
    }
}
