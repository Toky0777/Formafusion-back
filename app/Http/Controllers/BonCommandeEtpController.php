<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BonCommandeEtpController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $currentPage = $request->get('page', 1);

        $query = $this->getBaseQueryEtp();

        // Appliquer les filtres
        $this->applyFilters($query, $request);

        // Paginer les résultats
        $bonCommandes = $query
            ->paginate($perPage, ['*'], 'page', $currentPage);

        $totalDevis = $this->getTotalDevis($request);
        $totalBc = $this->getTotalBc($request);
        $totalNewBc = $this->getTotalNewBc($request);

        return response()->json([
            // 'nb_bc' => $nbBc,
            'total_devis' => $totalDevis,
            'total_bc' => $totalBc,
            'total_new_bc' => $totalNewBc,
            'bon_commandes' => $bonCommandes->items(),
            'current_page' => $bonCommandes->currentPage(),
            'last_page' => $bonCommandes->lastPage(),
            'per_page' => $bonCommandes->perPage(),
            'total' => $bonCommandes->total(),
            'from' => $bonCommandes->firstItem(),
            'to' => $bonCommandes->lastItem(),
        ]);
    }

    private function getBaseQueryEtp()
     {
        return DB::table('bon_commandes as bc')
            ->select([
                'bc.idBC',
                'bc.idDevis',
                'bcs.idStatus',
                'bcs.status_name',
                'bcs.status_color',
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
            ->join('bc_status as bcs', 'bc.idStatus', '=', 'bcs.idStatus')
            ->leftJoin('bc_contacts as c', 'bc.idContact', '=', 'c.idContact')
            ->join('invoice_details_profo as idp', 'bc.idDevis', '=', 'idp.idInvoice')
            ->leftJoin('bc_documents as bd', 'bc.idBC', '=', 'bd.idBC')
            ->leftJoin('projets as p', 'bc.idBC', '=', 'p.idBc')
            ->where('cu.idCustomer', Customer::idCustomer())
            ->groupBy([
                'bc.idBC'
            ])
            ->orderBy('bc.date', 'desc');
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
            ->where('cu.idCustomer', Customer::idCustomer());

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
