<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BankAcount;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Customer;

class FactureAcompteController extends Controller
{
    public function getAllEtps()
    {
        $allEtps = DB::table('v_collaboration_cfp_etps')
            ->select('idEtp', 'etp_name', 'etp_email', 'etp_nif', 'etp_ville', 'etp_stat', 'etp_rcs', 'etp_addr_lot', 'etp_addr_quartier', 'etp_addr_code_postal', 'etp_phone')
            ->where('idCfp', Customer::idCustomer())
            ->get();

        return response()->json(['clients' => $allEtps]);
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
            ->acompte();
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
            ->where('idTypeFacture', 3)
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

    public function edit($id)
    {
        $customer = DB::table('v_detail_customers')
            ->select('idCustomer', 'initialName', 'customerName', 'customer_addr_quartier', 'customer_addr_rue', 'customer_addr_lot', 'customer_addr_code_postal', 'nif', 'stat', 'rcs', 'customerPhone', 'customerEmail', 'siteWeb', 'description', 'logo', 'customer_slogan')
            ->where('idCustomer', Customer::idCustomer())
            ->first();

        $invoice = DB::table('invoices')
            ->join('mode_paiements', 'invoices.idPaiement', '=', 'mode_paiements.idPaiement')
            ->join('pm_types', 'mode_paiements.idTypePm', '=', 'pm_types.idTypePm')
            ->join('type_factures', 'invoices.idTypeFacture', '=', 'type_factures.idTypeFacture')
            ->leftJoin('invoice_contacts as ic', 'invoices.idContact', '=', 'ic.idContact')
            ->select('invoices.*', 'mode_paiements.idTypePm', 'pm_types.*', 'type_factures.*', 'ic.contact_name', 'ic.contact_mail', 'ic.contact_phone')
            ->where('idInvoice', $id);

        if (!$invoice->exists()) {
            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ], 404);
        }

        $invoiceData = $invoice->first();

        if ($invoiceData->idTypeClient == 1) {
            //entreprise
            $typeCustomer = DB::table('customers')
                ->where('idCustomer', $invoiceData->idEntreprise)
                ->value('idTypeCustomer');

            if ($typeCustomer == 2) {
                $entreprise = DB::table('v_collaboration_cfp_etps')
                    ->select('idEtp', 'etp_name', 'etp_email', 'etp_nif', 'etp_ville', 'etp_stat', 'etp_rcs', 'etp_addr_lot', 'etp_addr_quartier', 'etp_addr_code_postal', 'etp_phone')
                    ->where('idEtp', $invoiceData->idEntreprise)
                    ->first();
            } elseif ($typeCustomer == 1) {
                $entreprise = DB::table('v_cfp_all')
                    ->select(
                        'customerName as etp_name',
                        'nif as etp_nif',
                        'stat as etp_stat',
                        'rcs as etp_rcs',
                        'customerEmail as etp_email',
                        'customerPhone as etp_phone',
                        'customer_addr_quartier as etp_addr_quartier',
                        'customer_ville as etp_ville',
                        'customer_addr_code_postal as etp_addr_code_postal',
                        'customer_addr_lot as etp_addr_lot',
                        'idCfp as idEtp'
                    )
                    ->where('idCfp', $invoiceData->idEntreprise)
                    ->first();
            }
        } elseif ($invoiceData->idTypeClient == 2) {
            $entreprise = DB::table('v_list_particuliers as p')
                ->select(
                    'p.part_name as etp_name',
                    'p.part_email as etp_email',
                    DB::raw('NULL as etp_nif'),
                    DB::raw('NULL as etp_stat'),
                    DB::raw('NULL as etp_rcs'),
                    'p.part_phone as etp_phone',
                    'p.part_addr_quartier as etp_addr_quartier',
                    'p.part_addr_code_postal as etp_addr_code_postal',
                    'p.part_addr_lot as etp_addr_lot',
                    'p.idParticulier as idEtp',
                    'p.part_ville as etp_ville',
                )
                ->where('p.idParticulier', $invoiceData->idEntreprise)
                ->first();
        }

        $invoiceDetails = DB::table('invoice_details_acompte as ida')
            ->join('unites', 'ida.idUnite', '=', 'unites.idUnite')
            ->select('ida.*', 'unites.unite_name as unit_name')
            ->where('ida.idInvoice', $id)
            ->orderBy('ida.idItem', 'asc')
            ->get();

        $unites = DB::table('unites')
            ->select('idUnite', 'unite_name')
            ->get();

        $pm = DB::table('pm_types')->select('idTypePm', 'pm_type_name')->get();
        $type_invoice = DB::table('type_factures')->get();

        $ville_codeds = DB::table('ville_codeds')->get();
        $accounts = BankAcount::where('ba_idCustomer', Customer::idCustomer())->get();
        $companies = Company::where('idCustomer', Customer::idCustomer())->select('id', 'name', 'nif', 'stat')->get();

        $acompteInfo = DB::table('invoice_acomptes')
            ->where('idInvoice', $id)
            ->first();

        return response()->json([
            'status' => 200,
            'customer' => $customer,
            'invoice' => $invoiceData,
            'entreprise' => $entreprise,
            'invoiceDetails' => $invoiceDetails,
            'unites' => $unites,
            'pm' => $pm,
            'type_invoice' => $type_invoice,
            'ville_codeds' => $ville_codeds,
            'accounts' => $accounts,
            'companies' => $companies,
            'acompteInfo' => $acompteInfo
        ]);
    }
}
