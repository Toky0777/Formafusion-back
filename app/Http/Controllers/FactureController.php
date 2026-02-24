<?php

namespace App\Http\Controllers;

use App\Exports\InvoicesExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreFactureRequest;
use App\Http\Requests\UpdateFactureRequest;
use App\Mail\InvoiceMail;
use App\Models\BankAcount;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\InvoicePayment;
use App\Models\InvoiceDeleted;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Models\Customer;
use App\Models\InvoiceDetailAcompte;
use App\Models\InvoiceDetailProfo;
use App\Models\MobileMoneyAcount;
use App\Services\BrevoService;
use Carbon\Carbon;
use Exception;

class FactureController extends Controller
{
    public function index(Request $request, $id)
    {
        $perPage = $request->input('perPage', 10);

        // par status groupé(1 à 6)
        if (in_array($id, [1, 2, 3, 4, 5, 6])) {
            switch ($id) {
                case 1:
                    $invoices = $this->filterInvoices($request)
                        ->paginate($perPage);
                    break;

                case 2:
                    $invoices = $this->filterInvoices($request)
                        ->whereIn('invoice_status', [2, 3, 5, 6, 7, 8, 10, 11, 12, 13, 14, 15])
                        ->paginate($perPage);
                    break;

                case 3:
                    $invoices = $this->filterInvoices($request)
                        ->whereNotIn('invoice_status', [1, 4, 9])
                        ->where('invoice_date_pm', '<', Carbon::now())
                        ->paginate($perPage);
                    break;

                case 4:
                    $invoices = $this->filterInvoices($request)
                        ->whereIn('invoice_status', [1])
                        ->paginate($perPage);
                    break;

                // SAINE
                case 5:
                    $invoices = $this->filterInvoices($request)
                        ->whereIn('invoice_status', [2, 3, 5, 6, 7, 8, 10])
                        ->paginate($perPage);
                    break;

                // PERDU
                case 6:
                    $invoices = $this->filterInvoices($request)
                        ->whereIn('invoice_status', [11, 12, 13, 14, 15])
                        ->paginate($perPage);
                    break;
            }
        }
        // par status directement
        elseif ($id > 10) {
            $invoices = $this->filterInvoices($request)
                ->where('invoice_status', $id)
                ->paginate($perPage);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Introuvable !'
            ], 204);
        }

        $countInvoices = $invoices->total();

        return response()->json([
            'status' => 200,
            'invoices' => $invoices,
            'countInvoices' => $countInvoices,
            'pm_types' => $this->getTypePaiements(),
            'ba_accounts' => $this->getBankAcounts(),
            'mm_accounts' => $this->getMobileMoneyAcounts()
        ]);
    }

    public function getTypePaiements()
    {
        return DB::table('pm_types')->select('idTypePm', 'pm_type_name')->get();
    }

    public function getBankAcounts()
    {
        return BankAcount::where('ba_idCustomer', Customer::idCustomer())->get();
    }
    public function getMobileMoneyAcounts()
    {
        return MobileMoneyAcount::where('mm_idCustomer', Customer::idCustomer())->get();
    }

    public function getFiltre()
    {
        $statuses = DB::table('invoice_status')->select('idInvoiceStatus', 'invoice_status_name')->get();
        $entreprises = $this->getEntreprises();
        return response()->json([
            'status' => 200,
            'entreprises' => $entreprises,
            'statuses' => $statuses
        ]);
    }

    // Dans la méthode filterInvoices, ajoutez la condition pour idTypeFacture :
    private function filterInvoices($request)
    {
        return Invoice::with([
            'entrepriseFromVcollaboration',
            'particulier',
            'status',
            'payments',
            'company'
        ])
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
            ->when($request->idTypeFacture, function ($query) use ($request) {
                $query->where('idTypeFacture', $request->idTypeFacture);
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
            ->doesntHave('deletedInvoices')
            ->standardAcompteSolde()
            ->orderBy('idInvoice', 'desc');
    }
    public function getInvoiceGrouped($id)
    {
        $data =  Invoice::with([
            'entrepriseFromVcollaboration',
            'particulier',
            'status',
            'payments',
            'company'
        ])
            ->leftJoin('invoice_contacts as ic', 'invoices.idContact', '=', 'ic.idContact')
            ->select(
                'invoices.*',
                'ic.contact_name',
                'ic.contact_mail',
                'ic.contact_phone'
            )
            ->where('idEntreprise', $id)
            ->whereIn('invoice_status', [2, 3, 5, 6])
            ->where('idCustomer', Customer::idCustomer())
            ->doesntHave('deletedInvoices')
            ->standard()
            ->orderBy('idInvoice', 'desc');
        return response()->json([
            'status' => 200,
            'count' => $data->count(),
            'invoices' => $data->get(),

        ]);
    }

    public function dashboard(Request $request)
    {
        $countInvoices = $this->filterInvoices($request)
            ->count();

        return response()->json([
            'status' => 200,
            'countInvoices' => $countInvoices,
            'total_montant' => $this->getTotalMontant(),
            'total_echues' => $this->getTotalEchues(),
            'total_perdu' => $this->getInvoicePerdu(),
            'restantSaine' => $this->getRestantSaine(),
            'total_douteuse' => $this->getSumByStatus(11),
            'total_litige' => $this->getSumByStatus(12),
            'total_irrecouvrable' => $this->getSumByStatus(13),
            'total_transmis_huissier' => $this->getSumByStatus(14),
            'total_poursuite_judiciaire' => $this->getSumByStatus(15),
        ]);
    }

    private function getFactureQuery()
    {
        return Invoice::with(['entrepriseFromVcollaboration', 'particulier', 'status'])
            ->where('idCustomer', Customer::idCustomer())
            ->doesntHave('deletedInvoices')
            ->standard()
            ->orderBy('idInvoice', 'desc');
    }

    private function getInvoicePerdu()
    {
        $invoicesQuery = $this->getFactureQuery();
        $invoicesPerdu = $invoicesQuery->whereIn('invoice_status', [11, 12, 13, 14, 15])->get();

        $total_montant_perdu = $invoicesPerdu->sum('invoice_total_amount');
        return $total_montant_perdu;
    }

    private function getTotalMontant()
    {
        $invoicesQuery = $this->getFactureQuery();
        $invoices = $invoicesQuery->whereNotIn('invoice_status', [1, 4, 9, 10])->get();

        $total_montant = $invoices->sum('invoice_total_amount');
        return $total_montant;
    }

    private function getTotalPayed()
    {
        $invoicesQuery = $this->getFactureQuery();
        $invoices = $invoicesQuery->whereNotIn('invoice_status', [1, 4, 9, 10])->get();

        $total_paye = $invoices->pluck('payments')->flatten()->sum('amount');
        return $total_paye;
    }

    private function getRestantDu()
    {
        $total_montant = $this->getTotalMontant();
        $total_paye = $this->getTotalPayed();
        return $total_montant - $total_paye;
    }

    private function getTotalEchues()
    {
        $invoicesQuery = $this->getFactureQuery();
        $invoices = $invoicesQuery->whereNotIn('invoice_status', [1, 4, 9, 10])
            ->where('invoice_date_pm', '<', Carbon::now())
            ->get();

        $total_montant_echu = $invoices->sum('invoice_total_amount');
        $total_paid_past_due = $invoices->pluck('payments')->flatten()->sum('amount');
        return $total_montant_echu - $total_paid_past_due;
    }

    public function getRestantSaine()
    {
        $restantDu = $this->getRestantDu();
        $total_montant_perdu = $this->getInvoicePerdu();
        return $restantDu - $total_montant_perdu;
    }

    public function getSumByStatus($idStatus)
    {
        $invoicesQuery = $this->getFactureQuery();
        $invoices = $invoicesQuery->where('invoice_status', $idStatus)->get();

        $sum = $invoices->sum('invoice_total_amount');
        return $sum;
    }

    private function getEntreprises()
    {
        return Invoice::with(['entrepriseFromVcollaboration', 'particulier'])
            ->standard()
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
                return $entreprise->idEtp ?? $entreprise->idCfp;
            })
            ->sortBy(function ($entreprise) {
                return $entreprise->etp_name ?? $entreprise->part_name;
            });
    }

    public function getAllEtps()
    {
        $allClients = DB::table('v_collaboration_cfp_etps')
            ->select(
                'idEtp',
                'etp_name',
                'etp_email',
                'etp_nif',
                'etp_ville',
                'etp_stat',
                'etp_rcs',
                'etp_addr_lot',
                'etp_addr_quartier',
                'etp_addr_code_postal',
                DB::raw("'Entreprise' as type_client"),
                DB::raw("1 as idTypeClient ")
            )
            ->where('idCfp', Customer::idCustomer());

        // Sous-traitants (même structure)
        $subContractors = DB::table('v_list_sub_contractors')
            ->select(
                'idCfp as idEtp',
                'cfp_name as etp_name',
                'cfp_email as etp_email',
                'cfp_nif as etp_nif',
                'cfp_ville as etp_ville',
                'cfp_stat as etp_stat',
                'cfp_rcs as etp_rcs',
                'cfp_addr_lot as etp_addr_lot',
                'cfp_addr_quartier as etp_addr_quartier',
                'cfp_addr_code_postal as etp_addr_code_postal',
                DB::raw("'Entreprise' as type_client"),
                DB::raw("1 as idTypeClient ")
            )
            ->join('users', 'idCfp', '=', 'users.id')
            ->where('idSubContractor', Customer::idCustomer());

        // Particuliers (même structure, champs administratifs à NULL)
        $particuliers = DB::table('v_list_particuliers as p')
            ->select(
                'p.idParticulier as idEtp',
                'p.part_name as etp_name',
                'p.part_email as etp_email',
                DB::raw('NULL as etp_nif'),
                DB::raw('NULL as etp_ville'),
                DB::raw('NULL as etp_stat'),
                DB::raw('NULL as etp_rcs'),
                'p.part_addr_lot as etp_addr_lot',
                'p.part_addr_quartier as etp_addr_quartier',
                DB::raw('NULL as etp_addr_code_postal'),
                DB::raw("'Particulier' as type_client"),
                DB::raw("2 as idTypeClient ")
            );

        // Union des trois
        $clients = $allClients
            ->unionAll($subContractors)
            ->unionAll($particuliers)
            ->orderBy('etp_name', 'asc')
            ->get();
        return response()->json(['clients' => $clients]);
    }

    public function getProjectsByClient($clientId)
    {
        $typeCustomer = DB::table('customers')
            ->where('idCustomer', $clientId)
            ->value('idTypeCustomer');

        if (!in_array($typeCustomer, [1, 2])) {
            return response()->json([]); // Si le type de client est inconnu, retourne une réponse vide
        }

        $projets = $this->getProjects($clientId, $typeCustomer);

        return response()->json($projets);
    }

    public function getProjects($clientId, $typeCustomer)
    {
        $query = DB::table('v_projet_cfps')
            ->select('v_projet_cfps.idProjet', 'module_name', 'numero_bc as project_reference', 'dateDebut')
            ->leftJoin('invoice_details', 'v_projet_cfps.idProjet', '=', 'invoice_details.idProjet')
            ->where('project_is_active', '=', 1)
            ->where('module_name', '!=', 'Default module')
            // ->whereNull('invoice_details.idProjet')
            ->orderBy('dateDebut', 'desc');

        if ($typeCustomer == 2) {
            // Récupérer les projets inter
            $projet_inter = DB::table('inter_entreprises')
                ->where('idEtp', $clientId)
                ->pluck('idProjet');

            // projets intra + inter
            $query->where('idCustomer', Customer::idCustomer())
                ->where(function ($q) use ($clientId, $projet_inter) {
                    $q->where('idEtp', $clientId)  // Projets intra
                        ->orWhereIn('idProjet', $projet_inter); // Projets inter
                });
        } elseif ($typeCustomer == 1) {
            $idProjetSubContractors = DB::table('project_sub_contracts')
                ->where('idSubContractor', Customer::idCustomer())
                ->pluck('idProjet'); // Récupère les projets où l'utilisateur est sous-traitant

            $query->whereIn('idProjet', $idProjetSubContractors)
                ->where('idCfp', $clientId);
        }

        return $query->get();
    }

    public function getDossierByClient($clientId)
    {
        $dossiers = DB::table('dossiers')
            ->leftJoin('v_projet_cfps', 'dossiers.idDossier', '=', 'v_projet_cfps.idDossier')
            ->where('v_projet_cfps.idEtp', $clientId)
            ->select(
                'dossiers.idDossier',
                'dossiers.nomDossier'
            )
            ->groupBy('dossiers.idDossier', 'dossiers.nomDossier')
            ->orderBy('dossiers.nomDossier', 'asc')
            ->get();

        return response()->json($dossiers);
    }

    function getProjetByDossier($idDossier)
    {
        $projets = DB::table('v_projet_cfps')
            ->select(
                'idProjet',
                'dateDebut',
                'module_name',
                'project_reference'
            )
            ->where('idCustomer', Customer::idCustomer())
            ->where('idDossier', $idDossier)
            ->where('project_is_trashed', 0)
            ->orderBy('dateDebut', 'asc')
            ->get();
        return response()->json($projets);
    }


    public function create()
    {
        $type_invoice = DB::table('type_factures')->get();

        $customer = DB::table('v_detail_customers')
            ->select('idCustomer')
            ->where('idCustomer', Customer::idCustomer())
            ->first();

        $unites = DB::table('unites')
            ->select('idUnite', 'unite_name')
            ->get();

        $fv = DB::table('frais')
            ->select('idFrais', 'Frais', 'exemple')
            ->get();

        $pm = DB::table('pm_types')->select('idTypePm', 'pm_type_name')->get();
        $ville_codeds = DB::table('ville_codeds')->get();

        $accounts = BankAcount::where('ba_idCustomer', Customer::idCustomer())->get();

        $number_invoice = DB::table('invoices')
            ->select('invoice_number', 'invoice_date')
            ->orderBy('idInvoice', 'desc')
            ->where('idCustomer', Customer::idCustomer())
            ->take(3)
            ->get();

        $companies = Company::where('idCustomer', Customer::idCustomer())->select('id', 'name', 'nif', 'stat')->get();

        return response()->json([
            'customer' => $customer,
            'unites' => $unites,
            'fv' => $fv,
            'pm' => $pm,
            'type_invoice' => $type_invoice,
            'ville_codeds' => $ville_codeds,
            'accounts' => $accounts,
            'number_invoice' => $number_invoice,
            'companies' => $companies
        ]);
    }

    public function store(StoreFactureRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated, $request, &$redirectUrl) {
                $idPaiement = DB::table('mode_paiements')->insertGetId([
                    'idTypePm' => $validated['idPaiement']
                ]);
                $subTotal = 0;

                $invoiceId = DB::table('invoices')->insertGetId([
                    'invoice_number' => $validated['invoice_number'],
                    'invoice_bc' => $validated['invoice_bc'],
                    'invoice_date' => $validated['invoice_date'],
                    'invoice_date_pm' => $validated['invoice_date_pm'],
                    'invoice_status' => $validated['invoice_status'],
                    'invoice_condition' => $validated['invoice_condition'],
                    'invoice_reduction' => $validated['invoice_reduction'],
                    'invoice_tva' => $validated['invoice_tva'],
                    'invoice_total_amount' => $validated['invoice_total_amount'],
                    'invoice_letter' => $validated['invoice_letter'],
                    'idCustomer' => $validated['idCustomer'],
                    'idCompany' => $validated['idCompany'],
                    'idEntreprise' => $validated['idEntreprise'],
                    'idPaiement' => $idPaiement,
                    'idTypeFacture' => $validated['idTypeFacture'],
                    'idBankAcount' => $validated['idBankAcount'] ?? null,
                    'idTypeClient' => $validated['idTypeClient'],
                    'idContact' => $validated['idContact'] ?? null,
                ]);

                if ($request->idTypeFacture == 1) {
                    // Facture standard
                    $items = array_filter($request->items);
                    foreach ($items as $item) {
                        $itemTotalPrice = ($item['item_qty'] * $item['item_unit_price']);
                        $subTotal += $itemTotalPrice;
                        InvoiceDetail::create([
                            'idInvoice' => $invoiceId,
                            'idItems' => $item['idItems'],
                            'idProjet' => $item['idProjet'],
                            'item_qty' => $item['item_qty'],
                            'item_description' => $item['item_description'],
                            'item_unit_price' => $item['item_unit_price'],
                            'idUnite' => $item['idUnite'],
                            'item_total_price' => $itemTotalPrice,
                        ]);
                    }
                } elseif ($request->idTypeFacture == 2) {
                    // Facture proforma
                    $items = array_filter($request->items);
                    foreach ($items as $item) {
                        $itemTotalPrice = ($item['item_qty'] * $item['item_unit_price']);
                        $subTotal += $itemTotalPrice;
                        InvoiceDetailProfo::create([
                            'idInvoice' => $invoiceId,
                            'idItems' => $item['idItems'],
                            'idModule' => $item['idModule'],
                            'item_qty' => $item['item_qty'],
                            'item_description' => $item['item_description'],
                            'item_unit_price' => $item['item_unit_price'],
                            'idUnite' => $item['idUnite'],
                            'item_total_price' => $itemTotalPrice,
                        ]);
                    }
                } elseif ($request->idTypeFacture == 3) {
                    // Facture d'acompte
                    DB::table('invoice_acomptes')->insert([
                        'idInvoice' => $invoiceId,
                        'idBC' => $validated['invoice_bc']
                    ]);

                    $itemTotalPrice = ($validated['item_qty'] * $validated['item_unit_price']);
                    $subTotal += $itemTotalPrice;
                    InvoiceDetailAcompte::create([
                        'idInvoice' => $invoiceId,
                        'item_qty' => $validated['item_qty'],
                        'item_description' => $validated['item_description'],
                        'item_unit_price' => $validated['item_unit_price'],
                        'idUnite' => $validated['idUnite'],
                        'item_total_price' => $itemTotalPrice,
                    ]);
                } elseif ($request->idTypeFacture == 4) {
                    $bc_amount = DB::table('bon_commandes')
                        ->where('idBC', $validated['invoice_bc'])
                        ->value('montant');
                    $acount_paid = DB::table('invoice_acomptes')
                        ->where('idBC', $validated['invoice_bc'])
                        ->join('invoices', 'invoice_acomptes.idInvoice', '=', 'invoices.idInvoice')
                        ->sum('invoices.invoice_total_amount');
                    // Facture de solde
                    DB::table('invoice_soldes')->insert([
                        'idInvoice' => $invoiceId,
                        'idBC' => $validated['invoice_bc'],
                        'bc_amount' => $bc_amount,
                        'acount_paid' => $acount_paid ?? 0,
                    ]);

                    $items = [];
                    if ($request->has('item_service') && is_array($request->item_service)) {
                        foreach ($request->item_service as $index => $service) {
                            $items[] = [
                                'item_service' => $service,
                                'item_qty' => $request->item_qty[$index] ?? 1,
                                'item_description' => $request->item_description[$index] ?? '',
                                'item_unit_price' => $request->item_unit_price[$index] ?? 0,
                                'idUnite' => $request->idUnite[$index] ?? 4,
                                'item_total_price' => $request->item_total_price[$index] ?? 0,
                            ];
                        }
                    }

                    foreach ($items as $item) {
                        $itemTotalPrice = $item['item_total_price'];
                        $subTotal += $itemTotalPrice;
                        DB::table('invoice_details_solde')->insert([
                            'idInvoice' => $invoiceId,
                            'item_service' => $item['item_service'],
                            'item_qty' => $item['item_qty'],
                            'item_description' => $item['item_description'],
                            'item_unit_price' => $item['item_unit_price'],
                            'idUnite' => $item['idUnite'],
                            'item_total_price' => $item['item_total_price'],
                        ]);
                    }
                }

                DB::table('invoices')
                    ->where('idInvoice', $invoiceId)
                    ->update(['invoice_sub_total' => $subTotal]);

                if (isset($validated['standards'])) {
                    DB::table('invoice_standards')->insert([
                        'idInvoice' => $invoiceId
                    ]);
                }

                $redirectUrl = $invoiceId;
            });
            return response()->json(['idInvoice' => $redirectUrl], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function view($invoiceId)
    {
        $invoice = DB::table('invoices')
            ->join('mode_paiements', 'invoices.idPaiement', '=', 'mode_paiements.idPaiement')
            ->join('pm_types', 'mode_paiements.idTypePm', '=', 'pm_types.idTypePm')
            ->join('customers', 'invoices.idCustomer', '=', 'customers.idCustomer')
            ->leftJoin('companies', 'invoices.idCompany', '=', 'companies.id')
            ->join('type_factures', 'invoices.idTypeFacture', '=', 'type_factures.idTypeFacture')
            ->join('ville_codeds as vc', 'customers.idVilleCoded', 'vc.id')
            ->leftJoin('bankacounts', 'invoices.idBankAcount', 'bankacounts.id')
            ->leftJoin('ville_codeds', 'bankacounts.ba_idPostal', 'ville_codeds.id')
            ->select('invoices.*', 'pm_types.*', 'customers.logo', 'customers.customer_addr_lot', 'customers.customer_addr_quartier', 'companies.*', 'type_factures.*', 'vc.vi_code_postal as customer_addr_code_postal', 'bankacounts.*', 'ville_codeds.*')
            ->where('idInvoice', $invoiceId);

        if ($invoice->exists()) {
            $typeCustomer = DB::table('customers')
                ->where('idCustomer', $invoice->first()->idEntreprise)
                ->value('idTypeCustomer');

            if ($typeCustomer == 2) {
                $entreprise = DB::table('v_collaboration_cfp_etps')
                    ->select('etp_name', 'etp_nif', 'etp_stat', 'etp_addr_quartier', 'etp_addr_code_postal', 'etp_addr_lot', 'idEtp')
                    ->where('idEtp', $invoice->first()->idEntreprise)
                    ->first();
            } elseif ($typeCustomer == 1) {
                $entreprise = DB::table('v_cfp_all')
                    ->select(
                        'customerName as etp_name',
                        'nif as etp_nif',
                        'stat as etp_stat',
                        'customer_addr_quartier as etp_addr_quartier',
                        'customer_addr_code_postal as etp_addr_code_postal',
                        'customer_addr_lot as etp_addr_lot',
                        'idCfp as idEtp'
                    )
                    ->where('idCfp', $invoice->first()->idEntreprise)
                    ->first();
            } else {
                $entreprise = DB::table('v_list_particuliers')
                    ->select(
                        DB::raw("CONCAT(part_name, ' ', part_firstname) as etp_name"),
                        'part_email as etp_email',
                        'part_cin as etp_nif',
                        'part_phone as etp_phone',
                        'part_addr_lot as etp_addr_lot',
                        'part_addr_quartier as etp_addr_quartier',
                        DB::raw("NULL as etp_stat"),
                        DB::raw("NULL as etp_rcs"),
                        DB::raw("NULL as etp_ville"),
                        DB::raw("NULL as etp_addr_code_postal"),
                        'idParticulier as idEtp'
                    )
                    ->where('idParticulier', $invoice->first()->idEntreprise)
                    ->first();
            }

            if ($invoice->first()->idTypeFacture == 1) {
                // Récupérer PROJET if STANDARD
                $invoiceDetails = DB::table('invoice_details')
                    ->join('v_projet_cfps', 'invoice_details.idProjet', '=', 'v_projet_cfps.idProjet')
                    ->join('unites', 'invoice_details.idUnite', '=', 'unites.idUnite')
                    ->select('invoice_details.*', 'unites.unite_name as unit_name', 'v_projet_cfps.module_name as module_name')
                    ->where('idInvoice', $invoiceId)
                    ->where('idItems', 0)
                    ->get();
                // Récupérer les détails de la facture avec les noms des unités
                $invoiceDetailsOther = DB::table('invoice_details')
                    ->join('unites', 'invoice_details.idUnite', '=', 'unites.idUnite')
                    ->join('frais', 'invoice_details.idItems', '=', 'frais.idFrais')
                    ->select('invoice_details.*', 'unites.unite_name as unit_name', 'frais.*')
                    ->where('idInvoice', $invoiceId)
                    ->orderBy('idItems', 'asc')
                    ->get();
            } elseif ($invoice->first()->idTypeFacture == 2) {
                // Récupérer COURS if PROFORMA
                $invoiceDetails = DB::table('invoice_details_profo')
                    ->join('v_module_cfps', 'invoice_details_profo.idModule', '=', 'v_module_cfps.idModule')
                    ->join('unites', 'invoice_details_profo.idUnite', '=', 'unites.idUnite')
                    ->select('invoice_details_profo.*', 'unites.unite_name as unit_name', 'v_module_cfps.moduleName as module_name')
                    ->where('idInvoice', $invoiceId)
                    ->where('idItems', 0)
                    ->get();

                $invoiceDetailsOther = DB::table('invoice_details_profo')
                    ->join('unites', 'invoice_details_profo.idUnite', '=', 'unites.idUnite')
                    ->join('frais', 'invoice_details_profo.idItems', '=', 'frais.idFrais')
                    ->select('invoice_details_profo.*', 'unites.unite_name as unit_name', 'frais.*')
                    ->where('idInvoice', $invoiceId)
                    ->orderBy('idItems', 'asc')
                    ->get();
            } elseif ($invoice->first()->idTypeFacture == 3) {
                // Récupérer les détails pour facture d'acompte
                $invoiceDetails = DB::table('invoice_details_acompte')
                    ->join('unites', 'invoice_details_acompte.idUnite', '=', 'unites.idUnite')
                    ->select('invoice_details_acompte.*', 'unites.unite_name as unit_name')
                    ->where('idInvoice', $invoiceId)
                    ->get();

                $invoiceDetailsOther = collect([]);

                // Récupérer les informations de l'acompte avec le numéro de BC
                $acompteInfo = DB::table('invoice_acomptes as ia')
                    ->join('bon_commandes as bc', 'ia.idBC', '=', 'bc.idBC')
                    ->select(
                        'ia.*',
                        'bc.numero',
                        'bc.date',
                        'bc.montant'
                    )
                    ->where('ia.idInvoice', $invoiceId)
                    ->first();
            } elseif ($invoice->first()->idTypeFacture == 4) {
                // details de la facture de solde
                $invoiceDetails = DB::table('invoice_details_solde as ids')
                    ->leftJoin('invoice_deleted as id', 'ids.idInvoice', '=', 'id.idInvoice')
                    ->join('unites', 'ids.idUnite', '=', 'unites.idUnite')
                    ->select('ids.*', 'unites.unite_name as unit_name')
                    ->where('ids.idInvoice', $invoiceId)
                    ->whereNull('id.idInvoice')
                    ->get();

                $invoiceDetailsOther = collect([]);

                // Récupérer les informations du solde
                $soldeInfo = DB::table('invoice_soldes as is')
                    ->join('bon_commandes as bc', 'is.idBC', '=', 'bc.idBC')
                    ->select('bc.numero')
                    ->where('is.idInvoice', $invoiceId)
                    ->first();
            }

            return response()->json([
                'status' => 200,
                'invoice' => $invoice->first(),
                'invoiceDetails' => $invoiceDetails,
                'invoiceDetailsOther' => $invoiceDetailsOther,
                'entreprise' => $entreprise,
                'acompteInfo' => $acompteInfo ?? null,
                'soldeInfo' => $soldeInfo ?? null
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Introuvable !'
            ], 204);
        }
    }

    public function show($id)
    {
        // Récupérer la facture
        $invoice = DB::table('invoices')
            ->join('mode_paiements', 'invoices.idPaiement', '=', 'mode_paiements.idPaiement')
            ->join('pm_types', 'mode_paiements.idTypePm', '=', 'pm_types.idTypePm')
            ->join('customers', 'invoices.idCustomer', '=', 'customers.idCustomer')
            ->leftJoin('companies', 'invoices.idCompany', '=', 'companies.id')
            ->join('type_factures', 'invoices.idTypeFacture', '=', 'type_factures.idTypeFacture')
            ->join('ville_codeds as vc', 'customers.idVilleCoded', 'vc.id')
            ->join('invoice_status', 'invoices.invoice_status', 'invoice_status.idInvoiceStatus')
            ->leftJoin('bankacounts', 'invoices.idBankAcount', 'bankacounts.id')
            ->leftJoin('ville_codeds', 'bankacounts.ba_idPostal', 'ville_codeds.id')
            ->leftJoin('invoice_contacts as ic', 'invoices.idContact', '=', 'ic.idContact')
            ->select(
                'invoices.*',
                'pm_types.*',
                'customers.logo',
                'customers.customer_addr_lot',
                'customers.customer_addr_quartier',
                'companies.*',
                'type_factures.*',
                'vc.vi_code_postal as customer_addr_code_postal',
                'invoice_status.*',
                'bankacounts.*',
                'ville_codeds.*',
                'ic.contact_name',
                'ic.contact_mail',
                'ic.contact_phone'
            )
            ->where('idInvoice', $id)
            ->first();

        $payments = DB::table('invoice_payments as ip')
            ->where('invoice_id', $id)
            ->leftJoin('pm_types', 'ip.payment_method_id', '=', 'pm_types.idTypePm')
            ->leftJoin('bankacounts', 'ip.payment_bank_id', '=', 'bankacounts.id')
            ->select(
                'ip.*',
                'pm_types.idTypePm',
                'pm_types.pm_type_name',
                'bankacounts.id',
                'bankacounts.ba_idCustomer',
                'bankacounts.ba_titulaire',
                'bankacounts.ba_name',
                'bankacounts.ba_idPostal',
                'bankacounts.ba_quartier',
                'bankacounts.ba_account_number'
            )
            ->where('ip.invoice_id', $id)
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'invoice_id' => $payment->invoice_id,
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date,
                    'payment_method_id' => $payment->payment_method_id,
                    'payment_bank_id' => $payment->payment_bank_id,
                    'payment_mobilemoney_id' => $payment->payment_mobilemoney_id,
                    'payment_description' => $payment->payment_description,
                    'created_at' => $payment->created_at,
                    'updated_at' => $payment->updated_at,
                    'mode_paiement' => $payment->idTypePm ? [
                        'idTypePm' => $payment->idTypePm,
                        'pm_type_name' => $payment->pm_type_name
                    ] : null,
                    'bankacount' => $payment->id ? [
                        'id' => $payment->id,
                        'ba_idCustomer' => $payment->ba_idCustomer,
                        'ba_titulaire' => $payment->ba_titulaire,
                        'ba_name' => $payment->ba_name,
                        'ba_idPostal' => $payment->ba_idPostal,
                        'ba_quartier' => $payment->ba_quartier,
                        'ba_account_number' => $payment->ba_account_number
                    ] : null
                ];
            });

        // Ajouter les paiements à l'objet facture
        if ($invoice) {
            $invoice->payments = $payments;
        }

        if ($invoice->idTypeClient == 1) {
            //entreprise
            $typeCustomer = DB::table('customers')
                ->where('idCustomer', $invoice->idEntreprise)
                ->value('idTypeCustomer');

            if ($typeCustomer == 2) {
                $entreprise = DB::table('v_collaboration_cfp_etps')
                    ->select('etp_name', 'etp_nif', 'etp_stat', 'etp_addr_quartier', 'etp_addr_code_postal', 'etp_addr_lot', 'idEtp', 'etp_ville')
                    ->where('idEtp', $invoice->idEntreprise)
                    ->first();
            } elseif ($typeCustomer == 1) {
                $entreprise = DB::table('v_cfp_all')
                    ->select(
                        'customerName as etp_name',
                        'nif as etp_nif',
                        'stat as etp_stat',
                        'customer_addr_quartier as etp_addr_quartier',
                        'customer_ville as etp_ville',
                        'customer_addr_code_postal as etp_addr_code_postal',
                        'customer_addr_lot as etp_addr_lot',
                        'idCfp as idEtp'
                    )
                    ->where('idCfp', $invoice->idEntreprise)
                    ->first();
            }
        } elseif ($invoice->idTypeClient == 2) {
            //particulier
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
                ->where('p.idParticulier', $invoice->idEntreprise)
                ->first();
        }

        if ($invoice->idTypeFacture == 1) {
            // Récupérer PROJET if STANDARD
            $invoiceDetails = DB::table('invoice_details')
                ->join('v_projet_cfps as p', 'invoice_details.idProjet', '=', 'p.idProjet')
                ->join('unites', 'invoice_details.idUnite', '=', 'unites.idUnite')
                ->leftJoin('bc_documents as doc', 'p.idBc', '=', 'doc.idBC')
                ->select('invoice_details.*', 'unites.unite_name as unit_name', 'p.module_name as module_name', 'p.idProjet as idProjet', 'p.idBc as idBC', 'p.numero_bc as numero_bc', 'doc.idDocument as idDocument')
                ->where('idInvoice', $id)
                ->where('idItems', 0)
                ->get();
            // Récupérer les détails de la facture avec les noms des unités
            $invoiceDetailsOther = DB::table('invoice_details')
                ->join('unites', 'invoice_details.idUnite', '=', 'unites.idUnite')
                ->join('frais', 'invoice_details.idItems', '=', 'frais.idFrais')
                ->select('invoice_details.*', 'unites.unite_name as unit_name', 'frais.*')
                ->where('idInvoice', $id)
                ->orderBy('idItems', 'asc')
                ->get();
        } elseif ($invoice->idTypeFacture == 2) {
            // Récupérer COURS if PROFORMA
            $invoiceDetails = DB::table('invoice_details_profo')
                ->join('v_module_cfps', 'invoice_details_profo.idModule', '=', 'v_module_cfps.idModule')
                ->join('unites', 'invoice_details_profo.idUnite', '=', 'unites.idUnite')
                ->select('invoice_details_profo.*', 'unites.unite_name as unit_name', 'v_module_cfps.moduleName as module_name')
                ->where('idInvoice', $id)
                ->where('idItems', 0)
                ->get();

            $invoiceDetailsOther = DB::table('invoice_details_profo')
                ->join('unites', 'invoice_details_profo.idUnite', '=', 'unites.idUnite')
                ->join('frais', 'invoice_details_profo.idItems', '=', 'frais.idFrais')
                ->select('invoice_details_profo.*', 'unites.unite_name as unit_name', 'frais.*')
                ->where('idInvoice', $id)
                ->orderBy('idItems', 'asc')
                ->get();
        } elseif ($invoice->idTypeFacture == 3) {
            // Pour les acomptes
            $invoiceDetails = DB::table('invoice_details_acompte')
                ->join('unites', 'invoice_details_acompte.idUnite', '=', 'unites.idUnite')
                ->select('invoice_details_acompte.*', 'unites.unite_name as unit_name')
                ->where('idInvoice', $id)
                ->get();

            $invoiceDetailsOther = collect([]);

            // Récupérer les informations de l'acompte avec le numéro de BC
            $acompteInfo = DB::table('invoice_acomptes as ia')
                ->join('bon_commandes as bc', 'ia.idBC', '=', 'bc.idBC')
                ->select(
                    'ia.*',
                    'bc.idBC',
                    'bc.numero',
                    'bc.montant'
                )
                ->where('ia.idInvoice', $id)
                ->first();
        } elseif ($invoice->idTypeFacture == 4) {
            // details de la facture de solde
            $invoiceDetails = DB::table('invoice_details_solde as ids')
                ->leftJoin('invoice_deleted as id', 'ids.idInvoice', '=', 'id.idInvoice')
                ->join('unites', 'ids.idUnite', '=', 'unites.idUnite')
                ->select('ids.*', 'unites.unite_name as unit_name')
                ->where('ids.idInvoice', $id)
                ->whereNull('id.idInvoice')
                ->get();

            $invoiceDetailsOther = collect([]);

            // Récupérer les informations du solde
            $soldeInfo = DB::table('invoice_soldes as is')
                ->join('bon_commandes as bc', 'is.idBC', '=', 'bc.idBC')
                ->select('bc.idBC', 'bc.numero')
                ->where('is.idInvoice', $id)
                ->first();
        }

        return response()->json([
            'status' => 200,
            'invoice' => $invoice,
            'invoiceDetails' => $invoiceDetails,
            'invoiceDetailsOther' => $invoiceDetailsOther,
            'entreprise' => $entreprise,
            'acompteInfo' => $acompteInfo ?? null,
            'soldeInfo' => $soldeInfo ?? null
        ]);
    }

    public function edit($id)
    {
        $customer = DB::table('v_detail_customers')
            ->select('idCustomer')
            ->where('idCustomer', Customer::idCustomer())
            ->first();

        $invoice = DB::table('invoices')
            ->join('mode_paiements', 'invoices.idPaiement', '=', 'mode_paiements.idPaiement')
            ->join('pm_types', 'mode_paiements.idTypePm', '=', 'pm_types.idTypePm')
            ->join('type_factures', 'invoices.idTypeFacture', '=', 'type_factures.idTypeFacture')
            ->leftJoin('invoice_contacts as ic', 'invoices.idContact', '=', 'ic.idContact')
            ->select('invoices.*', 'mode_paiements.idTypePm', 'pm_types.*', 'type_factures.*', 'ic.contact_name', 'ic.contact_mail', 'ic.contact_phone')
            ->where('idInvoice', $id)
            ->first();

        $typeCustomer = DB::table('customers')
            ->where('idCustomer', $invoice->idEntreprise)
            ->value('idTypeCustomer');

        if ($invoice->idTypeClient == 1) {
            //entreprise
            $typeCustomer = DB::table('customers')
                ->where('idCustomer', $invoice->idEntreprise)
                ->value('idTypeCustomer');

            if ($typeCustomer == 2) {
                $entreprise = DB::table('v_collaboration_cfp_etps')
                    ->select('idEtp', 'etp_name', 'etp_email', 'etp_nif', 'etp_ville', 'etp_stat', 'etp_rcs', 'etp_addr_lot', 'etp_addr_quartier', 'etp_addr_code_postal', 'etp_phone')
                    ->where('idEtp', $invoice->idEntreprise)
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
                    ->where('idCfp', $invoice->idEntreprise)
                    ->first();
            }
        } elseif ($invoice->idTypeClient == 2) {
            //particulier
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
                ->where('p.idParticulier', $invoice->idEntreprise)
                ->first();
        }

        if ($invoice->idTypeFacture == 1) {
            $invoiceDetailsProjets = DB::table('invoice_details')
                ->join('v_projet_cfps', 'invoice_details.idProjet', '=', 'v_projet_cfps.idProjet')
                ->join('unites', 'invoice_details.idUnite', '=', 'unites.idUnite')
                ->select('invoice_details.*', 'unites.idUnite as idUnite', 'unites.unite_name as unit_name', 'v_projet_cfps.idProjet')
                ->where('idInvoice', $id)
                ->where('idItems', 0)
                ->get();

            $invoiceDetails = DB::table('invoice_details')
                ->join('unites', 'invoice_details.idUnite', '=', 'unites.idUnite')
                ->join('frais', 'invoice_details.idItems', '=', 'frais.idFrais')
                ->select('invoice_details.*', 'unites.unite_name as unit_name', 'frais.Frais')
                ->where('idInvoice', $id)
                ->orderBy('idItems', 'asc')
                ->get();
        } elseif ($invoice->idTypeFacture == 2) {
            $invoiceDetailsProjets = DB::table('invoice_details_profo')
                ->join('v_module_cfps', 'invoice_details_profo.idModule', '=', 'v_module_cfps.idModule')
                ->join('unites', 'invoice_details_profo.idUnite', '=', 'unites.idUnite')
                ->select('invoice_details_profo.*', 'unites.idUnite as idUnite', 'unites.unite_name as unit_name', 'v_module_cfps.idModule')
                ->where('idInvoice', $id)
                ->where('idItems', 0)
                ->get();

            $invoiceDetails = DB::table('invoice_details_profo')
                ->join('unites', 'invoice_details_profo.idUnite', '=', 'unites.idUnite')
                ->join('frais', 'invoice_details_profo.idItems', '=', 'frais.idFrais')
                ->select('invoice_details_profo.*', 'unites.unite_name as unit_name', 'frais.Frais')
                ->where('idInvoice', $id)
                ->orderBy('idItems', 'asc')
                ->get();
        } elseif ($invoice->idTypeFacture == 3) {
            $invoiceDetails = DB::table('invoice_details_acompte')
                ->join('unites', 'invoice_details_acompte.idUnite', '=', 'unites.idUnite')
                ->select('invoice_details_acompte.*', 'unites.unite_name as unit_name', 'unites.idUnite as idUnite')
                ->where('idInvoice', $id)
                ->get();

            $invoiceDetailsProjets = collect([]);
        } elseif ($invoice->idTypeFacture == 4) {
            // Facture de solde
            $invoiceDetailsProjets = DB::table('invoice_details_solde')
                ->join('unites', 'invoice_details_solde.idUnite', '=', 'unites.idUnite')
                ->select('invoice_details_solde.*', 'unites.unite_name as unit_name')
                ->where('idInvoice', $id)
                ->get();

            // LISTE DES ACOMPTES
            $invoiceDetails = collect([]);
        }

        $unites = DB::table('unites')
            ->select('idUnite', 'unite_name')
            ->get();

        if ($invoice->idTypeClient == 1) {
            $projets = $this->getProjects($invoice->idEntreprise, $typeCustomer);
        } elseif ($invoice->idTypeClient == 2) {
            $projets = $this->getProjectsParticulier($invoice->idEntreprise);
        }

        $fv = DB::table('frais')
            ->select('idFrais', 'Frais', 'exemple')
            ->get();

        $pm = DB::table('pm_types')->select('idTypePm', 'pm_type_name')->get();
        $type_invoice = DB::table('type_factures')->get();
        $ville_codeds = DB::table('ville_codeds')->get();

        $accounts = BankAcount::where('ba_idCustomer', Customer::idCustomer())->get();
        $companies = Company::where('idCustomer', Customer::idCustomer())->select('id', 'name', 'nif', 'stat')->get();

        return response()->json([
            'status' => 200,
            'customer' => $customer,
            'invoice' => $invoice,
            'entreprise' => $entreprise,
            'invoiceDetails' => $invoiceDetails,
            'invoiceDetailsProjets' => $invoiceDetailsProjets,
            'unites' => $unites,
            'projets' => $projets,
            'fv' => $fv,
            'pm' => $pm,
            'type_invoice' => $type_invoice,
            'ville_codeds' => $ville_codeds,
            'accounts' => $accounts,
            'companies' => $companies
        ]);
    }

    public function getProjectsParticulier($idParticulier)
    {
        $projets = DB::table('particulier_projet as pp')
            ->join('v_projet_cfps as vp', 'pp.idProjet', '=', 'vp.idProjet')
            ->where('pp.idParticulier', $idParticulier)
            ->select(
                'vp.idProjet',
                'vp.module_name',
                'vp.project_reference',
                'vp.dateDebut'
            )
            ->get();

        return $projets;
    }
    public function update(UpdateFactureRequest $request, $idInvoice)
    {
        $validated = $request->validated();
        try {
            DB::transaction(function () use ($validated, $request, $idInvoice, &$redirectUrl) {
                // Mise à jour du mode de paiement
                $idPaiement = DB::table('mode_paiements')
                    ->where('idPaiement', function ($query) use ($idInvoice) {
                        $query->select('idPaiement')
                            ->from('invoices')
                            ->where('idInvoice', $idInvoice)
                            ->limit(1);
                    })
                    ->update([
                        'idTypePm' => $validated['idPaiement']
                    ]);

                $subTotal = 0;

                // Mise à jour de la facture
                DB::table('invoices')
                    ->where('idInvoice', $idInvoice)
                    ->update([
                        'invoice_number' => $validated['invoice_number'],
                        'invoice_bc' => $validated['invoice_bc'],
                        'invoice_date' => $validated['invoice_date'],
                        'invoice_date_pm' => $validated['invoice_date_pm'],
                        'invoice_status' => $validated['invoice_status'],
                        'invoice_condition' => $validated['invoice_condition'],
                        'invoice_reduction' => $validated['invoice_reduction'],
                        'invoice_tva' => $validated['invoice_tva'],
                        'invoice_total_amount' => $validated['invoice_total_amount'],
                        'invoice_letter' => $validated['invoice_letter'],
                        'idCustomer' => $validated['idCustomer'],
                        'idCompany' => $validated['idCompany'],
                        'idEntreprise' => $validated['idEntreprise'],
                        'idPaiement' => $validated['idPay'],
                        'idTypeFacture' => $validated['idTypeFacture'],
                        'idBankAcount' => $validated['idBankAcount'] ?? null,
                        'idTypeClient' => $validated['idTypeClient'],
                        'idContact' => $validated['idContact'] ?? null,
                    ]);

                if ($request->idTypeFacture == 1) {
                    DB::table('invoice_details')->where('idInvoice', $idInvoice)->delete();
                    //mampiditra invoice_details
                    $items = array_filter($request->items);

                    foreach ($items as $item) {
                        if (!$item) continue;
                        $itemTotalPrice = ($item['item_qty'] * $item['item_unit_price']);
                        $subTotal += $itemTotalPrice;

                        InvoiceDetail::create([
                            'idInvoice' => $idInvoice,
                            'idItems' => $item['idItems'],
                            'idProjet' => $item['idProjet'],
                            'item_qty' => $item['item_qty'],
                            'item_description' => $item['item_description'],
                            'item_unit_price' => $item['item_unit_price'],
                            'idUnite' => $item['idUnite'],
                            'item_total_price' => $itemTotalPrice,
                        ]);
                    }
                } elseif ($request->idTypeFacture == 2) {
                    DB::table('invoice_details_profo')->where('idInvoice', $idInvoice)->delete();

                    $items = array_filter($request->items);

                    foreach ($items as $item) {
                        if (!is_array($item)) continue;

                        if (isset($item['item_qty']) && is_array($item['item_qty'])) {
                            foreach ($item['item_qty'] as $i => $qty) {
                                $itemQty = (float) $qty;
                                $itemUnitPrice = (float) ($item['item_unit_price'][$i] ?? 0);

                                InvoiceDetailProfo::create([
                                    'idInvoice' => $idInvoice,
                                    'idItems' => $item['idItems'][$i] ?? null,
                                    'idModule' => $item['idModule'][$i] ?? null,
                                    'idUnite' => $item['idUnite'][$i] ?? null,
                                    'item_qty' => $itemQty,
                                    'item_description' => $item['item_description'][$i] ?? '',
                                    'item_unit_price' => $itemUnitPrice,
                                    'item_total_price' => $itemQty * $itemUnitPrice,
                                ]);
                            }
                        } elseif (isset($item['item_qty'], $item['item_unit_price'])) {
                            $itemQty = (float) $item['item_qty'];
                            $itemUnitPrice = (float) $item['item_unit_price'];
                            $itemTotalPrice = $itemQty * $itemUnitPrice;
                            $subTotal += $itemTotalPrice;

                            InvoiceDetailProfo::create([
                                'idInvoice' => $idInvoice,
                                'idItems' => $item['idItems'] ?? null,
                                'idModule' => $item['idModule'] ?? null,
                                'item_qty' => $itemQty,
                                'item_description' => $item['item_description'] ?? '',
                                'item_unit_price' => $itemUnitPrice,
                                'idUnite' => $item['idUnite'] ?? null,
                                'item_total_price' => $itemTotalPrice,
                            ]);
                        }
                    }
                } elseif ($request->idTypeFacture == 3) {
                    DB::table('invoice_details_acompte')->where('idInvoice', $idInvoice)->delete();
                    DB::table('invoice_acomptes')->where('idInvoice', $idInvoice)->delete();

                    DB::table('invoice_acomptes')->insert([
                        'idInvoice' => $idInvoice,
                        'idBC' => $validated['invoice_bc']
                    ]);

                    $item_qty = $request->item_qty;
                    $item_unit_price = $request->item_unit_price;
                    $itemTotalPrice = ($item_qty * $item_unit_price);
                    $subTotal += $itemTotalPrice;

                    DB::table('invoice_details_acompte')->insert([
                        'idInvoice' => $idInvoice,
                        'item_qty' => $item_qty,
                        'item_description' => $request->item_description,
                        'item_unit_price' => $item_unit_price,
                        'idUnite' => $request->idUnite,
                        'item_total_price' => $itemTotalPrice,
                    ]);
                } elseif ($request->idTypeFacture == 4) {
                    // Facture de solde
                    DB::table('invoice_details_solde')->where('idInvoice', $idInvoice)->delete();
                    DB::table('invoice_soldes')->where('idInvoice', $idInvoice)->delete();

                    // Récupérer les montants comme dans le store
                    $bc_amount = DB::table('bon_commandes')
                        ->where('idBC', $validated['invoice_bc'])
                        ->value('montant');

                    $acount_paid = DB::table('invoice_acomptes')
                        ->where('idBC', $validated['invoice_bc'])
                        ->join('invoices', 'invoice_acomptes.idInvoice', '=', 'invoices.idInvoice')
                        ->sum('invoices.invoice_total_amount');

                    DB::table('invoice_soldes')->insert([
                        'idInvoice' => $idInvoice,
                        'idBC' => $validated['invoice_bc'],
                        'bc_amount' => $bc_amount,
                        'acount_paid' => $acount_paid ?? 0,
                    ]);

                    $items = [];
                    if ($request->has('item_service') && is_array($request->item_service)) {
                        foreach ($request->item_service as $index => $service) {
                            $items[] = [
                                'item_service' => $service,
                                'item_qty' => $request->item_qty[$index] ?? 1,
                                'item_description' => $request->item_description[$index] ?? '',
                                'item_unit_price' => $request->item_unit_price[$index] ?? 0,
                                'idUnite' => $request->idUnite[$index] ?? 4,
                                'item_total_price' => $request->item_total_price[$index] ?? 0,
                            ];
                        }
                    }

                    $subTotal = 0;
                    foreach ($items as $item) {
                        $itemTotalPrice = $item['item_total_price'];
                        $subTotal += $itemTotalPrice;

                        DB::table('invoice_details_solde')->insert([
                            'idInvoice' => $idInvoice,
                            'item_service' => $item['item_service'],
                            'item_qty' => $item['item_qty'],
                            'item_description' => $item['item_description'],
                            'item_unit_price' => $item['item_unit_price'],
                            'idUnite' => $item['idUnite'],
                            'item_total_price' => $itemTotalPrice,
                        ]);
                    }
                }

                // Mise à jour du sous-total de la facture
                DB::table('invoices')
                    ->where('idInvoice', $idInvoice)
                    ->update(['invoice_sub_total' => $subTotal]);

                // Mise à jour des standards
                if (isset($validated['standards'])) {
                    DB::table('invoice_standards')
                        ->where('idInvoice', $idInvoice)
                        ->delete();

                    DB::table('invoice_standards')->insert([
                        'idInvoice' => $idInvoice
                    ]);
                }

                $redirectUrl =  $idInvoice;
            });

            return response()->json(['message' => 'Facture mise à jour avec succès!', 'idInvoice' => $redirectUrl], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $request->all(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }


    public function approve($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->invoice_status++;
        $invoice->save();

        return response()->json([
            'message' => 'OK'
        ]);
    }

    public function cancel($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->invoice_status = 9;
        $invoice->save();

        return response()->json([
            'message' => 'OK'
        ]);
    }

    public function convertir($id)
    {
        // DB::beginTransaction();

        try {
            $invoice = Invoice::findOrFail($id);
            // Mettre à jour le statut de la facture
            $invoice->invoice_status = 7; // Statut "converti"
            $invoice->save();

            return response()->json([
                'status' => 200,
                'message' => "Facture convertie et projet créé avec succès."
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => 'Erreur lors de la conversion : ' . $e->getMessage()
            ]);
        }
    }

    public function exportInvoicePdf($id)
    {
        $country = DB::table('customers as cst')
            ->select('cnt.name as country_name', 'cnt.code as country_code', 'cr.code as currency_code', 'cr.symbol as symbol', 'cnt.id_nif_name', 'nf.name as nif_name', 'nf.description as nif_description', 'cntf.id_stat_name', 'stn.name as stat_name')
            ->join('countriess as cnt', 'cst.id_country', 'cnt.id')
            ->join('currencies as cr', 'cnt.id_currency', 'cr.id')
            ->join('nif_names as nf', 'cnt.id_nif_name', 'nf.id')
            ->join('country_fulls as cntf', 'cnt.id', 'cntf.id')
            ->join('stat_names as stn', 'cntf.id_stat_name', 'stn.id');

        $invoice = DB::table('invoices')
            ->join('mode_paiements', 'invoices.idPaiement', '=', 'mode_paiements.idPaiement')
            ->join('pm_types', 'mode_paiements.idTypePm', '=', 'pm_types.idTypePm')
            ->join('customers', 'invoices.idCustomer', '=', 'customers.idCustomer')
            ->leftJoin('companies', 'invoices.idCompany', '=', 'companies.id')
            ->join('type_factures', 'invoices.idTypeFacture', '=', 'type_factures.idTypeFacture')
            ->join('ville_codeds as vc', 'customers.idVilleCoded', 'vc.id')
            ->leftJoin('bankacounts', 'invoices.idBankAcount', 'bankacounts.id')
            ->leftJoin('ville_codeds', 'bankacounts.ba_idPostal', 'ville_codeds.id')
            ->select('invoices.*', 'pm_types.*', 'customers.customerName', 'customers.customerPhone', 'customers.siteWeb', 'customers.customerEmail', 'customers.logo', 'customers.customer_addr_lot', 'customers.customer_addr_quartier', 'companies.*', 'type_factures.*', 'vc.vi_code_postal as customer_addr_code_postal', 'bankacounts.*', 'ville_codeds.*')
            ->where('idInvoice', $id)
            ->first();

        $typeCustomer = DB::table('customers')
            ->where('idCustomer', $invoice->idEntreprise)
            ->value('idTypeCustomer');

        if ($invoice->idTypeClient == 1) {
            //entreprise
            $typeCustomer = DB::table('customers')
                ->where('idCustomer', $invoice->idEntreprise)
                ->value('idTypeCustomer');

            if ($typeCustomer == 2) {
                $entreprise = DB::table('v_collaboration_cfp_etps')
                    ->select('idEtp', 'etp_name', 'etp_email', 'etp_nif', 'etp_ville', 'etp_stat', 'etp_rcs', 'etp_addr_lot', 'etp_addr_quartier', 'etp_addr_code_postal', 'etp_phone')
                    ->where('idEtp', $invoice->idEntreprise)
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
                    ->where('idCfp', $invoice->idEntreprise)
                    ->first();
            }
        } elseif ($invoice->idTypeClient == 2) {
            //particulier
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
                ->where('p.idParticulier', $invoice->idEntreprise)
                ->first();
        }

        if ($invoice->idTypeFacture == 1) {
            // Récupérer PROJET if STANDARD
            $invoiceDetails = DB::table('invoice_details')
                ->join('v_projet_cfps', 'invoice_details.idProjet', '=', 'v_projet_cfps.idProjet')
                ->join('unites', 'invoice_details.idUnite', '=', 'unites.idUnite')
                ->select('invoice_details.*', 'unites.unite_name as unit_name', 'v_projet_cfps.module_name as module_name')
                ->where('idInvoice', $id)
                ->where('idItems', 0)
                ->get();
            // Récupérer les détails de la facture avec les noms des unités
            $invoiceDetailsOther = DB::table('invoice_details')
                ->join('unites', 'invoice_details.idUnite', '=', 'unites.idUnite')
                ->join('frais', 'invoice_details.idItems', '=', 'frais.idFrais')
                ->select('invoice_details.*', 'unites.unite_name as unit_name', 'frais.*')
                ->where('idInvoice', $id)
                ->orderBy('idItems', 'asc')
                ->get();
        } elseif ($invoice->idTypeFacture == 2) {
            // Récupérer COURS if PROFORMA
            $invoiceDetails = DB::table('invoice_details_profo')
                ->join('v_module_cfps', 'invoice_details_profo.idModule', '=', 'v_module_cfps.idModule')
                ->join('unites', 'invoice_details_profo.idUnite', '=', 'unites.idUnite')
                ->select('invoice_details_profo.*', 'unites.unite_name as unit_name', 'v_module_cfps.moduleName as module_name')
                ->where('idInvoice', $id)
                ->where('idItems', 0)
                ->get();

            $invoiceDetailsOther = DB::table('invoice_details_profo')
                ->join('unites', 'invoice_details_profo.idUnite', '=', 'unites.idUnite')
                ->join('frais', 'invoice_details_profo.idItems', '=', 'frais.idFrais')
                ->select('invoice_details_profo.*', 'unites.unite_name as unit_name', 'frais.*')
                ->where('idInvoice', $id)
                ->orderBy('idItems', 'asc')
                ->get();
        } elseif ($invoice->idTypeFacture == 3) {
            // facture d'acompte
            $invoiceDetails = DB::table('invoice_details_acompte')
                ->join('unites', 'invoice_details_acompte.idUnite', '=', 'unites.idUnite')
                ->select('invoice_details_acompte.*', 'unites.unite_name as unit_name')
                ->where('idInvoice', $id)
                ->get();

            $invoiceDetailsOther = collect([]);

            // Récupérer les informations de l'acompte avec le numéro de BC
            $acompteInfo = DB::table('invoice_acomptes as ia')
                ->join('bon_commandes as bc', 'ia.idBC', '=', 'bc.idBC')
                ->select(
                    'ia.*',
                    'bc.numero',
                    'bc.date',
                    'bc.montant'
                )
                ->where('ia.idInvoice', $id)
                ->first();
        } elseif ($invoice->idTypeFacture == 4) {
            // details de la facture de solde
            $invoiceDetails = DB::table('invoice_details_solde as ids')
                ->leftJoin('invoice_deleted as id', 'ids.idInvoice', '=', 'id.idInvoice')
                ->join('unites', 'ids.idUnite', '=', 'unites.idUnite')
                ->select('ids.*', 'unites.unite_name as unit_name')
                ->where('ids.idInvoice', $id)
                ->whereNull('id.idInvoice')
                ->get();

            $invoiceDetailsOther = collect([]);

            // Récupérer les informations du solde
            $soldeInfo = DB::table('invoice_soldes as is')
                ->join('bon_commandes as bc', 'is.idBC', '=', 'bc.idBC')
                ->select('bc.numero')
                ->where('is.idInvoice', $id)
                ->first();
        }

        $pdf = Pdf::loadView('facture.preview1', [
            'invoice' => $invoice,
            'entreprise' => $entreprise,
            'invoiceDetails' => $invoiceDetails,
            'invoiceDetailsOther' => $invoiceDetailsOther,
            'setting' => $country->first(),
            'acompteInfo' => $acompteInfo ?? null,
            'soldeInfo' => $soldeInfo ?? null
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream($invoice->invoice_number . '.pdf');
    }
    public function sendInvoiceEmail($id)
    {
        $country = DB::table('customers as cst')
            ->select('cnt.name as country_name', 'cnt.code as country_code', 'cr.code as currency_code', 'cr.symbol as symbol', 'cr.unit as currency_unit', 'cnt.id_nif_name', 'nf.name as nif_name', 'nf.description as nif_description', 'cntf.id_stat_name', 'stn.name as stat_name')
            ->join('countriess as cnt', 'cst.id_country', 'cnt.id')
            ->join('currencies as cr', 'cnt.id_currency', 'cr.id')
            ->join('nif_names as nf', 'cnt.id_nif_name', 'nf.id')
            ->join('country_fulls as cntf', 'cnt.id', 'cntf.id')
            ->join('stat_names as stn', 'cntf.id_stat_name', 'stn.id');

        $customer = DB::table('v_detail_customers')
            ->select('idCustomer', 'initialName', 'customerName', 'customer_addr_quartier', 'customer_addr_rue', 'customer_addr_lot', 'customer_addr_code_postal', 'nif', 'stat', 'rcs', 'customerPhone', 'customerEmail', 'siteWeb', 'description', 'logo', 'customer_slogan')
            ->where('idCustomer', Customer::idCustomer())
            ->first();

        $invoice = DB::table('invoices')
            ->join('mode_paiements', 'invoices.idPaiement', '=', 'mode_paiements.idPaiement')
            ->join('pm_types', 'mode_paiements.idTypePm', '=', 'pm_types.idTypePm')
            ->join('customers', 'invoices.idCustomer', '=', 'customers.idCustomer')
            ->leftJoin('companies', 'invoices.idCompany', '=', 'companies.id')
            ->join('type_factures', 'invoices.idTypeFacture', '=', 'type_factures.idTypeFacture')
            ->join('ville_codeds as vc', 'customers.idVilleCoded', 'vc.id')
            ->leftJoin('bankacounts', 'invoices.idBankAcount', 'bankacounts.id')
            ->leftJoin('ville_codeds', 'bankacounts.ba_idPostal', 'ville_codeds.id')
            ->select('invoices.*', 'pm_types.*', 'customers.logo', 'customers.customer_addr_lot', 'customers.customer_addr_quartier', 'companies.*', 'type_factures.*', 'vc.vi_code_postal as customer_addr_code_postal', 'bankacounts.*', 'ville_codeds.*')
            ->where('idInvoice', $id)
            ->first();

        $typeCustomer = DB::table('customers')
            ->where('idCustomer', $invoice->idEntreprise)
            ->value('idTypeCustomer');

        if ($invoice->idTypeClient == 1) {
            //entreprise
            $typeCustomer = DB::table('customers')
                ->where('idCustomer', $invoice->idEntreprise)
                ->value('idTypeCustomer');

            if ($typeCustomer == 2) {
                $entreprise = DB::table('v_collaboration_cfp_etps')
                    ->select('etp_name', 'etp_email', 'etp_nif', 'etp_stat', 'etp_addr_quartier', 'etp_addr_code_postal', 'etp_addr_lot', 'idEtp', 'etp_ville')
                    ->where('idEtp', $invoice->idEntreprise)
                    ->first();
            } elseif ($typeCustomer == 1) {
                $entreprise = DB::table('v_cfp_all')
                    ->select(
                        'customerName as etp_name',
                        'nif as etp_nif',
                        'stat as etp_stat',
                        'customer_addr_quartier as etp_addr_quartier',
                        'customer_ville as etp_ville',
                        'customer_addr_code_postal as etp_addr_code_postal',
                        'customer_addr_lot as etp_addr_lot',
                        'idCfp as idEtp',
                        'customerEmail as etp_email'
                    )
                    ->where('idCfp', $invoice->idEntreprise)
                    ->first();
            }
        } elseif ($invoice->idTypeClient == 2) {
            //particulier
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
                ->where('p.idParticulier', $invoice->idEntreprise)
                ->first();
        }

        if ($invoice->idTypeFacture == 1) {
            // Récupérer PROJET if STANDARD
            $invoiceDetails = DB::table('invoice_details')
                ->join('v_projet_cfps', 'invoice_details.idProjet', '=', 'v_projet_cfps.idProjet')
                ->join('unites', 'invoice_details.idUnite', '=', 'unites.idUnite')
                ->select('invoice_details.*', 'unites.unite_name as unit_name', 'v_projet_cfps.module_name as module_name')
                ->where('idInvoice', $id)
                ->where('idItems', 0)
                ->get();
            // Récupérer les détails de la facture avec les noms des unités
            $invoiceDetailsOther = DB::table('invoice_details')
                ->join('unites', 'invoice_details.idUnite', '=', 'unites.idUnite')
                ->join('frais', 'invoice_details.idItems', '=', 'frais.idFrais')
                ->select('invoice_details.*', 'unites.unite_name as unit_name', 'frais.*')
                ->where('idInvoice', $id)
                ->orderBy('idItems', 'asc')
                ->get();
        } elseif ($invoice->idTypeFacture == 2) {
            // Récupérer COURS if PROFORMA
            $invoiceDetails = DB::table('invoice_details_profo')
                ->join('v_module_cfps', 'invoice_details_profo.idModule', '=', 'v_module_cfps.idModule')
                ->join('unites', 'invoice_details_profo.idUnite', '=', 'unites.idUnite')
                ->select('invoice_details_profo.*', 'unites.unite_name as unit_name', 'v_module_cfps.moduleName as module_name')
                ->where('idInvoice', $id)
                ->where('idItems', 0)
                ->get();

            $invoiceDetailsOther = DB::table('invoice_details_profo')
                ->join('unites', 'invoice_details_profo.idUnite', '=', 'unites.idUnite')
                ->join('frais', 'invoice_details_profo.idItems', '=', 'frais.idFrais')
                ->select('invoice_details_profo.*', 'unites.unite_name as unit_name', 'frais.*')
                ->where('idInvoice', $id)
                ->orderBy('idItems', 'asc')
                ->get();
        } elseif ($invoice->idTypeFacture == 3) {
            // facture d'acompte
            $invoiceDetails = DB::table('invoice_details_acompte')
                ->join('unites', 'invoice_details_acompte.idUnite', '=', 'unites.idUnite')
                ->select('invoice_details_acompte.*', 'unites.unite_name as unit_name')
                ->where('idInvoice', $id)
                ->get();

            $invoiceDetailsOther = collect([]);

            // Récupérer les informations de l'acompte avec le numéro de BC
            $acompteInfo = DB::table('invoice_acomptes as ia')
                ->join('bon_commandes as bc', 'ia.idBC', '=', 'bc.idBC')
                ->select(
                    'ia.*',
                    'bc.numero',
                    'bc.date',
                    'bc.montant'
                )
                ->where('ia.idInvoice', $id)
                ->first();
        } elseif ($invoice->idTypeFacture == 4) {
            // details de la facture de solde
            $invoiceDetails = DB::table('invoice_details_solde as ids')
                ->leftJoin('invoice_deleted as id', 'ids.idInvoice', '=', 'id.idInvoice')
                ->join('unites', 'ids.idUnite', '=', 'unites.idUnite')
                ->select('ids.*', 'unites.unite_name as unit_name')
                ->where('ids.idInvoice', $id)
                ->whereNull('id.idInvoice')
                ->get();

            $invoiceDetailsOther = collect([]);

            // Récupérer les informations du solde
            $soldeInfo = DB::table('invoice_soldes as is')
                ->join('bon_commandes as bc', 'is.idBC', '=', 'bc.idBC')
                ->select('bc.numero')
                ->where('is.idInvoice', $id)
                ->first();
        }

        try {
            // Générer le PDF pour l'email
            $pdf = Pdf::loadView('facture.preview1', [
                'customer' => $customer,
                'invoice' => $invoice,
                'entreprise' => $entreprise,
                'invoiceDetailsOther' => $invoiceDetailsOther,
                'invoiceDetails' => $invoiceDetails,
                'setting' => $country->first(),
                'acompteInfo' => $acompteInfo ?? null,
                'soldeInfo' => $soldeInfo ?? null
            ])->output();

            // Envoyer l'email avec Brevo
            if (!str_ends_with($entreprise->etp_email, 'forma-fusion.com')) {
                $brevo = new BrevoService();

                $subject = 'Votre facture d\'acompte';
                if ($invoice->idTypeFacture == 1) {
                    $subject = 'Votre facture';
                } elseif ($invoice->idTypeFacture == 2) {
                    $subject = 'Votre facture proforma';
                } else {
                    $subject = 'Votre facture d\'acompte';
                }

                $htmlContent = (new InvoiceMail($customer, $invoice, $entreprise, $invoiceDetailsOther, $invoiceDetails, $pdf))->render();

                $brevo->sendEmail($entreprise->etp_email, $subject, $htmlContent);
            }

            $invoices = Invoice::findOrFail($id);
            $invoices->invoice_status++;
            $invoices->save();

            return response()->json([
                'status' => 200,
                'message' => 'La Facture a été envoyé avec succès'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => "Une erreur s'est produite " . $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        DB::table('invoice_deleted')->insert([
            'idInvoice' => $id,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Facture supprimée avec succès',
            'deletedInvoiceId' => $id
        ]);
    }

    public function restore($id)
    {
        $deletedInvoice = InvoiceDeleted::where('idInvoice', $id)->first();
        if ($deletedInvoice) {
            $deletedInvoice->delete();

            return response()->json([
                'status' => 200,
                'message' => 'La facture a été restaurée avec succès.'
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'La facture n\'est pas marquée comme supprimée.'
            ]);
        }
    }

    public function getTresor()
    {
        $colors = [];
        $countInvoices = [];
        $mois = [
            1 => 'Janvier',
            2 => 'Février',
            3 => 'Mars',
            4 => 'Avril',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juillet',
            8 => 'Août',
            9 => 'Septembre',
            10 => 'Octobre',
            11 => 'Novembre',
            12 => 'Décembre'
        ];

        $invoiceStatus = DB::table('invoice_status')
            ->select('idInvoiceStatus', 'invoice_status_name')
            //->whereIn('idInvoiceStatus', [2, 3, 5, 6, 7, 8, 9])
            ->get();

        $countInvoiceStatus = count($invoiceStatus);

        foreach ($invoiceStatus as $key => $value) {
            $colors[$key] = $this->getColorHexa(++$key);
            $invoices = Invoice::with(['entreprise', 'status'])
                ->doesntHave('deletedInvoices')
                ->where('invoice_status', $key)
                ->orderBy('idInvoice', 'desc')
                ->get();
            $countInvoices[$key] = count($invoices);
        }

        return response()->json([
            'invoiceStatus' => $invoiceStatus,
            'colors' => $colors,
            'countInvoices' => $countInvoices,
            'countInvoiceStatus' => $countInvoiceStatus,
            'mois' => $mois
        ]);
    }

    private function getEntreprise($idEtp)
    {
        $entreprise = DB::table('v_collaboration_cfp_etps')
            ->select('etp_name', 'etp_nif', 'etp_stat', 'etp_addr_quartier', 'etp_addr_code_postal', 'etp_addr_lot', 'etp_email', 'idEtp', 'idCfp')
            ->where('idEtp', $idEtp)
            ->first();
        return $entreprise;
    }

    private function getColorHexa($idInvoice)
    {
        $color = "";
        if ($idInvoice == 1) { //Brouillon
            $color = '#808080';
        } else  if ($idInvoice == 2) { //Non Envoyé
            $color = '#f472b6';
        } else  if ($idInvoice == 3) { //Envoyé
            $color = '#06b6d4';
        } else  if ($idInvoice == 4) { //Payé
            $color = '#22d3ee';
        } else  if ($idInvoice == 5) { //Partiel
            $color = '#facc15';
        } else  if ($idInvoice == 6) { //Impayé
            $color = '#ef4444';
        } else  if ($idInvoice == 7) { //Convertis
            $color = '#0891b2';
        } else  if ($idInvoice == 8) { //Expiré
            $color = '#dc2626';
        } else  if ($idInvoice == 9) { //Annulé
            $color = '#f9a08d';
        }
        return $color;
    }

    private function getStatusInvoice($idInvoice)
    {
        $status = DB::table('invoice_status')
            ->select('invoice_status_name')
            ->where('idInvoiceStatus', $idInvoice)
            ->first();
        return $status;
    }

    public function getEvents()
    {
        $factures = [];
        $factures = Invoice::with(['entreprise', 'status'])
            ->doesntHave('deletedInvoices')
            ->where('idCustomer', Customer::idCustomer())
            ->whereIn('invoice_status', [2, 3, 5, 6, 7, 8, 9])
            ->orderBy('idInvoice', 'desc')
            ->get();

        if (count($factures) > 0) {
            foreach ($factures as $facture) {

                $events[] =  [

                    'idFacture' => $facture->idInvoice,

                    'idInvoiceStatus' => $facture->invoice_status,

                    'idNumber' => $facture->invoice_number,

                    'idEntreprise' => $facture->idEntreprise,

                    'nameEtp' => $this->getEntreprise($facture->idEntreprise)->etp_name,

                    'end' => $facture->invoice_date_pm,

                    'start' => $facture->invoice_date,

                    'status' => $this->getStatusInvoice($facture->invoice_status)->invoice_status_name,

                    'total' => $facture->invoice_total_amount,

                ];
            }
        } else {

            return response()->json(['pas de donnee']);
        }

        //dd($events);
        return response()->json(['factures' => $events]);
    }

    public function changeStatus($idInvoice, $status)
    {
        $invoice = Invoice::findOrFail($idInvoice);
        $invoice->invoice_status = $status;
        $invoice->save();

        return response()->json([
            'success' => true,
            'message' => 'mise à jour status avec succès!'
        ]);
    }
}
