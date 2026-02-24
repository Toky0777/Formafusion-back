<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class ChiffreAffaireRepportingCfp extends Controller
{
    // public function chiffreCfp(): View
    // {
    //     $total_price = $this->getTotalPriceProject();
    //     $total_reference = $this->getTotalReference();
    //     $total_dossier = $this->getTotalDossier();

    //     return view('CFP.Reporting.ca.index', [
    //         'ca_by_project' => $this->getProject(),
    //         'ca_by_module' => $this->getCaByModule(),
    //         'ca_by_customer' => $this->getCaByProject(),
    //         'ca_by_month' => $this->getCaByMonth(),
    //         'total_price' => $this->getTotalPriceProject(),
    //         'ca_by_folder' => $this->getCaByFolder(),
    //         'ca_by_refer' => $this->getCaByReference(),
    //         'total_price_reference' => $total_reference,
    //         'total_price_dossier' => $total_dossier,
    //         'percentage_reference' =>  number_format($this->getPercentageProject($total_reference, $total_price), 2),
    //         'percentage_dossier' =>  number_format($this->getPercentageProject($total_dossier, $total_price), 2)
    //     ]);
        
    // }

    public function caProjet()
    {
        $projects = $this->getProject(now()->year);
        $total_price = $this->getTotalPriceProject(now()->year);

        return response()->json([
            'projects' => $projects, 
            'total_price' => $total_price
        ]);
    }

    public function caProjetYear($year)
    {
        $projects = $this->getProject($year);
        $total_price = $this->getTotalPriceProject($year);

        return response()->json([
            'projects' => $projects,
            'total_price' => $total_price
        ]);
    }

    public function caFolder(){
        $folders = $this->getCaByFolder(now()->year);
        $total_price = $this->getTotalDossier(now()->year);
        $percentage = number_format($this->getPercentageProject($total_price, $this->getTotalPriceProject(now()->year)), 2);

        $totalProjects = array_sum(array_map(function($folder) {
            return count($folder['projects']);
        }, $folders));

        return response()->json([
            'folders' => $folders, 
            'total_price' => $total_price, 
            'percentage' => $percentage, 
            'totalProjects' => $totalProjects
        ]);
    }

    public function caFolderYear($year){
        $folders = $this->getCaByFolder($year);
        $total_price = $this->getTotalDossier($year);
        $percentage = number_format($this->getPercentageProject($total_price, $this->getTotalPriceProject($year)), 2);

        $totalProjects = array_sum(array_map(function($folder) {
            return count($folder['projects']);
        }, $folders));

        return response()->json([
            'folders' => $folders,
            'total_price' => $total_price,
            'totalProjects' => $totalProjects,
            'percentage' => $percentage
        ]);
    }

    public function caCustomer()
    {
        $customers = $this->getCaByProject(now()->year);
        $total_price = $this->getTotalPriceProject(now()->year);

        $totalProjects = array_sum(array_map(function($customer) {
            return count($customer['projects']);
        }, $customers));

        return response()->json([
            'customers' => $customers, 
            'total_price' => $total_price, 
            'totalProjects' => $totalProjects
        ]);
    }

    public function caCustomerYear($year)
    {
        $customers = $this->getCaByProject($year);
        $total_price = $this->getTotalPriceProject($year);

        $totalProjects = array_sum(array_map(function($customer) {
            return count($customer['projects']);
        }, $customers));

        return response()->json([
            'customers' => $customers,
            'total_price' => $total_price,
            'totalProjects' => $totalProjects
        ]);
    }

    public function caReference(){
        $references = $this->getCaByReference(now()->year);
        $total_price = $this->getTotalReference(now()->year);

        $totalProjects = array_sum(array_map(function($reference) {
            return count($reference['projects']);
        }, $references));

        $percentage = number_format($this->getPercentageProject($total_price, $this->getTotalPriceProject(now()->year)), 2);

        return response()->json([
            'references' => $references,  
            'total_price' => $total_price, 
            'percentage' => $percentage, 
            'totalProjects' => $totalProjects
        ]);
    }

    public function caReferenceYear($year){
        $references = $this->getCaByReference($year);
        $total_price = $this->getTotalReference($year);

        $totalProjects = array_sum(array_map(function($reference) {
            return count($reference['projects']);
        }, $references));

        $percentage = number_format($this->getPercentageProject($total_price, $this->getTotalPriceProject($year)), 2);

        return response()->json([
            'references' => $references,
            'total_price' => $total_price,
            'totalProjects' => $totalProjects,
            'percentage' => $percentage
        ]);
    }

    public function caMonth()
    {
        $months = $this->getCaByMonth(now()->year);
        $total_price = $this->getTotalPriceProject(now()->year);

        $totalProjects = array_sum(array_map(function($month) {
            return count($month['projects']);
        }, $months));

        return response()->json([
            'months' => $months, 
            'total_price' => $total_price, 
            'totalProjects' => $totalProjects
        ]);
    }

    public function caMonthYear($year)
    {
        $months = $this->getCaByMonth($year);
        $total_price = $this->getTotalPriceProject($year);

        $totalProjects = array_sum(array_map(function($month) {
            return count($month['projects']);
        }, $months));

        return response()->json([
            'months' => $months,
            'total_price' => $total_price,
            'totalProjects' => $totalProjects
        ]);
    }

    public function caModule(){
        $modules = $this->getCaByModule(now()->year);
        $total_price = $this->getTotalPriceProject(now()->year);
        
        $totalProjects = array_sum(array_map(function($module) {
            return count($module['projects']);
        }, $modules));

        return response()->json([
            'modules' => $modules, 
            'total_price' => $total_price, 
            'totalProjects' => $totalProjects
        ]);
    }

    public function caModuleYear($year){
        $modules = $this->getCaByModule($year);
        $total_price = $this->getTotalPriceProject($year);
        
        $totalProjects = array_sum(array_map(function($module) {
            return count($module['projects']);
        }, $modules));

        return response()->json([
            'modules' => $modules,
            'total_price' => $total_price,
            'totalProjects' => $totalProjects
        ]);
    }

    private function getTotalReference($year)
    {
        $total = DB::table('v_union_projets')
                        ->where(function ($query){
                            $query->where('idCfp_intra', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        ->whereNot('module_name', 'Default module')
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->whereNotNull('project_reference')
                        ->whereYear('dateDebut', $year)
                        ->sum('total_ttc');
        return $total;
    }

    private function getTotalDossier($year)
    {
        $idDossiers= DB::table('dossiers')
                ->where('idCfp', Customer::idCustomer())
                ->whereYear('created_at', $year)
                ->pluck('idDossier');

        $idProjets = DB::table('projets')
                    ->whereIn('idDossier', $idDossiers)
                    ->pluck('idProjet');
        
        $total = DB::table('v_union_projets')
                        ->whereIn('idProjet', $idProjets)
                        ->whereNot('module_name', 'Default module')
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->sum('total_ht');

        return $total;
    }

public function getCaByReference($year)
{
    $references = DB::table('v_union_projets')
        ->select('project_reference')
        ->where(function ($query){
            $query->where('idCfp_intra', Customer::idCustomer())
                ->orWhere('idCfp_inter', Customer::idCustomer());
        })
        ->whereNot('module_name', 'Default module')
        ->whereIn('project_status', ['Terminé', 'Cloturé'])
        ->whereNotNull('project_reference')
        ->whereYear('dateDebut', $year)
        ->groupBy('project_reference')
        ->get();

    $results = [];
    $i = 1;

    foreach($references as $reference){
        // Maka ny projects rehetra mifandraika amin'ny reference
        $projects = $this->getProjectByReference($reference->project_reference, $year);

        // Manala doublons amin'ny alalan'ny serialization (mifototra amin'ny singa manan-danja)
        $projectsUnique = collect($projects)->unique(function($item) {
            return $item['idProjet'] . '_' . $item['project_reference'] . '_' . $item['dateDebut'] . '_' . $item['total_ttc'];
        })->values()->all();

        $total = array_sum(array_column($projectsUnique, 'total_ttc'));
        $percentage = $this->getPercentageProject($total, $this->getTotalPriceProject($year));

        $results[] = [
            'id' => $i++,
            'name' => $reference->project_reference,
            'total_ttc' => $total,
            'percentage' => number_format($percentage, 2),
            'count_project' => count($projectsUnique),
            'projects' => $projectsUnique
        ];
    }

    return $results;
}


    private function getProjectByReference($ref, $year){
        $projects = DB::table('v_union_projets')
                        ->select('idProjet', 'module_name', 'dateDebut', 'dateFin', 'total_ttc', 'project_reference', 'etp_name')
                        ->where(function ($query){
                            $query->where('idCfp_intra', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        ->whereNot('module_name', 'Default module')
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->where('project_reference', $ref)
                        ->whereYear('dateDebut', $year)
                        ->whereNotNull('project_reference')
                        ->get();

        $results = [];

        foreach($projects as $project){
            $results[] = [
                'idProjet' => $project->idProjet,
                'moduleName' => $project->module_name,
                'project_reference' => $project->project_reference,
                'etpName' => $project->etp_name,
                'dateDebut' => Carbon::parse($project->dateDebut)->format('j.m.y'),
                'dateFin' => Carbon::parse($project->dateFin)->format('j.m.y'),
                'total_ttc' => $project->total_ttc,
                'percentage' => number_format($this->getPercentageProject($project->total_ttc, $this->getTotalPriceProject($year)), 2),
            ];
        }

        return $results;
    }

    private function getTotalProjectByReference($ref)
    {
        $total = DB::table('v_union_projets')
                        ->where(function ($query){
                            $query->where('idCfp_intra', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        ->whereNot('module_name', 'Default module')
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->where('project_reference', $ref)
                        ->sum('total_ttc');

        return $total;
    }

    private function getCaByFolder($year)
    {
        $dossiers = DB::table('dossiers')
            ->select('idDossier', 'nomDossier')
            ->where('idCfp', Customer::idCustomer())
            ->whereYear('created_at', $year)
            ->get();
        
        $results = [];

        foreach($dossiers as $dossier){
            $results[] = [
                'id' => $dossier->idDossier,
                'name' => $dossier->nomDossier,
                'total_ttc' => $this->getTotalProjectByFolder($dossier->idDossier),
                'percentage' => number_format($this->getPercentageProject($this->getTotalProjectByFolder($dossier->idDossier), $this->getTotalPriceProject($year)), 2),
                'count_project' => count($this->getProjectByFolder($dossier->idDossier, $year)),
                'projects' => $this->getProjectByFolder($dossier->idDossier, $year)
            ]; 
        }

        return $results;
    }

    private function getTotalProjectByFolder($idDossier){
        $idProjets = DB::table('projets')
            ->where('idDossier', $idDossier)
            ->pluck('idProjet');
        
        $total = DB::table('v_union_projets')
                        ->whereIn('idProjet', $idProjets)
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->where('project_is_trashed', 0)
                        ->sum('total_ttc');

        return $total;
    }

    private function getProjectByFolder($idDossier, $year){
        $idProjets = DB::table('projets')
            ->where('idDossier', $idDossier)
            ->pluck('idProjet');
        
        $projects = DB::table('v_union_projets')
                        ->select('idProjet', 'module_name', 'dateDebut', 'dateFin', 'total_ttc', 'project_reference', 'etp_name')
                        ->whereIn('idProjet', $idProjets)
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->where('project_is_trashed', 0)
                        ->get();

        $results = [];

        foreach($projects as $project){
            $results[] = [
                'idProjet' => $project->idProjet,
                'moduleName' => $project->module_name,
                'project_reference' => $project->project_reference,
                'etpName' => $project->etp_name,
                'dateDebut' => Carbon::parse($project->dateDebut)->format('j.m.y'),
                'dateFin' => Carbon::parse($project->dateFin)->format('j.m.y'),
                'total_ttc' => $project->total_ttc,
                'percentage' => number_format($this->getPercentageProject($project->total_ttc, $this->getTotalPriceProject($year)), 2),
            ];
        }

        return $results;
    }

    private function getCaByMonth($year)
    {
        $idProjectSubContractors = DB::table('project_sub_contracts')
                                        ->where('idSubContractor', Customer::idCustomer())
                                        ->pluck('idProjet');

        $projectByMonth = DB::table('v_union_projets')
            ->select(DB::raw('MONTH(dateDebut) as month_value'),
                        DB::raw('CASE WHEN MONTH(dateDebut) = 1 THEN "Janvier"
                                    WHEN MONTH(dateDebut) = 2 THEN "Fevrier"
                                    WHEN MONTH(dateDebut) = 3 THEN "Mars"
                                    WHEN MONTH(dateDebut) = 4 THEN "Avril"
                                    WHEN MONTH(dateDebut) = 5 THEN "Mai"
                                    WHEN MONTH(dateDebut) = 6 THEN "Juin"
                                    WHEN MONTH(dateDebut) = 7 THEN "Juillet"
                                    WHEN MONTH(dateDebut) = 8 THEN "Aout"
                                    WHEN MONTH(dateDebut) = 9 THEN "Septembre"
                                    WHEN MONTH(dateDebut) = 10 THEN "Octobre"
                                    WHEN MONTH(dateDebut) = 11 THEN "Novembre"
                                    WHEN MONTH(dateDebut) = 12 THEN "Decembre"
                                    END as month'),
                        DB::raw('SUM(total_ttc) as total_ttc'),
                        DB::raw('COUNT(idProjet) as count_project'))
            ->where(function ($query) {
                $query->where('idCfp_intra', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer());
            })
            ->whereNot('module_name', 'Default module')
            ->whereIn('project_status', ['Terminé', 'Cloturé'])
            ->where('project_is_trashed', 0)
            ->whereYear('dateDebut', $year)
            ->groupBy(DB::raw('MONTH(dateDebut)'))
            ->get();
        
        $projectByMonthSubContractor = DB::table('v_union_projets')
            ->select(DB::raw('MONTH(dateDebut) as month_value'),
                        DB::raw('CASE WHEN MONTH(dateDebut) = 1 THEN "Janvier"
                                    WHEN MONTH(dateDebut) = 2 THEN "Fevrier"
                                    WHEN MONTH(dateDebut) = 3 THEN "Mars"
                                    WHEN MONTH(dateDebut) = 4 THEN "Avril"
                                    WHEN MONTH(dateDebut) = 5 THEN "Mai"
                                    WHEN MONTH(dateDebut) = 6 THEN "Juin"
                                    WHEN MONTH(dateDebut) = 7 THEN "Juillet"
                                    WHEN MONTH(dateDebut) = 8 THEN "Aout"
                                    WHEN MONTH(dateDebut) = 9 THEN "Septembre"
                                    WHEN MONTH(dateDebut) = 10 THEN "Octobre"
                                    WHEN MONTH(dateDebut) = 11 THEN "Novembre"
                                    WHEN MONTH(dateDebut) = 12 THEN "Decembre"
                                    END as month'),
                        DB::raw('SUM(total_ht_sub_contractor) as total_ht_sub_contractor'),
                        DB::raw('COUNT(idProjet) as count_project'))
            ->whereIn('idProjet', $idProjectSubContractors)
            ->whereNot('module_name', 'Default module')
            ->whereIn('project_status', ['Terminé', 'Cloturé'])
            ->where('project_is_trashed', 0)
            ->whereYear('dateDebut', $year)
            ->groupBy(DB::raw('MONTH(dateDebut)'))
            ->get();
        
        $total_price = $this->getTotalPriceProject($year);
        
        $results = [];
        
        foreach (range(1, 12) as $monthValue) {
            $projectData = $projectByMonth->firstWhere('month_value', $monthValue);
            $subContractorData = $projectByMonthSubContractor->firstWhere('month_value', $monthValue);
        
            $monthlyTotalTTC = ($projectData ? $projectData->total_ttc : 0) + ($subContractorData ? $subContractorData->total_ht_sub_contractor : 0);
            $monthlyCount = ($projectData ? $projectData->count_project : 0) + ($subContractorData ? $subContractorData->count_project : 0);
        
            $percentage = $total_price > 0 ? number_format(($monthlyTotalTTC / $total_price) * 100, 2) : 0;
        
            $results[] = [
                'monthValue' => $monthValue,
                'month_name' => $this->getMonthName($monthValue),
                'count_project' => $monthlyCount,
                'total_ttc' => $monthlyTotalTTC,
                'percentage' => $percentage,
                'projects' => $this->getProjectMonth($monthValue, $year)
            ];
        }                    

        return $results;  
    }

    private function getMonthName($monthValue)
    {
        $months = [
            1 => "Janvier", 2 => "Fevrier", 3 => "Mars", 4 => "Avril", 5 => "Mai", 6 => "Juin",
            7 => "Juillet", 8 => "Aout", 9 => "Septembre", 10 => "Octobre", 11 => "Novembre", 12 => "Decembre"
        ];
        return $months[$monthValue] ?? null;
    }

    private function getProjectMonth($month, $year){
        $idProjectSubContractors = DB::table('project_sub_contracts')
                                        ->where('idSubContractor', Customer::idCustomer())
                                        ->pluck('idProjet');

        $projectsNoSubContractor = DB::table('v_union_projets')
                            ->select('idProjet', 'module_name', 'dateDebut', 'dateFin', 'total_ttc', 'project_reference', 'etp_name')
                            ->where(function ($query){
                                $query->where('idCfp_intra', Customer::idCustomer())
                                    ->orWhere('idCfp_inter', Customer::idCustomer());
                            })
                            ->whereMonth('dateDebut', $month)
                            ->whereNot('module_name', 'Default module')
                            ->whereIn('project_status', ['Terminé', 'Cloturé'])
                            ->where('project_is_trashed', 0)
                            ->whereYear('dateDebut', $year);

        $projectSubContractors = DB::table('v_union_projets')
                        ->select('idProjet', 'module_name', 'dateDebut', 'dateFin', 'total_ht_sub_contractor as total_ttc', 'project_reference', 'etp_name')
                        ->whereMonth('dateDebut', $month)
                        ->whereIn('idProjet', $idProjectSubContractors)
                        ->whereNot('module_name', 'Default module')
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->where('project_is_trashed', 0)
                        ->whereYear('dateDebut', $year);

        $projects = $projectsNoSubContractor->union($projectSubContractors)->get();
                    
        $results = [];

        foreach($projects as $project){
            $results[] = [
                'idProjet' => $project->idProjet,
                'moduleName' => $project->module_name,
                'project_reference' => $project->project_reference,
                'etpName' => $project->etp_name,
                'dateDebut' => Carbon::parse($project->dateDebut)->format('j.m.y'),
                'dateFin' => Carbon::parse($project->dateFin)->format('j.m.y'),
                'total_ttc' => $project->total_ttc,
                'percentage' => number_format($this->getPercentageProject($project->total_ttc, $this->getTotalPriceProject($year)), 2),
            ];
        }

        return $results;
    }

    private function getCaByProject($year)
    {
        $id_etp = $this->getIdEtp();

        $idProjectSubContractors = DB::table('project_sub_contracts')
                                        ->where('idSubContractor', Customer::idCustomer())
                                        ->pluck('idProjet');

        $customerNoSubContractor = DB::table('v_union_projets')
                        ->select('etp_name', DB::raw('1 as `index`'), DB::raw('SUM(total_ttc) as total_ttc'), DB::raw('COUNT(idProjet) as count_project'), DB::raw('COALESCE(idEtp, idEtp_inter) as idEtp'))
                        ->where(function ($query){
                            $query->where('idCfp_intra', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        ->where(function ($query) use($id_etp){
                            $query->whereIn('idEtp', $id_etp)
                                  ->orWhereIn('idEtp_inter', $id_etp);
                        })
                        ->whereNot('module_name', 'Default module')
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->where('project_is_trashed', 0)
                        ->whereYear('dateDebut', $year)
                        ->groupBy('etp_name');

        $customerSubContractor = DB::table('v_union_projets')
                            ->select('etp_name', DB::raw('2 as `index`'),DB::raw('SUM(total_ht_sub_contractor) as total_ttc'), DB::raw('COUNT(idProjet) as count_project'), DB::raw('COALESCE(idEtp, idEtp_inter) as idEtp'))
                            ->whereIn('idProjet', $idProjectSubContractors)
                            ->whereNot('module_name', 'Default module')
                            ->whereIn('project_status', ['Terminé', 'Cloturé'])
                            ->where('project_is_trashed', 0)
                            ->whereYear('dateDebut', $year)
                            ->groupBy('etp_name');

        $customers = $customerNoSubContractor->union($customerSubContractor)->orderBy('total_ttc', 'desc')->get();

        $total_price = $this->getTotalPriceProject($year);

        $results = [];
        foreach($customers as $customer){
            $results[] = [
                'idCustomer' => $customer->idEtp,
                'etp_name' => $customer->etp_name,
                'count_project' => $customer->count_project,
                'total_ttc' => $customer->total_ttc,
                'percentage' => number_format($this->getPercentageProject($customer->total_ttc, $total_price), 2),
                'projects' => $this->getProjectCustomer($customer->index, $customer->idEtp, $year)
            ];
        }

        return $results;
    }

    private function getProjectCustomer($index, $idEtp, $year){
        return ($index == 1) ? $this->getProjectCustomerNoSubContractor($idEtp, $year) : $this->getProjectCustomerSubContractor($idEtp, $year);
    }

    private function getProjectCustomerNoSubContractor($idEtp, $year)
    {
        $projects = DB::table('v_union_projets')
                        ->select('idProjet', 'module_name', 'dateDebut', 'dateFin', 'total_ttc', 'project_reference')
                        ->where(function ($query){
                            $query->where('idCfp_intra', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        ->where(function ($query) use($idEtp){
                            $query->where('idEtp', $idEtp)
                                  ->orWhere('idEtp_inter', $idEtp);
                        })
                        ->whereNot('module_name', 'Default module')
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->whereYear('dateDebut', $year)
                        ->where('project_is_trashed', 0)
                        ->get();
        
        $results = [];

        foreach($projects as $project){
            $results[] = [
                'idProjet' => $project->idProjet,
                'moduleName' => $project->module_name,
                'projectReference' => $project->project_reference,
                'total_ttc' => $project->total_ttc,
                'dateDebut' => Carbon::parse($project->dateDebut)->format('j.m.y'),
                'dateFin' => Carbon::parse($project->dateFin)->format('j.m.y'),
                'percentage' => number_format($this->getPercentageProject($project->total_ttc, $this->getTotalPriceProject($year)), 2)
            ];
        }

        return $results;
    }

    private function getProjectCustomerSubContractor($idEtp, $year){

        $idProjectSubContractors = DB::table('project_sub_contracts')
                                        ->where('idSubContractor', Customer::idCustomer())
                                        ->pluck('idProjet');
                                        
        $projects = DB::table('v_union_projets')
                        ->select('idProjet', 'module_name', 'dateDebut', 'dateFin', 'total_ht_sub_contractor', 'project_reference')
                        ->whereIn('idProjet', $idProjectSubContractors)
                        ->where(function ($query) use($idEtp){
                            $query->where('idEtp', $idEtp)
                                  ->orWhere('idEtp_inter', $idEtp);
                        })
                        ->whereNot('module_name', 'Default module')
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->whereYear('dateDebut', $year)
                        ->where('project_is_trashed', 0)
                        ->get();
        
        $results = [];

        foreach($projects as $project){
            $results[] = [
                'idProjet' => $project->idProjet,
                'moduleName' => $project->module_name,
                'projectReference' => $project->project_reference,
                'total_ttc' => $project->total_ht_sub_contractor,
                'dateDebut' => Carbon::parse($project->dateDebut)->format('j.m.y'),
                'dateFin' => Carbon::parse($project->dateFin)->format('j.m.y'),
                'percentage' => number_format($this->getPercentageProject($project->total_ht_sub_contractor, $this->getTotalPriceProject($year)), 2)
            ];
        }

        return $results;
    }

    private function getIdEtp()
    {
        $customer_inter = DB::table('v_union_projets')
                        ->where(function ($query){
                            $query->where('idCfp_intra', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        ->whereNot('module_name', 'Default module')
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->orderBy('total_ttc', 'desc')
                        ->pluck('idEtp_inter'); 

        $customer_intra = DB::table('v_union_projets')
                        ->where(function ($query){
                            $query->where('idCfp_intra', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        ->whereNot('module_name', 'Default module')
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->orderBy('total_ttc', 'desc')
                        ->pluck('idEtp'); 

        return array_unique(array_merge($customer_inter->toArray(), $customer_intra->toArray()));
    }

    private function getCaByModule($year)
    {
        $moduleWithoutSubcontractor = DB::table('v_union_projets')
                                        ->select('idModule', DB::raw('1 as `index`'),DB::raw('SUM(total_ttc) as total_ttc'), 'module_name', DB::raw('COUNT(idProjet) as count_project'))
                                        ->where(function ($query) {
                                            $query->where('idCfp_intra', Customer::idCustomer())
                                                ->orWhere('idCfp_inter', Customer::idCustomer());
                                        })
                                        ->whereNot('module_name', 'Default module')
                                        ->whereYear('dateDebut', $year)
                                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                                        ->groupBy('idModule');

        $subcontractorProjectIds = DB::table('project_sub_contracts as PSC')
                                        ->join('projets as P', 'P.idProjet', '=', 'PSC.idProjet')
                                        ->join('mdls as M', 'M.idModule', '=', 'P.idModule')
                                        ->where('PSC.idSubContractor', Customer::idCustomer())
                                        ->pluck('P.idProjet');

        $moduleSubContractor = DB::table('v_union_projets')
                                        ->select('idModule', DB::raw('2 as `index`'),DB::raw('SUM(total_ht_sub_contractor) as total_ttc'), 'module_name', DB::raw('COUNT(idProjet) as count_project'))
                                        ->whereIn('idProjet', $subcontractorProjectIds)
                                        ->whereNot('module_name', 'Default module')
                                        ->whereYear('dateDebut', $year)
                                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                                        ->groupBy('idModule');

        $modules = $moduleSubContractor->union($moduleWithoutSubcontractor)
                        // ->whereIn('project_status', ['Terminé', 'Cloturé']) 
                        ->where('project_is_trashed', 0) 
                        ->groupBy('idModule') 
                        ->orderByDesc('total_ttc') 
                        ->get();

        $total_price = $this->getTotalPriceProject($year); 

        $results = [];
        foreach($modules as $module){
            $total_ttc = $this->getPriceProjectByModule($module->index ,$module->idModule, $year);
            $results[] = [
                'id_module' => $module->idModule,
                'module_name' => $module->module_name,
                'count_project' => $module->count_project,
                'total_ttc' => $total_ttc,
                'percentage' => number_format($this->getPercentageProject($total_ttc, $total_price), 2),
                'projects' => $this->getProjectByModule($module->index ,$module->idModule, $year)
            ];
        }

        return $results;
    }

    private function getProjectByModule($index, $idModule, $year){
        return ($index == 1) ? $this->getProjectByModuleNoContractor($idModule, $year) : $this->getProjectByModuleSubContractor($idModule, $year);
    }

private function getProjectByModuleNoContractor($idModule, $year)
{
    $projects = DB::table('v_union_projets')
        ->select('idProjet', 'project_reference', 'etp_name', 'dateDebut', 'dateFin', 'total_ttc', 'project_type')
        ->where('idModule', $idModule)
        ->whereIn('project_status', ['Terminé', 'Cloturé'])
        ->whereYear('dateDebut', $year)
        ->where('project_is_trashed', 0)
        ->get()
        ->unique('idProjet'); // <-- Enlève les doublons

    $results = [];
    foreach ($projects as $project) {
        $etpName = $project->etp_name ?: 'Pas de client'; // <-- ici on vérifie si null ou vide

        $results[] = [
            'idProjet' => $project->idProjet,
            'project_reference' => $project->project_reference,
            'project_type' => $project->project_type,
            'etpName' => $etpName,
            'dateDebut' => Carbon::parse($project->dateDebut)->format('j.m.y'),
            'dateFin' => Carbon::parse($project->dateFin)->format('j.m.y'),
            'total_ttc' => $project->total_ttc,
            'percentage' => number_format($this->getPercentageProject(
                $project->total_ttc,
                $this->getTotalPriceProject($year)
            ), 2),
        ];
    }

    return $results;
}



    private function getPriceProjectByModuleNoContractor($idModule, $year)
    {
        $price = DB::table('v_union_projets')
                        ->select('idProjet', 'project_reference', 'etp_name', 'dateDebut', 'dateFin', 'total_ttc')
                        ->where('idModule', $idModule)
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->where('project_is_trashed', 0)
                        ->whereYear('dateDebut', $year)
                        ->sum('total_ttc');

        return $price;
    }

    private function getPriceProjectByModuleSubContractor($idModule, $year){
        $idProjects = DB::table('project_sub_contracts as PSC')
                            ->join('projets as P', 'P.idProjet', '=', 'PSC.idProjet')
                            ->join('mdls as M', 'M.idModule', '=', 'P.idModule')
                            ->join('v_union_projets as V', 'V.idProjet', '=', 'P.idProjet')
                            ->where('PSC.idSubContractor', Customer::idCustomer())
                            ->where('P.idModule', $idModule)
                            ->pluck('P.idProjet');
                            
        $price = DB::table('v_union_projets')
                        ->whereIn('idProjet', $idProjects)
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->where('project_is_trashed', 0)
                        ->whereYear('dateDebut', $year)
                        ->sum('total_ht_sub_contractor');

        return $price;
    }

    private function getPriceProjectByModule($index, $idModule, $year){
        return ($index == 1) ? $this->getPriceProjectByModuleNoContractor($idModule, $year) : $this->getPriceProjectByModuleSubContractor($idModule, $year);
    }


    private function getProjectByModuleSubContractor($idModule, $year){
        $idProjects = DB::table('project_sub_contracts as PSC')
                            ->join('projets as P', 'P.idProjet', '=', 'PSC.idProjet')
                            ->join('mdls as M', 'M.idModule', '=', 'P.idModule')
                            ->join('v_union_projets as V', 'V.idProjet', '=', 'P.idProjet')
                            ->where('PSC.idSubContractor', Customer::idCustomer())
                            ->where('P.idModule', $idModule)
                            ->pluck('P.idProjet');
                            
        $projects = DB::table('v_union_projets')
                        ->select('idProjet', 'project_reference', 'etp_name', 'dateDebut', 'dateFin', 'total_ht_sub_contractor')
                        ->whereIn('idProjet', $idProjects)
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->whereYear('dateDebut', $year)
                        ->where('project_is_trashed', 0)
                        ->get();
        $results = [];
        foreach($projects as $project){
            $results[] = [
                'idProjet' => $project->idProjet,
                'project_reference' => $project->project_reference,
                'etpName' => $project->etp_name,
                'dateDebut' => Carbon::parse($project->dateDebut)->format('j.m.y'),
                'dateFin' => Carbon::parse($project->dateFin)->format('j.m.y'),
                'total_ttc' => $project->total_ht_sub_contractor,
                'percentage' => number_format($this->getPercentageProject($project->total_ht_sub_contractor, $this->getTotalPriceProject($year)), 2),
            ];
        }

        return $results;
    }

    private function getProject($year){
        $projectsNoSubContractor = DB::table('v_union_projets')
                        ->select('idProjet', 'total_ttc', 'module_name', 'dateDebut', 'dateFin', 'project_reference')
                        ->where(function ($query){
                            $query->where('idCfp_intra', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        ->whereNot('module_name', 'Default module')
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->whereYear('dateDebut', $year); 

        $idProjectSubContractors = DB::table('project_sub_contracts')
                                        ->where('idSubContractor', Customer::idCustomer())
                                        ->pluck('idProjet');
        
        $projectSubContractors = DB::table('v_union_projets')
                                    ->select('idProjet', 'total_ht_sub_contractor as total_ttc', 'module_name', 'dateDebut', 'dateFin', 'project_reference')
                                    ->whereIn('idProjet', $idProjectSubContractors)
                                    ->whereNot('module_name', 'Default module')
                                    ->whereIn('project_status', ['Terminé', 'Cloturé'])
                                    ->whereYear('dateDebut', $year); 

        $projects = $projectsNoSubContractor->union($projectSubContractors)
                                            ->where('project_is_trashed', 0)
                                            ->orderBy('total_ttc', 'desc')
                                            ->get();

        $total_price = $this->getTotalPriceProject($year);

        $results = [];
        foreach($projects as $project){
            $results[] = [
                'id_projet' => $project->idProjet,
                'module_name' => $project->module_name,
                'project_reference' => $project->project_reference,
                'date_debut' => Carbon::parse($project->dateDebut)->format('j.m.y'),
                'date_fin' => Carbon::parse($project->dateFin)->format('j.m.y'),
                'total_ttc' => $project->total_ttc,
                'percentage' => number_format($this->getPercentageProject($project->total_ttc, $total_price), 2)
            ];
        }

        return $results;
    }

    private function getTotalPriceProject($year){
        $idProjectSubContractors = DB::table('project_sub_contracts')
                                        ->where('idSubContractor', Customer::idCustomer())
                                        ->pluck('idProjet');

        $priceNoSubcontractor = DB::table('v_union_projets')
                        ->where(function ($query){
                            $query->where('idCfp_intra', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        ->whereNot('module_name', 'Default module')
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->whereYear('dateDebut', $year)
                        ->value(DB::raw('SUM(total_ttc)'));
        
        $priceWithSubcontractor = DB::table('v_union_projets')
                        ->whereIn('idProjet', $idProjectSubContractors)
                        ->whereNot('module_name', 'Default module')
                        ->whereIn('project_status', ['Terminé', 'Cloturé'])
                        ->whereYear('dateDebut', $year)
                        ->value(DB::raw('SUM(total_ht_sub_contractor)'));
        
        return $priceNoSubcontractor + $priceWithSubcontractor;
    }

    private function getPercentageProject($price_project, $total_price)
    {
        return ($total_price == 0) ? 0 : $price_project * 100 / $total_price;
    }

    public function caPlace(){
        $villeCfp = DB::table('v_union_projets')
                    ->select('ville', 'idVille', DB::raw('COUNT(idProjet) as project_count'), DB::raw('SUM(total_ttc) as total_ttc'))
                    ->where(function ($query){
                        $query->where('idCfp_intra', Customer::idCustomer())
                            ->orWhere('idCfp_inter', Customer::idCustomer());
                    })
                    ->whereIn('project_status', ['Terminé', 'Cloturé'])
                    ->where('project_is_trashed', 0)
                    ->whereYear('dateDebut', now()->year)
                    ->groupBy('idVille')
                    ->get();

        $idProjectSubContractors = DB::table('project_sub_contracts')
                    ->where('idSubContractor', Customer::idCustomer())
                    ->pluck('idProjet');

        $villeSponsor = DB::table('v_union_projets')
                    ->select('ville', 'idVille', DB::raw('COUNT(idProjet) as project_count'), DB::raw('SUM(total_ht_sub_contractor) as total_ttc'))
                    ->whereIn('idProjet', $idProjectSubContractors)
                    ->whereIn('project_status', ['Terminé', 'Cloturé'])
                    ->where('project_is_trashed', 0)
                    ->whereYear('dateDebut', now()->year)
                    ->groupBy('idVille')
                    ->get();

        $villes = DB::table('villes')
                        ->pluck('idVille');

        $results = [];
        
        foreach($villes as $v)
        {
            $ville = DB::table('villes')
                        ->select('ville')->where('idVille', $v)->first();

            $villeCfpCount = $villeCfp->firstWhere('idVille', $v);
            $villeSponsorCount = $villeSponsor->firstWhere('idVille', $v);
            $projectCount = ($villeCfpCount ? $villeCfpCount->project_count : 0) + ($villeSponsorCount ? $villeSponsorCount->project_count : 0);
            $totalTtc = ($villeCfpCount ? $villeCfpCount->total_ttc : 0) + ($villeSponsorCount ? $villeSponsorCount->total_ttc : 0);
            $results[] = [
                'idVille' => $v,
                'ville' => $ville->ville,
                'projectCount' => $projectCount,
                'totalTtc' => $totalTtc,
                'percentage' => number_format($this->getPercentageProject($totalTtc, $this->getTotalPriceProject(now()->year)), 2),
                'ville_coded' => $this->getVilleCoded($v, now()->year)
            ];
        }

        $totalProjects = array_sum(array_column($results, 'projectCount'));

        $totalPrice = $this->getTotalPriceProject(now()->year);

        return response()->json([
            'results' => $results, 
            'totalPrice' => $totalPrice, 
            'totalProjects' => $totalProjects
        ]);
    }

    public function caPlaceYear($year){
        $villeCfp = DB::table('v_union_projets')
                    ->select('ville', 'idVille', DB::raw('COUNT(idProjet) as project_count'), DB::raw('SUM(total_ttc) as total_ttc'))
                    ->where(function ($query){
                        $query->where('idCfp_intra', Customer::idCustomer())
                            ->orWhere('idCfp_inter', Customer::idCustomer());
                    })
                    ->whereIn('project_status', ['Terminé', 'Cloturé'])
                    ->where('project_is_trashed', 0)
                    ->whereYear('dateDebut', $year)
                    ->groupBy('idVille')
                    ->get();

        $idProjectSubContractors = DB::table('project_sub_contracts')
                    ->where('idSubContractor', Customer::idCustomer())
                    ->pluck('idProjet');

        $villeSponsor = DB::table('v_union_projets')
                    ->select('ville', 'idVille', DB::raw('COUNT(idProjet) as project_count'), DB::raw('SUM(total_ht_sub_contractor) as total_ttc'))
                    ->whereIn('idProjet', $idProjectSubContractors)
                    ->whereIn('project_status', ['Terminé', 'Cloturé'])
                    ->where('project_is_trashed', 0)
                    ->whereYear('dateDebut', $year)
                    ->groupBy('idVille')
                    ->get();

        $villes = DB::table('villes')
                        ->pluck('idVille');

        $totalPrice = $this->getTotalPriceProject($year);

        $results = [];
        
        foreach($villes as $v)
        {
            $ville = DB::table('villes')
                        ->select('ville')->where('idVille', $v)->first();

            $villeCfpCount = $villeCfp->firstWhere('idVille', $v);
            $villeSponsorCount = $villeSponsor->firstWhere('idVille', $v);
            $projectCount = ($villeCfpCount ? $villeCfpCount->project_count : 0) + ($villeSponsorCount ? $villeSponsorCount->project_count : 0);
            $totalTtc = ($villeCfpCount ? $villeCfpCount->total_ttc : 0) + ($villeSponsorCount ? $villeSponsorCount->total_ttc : 0);
            $results[] = [
                'idVille' => $v,
                'ville' => $ville->ville,
                'projectCount' => $projectCount,
                'totalTtc' => $totalTtc,
                'percentage' => number_format($this->getPercentageProject($totalTtc, $totalPrice), 2),
                'ville_coded' => $this->getVilleCoded($v, $year)
            ];
        }

        $totalProjects = array_sum(array_column($results, 'projectCount'));

        return response()->json([
            'results' => $results,
            'totalPrice' => $totalPrice,
            'totalProjects' => $totalProjects
        ]);
    }

    private function getVilleCoded($idVille, $year){
        $villeCfp = DB::table('v_union_projets')
                    ->select('idVilleCoded', DB::raw('COUNT(idProjet) as project_count'), DB::raw('SUM(total_ttc) as total_ttc'))
                    ->where(function ($query){
                        $query->where('idCfp_intra', Customer::idCustomer())
                            ->orWhere('idCfp_inter', Customer::idCustomer());
                    })
                    ->whereIn('project_status', ['Terminé', 'Cloturé'])
                    ->where('idVille', $idVille)
                    ->where('project_is_trashed', 0)
                    ->whereYear('dateDebut', $year)
                    ->groupBy('idVilleCoded')
                    ->get();

        $idProjectSubContractors = DB::table('project_sub_contracts')
                    ->where('idSubContractor', Customer::idCustomer())
                    ->pluck('idProjet');

        $villeSponsor = DB::table('v_union_projets')
                    ->select('idVilleCoded', DB::raw('COUNT(idProjet) as project_count'), DB::raw('SUM(total_ht_sub_contractor) as total_ttc'))
                    ->whereIn('idProjet', $idProjectSubContractors)
                    ->whereIn('project_status', ['Terminé', 'Cloturé'])
                    ->where('project_is_trashed', 0)
                    ->where('idVille', $idVille)
                    ->whereYear('dateDebut', $year)
                    ->groupBy('idVilleCoded')
                    ->get();

        $villes = DB::table('ville_codeds')
                    ->where('idVille', $idVille)
                    ->pluck('id');

        $results = [];
        
        foreach($villes as $v)
        {
            $ville = DB::table('ville_codeds')
                        ->select('ville_name', 'vi_code_postal')->where('id', $v)->first();

            $villeCfpCount = $villeCfp->firstWhere('idVilleCoded', $v);
            $villeSponsorCount = $villeSponsor->firstWhere('idVilleCoded', $v);
            $projectCount = ($villeCfpCount ? $villeCfpCount->project_count : 0) + ($villeSponsorCount ? $villeSponsorCount->project_count : 0);
            $totalTtc = ($villeCfpCount ? $villeCfpCount->total_ttc : 0) + ($villeSponsorCount ? $villeSponsorCount->total_ttc : 0);
            if($projectCount > 0){
                $results[] = [
                    'ville' => $ville->ville_name,
                    'codePostal' => $ville->vi_code_postal,
                    'projectCount' => $projectCount,
                    'totalTtc' => $totalTtc,
                    'percentage' => number_format($this->getPercentageProject($totalTtc, $this->getTotalPriceProject($year)), 2)
                ];
            }
            
        }

        return $results;
    }
}
