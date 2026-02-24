<?php

namespace App\Traits;

use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait AnalyticQuery
{
    public function calculateInvoiceTotals($year)
    {
        // Récupération des factures
        $invoicesQuery = Invoice::with(['entrepriseFromVcollaboration', 'particulier', 'status'])
            ->where('idCustomer', Customer::idCustomer())
            ->standard()
            ->doesntHave('deletedInvoices')
            ->whereNotIn('invoice_status', [1, 4, 9])
            ->orderBy('idInvoice', 'desc')
            ->whereYear('invoice_date', $year);

        $invoices = $invoicesQuery->get();

        // Calcul des totaux
        $total_montant = $invoices->sum('invoice_total_amount');
        $total_paye = $invoices->pluck('payments')->flatten()->sum('amount');
        $restantDu = $total_montant - $total_paye;

        return [$total_paye, $restantDu];
    }


    public function getCfpProjectInLineChart(mixed $months, mixed $status, int $idCustomer, mixed $year = null)
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
            ->select('v_union_projets.idProjet', 'project_status', 'total_ttc', 'total_ht', 'total_ht_etp', 'total_ttc_etp', 'idPaiement', 'module_name', 'dateFin', 'dateDebut', 'idCfp_intra', 'idCfp_inter', 'etp_name', 'etp_logo', 'etp_initial_name', 'v_union_projets.idEtp')
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


    public function getCfpCustomers($year, mixed $status)
    {
        $idCfp = Customer::idCustomer();
        $projects = DB::table('v_union_projets')
            ->select('etp_name')
            ->where(function ($query) use ($idCfp) {
                $query->where('idCfp_intra', $idCfp)
                    ->orWhere('idCfp_inter', $idCfp);
            })
            ->whereYear('dateDebut', $year)
            ->whereNotNull('etp_name')
            ->whereIn(DB::raw("project_status COLLATE utf8mb4_unicode_ci"), $status)
            ->whereYear('dateDebut', $year)
            ->get()
            ->pluck('etp_name')
            ->unique()
            ->values();

        return $projects;
    }

    public function getBonCommande($year)
    {
        $idCfp = Customer::idCustomer();

        $bc_month = DB::table('bon_commandes')
            ->select(
                DB::raw('MONTH(date) as mois'),
                DB::raw('SUM(montant) as total_montant'),
                DB::raw('COUNT(*) as nombre_commandes')
            )
            ->whereYear('date', $year)
            ->where('idCfp', $idCfp)
            ->groupBy(DB::raw('MONTH(date)'))
            ->get();

        $totaux_montants = array_fill(1, 12, 0);
        $totaux_commandes = array_fill(1, 12, 0);

        foreach ($bc_month as $bc) {
            $totaux_montants[$bc->mois] = (float) round($bc->total_montant);
            $totaux_commandes[$bc->mois] = (int) $bc->nombre_commandes;
        }

        return [
            'totaux_bc_month' => array_values($totaux_montants),
            'nombre_commandes_month' => array_values($totaux_commandes),
        ];
    }

    public function getProjectPrev($month, $year,  $status)
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
            ];
        }
        return $data;
    }
}
