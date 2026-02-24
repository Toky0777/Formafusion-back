<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\UtilService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShowDrawerController extends Controller
{
    protected $utilService;

    public function __construct(UtilService $utilService)
    {
        $this->utilService = $utilService;
    }

    public function idCfp()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idCustomer;
    }

    private function partner($projectId)
    {
        $partner = DB::table('project_sub_contracts as PSC')
            ->join('projets as P', 'P.idProjet', 'PSC.idProjet')
            ->join('customers as C', 'C.idCustomer', 'P.idCustomer')
            ->where('P.idProjet', $projectId)
            ->value('C.customerName');

        return $partner;
    }

    private function priceSubcontactor($id)
    {
        $price = DB::table('projets')
            ->where('idProjet', $id)
            ->value('total_ht_sub_contractor');
        return $price;
    }

    private function priceWihtoutSubcontractor($id)
    {
        $price = DB::table('projets')
            ->where('idProjet', $id)
            ->value('total_ht');
        return $price;
    }

    private function priceProject($id)
    {
        $price = $this->partner($id) ? $this->priceSubcontactor($id) :  $this->priceWihtoutSubcontractor($id);
        return $price;
    }

    private function eachProject($projects)
    {
        $results = [];
        foreach ($projects as $project) {
            $results[] = [
                'id_projet' => $project->idProjet,
                'module_name' => $project->module_name,
                'date_debut' => Carbon::parse($project->dateDebut)->format('j.m.y'),
                'date_fin' => Carbon::parse($project->dateFin)->format('j.m.y'),
                'total_ht' => $this->utilService->formatPrice($this->priceProject($project->idProjet)),
                'total_apprenant' => $this->getTotalLearner($project->idProjet),
                'taux_presence' => $this->getPresence($project->idProjet),
                'average' => $this->getEval($project->idProjet),
                'project_type' => $project->project_type,
                'project_reference' => $project->project_reference,
                'commanditaire' => $this->partner($project->idProjet) ?? null,
                'ville' => $this->getVille($project->idVilleCoded),
                'isPaid' => $this->projectIsPaid($project->idProjet)
            ];
        }

        return $results;
    }

    private function projectIsPaid($id)
    {
        $isPaid = DB::table('invoice_details as ID')
            ->select('I.invoice_status')
            ->join('invoices as I', 'I.idInvoice', '=', 'ID.idInvoice')
            ->join('invoice_payments as IP', 'IP.invoice_id', '=', 'ID.idInvoice')
            ->where('ID.idProjet', $id)
            ->whereNotExists(function ($query) {
                $query->select('IL.id')
                    ->from('invoice_deleted as IL')
                    ->whereRaw('IL.idInvoice = ID.idInvoice');
            })
            ->first();

        return $isPaid->invoice_status ?? null;
    }

    private function getVille($id)
    {
        $ville = DB::table('ville_codeds')
            ->where('id', $id)
            ->value(DB::raw('CONCAT(ville_name, " ", "(", vi_code_postal, ")")'));

        return $ville;
    }

    private function eachProjects($projects, $etp_id)
    {
        $results = [];
        foreach ($projects as $project) {
            $results[] = [
                'id_projet' => $project->idProjet,
                'module_name' => $project->module_name,
                'date_debut' => Carbon::parse($project->dateDebut)->format('j.m.y'),
                'date_fin' => Carbon::parse($project->dateFin)->format('j.m.y'),
                'total_ht' => $this->utilService->formatPrice($project->total_ht),
                'total_apprenant' => $this->getTotalLearners($project->idProjet, $etp_id),
                'taux_presence' => $this->getPresence($project->idProjet),
                'average' => $this->getEval($project->idProjet),
                'project_type' => $project->project_type,
                'project_reference' => $project->project_reference,
                'isPaid' => $this->projectIsPaid($project->idProjet)
            ];
        }

        return $results;
    }

    private function getUnionProjects($status, $projectId, $etp_id)
    {
        $customerId = Customer::idCustomer();
        $projects = DB::table('v_union_projets')
            ->select('idProjet', 'dateDebut', 'dateFin', 'module_name', 'total_ht', 'project_type', 'project_reference',  'idCfp_intra', 'idCfp_inter')
            ->where('project_status', $status)
            ->whereIn('project_type', ['Intra', 'Inter'])
            ->where('project_is_trashed', 0)
            ->where('idModule', '!=', 1)
            ->whereIn('idProjet', $projectId)
            ->where(function ($query) use ($customerId) {
                $query->where('idCfp_intra', $customerId)
                    ->orWhere('idCfp_inter', $customerId);
            })
            ->orderBy('dateDebut', 'desc')
            ->get();

        return $this->eachProjects($projects, $etp_id);
    }

    private function getUnionProjectsEtp($status, $projectId, $customerId)
    {
        $projects = DB::table('v_union_projets')
            ->select('idProjet', 'dateDebut', 'dateFin', 'module_name', 'total_ht', 'project_type', 'project_reference',  'idCfp_intra', 'idCfp_inter')
            ->where('project_status', $status)
            ->whereIn('project_type', ['Intra', 'Inter'])
            ->where('project_is_trashed', 0)
            ->where('idModule', '!=', 1)
            ->whereIn('idProjet', $projectId)
            ->where(function ($query) use ($customerId) {
                $query->where('idCfp_intra', $customerId)
                    ->orWhere('idCfp_inter', $customerId);
            })
            ->orderBy('dateDebut', 'desc')
            ->get();

        return $this->eachProjects($projects, $customerId);
    }

    private function getUnionProject($etp_id, $status, $customerId)
    {
        $projects = DB::table('v_union_projets')
            ->select('idProjet', 'dateDebut', 'dateFin', 'module_name', 'total_ht', 'project_type', 'project_reference', 'idVilleCoded')
            ->where(function ($query) use ($etp_id) {
                $query->where('idEtp', $etp_id)
                    ->orWhere('idEtp_inter', $etp_id);
            })
            ->where(function ($query) use ($status) {
                $query
                    ->where('project_type', 'Interne')
                    ->orWhere(function ($query) use ($status) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->where('project_status', $status);
                    });
            })
            ->where(function ($query) use ($customerId) {
                $query->where('idCfp_intra', $customerId)
                    ->orWhere('idCfp_inter', $customerId);
            })
            ->where('project_is_trashed', 0)
            ->where('idModule', '!=', 1)
            ->orderBy('dateDebut', 'desc')
            ->get();

        return $this->eachProject($projects);
    }

    public function showEtpDrawer(Request $request)
    {
        $customerId = Customer::idCustomer();

        $referents = DB::table('users')
            ->select('users.name', 'users.email', 'employes.idCustomer', 'customers.customerPhone')
            ->join('employes', 'users.id', '=', 'employes.idEmploye')
            ->join('role_users', 'users.id', '=', 'role_users.user_id')
            ->join('customers', 'customers.idCustomer', '=', 'users.id')
            ->where('employes.idCustomer', $request->idEtp)
            ->whereIn('role_users.role_id', [3, 6, 8, 9])
            ->get();

        $invoices = DB::table('invoices as i')
            ->leftJoin('invoice_status as ist', 'i.invoice_status', '=', 'ist.idInvoiceStatus')
            ->select('i.idInvoice', 'i.invoice_number', 'i.invoice_date', 'i.idTypeFacture', 'i.invoice_total_amount', 'i.invoice_status', 'i.idEntreprise', 'ist.invoice_status_name')
            ->where('i.idCustomer', $customerId)
            ->where('i.idEntreprise', $request->idEtp)
            ->whereNot('i.invoice_status', 1)
            ->orderByDesc('i.invoice_date')
            ->whereIn('i.idTypeFacture', [1, 3, 4])
            ->get();

        $quotations = DB::table('invoices as i')
            ->leftJoin('invoice_status as ist', 'i.invoice_status', '=', 'ist.idInvoiceStatus')
            ->select('i.idInvoice', 'i.invoice_number', 'i.invoice_date', 'i.idTypeFacture', 'i.invoice_total_amount', 'i.invoice_status', 'i.idEntreprise', 'ist.invoice_status_name')
            ->where('i.idCustomer', $customerId)
            ->where('i.idEntreprise', $request->idEtp)
            ->whereNot('i.invoice_status', 1)
            ->orderByDesc('i.invoice_date')
            ->where('i.idTypeFacture', 2)
            ->get();

        $bon_commandes = DB::table('v_bon_commande')
            ->select('idBC', 'status_name', 'numero_bc', 'montant_bc', 'date_bc', 'idEtp')
            ->where('idCfp', $customerId)
            ->where('idEtp', $request->idEtp)
            ->orderByDesc('date_bc')
            ->get();


        $etp_id = $request->idEtp;

        $etp_is_grouped = DB::table('etp_groupeds')->where('idEntreprise', $etp_id)->exists();

        if ($etp_is_grouped) {
            $id_projet_learner = DB::table('detail_apprenants AS da')
                ->join('employes AS emp', 'da.idEmploye', 'emp.idEmploye')
                ->join('customers AS cst', 'emp.idCustomer', 'cst.idCustomer')
                ->join('projets as p', 'p.idProjet', '=', 'da.idProjet')
                ->where('emp.idCustomer', $etp_id)
                ->whereNot('p.project_is_closed', 1)
                ->whereNot('p.project_is_cancelled', 1)
                ->whereNot('p.project_is_repported', 1)
                ->groupBy('da.idProjet')
                ->pluck('da.idProjet');

            $id_projet_etp = DB::table('v_union_projets')
                ->where(function ($query) use ($etp_id) {
                    $query->where('idEtp', $etp_id)
                        ->orWhere('idEtp_inter', $etp_id);
                })
                ->where(function ($subQuery) use ($customerId) {
                    $subQuery->where('idCfp_intra', $customerId)
                        ->orWhere('idCfp_inter', $customerId);
                })
                ->pluck('idProjet');

            $projectId = array_unique(array_merge($id_projet_etp->toArray(), $id_projet_learner->toArray()));

            $projects_in_preparation = $this->getUnionProjects('En préparation', $projectId, $etp_id);

            $projects_finished = $this->getUnionProjects('Terminé', $projectId, $etp_id);

            $projects_in_progress = $this->getUnionProjects('En cours', $projectId, $etp_id);

            $projects_future = $this->getUnionProjects('Planifié', $projectId, $etp_id);

            $projects_fenced = $this->getUnionProjects('Clôturé', $projectId, $etp_id);
        } else {
            $projectIds = $this->getIdProjectWithSubContract($etp_id);
            $projects_in_preparation = $this->getUnionProjectEtp('En préparation', $projectIds);

            $projects_finished = $this->getUnionProjectEtp('Terminé', $projectIds);

            $projects_in_progress = $this->getUnionProjectEtp('En cours', $projectIds);

            $projects_future = $this->getUnionProjectEtp('Planifié', $projectIds);

            $projects_fenced = $this->getUnionProjectEtp('Clôturé', $projectIds);
        }

        $customer = DB::table('customers')
            ->select('idCustomer', 'customerName', 'customerEmail', 'nif as customerNif', 'stat as customerStat', 'customerPhone')
            ->where('idCustomer', $request->idEtp)
            ->first();

        return response()->json([
            'customer' => $customer,
            'referents' => $referents,
            'projects_finished' => $projects_finished,
            'projects_in_progress' => $projects_in_progress,
            'projects_future' => $projects_future,
            'projects_fenced' => $projects_fenced,
            'projects_in_preparation' => $projects_in_preparation,
            'invoices' => $invoices,
            'bon_commandes' => $bon_commandes,
            'quotations' => $quotations
        ]);
    }

    private function getUnionProjectEtp($status, $projectId)
    {
        $projects = DB::table('v_union_projets')
            ->select('idProjet', 'dateDebut', 'dateFin', 'module_name', 'total_ht', 'project_type', 'project_reference',  'idCfp_intra', 'idCfp_inter', 'idVilleCoded')
            ->where('project_status', $status)
            ->whereIn('project_type', ['Intra', 'Inter'])
            ->where('project_is_trashed', 0)
            ->where('idModule', '!=', 1)
            ->whereIn('idProjet', $projectId)
            ->orderBy('dateDebut', 'desc')
            ->distinct()
            ->get();

        return $this->eachProject($projects);
    }

    private function getIdProjectWithSubContract($customerId)
    {
        $projectIdSubContracts = DB::table('project_sub_contracts')->where('idSubContractor', Customer::idCustomer())->pluck('idProjet');

        $projectIdSubContractsUnion = DB::table('v_union_projets')
            ->where(function ($subQuery) use ($customerId) {
                $subQuery->where('idEtp', $customerId)
                    ->orWhere('idEtp_inter', $customerId);
            })
            ->whereIn('idProjet', $projectIdSubContracts)
            ->pluck('idProjet');

        $projectIdInUnionProject = DB::table('v_union_projets')
            ->where(function ($subQuery) {
                $subQuery->where('idCfp_intra', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer());
            })
            ->where(function ($subQuery) use ($customerId) {
                $subQuery->where('idEtp', $customerId)
                    ->orWhere('idEtp_inter', $customerId);
            })
            ->pluck('idProjet');

        $projectIds = array_merge($projectIdSubContractsUnion->toArray(), $projectIdInUnionProject->toArray());

        return array_unique($projectIds);
    }

    public function showCfpDrawer($idEtp)
    {
        $mainReferent = DB::table('users')
            ->select('users.*', 'employes.idCustomer', 'customers.customerPhone')
            ->join('employes', 'users.id', '=', 'employes.idEmploye')
            ->join('role_users', 'users.id', '=', 'role_users.user_id')
            ->join('customers', 'customers.idCustomer', 'users.id')
            ->where('employes.idCustomer', $idEtp)
            ->whereIn('role_users.role_id', [3, 6, 8, 9])
            ->get();

        $secondaryReferent = DB::table('users as U')
            ->select('U.photo', 'U.name', 'U.firstName', 'U.email', 'U.phone')
            ->join('role_users as RU', 'RU.user_id', '=', 'U.id')
            ->join('employes as E', 'E.idEmploye', '=', 'U.id')
            ->where('E.idCustomer', $idEtp)
            ->where('RU.role_id', 8)
            ->get();

        $etp_id = Customer::idCustomer();

        $etp_is_grouped = DB::table('etp_groupeds')->where('idEntreprise', $etp_id)->exists();

        if ($etp_is_grouped) {
            $id_projet_learner = DB::table('detail_apprenants AS da')
                ->join('employes AS emp', 'da.idEmploye', 'emp.idEmploye')
                ->join('customers AS cst', 'emp.idCustomer', 'cst.idCustomer')
                ->join('projets as p', 'p.idProjet', '=', 'da.idProjet')
                ->where('emp.idCustomer', $etp_id)
                ->whereNot('p.project_is_closed', 1)
                ->whereNot('p.project_is_cancelled', 1)
                ->whereNot('p.project_is_repported', 1)
                ->groupBy('da.idProjet')
                ->pluck('da.idProjet');

            $id_projet_etp = DB::table('v_union_projets')
                ->where(function ($query) use ($etp_id) {
                    $query->where('idEtp', $etp_id)
                        ->orWhere('idEtp_inter', $etp_id);
                })
                ->where(function ($subQuery) use ($idEtp) {
                    $subQuery->where('idCfp_intra', $idEtp)
                        ->orWhere('idCfp_inter', $idEtp);
                })
                ->pluck('idProjet');

            $projectId = array_unique(array_merge($id_projet_etp->toArray(), $id_projet_learner->toArray()));

            $projects_finished = $this->getUnionProjectsEtp('Terminé', $projectId, $idEtp);

            $projects_in_progress = $this->getUnionProjectsEtp('En cours', $projectId, $idEtp);

            $projects_future = $this->getUnionProjectsEtp('Planifié', $projectId, $idEtp);

            $projects_fenced = $this->getUnionProjectsEtp('Clôturé', $projectId, $idEtp);
        } else {
            $projects_finished = $this->getUnionProject($etp_id, 'Terminé', $idEtp);

            $projects_in_progress = $this->getUnionProject($etp_id, 'En cours', $idEtp);

            $projects_future = $this->getUnionProject($etp_id, 'Planifié', $idEtp);

            $projects_fenced = $this->getUnionProject($etp_id, 'Clôturé', $idEtp);
        }

        // $customer = DB::table('customers')
        //     ->select('customerPhone', 'siteWeb', 'logo', 'customerName', 'customer_slogan', DB::raw('CONCAT(COALESCE(customer_addr_lot, ""), " ", COALESCE(customer_addr_quartier ,""), " ",COALESCE(customer_addr_code_postal ,"")) as addresse'))
        //     ->where('idCustomer', $request->idEtp)
        //     ->first();

        $customer = DB::table('v_detail_customers')
            ->select('customerPhone', 'siteWeb', 'logo', 'customerName', 'customer_slogan', DB::raw('CONCAT(COALESCE(customer_addr_lot, ""), " ", COALESCE(customer_addr_quartier ,""), " ",COALESCE(ville_name ,""), " ",COALESCE(customer_addr_code_postal ,"")) as addresse'))
            ->where('idCustomer', $idEtp)
            ->first();

        return response()->json([
            'customer' => $customer,
            'referents' => $mainReferent,
            'secondary_referent' => $secondaryReferent,
            'projects_finished' => $projects_finished,
            'projects_in_progress' => $projects_in_progress,
            'projects_future' => $projects_future,
            'projects_fenced' => $projects_fenced
        ]);
    }

    public function showApprenantWithProject(Request $request)
    {
        $user = DB::table('users')
            ->select('name', 'firstName', 'photo', 'email', 'phone')
            ->where('id', $request->id)
            ->first();


        $projects_finished = $this->getProjectApprenant($request->id, 'Terminé');
        $projects_fenced = $this->getProjectApprenant($request->id, 'Cloturé');
        $projects_future = $this->getProjectApprenant($request->id, 'Planifié');
        $projects_in_progress = $this->getProjectApprenant($request->id, 'En cours');

        return response()->json([
            'user' => $user,
            'projects_finished' => $projects_finished,
            'projects_in_progress' => $projects_in_progress,
            'projects_future' => $projects_future,
            'projects_fenced' => $projects_fenced
        ]);
    }

    public function showApprenantWithProjectCfp(Request $request)
    {
        $user = DB::table('users as U')
            ->join('employes as E', 'E.idEmploye', 'U.id')
            ->join('customers as C', 'C.idCustomer', 'E.idCustomer')
            ->select('U.name', 'U.firstName', 'U.photo', 'U.email', 'U.phone', 'C.customerName', 'U.matricule')
            ->where('id', $request->id)
            ->first();

        $projects_in_preparation = $this->getProjectApprenantCfp($request->id, 'En préparation');
        $projects_finished = $this->getProjectApprenantCfp($request->id, 'Terminé');
        $projects_fenced = $this->getProjectApprenantCfp($request->id, 'Cloturé');
        $projects_future = $this->getProjectApprenantCfp($request->id, 'Planifié');
        $projects_in_progress = $this->getProjectApprenantCfp($request->id, 'En cours');

        return response()->json([
            'user' => $user,
            'projects_finished' => $projects_finished,
            'projects_in_progress' => $projects_in_progress,
            'projects_future' => $projects_future,
            'projects_in_preparation' => $projects_in_preparation,
            'projects_fenced' => $projects_fenced
        ]);
    }

    private function getProjectApprenantCfp($id_employe, $status)
    {
        $projectIds = DB::table('detail_apprenants AS da')
            ->select('M.moduleName', 'T.type', 'P.total_ht', 'P.dateDebut', 'P.dateFin', 'C.customerName', 'P.idProjet')
            ->join('employes AS emp', 'da.idEmploye', 'emp.idEmploye')
            ->join('customers AS cst', 'emp.idCustomer', 'cst.idCustomer')
            ->join('projets as P', 'P.idProjet', '=', 'da.idProjet')
            ->join('mdls as M', 'M.idModule', '=', 'P.idModule')
            ->join('customers AS C', 'C.idCustomer', '=', 'emp.idCustomer')
            ->join('type_projets as T', 'T.idTypeProjet', '=', 'P.idTypeProjet')
            ->where('P.idCustomer', Customer::idCustomer())
            ->where('da.idEmploye', $id_employe)
            ->pluck('P.idProjet');

        $projects = DB::table('v_union_projets')
            ->select('idProjet', 'dateDebut', 'dateFin', 'module_name', 'total_ht', 'project_type', 'project_reference',  'idCfp_intra', 'idCfp_inter')
            ->where('project_status', $status)
            ->whereIn('project_type', ['Intra', 'Inter'])
            ->where('project_is_trashed', 0)
            ->where('idModule', '!=', 1)
            ->whereIn('idProjet', $projectIds)
            ->orderBy('dateDebut', 'desc')
            ->get();

        $results = [];

        foreach ($projects as $project) {
            $results[] = [
                'id_projet' => $project->idProjet,
                'reference' => $project->project_reference,
                'module_name' => $project->module_name,
                'date_debut' => $project->dateDebut,
                'date_fin' => $project->dateFin,
                'presence' => $this->getPresenceByEmploye($id_employe, $project->idProjet)
            ];
        }

        return $results;
    }

    private function getProjectApprenant($id_employe, $status)
    {
        $projectIds = DB::table('detail_apprenants AS da')
            ->select('M.moduleName', 'T.type', 'P.total_ht', 'P.dateDebut', 'P.dateFin', 'C.customerName', 'P.idProjet')
            ->join('employes AS emp', 'da.idEmploye', 'emp.idEmploye')
            ->join('customers AS cst', 'emp.idCustomer', 'cst.idCustomer')
            ->join('projets as P', 'P.idProjet', '=', 'da.idProjet')
            ->join('mdls as M', 'M.idModule', '=', 'P.idModule')
            ->join('customers AS C', 'C.idCustomer', '=', 'P.idCustomer')
            ->join('type_projets as T', 'T.idTypeProjet', '=', 'P.idTypeProjet')
            ->where('emp.idCustomer', Customer::idCustomer())
            ->where('da.idEmploye', $id_employe)
            ->pluck('P.idProjet');

        $projects = DB::table('v_union_projets')
            ->select('idProjet', 'dateDebut', 'dateFin', 'module_name', 'total_ht', 'project_type', 'project_reference',  'idCfp_intra', 'idCfp_inter')
            ->where('project_status', $status)
            ->whereIn('project_type', ['Intra', 'Inter'])
            ->where('project_is_trashed', 0)
            ->where('idModule', '!=', 1)
            ->whereIn('idProjet', $projectIds)
            ->orderBy('dateDebut', 'desc')
            ->get();

        $results = [];

        foreach ($projects as $project) {
            $results[] = [
                'id_projet' => $project->idProjet,
                'reference' => $project->project_reference,
                'customer_name' => $this->getCustomer($project->idCfp_intra ?? $project->idCfp_inter),
                'module_name' => $project->module_name,
                'date_debut' => $project->dateDebut,
                'date_fin' => $project->dateFin,
                'presence' => $this->getPresenceByEmploye($id_employe, $project->idProjet)
            ];
        }

        return $results;
    }

    private function getCustomer($id)
    {
        $customer = DB::table('customers')
            ->where('idCustomer', $id)
            ->value('customerName');

        return $customer;
    }

    private function getPresenceByEmploye($id_employe, $id_projet)
    {
        $get_presence = DB::table('emargements')
            ->where('idProjet', $id_projet)
            ->where('idEmploye', $id_employe)
            ->where('isPresent', 3)
            ->value(DB::raw('COUNT(isPresent)'));

        $count_seance = DB::table('seances')
            ->where('idProjet', $id_projet)
            ->value(DB::raw('COUNT(idSeance)'));

        if ($count_seance > 0) {
            $presence = $get_presence * 100 / $count_seance;
        } else {
            $presence = 0;
        }
        return round($presence, 0);
    }

    public function showFormDrawer(Request $request)
    {
        $formateur = DB::table('users')
            ->select('users.*', 'formateurs.idCustomer')
            ->join('formateurs', 'users.id', '=', 'formateurs.idFormateur')
            ->join('role_users', 'users.id', '=', 'role_users.user_id')
            ->where('formateurs.idCustomer', $request->idFormateur)
            ->first();
        return response()->json(['formateur' => $formateur]);
    }

    public function showSessionDrawer(Request $request)
    {
        $sessions = DB::table('seances')
            ->select(
                'seances.idSeance',
                'seances.dateSeance',
                DB::raw("DATE_FORMAT(seances.heureDebut, '%Hh%i') as heureDebut"),
                DB::raw("DATE_FORMAT(seances.heureFin, '%Hh%i') as heureFin"),
                'seances.idProjet',
                'seances.isDone'
            )
            ->where('idProjet', $request->idProjet)
            ->get();

        $module = DB::table('projets')
            ->join('mdls', 'projets.idModule', 'mdls.idModule')
            ->select('mdls.*')
            ->where('idProjet', $request->idProjet)
            ->first();

        $totalSessionHours = DB::table('v_seances')
            ->selectRaw("IFNULL(TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))), '%H:%i'), '0') as sumHourSession")
            ->where('idProjet', $request->idProjet)
            ->first();

        return response()->json(['sessions' => $sessions, 'module' => $module, 'totalSessionHours' => $totalSessionHours]);
    }

    function showDossierDrawer($idProjet)
    {
        $dossier = DB::table('projets')
            ->select('idDossier')
            ->where('project_is_trashed', 0)
            ->where('idProjet', $idProjet)
            ->first();

        if (!$dossier || !isset($dossier->idDossier)) {

            $module = DB::table('projets')
                ->join('mdls', 'projets.idModule', '=', 'mdls.idModule')
                ->select('mdls.*')
                ->where('idProjet', $idProjet)
                ->first();

            return response()->json([
                'module' => $module
            ]);
        }
        $idDossier = $dossier->idDossier;

        $projects = DB::table('v_projet_cfps')
            ->select(
                'idProjet',
                'dateDebut',
                'idEtp',
                'dateFin',
                'module_name',
                'etp_name',
                'total_ht',
                'project_status',
                'project_type',
                'project_reference',
                DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'),
                'idCfp_inter',
                'modalite',
                'idModule'
            )
            ->where(function ($query) {
                $query->where('idCfp', $this->idCfp())
                    ->orWhere('idCfp_inter', $this->idCfp());
            })
            ->where('headYear', Carbon::now()->format('Y'))
            ->where('project_is_trashed', 0)
            ->where('idDossier', $idDossier)
            ->orderBy('dateDebut', 'asc')
            ->get();

        $projets = [];
        foreach ($projects as $project) {
            $projets[] = [
                'idProjet' => $project->idProjet,
                'idCfp_inter' => $project->idCfp_inter,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'idEtp' => $project->idEtp,
                'project_type' => $project->project_type,
                'project_reference' => $project->project_reference,
                'modalite' => $project->modalite,
                'etp_name_in_situ' => $project->etp_name,
                'total_ht' => $this->utilService->formatPrice($project->total_ht),
                'project_status' => $project->project_status,
                'idModule' => $project->idModule,
            ];
        }

        $module = DB::table('projets')
            ->join('mdls', 'projets.idModule', '=', 'mdls.idModule')
            ->select('mdls.*')
            ->where('idProjet', $idProjet)
            ->first();

        $documents = DB::table('v_document_dossier')
            ->where('idDossier', $idDossier)
            ->get();

        $nomDossier = DB::table('v_document_dossier')
            ->select('nomDossier', 'idDossier')
            ->where('idDossier', $idDossier)
            ->first();

        $endpoint = config('filesystems.disks.do.url_cdn_digital');
        $bucket = config('filesystems.disks.do.bucket');
        $digitalOcean = $endpoint . '/' . $bucket;

        return response()->json([
            'documents' => $documents,
            'module' => $module,
            'nomDossier' => $nomDossier,
            'projets' => $projets,
            'digitalOcean' => $digitalOcean
        ]);
    }

    public function getEtpProjectInter($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null) {
            $etp = DB::table('v_projet_cfps')
                ->select('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
                ->where('idProjet', $idProjet)
                ->whereNot('idEtp', Customer::idCustomer())
                ->groupBy('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
                ->get();
        } elseif ($idCfp_inter != null) {
            $etp = DB::table('v_list_entreprise_inter')
                ->select('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
                ->where('idProjet', $idProjet)
                ->where('etp_name', '!=', 'null')
                ->orderBy('etp_name', 'asc')
                ->groupBy('idEtp')
                ->get();
        }

        return $etp->toArray();
    }

    public function showDocumentDrawer(Request $request)
    {
        $module = DB::table('projets')
            ->join('mdls', 'projets.idModule', 'mdls.idModule')
            ->select('mdls.*')
            ->where('idProjet', $request->idProjet)
            ->first();

        $idDossier = DB::table('projets')
            ->select('idDossier')
            ->where('idProjet', $request->idProjet)
            ->first();

        $documents = DB::table('v_document_dossier')
            ->where('idDossier', $idDossier->idDossier)
            ->orderBy('updated_at', 'desc')
            ->get();

        $endpoint = config('filesystems.disks.do.url_cdn_digital');
        $bucket = config('filesystems.disks.do.bucket');
        $digitalOcean = $endpoint . '/' . $bucket;

        return response()->json([
            'module' => $module,
            'documents' => $documents,
            'digitalOcean' => $digitalOcean
        ]);
    }

    public function showApprenantDrawer(Request $request)
    {
        $idCfp_inter = DB::table('v_projet_cfps')
            ->where('idProjet', $request->idProjet)
            ->pluck('idCfp_inter')
            ->first();

        $ap1 = DB::table('detail_apprenants')
            ->join('users', 'detail_apprenants.idEmploye', '=', 'users.id')
            ->join('employes', 'users.id', '=', 'employes.idEmploye')
            ->join('fonctions', 'employes.idFonction', '=', 'fonctions.idFonction')
            ->join('customers', 'employes.idCustomer', '=', 'customers.idCustomer')
            ->select('users.name', 'users.email', 'users.matricule', 'users.firstName', 'users.phone', 'users.photo', 'fonctions.fonction', 'customers.customerName', 'customers.idCustomer AS idEtp')
            ->where('idProjet', $request->idProjet)
            ->get();

        $ap2 = DB::table('detail_apprenant_inters')
            ->join('users', 'detail_apprenant_inters.idEmploye', '=', 'users.id')
            ->join('employes', 'users.id', '=', 'employes.idEmploye')
            ->join('fonctions', 'employes.idFonction', '=', 'fonctions.idFonction')
            ->join('customers', 'employes.idCustomer', '=', 'customers.idCustomer')
            ->select('users.name', 'users.email', 'users.matricule', 'users.firstName', 'users.phone', 'users.photo', 'fonctions.fonction', 'customers.customerName', 'customers.idCustomer AS idEtp')
            ->where('idProjet', $request->idProjet)
            ->get();

        $apprenants = $ap1->merge($ap2);

        $etps = DB::table('v_projet_cfps')
            ->select('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
            ->where('idProjet', $request->idProjet)
            // ->whereNot('idEtp', Customer::idCustomer())
            ->groupBy('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
            ->get();

        $module = DB::table('projets')
            ->join('mdls', 'projets.idModule', 'mdls.idModule')
            ->select('mdls.*')
            ->where('idProjet', $request->idProjet)
            ->first();

        $endpoint = config('filesystems.disks.do.url_cdn_digital');
        $bucket = config('filesystems.disks.do.bucket');
        $digitalOcean = $endpoint . '/' . $bucket;

        return response()->json(['apprenants' => $apprenants, 'module' => $module, 'etps' => $etps, 'digitalOcean' => $digitalOcean, 'idCfp_inter' => $idCfp_inter]);
    }

    private function getEval($idProjet)
    {
        $result = DB::table('eval_chauds')
            ->select(
                DB::raw('SUM(firstNotes.generalApreciate) as sumFirstNotes'),
                DB::raw('COUNT(DISTINCT firstNotes.idEmploye) as totalEmployees')
            )
            ->fromSub(function ($query) use ($idProjet) {
                $query->select('idEmploye', 'idProjet', 'generalApreciate')
                    ->from('eval_chauds')
                    ->where('idProjet', $idProjet)
                    ->whereNotNull('generalApreciate')
                    ->groupBy('idEmploye', 'idProjet');
            }, 'firstNotes')
            ->first();

        $average = $result->totalEmployees > 0 ? $result->sumFirstNotes / $result->totalEmployees : 0;

        return round($average, 1);
    }

    private function getPresence($idProjet)
    {
        $getAppr = DB::table('v_emargement_appr_inter')
            ->select('idProjet', 'idEmploye', 'name', 'firstName', 'photo')
            ->where('idProjet', $idProjet)
            ->groupBy('idEmploye')
            ->get();

        $getEmargement = DB::table('emargements')
            ->select('idProjet', 'idSeance', 'isPresent')
            ->where('idProjet', $idProjet)
            ->groupBy('idSeance')
            ->get();

        $present = DB::table('emargements')
            ->select('idProjet', 'idSeance', 'isPresent')
            ->where('idProjet', $idProjet)
            ->where('isPresent', 3)
            ->get();

        $getAppr = DB::table('v_emargement_appr_inter')
            ->select('idProjet', 'idEmploye', 'name', 'firstName', 'photo')
            ->where('idProjet', $idProjet)
            ->groupBy('idEmploye')
            ->get();

        $countAppr = count($getAppr);

        $countPresent = count($present);

        $countEmargement = count($getEmargement);
        $divide = $countAppr * $countEmargement;

        if ($divide > 0) {
            $percentPresent = number_format(($countPresent / $divide) * 100, 1, ',', ' ');
        } else {
            $percentPresent = 0;
        }

        return $percentPresent;
    }

    private function getTotalLearner($idProjet)
    {
        $totalLearner = DB::table('employes AS E')
            ->join('customers AS C', 'C.idCustomer', '=', 'E.idCustomer')
            ->join('users AS U', 'U.id', '=', 'E.idEmploye')
            ->join('role_users AS R', 'R.user_id', '=', 'U.id')
            ->join('detail_apprenants AS D', 'D.idEmploye', '=', 'U.id')
            ->where('R.role_id', 4)
            ->where('D.idPRojet', $idProjet)->count();
        return $totalLearner;
    }

    private function getTotalLearners($idProjet, $idCustomer)
    {
        $totalLearner = DB::table('employes AS E')
            ->select('D.idEmploye')
            ->join('customers AS C', 'C.idCustomer', '=', 'E.idCustomer')
            ->join('users AS U', 'U.id', '=', 'E.idEmploye')
            ->join('role_users AS R', 'R.user_id', '=', 'U.id')
            ->join('detail_apprenants AS D', 'D.idEmploye', '=', 'U.id')
            ->where('R.role_id', 4)
            ->where('E.idCustomer', $idCustomer)
            ->where('D.idPRojet', $idProjet)
            ->count();

        return $totalLearner;
    }

    // Function pour voir le plan de repérage
    function showPLanDeReperageDrawer($idProjet)
    {
        $plandereperage = DB::table('projets')
            ->select('salles.idSalle', 'salle_name', 'salle_image', 'lieux.li_name', 'lieux.li_quartier', 'ville_codeds.ville_name', 'ville_codeds.vi_code_postal', 'villes.ville')
            ->join('salles', 'salles.idSalle', 'projets.idSalle')
            ->join('lieux', 'lieux.idLieu', 'salles.idLieu')
            ->join('ville_codeds', 'ville_codeds.id', 'lieux.idVilleCoded')
            ->join('villes', 'villes.idVille', 'ville_codeds.idVille')
            ->where('idProjet', $idProjet)
            ->get();

        $module = DB::table('projets')
            ->join('mdls', 'projets.idModule', '=', 'mdls.idModule')
            ->select('mdls.*')
            ->where('idProjet', $idProjet)
            ->first();

        return response()->json([
            'module' => $module,
            'plandereperage' => $plandereperage
        ]);
    }
}
