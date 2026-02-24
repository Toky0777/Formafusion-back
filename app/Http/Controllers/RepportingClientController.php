<?php

namespace App\Http\Controllers;

use App\Exports\ApprenantExcelExport;
use App\Exports\ClientExcelExport;
use App\Exports\FinanceExport;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;


class RepportingClientController extends Controller
{
    public function idCfp()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idCustomer;
    }

    private function idProjetSuBContrators(){
        $idProjectSubContractors = DB::table('project_sub_contracts')
        ->where('idSubContractor', Customer::idCustomer())
        ->pluck('idProjet');

        return $idProjectSubContractors;
    }

    private function isNotSubContractor($idCustomer){
        $customer = DB::table('v_union_projets')
                    ->select('idProjet', 'etp_name', 'project_type', 'dateDebut', 'dateFin', 'total_ttc', 'project_status', 'ville')
                    ->where(function ($query){
                        $query->where('idCfp_intra', Customer::idCustomer())
                            ->orWhere('idCfp_inter', Customer::idCustomer());
                    })
                    ->where(function ($query) use($idCustomer){
                        $query->where('idEtp', $idCustomer)
                            ->orWhere('idEtp_inter', $idCustomer);
                    })
                    ->whereIn('project_status', ['Terminé', 'En cours'])
                    ->exists();

        return $customer;
    }

    public function searchByCustomer(Request $request)
    {
        $request->validate([
            'customer' => 'required|string'
        ]);

        // Utilisez first() directement sur la requête
        $customer = DB::table('customers')
                    ->select('customerName', 'logo', 'customerEmail', 'customerPhone', 'idCustomer')
                    ->where('customerName', $request->customer)
                    ->first();

        if(!$customer) {
            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ], 404);
        }

        $referents = DB::table('users')
                    ->select('users.*', 'employes.idCustomer', 'customers.customerPhone')
                    ->join('employes', 'users.id', '=', 'employes.idEmploye')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->join('customers', 'employes.idCustomer', '=', 'customers.idCustomer') // Correction ici
                    ->where('employes.idCustomer', $customer->idCustomer)
                    ->whereIn('role_users.role_id', [3, 6, 8, 9])
                    ->get();
        
        $results = $this->getGeneralReporting($customer->idCustomer);

        $months = [
            1 => 'Janvier', 2 => 'Fevrier', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Aout',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Decembre'
        ];

        $total_project = $this->totalProject($customer->idCustomer);
        $total_learner = $this->totalLearner($customer->idCustomer);

        return response()->json([
            'status' => 200,
            'results' => $results, 
            'months' => $months, 
            'referents' => $referents, 
            'total_project' => $total_project, 
            'total_learner' => $total_learner
        ]);
    }

    private function totalLearner($idCustomer){
        $customer = $this->isNotSubContractor($idCustomer);

        return $customer ? $this->totalLearnerCfp($idCustomer) : $this->totalLearnerSponsor($idCustomer);
    }

    private function totalProject($idCustomer){
        $customer = $this->isNotSubContractor($idCustomer);

        return $customer ? $this->totalProjectCfp($idCustomer) : $this->totalProjectSponsor($idCustomer);
    }

    private function getGeneralReporting($idCustomer){
        $customer = $this->isNotSubContractor($idCustomer);

        return $customer ? $this->getGeneralReportingCfp($idCustomer) : $this->getGeneralReportingSponsor($idCustomer);
    }

    private function getGeneralReportingCfp($idCustomer){
        $project_years = DB::table('v_union_projets')
                            ->select(DB::raw('YEAR(dateDebut) as year'))
                            ->where(function ($query) use($idCustomer){
                                $query->where('idEtp', $idCustomer)
                                    ->orWhere('idEtp_inter', $idCustomer);
                            })
                            ->where(function ($query){
                                $query->where('idCfp_intra', Customer::idCustomer())
                                    ->orWhere('idCfp_inter', Customer::idCustomer());
                            })
                            ->groupBy(DB::raw('YEAR(dateDebut)'))
                            ->orderBy(DB::raw('YEAR(dateDebut)'), 'desc')
                            ->whereIn('project_status', ['Terminé', 'Cloturé'])
                                                        ->where('project_is_trashed', 0)
                            ->get();

        $results = [
            'ca' => [],
            'count_projects' => [],
            'story_projects' => [],
            'learners' => []
        ];

        foreach ($project_years as $project) {
            $year = $project->year;

            $results['ca'][] = [
                'year' => $year,
                'ca_customer' => $this->getCaByYearCfp($year, $idCustomer),
                'total' => $this->totalCa($year, $idCustomer)
            ];

            $results['count_projects'][] = [
                'year' => $year,
                'projects' => $this->getProjectByYearCfp($year, $idCustomer)
            ];

            $results['story_projects'][] = [
                'year' => $year,
                'story_projects' => $this->getStoryProjectByYearCfp($idCustomer, $year)
            ];

            $results['learners'][] = [
                'year' => $year,
                'learners' => $this->getLearnerByYearCfp($year, $idCustomer)
            ];
        }

        return $results;
    }

    private function totalCa($year, $idCustomer){
        $customer = $this->isNotSubContractor($idCustomer);
        return $customer ? $this->totalCaCfp($year ,$idCustomer) : $this->totalCaSponsor($year ,$idCustomer);
    }

    private function getGeneralReportingSponsor($idCustomer){

        $idProjectSubContractors = $this->idProjetSuBContrators();

        $project_years = DB::table('v_union_projets')
                            ->select(DB::raw('YEAR(dateDebut) as year'))
                            ->where(function ($query) use($idCustomer){
                                $query->where('idEtp', $idCustomer)
                                    ->orWhere('idEtp_inter', $idCustomer);
                            })
                            ->whereIn('idProjet', $idProjectSubContractors)
                            ->groupBy(DB::raw('YEAR(dateDebut)'))
                            ->orderBy(DB::raw('YEAR(dateDebut)'), 'desc')
                            ->whereIn('project_status', ['Terminé', 'Cloturé'])
                                                        ->where('project_is_trashed', 0)
                            ->get();

        $results = [
            'ca' => [],
            'count_projects' => [],
            'story_projects' => [],
            'learners' => []
        ];

        foreach ($project_years as $project) {
            $year = $project->year;

            $results['ca'][] = [
                'year' => $year,
                'ca_customer' => $this->getCaByYearSponsor($year, $idCustomer),
                'total' => $this->totalCa($year, $idCustomer)
            ];

            $results['count_projects'][] = [
                'year' => $year,
                'projects' => $this->getProjectByYearSponsor($year, $idCustomer)
            ];

            $results['story_projects'][] = [
                'year' => $year,
                'story_projects' => $this->getStoryProjectByYearSponsor($idCustomer, $year)
            ];

            $results['learners'][] = [
                'year' => $year,
                'learners' => $this->getLearnerByYearSponsor($year, $idCustomer)
            ];
        }

        return $results;
    }


    private function totalCaCfp($year, $idCustomer)
    {
        $query = DB::table('v_union_projets')
                    ->where(function ($query) use ($idCustomer) {
                        $query->where('idEtp', $idCustomer)
                            ->orWhere('idEtp_inter', $idCustomer);
                    })
                    ->where(function ($query) {
                        $query->where('idCfp_intra', Customer::idCustomer())
                            ->orWhere('idCfp_inter', Customer::idCustomer());
                    })
                    ->whereYear('dateDebut', $year)
                    ->whereIn('project_status', ['Terminé', 'Cloturé'])
                                                ->where('project_is_trashed', 0)
                    ->value(DB::raw('SUM(total_ttc) as total_ttc'));
        return $query;
    }

    private function totalCaSponsor($year, $idCustomer)
    {
        $idProjectSubContractors = $this->idProjetSuBContrators();
        $query = DB::table('v_union_projets')
                    ->where(function ($query) use ($idCustomer) {
                        $query->where('idEtp', $idCustomer)
                            ->orWhere('idEtp_inter', $idCustomer);
                    })
                    ->whereIn('idProjet', $idProjectSubContractors)
                    ->whereYear('dateDebut', $year)
                    ->whereIn('project_status', ['Terminé', 'Cloturé'])
                                                ->where('project_is_trashed', 0)
                    ->value(DB::raw('SUM(total_ht_sub_contractor) as total_ttc'));
        return $query;
    }

    private function totalProjectCfp($idCustomer){
        $total_project = DB::table('v_union_projets')
                    ->where(function ($query) use ($idCustomer) {
                        $query->where('idEtp', $idCustomer)
                            ->orWhere('idEtp_inter', $idCustomer);
                    })
                    ->where(function ($query) {
                        $query->where('idCfp_intra', Customer::idCustomer())
                            ->orWhere('idCfp_inter', Customer::idCustomer());
                    })
                    ->whereIn('project_status', ['Terminé', 'Cloturé'])
                                                ->where('project_is_trashed', 0)
                    ->value(DB::raw('COUNT(idProjet) as nb_project'));
        return $total_project;
    }

    private function totalProjectSponsor($idCustomer){
        $idProjectSubContractors = $this->idProjetSuBContrators();

        $total_project = DB::table('v_union_projets')
                    ->where(function ($query) use ($idCustomer) {
                        $query->where('idEtp', $idCustomer)
                            ->orWhere('idEtp_inter', $idCustomer);
                    })
                    ->whereIn('idProjet', $idProjectSubContractors)
                    ->whereIn('project_status', ['Terminé', 'Cloturé'])
                                                ->where('project_is_trashed', 0)
                    ->value(DB::raw('COUNT(idProjet) as nb_project'));
        return $total_project;
    }

    private function totalLearnerCfp($idCustomer){
        $total_learner = DB::table('v_union_projets AS V')
                ->join('detail_apprenants AS D', 'D.idProjet', '=', 'V.idProjet')
                ->join('employes as E', 'E.idEmploye', '=', 'D.idEmploye')
                ->where(function ($query) use ($idCustomer) {
                    $query->where('V.idEtp', $idCustomer)
                        ->orWhere('V.idEtp_inter', $idCustomer);
                })
                ->where(function ($query) {
                    $query->where('V.idCfp_intra', Customer::idCustomer())
                        ->orWhere('V.idCfp_inter', Customer::idCustomer());
                })
                ->where('V.dateFin', '<', now())
                ->value( DB::raw('COUNT(D.idEmploye) as nb_learner'));
        return $total_learner;
    }

    private function totalLearnerSponsor($idCustomer){
        $idProjectSubContractors = $this->idProjetSuBContrators();

        $total_learner = DB::table('v_union_projets AS V')
                ->join('detail_apprenants AS D', 'D.idProjet', '=', 'V.idProjet')
                ->join('employes as E', 'E.idEmploye', '=', 'D.idEmploye')
                ->where(function ($query) use ($idCustomer) {
                    $query->where('V.idEtp', $idCustomer)
                        ->orWhere('V.idEtp_inter', $idCustomer);
                })
                ->where('V.dateFin', '<', now())
                ->whereIn('V.idProjet', $idProjectSubContractors)
                ->value( DB::raw('COUNT(D.idEmploye) as nb_learner'));
        return $total_learner;
    }

    public function getCaByYearCfp($year, $idCustomer)
    {
        $months = [
            1 => 'Janvier', 2 => 'Fevrier', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Aout',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Decembre'
        ];

        $projects = DB::table('v_union_projets')
                    ->select(
                        DB::raw('MONTH(dateDebut) as month_number'),
                        DB::raw('SUM(total_ttc) as total_ttc')
                    )
                    ->where(function ($query) use ($idCustomer) {
                        $query->where('idEtp', $idCustomer)
                            ->orWhere('idEtp_inter', $idCustomer);
                    })
                    ->where(function ($query) {
                        $query->where('idCfp_intra', Customer::idCustomer())
                            ->orWhere('idCfp_inter', Customer::idCustomer());
                    })
                    ->whereYear('dateDebut', $year)
                    ->whereIn('project_status', ['Terminé', 'Cloturé'])
                                                ->where('project_is_trashed', 0)
                    ->groupBy(DB::raw('MONTH(dateDebut)'))
                    ->get()
                    ->keyBy('month_number');

        $results = [];
        foreach ($months as $month_number => $month_name) {
            $results[] = [
                'month' => $month_name,
                'total_ttc' => $projects->get($month_number)->total_ttc ?? 0
            ];
        }

        return $results;
    }

    public function getCaByYearSponsor($year, $idCustomer)
    {
        $months = [
            1 => 'Janvier', 2 => 'Fevrier', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Aout',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Decembre'
        ];

        $idProjectSubContractors = $this->idProjetSuBContrators();

        $projects = DB::table('v_union_projets')
                    ->select(
                        DB::raw('MONTH(dateDebut) as month_number'),
                        DB::raw('SUM(total_ttc) as total_ttc')
                    )
                    ->where(function ($query) use ($idCustomer) {
                        $query->where('idEtp', $idCustomer)
                            ->orWhere('idEtp_inter', $idCustomer);
                    })
                    ->whereIn('idProjet', $idProjectSubContractors)
                    ->whereYear('dateDebut', $year)
                    ->whereIn('project_status', ['Terminé', 'Cloturé'])
                                                ->where('project_is_trashed', 0)
                    ->groupBy(DB::raw('MONTH(dateDebut)'))
                    ->get()
                    ->keyBy('month_number');

        $results = [];
        foreach ($months as $month_number => $month_name) {
            $results[] = [
                'month' => $month_name,
                'total_ttc' => $projects->get($month_number)->total_ttc ?? 0
            ];
        }

        return $results;
    }

    public function getProjectByYearCfp($year, $idCustomer)
    {
        $months = [
            1 => 'Janvier', 2 => 'Fevrier', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Aout',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Decembre'
        ];

        $projects = DB::table('v_union_projets')
                    ->select(
                        DB::raw('MONTH(dateDebut) as month_number'),
                        DB::raw('COUNT(idProjet) as nb_project')
                    )
                    ->where(function ($query) use ($idCustomer) {
                        $query->where('idEtp', $idCustomer)
                            ->orWhere('idEtp_inter', $idCustomer);
                    })
                    ->where(function ($query) {
                        $query->where('idCfp_intra', Customer::idCustomer())
                            ->orWhere('idCfp_inter', Customer::idCustomer());
                    })
                    ->whereYear('dateDebut', $year)
                    ->whereIn('project_status', ['Terminé', 'Cloturé'])
                                                ->where('project_is_trashed', 0)
                    ->groupBy(DB::raw('MONTH(dateDebut)'))
                    ->get()
                    ->keyBy('month_number');

        $results = [];
        foreach ($months as $month_number => $month_name) {
            $results[] = [
                'month' => $month_name,
                'nb_project' => $projects->get($month_number)->nb_project ?? 0
            ];
        }

        return $results;
    }

    public function getProjectByYearSponsor($year, $idCustomer)
    {
        $months = [
            1 => 'Janvier', 2 => 'Fevrier', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Aout',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Decembre'
        ];

        $projects = DB::table('v_union_projets')
                    ->select(
                        DB::raw('MONTH(dateDebut) as month_number'),
                        DB::raw('COUNT(idProjet) as nb_project')
                    )
                    ->where(function ($query) use ($idCustomer) {
                        $query->where('idEtp', $idCustomer)
                            ->orWhere('idEtp_inter', $idCustomer);
                    })
                    ->whereYear('dateDebut', $year)
                    ->whereIn('project_status', ['Terminé', 'Cloturé'])
                                                ->where('project_is_trashed', 0)
                    ->groupBy(DB::raw('MONTH(dateDebut)'))
                    ->get()
                    ->keyBy('month_number');

        $results = [];
        foreach ($months as $month_number => $month_name) {
            $results[] = [
                'month' => $month_name,
                'nb_project' => $projects->get($month_number)->nb_project ?? 0
            ];
        }

        return $results;
    }

    public function getLearnerByYearCfp($year, $idCustomer)
    { 
        $months = [
            1 => 'Janvier', 2 => 'Fevrier', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Aout',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Decembre'
        ];

        $projects = DB::table('v_union_projets AS V')
                ->join('detail_apprenants AS D', 'D.idProjet', '=', 'V.idProjet')
                ->join('employes as E', 'E.idEmploye', '=', 'D.idEmploye')
                ->select(
                    DB::raw('MONTH(V.dateDebut) as month_number'),
                    DB::raw('COUNT(D.idEmploye) as nb_learner')
                )
                ->where(function ($query) use ($idCustomer) {
                    $query->where('V.idEtp', $idCustomer)
                        ->orWhere('V.idEtp_inter', $idCustomer);
                })
                ->where(function ($query) {
                    $query->where('V.idCfp_intra', Customer::idCustomer())
                        ->orWhere('V.idCfp_inter', Customer::idCustomer());
                })
                ->whereYear('V.dateDebut', $year)
                ->where('V.dateFin', '<', now())
                ->groupBy(DB::raw('MONTH(V.dateDebut)'))
                ->get()
                ->keyBy('month_number');

        $results = [];
        foreach ($months as $month_number => $month_name) {
            $results[] = [
                'month' => $month_name,
                'nb_learner' => $projects->get($month_number)->nb_learner ?? 0
            ];
        }

        return $results;
    }

    public function getLearnerByYearSponsor($year, $idCustomer)
    { 
        $months = [
            1 => 'Janvier', 2 => 'Fevrier', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Aout',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Decembre'
        ];

        $projects = DB::table('v_union_projets AS V')
                ->join('detail_apprenants AS D', 'D.idProjet', '=', 'V.idProjet')
                ->join('employes as E', 'E.idEmploye', '=', 'D.idEmploye')
                ->select(
                    DB::raw('MONTH(V.dateDebut) as month_number'),
                    DB::raw('COUNT(D.idEmploye) as nb_learner')
                )
                ->where(function ($query) use ($idCustomer) {
                    $query->where('V.idEtp', $idCustomer)
                        ->orWhere('V.idEtp_inter', $idCustomer);
                })
                ->whereYear('V.dateDebut', $year)
                ->where('V.dateFin', '<', now())
                ->groupBy(DB::raw('MONTH(V.dateDebut)'))
                ->get()
                ->keyBy('month_number');

        $results = [];
        foreach ($months as $month_number => $month_name) {
            $results[] = [
                'month' => $month_name,
                'nb_learner' => $projects->get($month_number)->nb_learner ?? 0
            ];
        }

        return $results;
    }

    public function getStoryProjectByYearCfp($idCustomer, $year)
    {
        $months = [
            1 => 'Janvier', 2 => 'Fevrier', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Aout',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Decembre'
        ];

        $results = [];
        foreach ($months as $month_number => $month_name) {
            $results[] = [
                'month' => $month_name,
                'projects' => $this->getStoryProjectByMonthCfp($month_number, $year, $idCustomer)
            ];
        }

        return $results;
    }

    public function getStoryProjectByYearSponsor($idCustomer, $year)
    {
        $months = [
            1 => 'Janvier', 2 => 'Fevrier', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Aout',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Decembre'
        ];

        $results = [];
        foreach ($months as $month_number => $month_name) {
            $results[] = [
                'month' => $month_name,
                'projects' => $this->getStoryProjectByMonthSponsor($month_number, $year, $idCustomer)
            ];
        }

        return $results;
    }

    private function getStoryProjectByMonthCfp($month_number, $year, $idCustomer){
        $projects = DB::table('v_union_projets')
                    ->select('idProjet', 'module_name', 'project_type', 'total_ttc', 'dateDebut', 'dateFin')
                    ->where(function ($query) use ($idCustomer) {
                        $query->where('idEtp', $idCustomer)
                            ->orWhere('idEtp_inter', $idCustomer);
                    })
                    ->where(function ($query) {
                        $query->where('idCfp_intra', Customer::idCustomer())
                            ->orWhere('idCfp_inter', Customer::idCustomer());
                    })
                    ->whereYear('dateDebut', $year)
                    ->whereIn('project_status', ['Terminé', 'Cloturé'])
                                                ->where('project_is_trashed', 0)
                    ->whereMonth('dateDebut', $month_number)
                    ->get();
        
        return $projects;
    }

    private function getStoryProjectByMonthSponsor($month_number, $year, $idCustomer){
        $projects = DB::table('v_union_projets')
                    ->select('idProjet', 'module_name', 'project_type', 'total_ht_sub_contractor as total_ttc', 'dateDebut', 'dateFin')
                    ->where(function ($query) use ($idCustomer) {
                        $query->where('idEtp', $idCustomer)
                            ->orWhere('idEtp_inter', $idCustomer);
                    })
                    ->whereYear('dateDebut', $year)
                    ->whereIn('project_status', ['Terminé', 'Cloturé'])
                                                ->where('project_is_trashed', 0)
                    ->whereMonth('dateDebut', $month_number)
                    ->get();
        
        return $projects;
    }



   public function getCustomer()
    {
        $customers = DB::table('customers as C')
            ->join('cfp_etps as E', 'C.idCustomer', '=', 'E.idEtp')
            ->where('idCfp', Customer::idCustomer()) // Ensure this method correctly returns the ID
            ->select('C.idCustomer', 'C.customerName') // Select both ID and name
            ->get(); // Get the results as a collection of objects

        return response()->json($customers); // Return the collection as JSON
    }


    // Export Apprenant List
    public function exportFinanceXl()
    {
        return Excel::download(new FinanceExport, 'Finance.xlsx');
    }
    public function exportXlCl(Request $request)
    {
        return Excel::download(new ClientExcelExport($request->session()->get('data')), 'client.xlsx');
    }
    public function exportPdfCl()
    {
        $all_learner = session()->get('data');
        $data_filter = session()->get('data_filter');
        $pdf = PDF::loadView('CFP.Reporting.client.client', compact(['all_learner', 'data_filter']))->setPaper('a4', 'landscape')->setOption(['defaultFont' => 'Helvetica']);
        return $pdf->download('reportingClient.pdf');
    }

    public function searchEtp($name_etp)
    {
        $idCfp = Auth::user()->id;

        $etps = DB::table('v_apprenant_information')
            ->where('idCfp', $idCfp)
            ->where(function ($query) use ($name_etp) {
                $query->where('etp_name', 'LIKE', '%' . $name_etp . '%');
            })
            ->get();

        return response()->json(['etps' => $etps]);
    }
}
