<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BonCommandeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $currentPage = $request->get('page', 1);

        $query = $this->getBaseQuery();

        $this->applyFilters($query, $request);

        $bonCommandes = $query->paginate($perPage, ['*'], 'page', $currentPage);

        $bonCommandes->getCollection()->transform(function ($commande) {

            $status = $this->getStatusOrder($commande->idBC, $commande->nb_projets);

            return [
                'idBC'             => $commande->idBC,
                'idDevis'        => $commande->idDevis,
                'status'         => $status,
                'numero_devis'   => $commande->numero_devis,
                'numero_bc'      => $commande->numero_bc,
                'montant_devis'  => $commande->montant_devis,
                'montant_bc'     => $commande->montant_bc,
                'date_devis'     => $commande->date_devis,
                'date_bc'        => $commande->date_bc,
                'date_debut'     => $commande->date_debut,
                'date_fin'       => $commande->date_fin,
                'modalite'       => $commande->modalite,
                'idEtp'          => $commande->idEtp,
                'etp_name'       => $commande->etp_name,
                'etp_email'      => $commande->etp_email,
                'contact_name'   => $commande->contact_name,
                'contact_mail'   => $commande->contact_mail,
                'contact_phone'  => $commande->contact_phone,
                'idCfp'          => $commande->idCfp,
                'idDocument'     => $commande->idDocument,
                'nb_projets'     => $commande->nb_projets,
                'details_devis'  => $commande->details_devis,
            ];
        });

        return response()->json([
            'total_devis'  => $this->getTotalDevis($request),
            'total_bc'     => $this->getTotalBc($request),
            'total_new_bc' => $this->getTotalNewBc($request),
            'bon_commandes' => $bonCommandes->items(),
            'current_page' => $bonCommandes->currentPage(),
            'last_page'    => $bonCommandes->lastPage(),
            'per_page'     => $bonCommandes->perPage(),
            'total'        => $bonCommandes->total(),
            'from'         => $bonCommandes->firstItem(),
            'to'           => $bonCommandes->lastItem(),
        ]);
    }

    private function getBaseQuery()
    {
        return DB::table('bon_commandes as bc')
            ->select([
                'bc.idBC',
                'bc.idDevis',
                'i.invoice_number as numero_devis',
                'bc.numero as numero_bc',
                'i.invoice_total_amount as montant_devis',
                'bc.montant as montant_bc',
                'i.invoice_date as date_devis',
                'bc.date as date_bc',
                'bc.date_debut as date_debut',
                'bc.date_fin as date_fin',
                'bc.modalite',
                'cu.idCustomer as idEtp',
                'cu.customerName as etp_name',
                'cu.customerEmail as etp_email',
                'c.contact_name',
                'c.contact_mail',
                'c.contact_phone',
                'bc.idCfp',
                'bd.idDocument',
                DB::raw('COUNT(DISTINCT p.idProjet) as nb_projets'),
                // Détails de la facture proforma groupés
                DB::raw('GROUP_CONCAT(CONCAT(idp.item_description, " - ", idp.item_qty, " - ", idp.item_unit_price, " - ", idp.item_total_price) SEPARATOR " | ") as details_devis')
            ])
            ->join('invoices as i', 'bc.idDevis', '=', 'i.idInvoice')
            ->join('customers as cu', 'i.idEntreprise', '=', 'cu.idCustomer')
            ->leftJoin('bc_contacts as c', 'bc.idContact', '=', 'c.idContact')
            ->join('invoice_details_profo as idp', 'bc.idDevis', '=', 'idp.idInvoice')
            ->leftJoin('bc_documents as bd', 'bc.idBC', '=', 'bd.idBC')
            ->leftJoin('projets as p', 'bc.idBC', '=', 'p.idBc')
            ->where('bc.idCfp', Customer::idCustomer())
            ->groupBy([
                'bc.idBC'
            ])
            ->orderBy('bc.date', 'desc');
    }

    public function getProjectIds($orderId)
    {
        return DB::table('projets')
            ->where('idBc', $orderId)
            ->pluck('idProjet')
            ->toArray();
    }

    private function getProjectStatuses($projectIds)
    {
        return DB::table('v_projects')
            ->whereIn('idProjet', $projectIds)
            ->pluck('project_status')
            ->toArray();
    }

    private function resolveProjectStatus(array $statuses)
    {
        $statuses = array_unique($statuses);

        if (empty($statuses)) return "Nouveau";

        if (count($statuses) === 1 && $statuses[0] === 'Terminé') {
            return "Terminé";
        }

        if (in_array('En préparation', $statuses) && in_array('Terminé', $statuses)) {
            return "En cours";
        }

        if (in_array('En cours', $statuses) && in_array('Terminé', $statuses)) {
            return "En cours";
        }

        if (in_array('En préparation', $statuses)) return "En préparation";
        if (in_array('En cours', $statuses)) return "En cours";

        return $statuses[0];
    }

    private function getStatusOrder($orderId, $nbProject)
    {
        if ($nbProject > 0) {

            $projectIds = $this->getProjectIds($orderId);

            $invoiceStatus = $this->getInvoiceStatusByProject($projectIds)->toArray();

            if (in_array("Partiel", $invoiceStatus)) {
                return "Partiel";
            }

            if (count($invoiceStatus) > 0) {
                if (count(array_unique($invoiceStatus)) === 1 && $invoiceStatus[0] === "Envoyé") {
                    return "Non payé";
                }

                if (in_array("Payé", $invoiceStatus)) {
                    return "Payé";
                }
            }
            $projectStatuses = $this->getProjectStatuses($projectIds);
            return $this->resolveProjectStatus($projectStatuses);
        }

        return "Nouveau";
    }


    private function getInvoiceStatusByProject($projectIds)
    {
        $invoice = DB::table('invoice_details')
            ->join('invoices', 'invoices.idInvoice', '=', 'invoice_details.idInvoice')
            ->join('invoice_status', 'invoice_status.idInvoiceStatus', '=', 'invoices.invoice_status')
            ->whereIn('invoice_details.idProjet', $projectIds)
            ->pluck('invoice_status.invoice_status_name');

        return $invoice;
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->has('numero_bc') && !empty($request->numero_bc)) {
            $query->where('bc.numero', 'like', '%' . $request->numero_bc . '%');
        }

        if ($request->has('etp_name') && !empty($request->etp_name)) {
            $query->where('cu.customerName', 'like', '%' . $request->etp_name . '%');
        }

        if ($request->has('date_bc_fin') && !empty($request->date_bc_fin)) {
            $query->where('bc.date', $request->date_bc_fin);
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('bc.idStatus', $request->status);
        }
    }

    private const QUERY_TYPES = [
        'devis' => 'COALESCE(SUM(i.invoice_total_amount), 0) as total_devis',
        'bc' => 'COALESCE(SUM(bc.montant), 0) as total_bc',
        'new_bc' => 'COALESCE(SUM(bc.montant), 0) as total_new_bc'
    ];

    private function getTotalData(Request $request, string $queryType, ?string $additionalWhere = null)
    {
        $query = DB::table('bon_commandes as bc')
            ->select(DB::raw(self::QUERY_TYPES[$queryType]))
            ->join('invoices as i', 'bc.idDevis', '=', 'i.idInvoice')
            ->join('customers as cu', 'i.idEntreprise', '=', 'cu.idCustomer')
            ->join('bc_status as bcs', 'bc.idStatus', '=', 'bcs.idStatus')
            ->where('bc.idCfp', Customer::idCustomer());

        if ($additionalWhere) {
            $query->whereRaw($additionalWhere);
        }

        $this->applyFilters($query, $request);

        $columnName = match ($queryType) {
            'count' => 'total',
            'devis' => 'total_devis',
            'bc', 'new_bc' => "total_{$queryType}",
            default => 'total'
        };

        return $query->value($columnName);
    }

    private function getTotalDevis(Request $request)
    {
        return $this->getTotalData($request, 'devis');
    }

    private function getTotalBc(Request $request)
    {
        return $this->getTotalData($request, 'bc');
    }

    private function getTotalNewBc(Request $request)
    {
        return $this->getTotalData($request, 'new_bc', 'bc.idStatus = 1');
    }

    public function getBonCommandByEtp($etpId)
    {
        $bonCommandes = DB::table('v_bon_commande')->where('idCfp', Customer::idCustomer())->where('idEtp', $etpId)->get();
        if ($bonCommandes->isEmpty()) {
            return response()->json(['message' => 'Aucun Bon de commande'], 204);
        }
        return response()->json([
            'purchase_orders' => $bonCommandes,
        ], 200);
    }

    public function getBcByIdEtp($idEtp)
    {
        $bonCommandes = DB::table('v_bon_commande')
            ->select('idBC', 'numero_bc', 'montant_bc', 'date_bc')
            ->where('idCfp', Customer::idCustomer())->where('idEtp', $idEtp)->get();
        if ($bonCommandes->isEmpty()) {
            return response()->json(['message' => 'Aucun Bon de commande'], 204);
        }
        return response()->json($bonCommandes);
    }

    public function show($id)
    {
        try {
            $bonCommande = DB::table('bon_commandes as bc')
                ->select([
                    'bc.idBC',
                    'bc.numero',
                    'bc.montant',
                    'bc.date',
                    'bc.date_debut',
                    'bc.date_fin',
                    'bc.modalite',
                    'bc.idDevis',
                    'bc.idContact',
                    'bc.idStatus',
                    'bcs.status_name',
                    'bcs.status_color',
                    'i.invoice_number as numero_devis',
                    'i.invoice_total_amount as montant_devis',
                    'i.invoice_date as date_devis',
                    'cu.idCustomer as idEtp',
                    'cu.customerName as etp_name',
                    'cu.customerEmail as etp_email',
                    'cu.customerPhone as etp_phone',
                    'c.contact_name',
                    'c.contact_mail',
                    'c.contact_phone',
                    'bc.idCfp',
                    DB::raw('COUNT(DISTINCT p.idProjet) as nb_projets'),
                    // Détails de la facture proforma
                    DB::raw('GROUP_CONCAT(CONCAT(idp.item_description, " - ", idp.item_qty, " unités - ", idp.item_unit_price, " Ar - ", idp.item_total_price, " Ar") SEPARATOR " | ") as details_devis')
                ])
                ->join('invoices as i', 'bc.idDevis', '=', 'i.idInvoice')
                ->join('customers as cu', 'i.idEntreprise', '=', 'cu.idCustomer')
                ->join('bc_status as bcs', 'bc.idStatus', '=', 'bcs.idStatus')
                ->leftJoin('bc_contacts as c', 'bc.idContact', '=', 'c.idContact')
                ->leftJoin('invoice_details_profo as idp', 'bc.idDevis', '=', 'idp.idInvoice')
                ->leftJoin('projets as p', 'bc.idBC', '=', 'p.idBc')
                ->where('bc.idBC', $id)
                ->where('bc.idCfp', Customer::idCustomer())
                ->groupBy([
                    'bc.idBC'
                ])
                ->first();

            if (!$bonCommande) {
                return response()->json(['message' => 'Bon de commande non trouvé'], 404);
            }

            // Récupérer les documents associés
            $documents = DB::table('bc_documents')
                ->where('idBC', $id)
                ->select([
                    'idDocument',
                    'document_name',
                    'file_name',
                    'file_path',
                    'file_type',
                    'created_at',
                    'updated_at'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            $projets = DB::table('v_projet_cfps as p')
                ->select([
                    'p.idProjet',
                    'p.module_name',
                    'p.dateDebut',
                    'p.dateFin',
                    'p.li_name',
                    'p.etp_name',
                    'p.project_status'
                ])
                ->where('p.idBc', $id)
                ->orderBy('p.dateFin', 'desc')
                ->get();

            return response()->json([
                'bon_commande' => $bonCommande,
                'documents' => $documents,
                'projets' => $projets
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du bon de commande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'numero' => 'required|string|max:50',
                'montant' => 'nullable|numeric',
                'date' => 'nullable|date',
                'date_debut' => 'nullable|date',
                'date_fin' => 'nullable|date',
                'idDevis' => 'nullable|integer',
                'idContact' => 'nullable|integer',
                'modalite' => 'nullable|integer|min:0|max:999',
                // Pour ajout contact direct
                'contact_name' => 'nullable|string|max:100',
                'contact_mail' => 'nullable|max:100',
                'contact_phone' => 'nullable|string|max:20',
                'idEtp' => 'nullable'
            ]);

            $data['idCfp'] = Customer::idCustomer();
            $data['idStatus'] = 1;

            // Si pas d'idContact mais infos contact fournies, on crée le contact
            if (empty($data['idContact']) && !empty($data['contact_name'])) {
                $contactData = [
                    'contact_name' => $data['contact_name'],
                    'contact_mail' => $data['contact_mail'] ?? null,
                    'contact_phone' => $data['contact_phone'] ?? null,
                ];
                $data['idContact'] = DB::table('bc_contacts')->insertGetId($contactData);
            }

            // Nettoyage des champs inutiles pour bon_commandes
            unset($data['contact_name'], $data['contact_mail'], $data['contact_phone']);

            $id = DB::table('bon_commandes')->insertGetId($data);

            return response()->json(['idBC' => $id] + $data, 201);
        } catch (ValidationException $e) {
            // Erreurs de validation
            return response()->json([
                'message' => 'Données invalides.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Erreur générale (SQL, logique, etc.)
            return response()->json([
                'message' => 'Erreur serveur lors de l\'enregistrement.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'numero' => 'sometimes|required|string|max:50',
                'montant' => 'nullable|numeric',
                'date' => 'nullable|date',
                'date_debut' => 'nullable|date',
                'date_fin' => 'nullable|date',
                'idDevis' => 'sometimes|required|integer',
                'idContact' => 'nullable|integer',
                'modalite' => 'nullable|integer|min:0|max:999',
                // Pour ajout/modification contact direct
                'contact_name' => 'nullable|string|max:100',
                'contact_mail' => 'nullable|email|max:100',
                'contact_phone' => 'nullable|string|max:20',
            ]);

            $data['idCfp'] = Customer::idCustomer();

            // Récupérer le bon de commande existant
            $bonCommande = DB::table('bon_commandes')->where('idBC', $id)->first();

            if (!$bonCommande) {
                return response()->json(['message' => 'Bon de commande non trouvé'], 404);
            }

            // Gestion du contact
            $idContact = $bonCommande->idContact;

            if (!empty($data['contact_name'])) {
                // Si un contact existe déjà, on le met à jour
                if ($idContact) {
                    DB::table('bc_contacts')
                        ->where('idContact', $idContact)
                        ->update([
                            'contact_name' => $data['contact_name'],
                            'contact_mail' => $data['contact_mail'] ?? null,
                            'contact_phone' => $data['contact_phone'] ?? null,
                        ]);
                } else {
                    // Sinon on crée un nouveau contact
                    $idContact = DB::table('bc_contacts')->insertGetId([
                        'contact_name' => $data['contact_name'],
                        'contact_mail' => $data['contact_mail'] ?? null,
                        'contact_phone' => $data['contact_phone'] ?? null,
                    ]);
                }
                $data['idContact'] = $idContact;
            }

            // Nettoyage des champs inutiles pour bon_commandes
            unset($data['contact_name'], $data['contact_mail'], $data['contact_phone']);

            $affected = DB::table('bon_commandes')->where('idBC', $id)->update($data);

            if ($affected === 0) {
                return response()->json(['message' => 'Aucune modification effectuée'], 200);
            }

            return response()->json(['message' => 'Bon de commande mis à jour', 'idBC' => $id]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Données invalides.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur serveur lors de la mise à jour.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $deleted = DB::table('bon_commandes')->where('idBC', $id)->delete();

        if ($deleted === 0) {
            return response()->json(['message' => 'Bon de commande non trouvé'], 204);
        }

        return response()->json(['message' => 'Bon de commande supprimé']);
    }

    public function getAllEtpDevis()
    {
        $allEtpDevis = $this->devisQuery()
            ->select('etp.idEtp', 'etp.etp_name')
            ->groupBy('etp.idEtp', 'etp.etp_name')
            ->orderBy('etp.etp_name', 'asc')
            ->get();

        return response()->json($allEtpDevis);
    }

    public function getDevisByEtp(Request $request)
    {
        $etpIds = $request->input('etpIds', []);
        if (!is_array($etpIds)) {
            $etpIds = [$etpIds];
        }

        $allDevis = $this->devisQuery()
            ->whereIn('etp.idEtp', $etpIds)
            ->select(
                'i.idInvoice as idDevis',
                'i.invoice_number',
                'i.invoice_date',
                'i.invoice_total_amount as invoice_amount',
                'etp.etp_name',
                DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(idp.item_description SEPARATOR "; "), "; ", 1) as devis_description')
            )
            ->join('invoice_details_profo as idp', 'i.idInvoice', '=', 'idp.idInvoice')
            ->groupBy('i.idInvoice')
            ->orderBy('i.idInvoice', 'desc')
            ->get();

        return response()->json($allDevis, 200);
    }

    public function getPurchaseOrderByEtp(Request $request)
    {
        $etpIds = $request->input('etpId', []);
        if (!is_array($etpIds)) {
            $etpIds = [$etpIds];
        }

        $purchaseOrders = DB::table('bon_commandes as BC')
            ->leftJoin('invoices as I', 'BC.idDevis', 'I.idInvoice')
            ->select('BC.numero as label', 'BC.idBc as id')
            ->where('idCfp', Customer::idCustomer())
            ->where(function ($query) use ($etpIds) {
                $query->whereIn('BC.idEtp', $etpIds)
                    ->orWhereIn('I.idEntreprise', $etpIds);
            })
            ->groupBy('BC.idBc')
            ->get();

        return response()->json($purchaseOrders, 200);
    }


    private function devisQuery()
    {
        return DB::table('invoices as i')
            ->leftJoin('invoice_deleted as d', 'i.idInvoice', '=', 'd.idInvoice')
            ->whereNull('d.idInvoice')
            ->join('v_collaboration_cfp_etps as etp', 'i.idEntreprise', '=', 'etp.idEtp')
            ->where('i.idCustomer', Customer::idCustomer())
            ->where('i.idTypeFacture', 2);
    }

    public function changeStatus($idBc, $idStatus)
    {
        $bc = DB::table('bon_commandes')->where('idBC', $idBc)->first();
        if (!$bc) {
            return response()->json(['message' => 'Bon de commande non trouvé'], 204);
        }
        DB::table('bon_commandes')->where('idBC', $idBc)->update(['idStatus' => $idStatus]);
        return response()->json([
            'success' => true,
            'message' => 'mise à jour status avec succès!'
        ]);
    }

    public function uploadDocument(Request $request, $idBC)
    {
        try {
            $request->validate([
                'document' => 'required|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png|max:10240',
            ]);

            // Vérifier que le bon de commande existe
            $bonCommande = DB::table('bon_commandes')->where('idBC', $idBC)->first();
            if (!$bonCommande) {
                return response()->json(['error' => 'Bon de commande non trouvé'], 204);
            }

            $file = $request->file('document');
            if (!$file || !$file->isValid()) {
                return response()->json(['error' => 'Fichier invalide'], 400);
            }

            $originalName = $file->getClientOriginalName();

            // Stockage dans storage/app/public/bc_documents/{idBC}
            $uniqueName = uniqid() . '_' . $originalName;
            $path = $file->storeAs("public/document/$idBC", $uniqueName);

            if (!$path) {
                return response()->json(['error' => 'Échec du stockage'], 500);
            }

            // Sauvegarde dans la table bc_documents
            $documentId = DB::table('bc_documents')->insertGetId([
                'idBC' => $idBC,
                'document_name' => "commande_$idBC",
                'file_name' => $uniqueName,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => 'Document uploadé avec succès',
                'document_id' => $documentId,
                'file_name' => $originalName,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Données invalides',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de l\'upload: ' . $e->getMessage()
            ], 500);
        }
    }
    public function downloadDocument($idDocument)
    {
        try {
            $document = DB::table('bc_documents')->where('idDocument', $idDocument)->first();

            if (!$document) {
                return response()->json(['error' => 'Document non trouvé'], 404);
            }

            if (!Storage::exists($document->file_path)) {
                return response()->json(['error' => 'Fichier non trouvé'], 404);
            }

            return Storage::download($document->file_path, $document->document_name . '.' . pathinfo($document->file_name, PATHINFO_EXTENSION));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du téléchargement: ' . $e->getMessage()
            ], 500);
        }
    }
    public function deleteDocument($idDocument)
    {
        try {
            $document = DB::table('bc_documents')->where('idDocument', $idDocument)->first();

            if (!$document) {
                return response()->json(['error' => 'Document non trouvé'], 404);
            }

            // Supprimer le fichier physique
            if (Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }

            // Supprimer l'enregistrement en base
            DB::table('bc_documents')->where('idDocument', $idDocument)->delete();

            return response()->json([
                'success' => 'Document supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getDocuments($idBC)
    {
        try {
            $documents = DB::table('bc_documents')
                ->where('idBC', $idBC)
                ->select([
                    'idDocument',
                    'document_name',
                    'file_name',
                    'file_path',
                    'file_type',
                    'created_at',
                    'updated_at'
                ])
                ->orderBy('created_at', 'desc')
                ->get();
            return response()->json($documents);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getFirstDocument($bcId)
    {
        try {
            $documents = DB::table('bc_documents')
                ->where('idBC', $bcId)
                ->select([
                    'idDocument'
                ])
                ->first();
            return response()->json($documents);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération: ' . $e->getMessage()
            ], 500);
        }
    }

    public function previewDocument($idDocument)
    {
        try {
            $document = DB::table('bc_documents')->where('idDocument', $idDocument)->first();

            if (!$document) {
                return response()->json(['error' => 'Document non trouvé'], 204);
            }

            $filePath = storage_path('app/' . $document->file_path);

            if (!file_exists($filePath)) {
                return response()->json(['error' => 'Fichier non trouvé'], 204);
            }

            return response()->file($filePath, [
                'Content-Type' => $document->file_type,
                'Content-Disposition' => 'inline; filename="' . $document->file_name . '"'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la prévisualisation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProjetsByBonCommande($idBC)
    {
        try {
            $projets = DB::table('v_projet_cfps as p')
                ->select([
                    'p.idProjet',
                    'p.module_name',
                    'p.dateDebut',
                    'p.dateFin',
                    'p.li_name',
                    'p.etp_name',
                    'p.project_status'
                ])
                ->where('p.idBc', $idBC)
                ->orderBy('p.dateFin', 'desc')
                ->get();

            if ($projets->isEmpty()) {
                return response()->json(['message' => 'Aucun projet associé à ce bon de commande'], 204);
            }

            return response()->json($projets);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des projets',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
