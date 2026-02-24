<?php

namespace App\Http\Controllers;

use App\Models\BankAcount;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FactureSoldeController extends Controller
{
    public function getAllEtps()
    {
        $allEtps = DB::table('v_collaboration_cfp_etps')
            ->select('idEtp', 'etp_name', 'etp_email', 'etp_nif', 'etp_ville', 'etp_stat', 'etp_rcs', 'etp_addr_lot', 'etp_addr_quartier', 'etp_addr_code_postal', 'etp_phone')
            ->where('idCfp', Customer::idCustomer())
            ->get();

        return response()->json(['clients' => $allEtps]);
    }

    public function getAllDetailAcompteByBC($idBC)
    {
        $details = DB::table('invoice_details_acompte as ida')
            ->join('invoice_acomptes as ia', 'ida.idInvoice', '=', 'ia.idInvoice')
            ->leftJoin('invoice_deleted as id', 'ia.idInvoice', '=', 'id.idInvoice')
            ->where('ia.idBC', $idBC)
            ->whereNull('id.idInvoice')
            ->get();
        $montant_bc = DB::table('bon_commandes')
            ->where('idBC', $idBC)
            ->value('montant');
        return response()->json([
            'details' => $details,
            'montant_bc' => $montant_bc
        ]);
    }

    public function getAllDetailBC($id)
    {
        try {
            $bonCommande = DB::table('bon_commandes as bc')
                ->select([
                    'bc.idBC',
                    'bc.numero',
                    'bc.montant',
                    'bc.date'
                ])
                ->where('bc.idBC', $id)
                ->where('bc.idCfp', Customer::idCustomer())
                ->first();

            return response()->json([
                'bon_commande' => $bonCommande
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du bon de commande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $invoices = $this->filterInvoices($request)
            ->doesntHave('deletedInvoices')
            ->orderBy('idInvoice', 'desc')
            ->paginate($perPage);

        $countInvoices = $invoices->total();

        $entreprises = Invoice::with(['entrepriseFromVcollaboration', 'particulier'])
            ->acompte()
            ->doesntHave('deletedInvoices')
            ->where('idCustomer', Customer::idCustomer())
            ->select('idEntreprise')
            ->distinct()
            ->get()
            ->flatMap(function ($invoice) {
                return [
                    $invoice->entrepriseFromVcollaboration,
                    $invoice->particulier
                ];
            })
            ->filter()
            ->unique(function ($entreprise) {
                return $entreprise->idEtp ?? $entreprise->idCfp ?? $entreprise->idParticulier;
            })
            ->sortBy(function ($entreprise) {
                return $entreprise->etp_name ?? $entreprise->customerName ?? $entreprise->part_name;
            });

        $statuses = DB::table('invoice_status')->select('idInvoiceStatus', 'invoice_status_name')->get();
        $accounts = BankAcount::where('ba_idCustomer', Customer::idCustomer())->get();

        return response()->json([
            'invoices' => $invoices,
            'countInvoices' => $countInvoices,
            'entreprises' => $entreprises,
            'statuses' => $statuses,
            'accounts' => $accounts
        ]);
    }

    private function filterInvoices($request)
    {
        return Invoice::with(['entrepriseFromVcollaboration', 'particulier', 'status', 'payments'])
            ->leftJoin('invoice_contacts as ic', 'invoices.idContact', '=', 'ic.idContact')
            ->select(
                'invoices.*',
                'ic.contact_name',
                'ic.contact_mail',
                'ic.contact_phone'
            )
            ->when($request->idEntreprise, function ($query) use ($request) {
                $query->where(function ($subQuery) use ($request) {
                    $subQuery->where('idEntreprise', $request->idEntreprise)
                        ->orWhereHas('entrepriseFromVcollaboration', function ($q) use ($request) {
                            $q->where('idEtp', $request->idEntreprise);
                        })
                        ->orWhereHas('particulier', function ($q) use ($request) {
                            $q->where('idParticulier', $request->idEntreprise);
                        });
                });
            })
            ->when($request->invoice_status, function ($query) use ($request) {
                $query->where('invoice_status', $request->invoice_status);
            })
            ->when($request->invoice_number, function ($query) use ($request) {
                $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
            })
            ->when($request->invoice_date_debut || $request->invoice_date_fin, function ($query) use ($request) {
                $dateDebut = $request->invoice_date_debut;
                $dateFin = $request->invoice_date_fin;

                if ($dateDebut && !$dateFin) {
                    $dateFin = Invoice::max('invoice_date_pm');
                } elseif (!$dateDebut && $dateFin) {
                    $dateDebut = Invoice::min('invoice_date_pm');
                }

                $query->whereBetween('invoice_date_pm', [$dateDebut, $dateFin]);
            })
            ->where('idCustomer', Customer::idCustomer())
            ->solde();
    }

    public function create()
    {
        $type_invoice = DB::table('type_factures')->get();

        $customer = DB::table('v_detail_customers')
            ->select('idCustomer', 'initialName', 'customerName', 'customer_addr_quartier', 'customer_addr_rue', 'customer_addr_lot', 'customer_addr_code_postal', 'nif', 'stat', 'rcs', 'customerPhone', 'customerEmail', 'siteWeb', 'description', 'logo', 'customer_slogan')
            ->where('idCustomer', Customer::idCustomer())
            ->first();

        $unites = DB::table('unites')
            ->select('idUnite', 'unite_name')
            ->get();

        $pm = DB::table('pm_types')->select('idTypePm', 'pm_type_name')->get();
        $ville_codeds = DB::table('ville_codeds')->get();
        $accounts = BankAcount::where('ba_idCustomer', Customer::idCustomer())->get();

        $number_invoice = DB::table('invoices')
            ->select('invoice_number', 'invoice_date')
            ->orderBy('idInvoice', 'desc')
            ->where('idTypeFacture', 4)
            ->where('idCustomer', Customer::idCustomer())
            ->take(3)
            ->get();

        $companies = Company::where('idCustomer', Customer::idCustomer())->select('id', 'name', 'nif', 'stat')->get();

        return response()->json([
            'customer' => $customer,
            'unites' => $unites,
            'pm' => $pm,
            'type_invoice' => $type_invoice,
            'ville_codeds' => $ville_codeds,
            'accounts' => $accounts,
            'number_invoice' => $number_invoice,
            'companies' => $companies
        ]);
    }
}
