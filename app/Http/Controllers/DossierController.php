<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\DossierService;
use App\Services\UtilService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\Storage;
// Pour Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

// Pour Word
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');

class DossierController extends Controller
{

    protected $dossierService;
    protected $utilService;


    public function __construct(DossierService $dossierService, UtilService $utilService)
    {
        $this->dossierService = $dossierService;
        $this->utilService = $utilService;
    }

    public function allFolder()
    {
        $folders = DB::table('dossiers')
            ->select('idDossier as id', 'nomDossier as name')
            ->where('idCfp', Customer::idCustomer())
            ->get();

        return response()->json($folders, 200);
    }

    public function getAllDossier(Request $request)
    {
        $year = $request->input('year');

        $idCfp = Customer::idCustomer();

        $dossiers = $this->dossierService->getAllDossiersByCfpAndYear($idCfp, $year);

        if ($dossiers->isEmpty()) {
            return response()->json([
                'message' => 'Aucun dossier trouvé pour cet utilisateur.'
            ]);
        }

        return response()->json([
            'message' => 'Dossiers récupérés avec succès.',
            'dossiers' => $dossiers,
            'idProjet' => $request->idProjet
        ]);
    }

    public function getPublicDocument($id)
    {
        $document = DB::table('documents')->where('id', $id)->first();

        if (!$document) {
            abort(404);
        }

        $filePath = storage_path("app/{$document->path}");

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath, [
            'Content-Type' => mime_content_type($filePath),
            'Content-Disposition' => 'inline; filename="' . $document->nom_document . '"',
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'dossier' => 'required|min:2|max:200',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        $dossier = $request->input('dossier');
        $idCfp = Auth::user()->id;

        $idDossier = $this->dossierService->createDossier($dossier, $idCfp);

        return response()->json(
            [
                'success' => "Dossier créé avec succès : $dossier",
                'idDossier' => $idDossier,
            ]
        );
    }

    public function showByIdCfp(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        $cfpId = Customer::idCustomer();

        $dossiers = $this->dossierService->getDossiersByCfpAndYear($cfpId, $year, $month);

        if ($dossiers->isEmpty()) {
            return response()->json(['message' => 'Aucun dossier trouvé pour cet utilisateur.']);
        }

        return response()->json([
            'message' => 'Dossiers récupérés avec succès.',
            'dossiers' => $dossiers,
        ]);
    }

    public function edit(Request $request, $idDossier)
    {
        $request->validate([
            'nomDossier' => 'required|min:2|max:200',
        ]);

        $nouveauNom = $request->input('nomDossier');

        if ($this->dossierService->dossierExists($nouveauNom)) {
            return response()->json([
                'success' => false,
                'message' => 'Un dossier avec ce nom existe déjà.',
            ]);
        }

        $updated = $this->dossierService->updateDossier($idDossier, $nouveauNom);

        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Dossier mis à jour avec succès.',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Dossier non trouvé.',
            ], 404);
        }
    }

    public function destroy($idDossier)
    {
        try {
            // On capture la valeur de retour de la transaction
            $deleted = DB::transaction(function () use ($idDossier) {
                // Récupération des chemins des fichiers liés au dossier
                $filePaths = DB::table('documents')
                    ->where('idDossier', $idDossier)
                    ->pluck('path');

                // Suppression des fichiers du disque
                foreach ($filePaths as $filePath) {
                    if (Storage::disk('do')->exists($filePath)) {
                        Storage::disk('do')->delete($filePath);
                    }
                }

                // Suppression des enregistrements liés aux documents
                DB::table('documents')->where('idDossier', $idDossier)->delete();

                // Mise à jour des projets pour détacher le dossier
                DB::table('projets')
                    ->where('idDossier', $idDossier)
                    ->update(['idDossier' => null]);

                // Suppression du dossier lui-même
                $deleted = DB::table('dossiers')->where('idDossier', $idDossier)->delete();

                // Si aucun dossier supprimé, on lance une exception pour rollback
                if (!$deleted) {
                    throw new \Exception('Dossier non trouvé.');
                }

                return $deleted;
            });

            return response()->json([
                'success' => true,
                'message' => 'Dossier supprimé avec succès.',
            ]);
        } catch (\Exception $e) {
            FacadesLog::error('Erreur lors de la suppression du dossier : ' . $e->getMessage());
            FacadesLog::info('Suppression du dossier : ' . $idDossier);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du dossier.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDossierDetail($idDossier)
    {
        $idCfp = Customer::idCustomer();
        $nomDossier = $this->dossierService->getNomDossier($idDossier);
        $entreprises = $this->dossierService->getEntreprisesDossierDetail($idDossier, $idCfp);
        $nomsEntreprises = $entreprises->pluck('etp_name');
        $montantData = $this->dossierService->getMontantTotalDossierDetail($idDossier);
        $project_types = $this->dossierService->getProjectTypesDossierDetail($idDossier, $idCfp);
        $module_names = $this->dossierService->getModuleNamesDossierDetail($idDossier, $idCfp);
        $villes = $this->dossierService->getVillesDossierDetail($idDossier, $idCfp);
        $dateMinProjet = $this->dossierService->getDateMinProjetDossierDetail($idDossier);
        $dateMaxProjet = $this->dossierService->getDateMaxProjetDossierDetail($idDossier);
        $nombreDocument = $this->dossierService->getNombreDocumentDossierDetail($idDossier);
        $nbProjet = $this->dossierService->getNbProjetDossierDetail($idDossier);
        $apprenantsData = $this->dossierService->getApprenantCountDossierDetail($idDossier);
        $status = $this->dossierService->getPaymentStatusDossierDetail($idDossier);
        $projets = $this->dossierService->getProjetsForDossier($idDossier);

        return response()->json([
            'nomDossier' => $nomDossier,
            'apprenants' => $apprenantsData['total'] ?? 0,
            'entreprises' => $entreprises,
            'montantTotal' => $montantData->montantTotal,
            'montantIntra' => $montantData->montantIntra,
            'montantInter' => $montantData->montantInter,
            'project_types' => $project_types,
            'dateMinProjet' => $dateMinProjet,
            'dateMaxProjet' => $dateMaxProjet,
            'villes' => $villes,
            'module_names' => $module_names,
            'projet_count' => $nbProjet,
            'nombreDocument' => $nombreDocument,
            'payment_status' => $status,
            'nomsEntreprises' => $nomsEntreprises,
            'projets' => $projets,
            'status' => $status,
        ]);
    }

    function getFichier($idDossier)
    {
        $idCfp = Customer::idCustomer();
        $entreprises = $this->dossierService->getEntreprisesDossierDetail($idDossier, $idCfp);
        $montantTotal = $this->dossierService->getMontantTotalDossierDetail($idDossier);
        $project_types = $this->dossierService->getProjectTypesDossierDetail($idDossier, $idCfp);
        $dateMinProjet = $this->dossierService->getDateMinProjetDossierDetail($idDossier);
        $dateMaxProjet = $this->dossierService->getDateMaxProjetDossierDetail($idDossier);
        $minStatus = $this->dossierService->getMinStatus($idDossier);

        $projects = DB::table('v_projet_cfps')
            ->select(
                'idProjet',
                'dateDebut',
                'ville',
                'idEtp',
                'dateFin',
                'project_status',
                'module_name',
                'etp_name',
                DB::raw('COALESCE(total_ht, 0) AS total_ht'),
                'project_type',
                'project_reference',
                DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'),
                'idCfp_inter',
                'modalite',
                'idModule'
            )
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                });
            })
            ->where('idDossier', $idDossier)
            ->where('project_is_trashed', 0)
            ->orderBy('dateDebut', 'asc')
            ->get();

        $projets = [];
        $totalHtSum = 0;
        $totalNbApprenants = 0;

        /** @var \stdClass $project */
        foreach ($projects as $project) {
            $totalHtSum += $project->total_ht;
            $nbApprenants = $this->getNombreApprenant($project->idProjet);
            $totalNbApprenants += $nbApprenants;

            $projets[] = [
                'projectIsPaid' => $this->projectIsPaid($project->idProjet),
                'nbApprenant' => $nbApprenants,
                'idProjet' => $project->idProjet,
                'ville' => $project->ville,
                'idCfp_inter' => $project->idCfp_inter,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'total_ht' => $project->total_ht,
                'module_name' => $project->module_name,
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'idEtp' => $project->idEtp,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'project_reference' => $project->project_reference,
                'modalite' => $project->modalite,
                'etp_name_in_situ' => $project->etp_name,
                'idModule' => $project->idModule,
            ];
        }

        $documents = DB::table('v_document_dossier')
            ->select(
                'idDocument',
                'taille',
                'titre',
                'path',
                'idDossier',
                'filename',
                'type_document',
                'idTypeDocument',
                'idSectionDocument',
                'section_document',
                'idProjet',
                'extension'
            )
            ->where('idDossier', $idDossier)
            ->get();

        $nomDossier = DB::table('dossiers')
            ->select('nomDossier', 'idDossier')
            ->where('idDossier', $idDossier)
            ->first();

        return response()->json([
            'documents' => $documents,
            'nomDossier' => $nomDossier,
            'projets' => $projets,
            'total_ht_sum' => $totalHtSum,
            'totalNbApprenants' => $totalNbApprenants,
            'entreprises' => $entreprises,
            'montantTotal' => $montantTotal->montantTotal,
            'project_types' => $project_types,
            'dateMinProjet' => $dateMinProjet,
            'dateMaxProjet' => $dateMaxProjet,
            'minStatus' => $minStatus
        ]);
    }

    public function ajoutProjetInFolder($idDossier, $idProjet)
    {
        try {
            DB::table('projets')
                ->where('idProjet', $idProjet)
                ->update(['idDossier' => $idDossier]);

            $folder = DB::table('dossiers')
                ->select(
                    'nomDossier as name',
                    DB::raw('(SELECT COUNT(*) FROM documents WHERE documents.idDossier = dossiers.idDossier) as document_count'),
                    DB::raw('(SELECT COUNT(*) FROM projets WHERE projets.idDossier = dossiers.idDossier) as project_count')
                )
                ->where('idDossier', $idDossier)
                ->first();

            return response()->json([
                'success' => 'Projet ajouté à ce dossier avec succès.',
                'folder' => $folder
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Échec de l\'ajout du projet dans ce dossier.',
            ]);
        }
    }

    public function showByDossier($idDossier)
    {
        $dossier = DB::table('dossiers')->where('idDossier', $idDossier);

        if ($dossier->exists()) {
            return response()->json([
                'status' => 200,
                'dossier' => $dossier->first()
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ], 404);
        }
    }

    public function editDocument($idDocument, Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'type_document' => 'required|integer',
        ]);

        $updated = DB::table('documents')
            ->where('idDocument', $idDocument)
            ->update([
                'titre' => $request->input('titre'),
                'idTypeDocument' => $request->input('type_document'),
                'updated_at' => now()
            ]);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Document mis à jour avec succès']);
        } else {
            return response()->json(['success' => false, 'message' => 'Échec de la mise à jour du document'], 500);
        }
    }

    public function destroyDocument($idDocument)
    {
        $document = DB::table('documents')
            ->where('idDocument', $idDocument)
            ->first();

        if ($document) {
            $filePath = $document->path;

            DB::transaction(function () use ($filePath, $idDocument) {
                Storage::disk('do')->delete($filePath);
                DB::table('documents')
                    ->where('idDocument', $idDocument)
                    ->delete();
            });

            return response()->json([
                'success' => 'Document supprimé avec succès.',
            ]);
        } else {
            return response()->json([
                'error' => 'Document non trouvé.',
            ]);
        }
    }

    public function supprimeProjetInFolder($idDossier, $idProjet)
    {
        $updated = DB::table('projets')
            ->where('idProjet', $idProjet)
            ->update(['idDossier' => null]);

        if ($updated) {
            return response()->json([
                'success' => 'Projet supprimé dans ce dossier avec succès.',
            ]);
        } else {
            return response()->json([
                'error' => 'Échec de suppression du projet dans ce dossier.',
            ]);
        }
    }

    public function uploadFichier(Request $request, $idDossier)
    {
        try {
            $request->validate([
                'myFile'          => 'required|mimes:pdf,txt,ppt,pptx,csv,xls,xlsx|max:2048',
                'title'           => 'required|string|max:200',
                'section_document' => 'required',
                'type_document'   => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error'   => 'Les données ne sont pas valides.',
                'details' => $e->errors()
            ], 422);
        }

        $file = $request->file('myFile');
        if (!$file || !$file->isValid()) {
            return response()->json(['error' => 'Aucun fichier sélectionné ou fichier invalide.'], 400);
        }

        $fileSize      = $file->getSize();
        $fileSizeInMb  = round($fileSize / (1024 * 1024), 2);
        $fileExtension = $file->extension();
        $originalName  = $file->getClientOriginalName();

        if ($fileSize > 2 * 1024 * 1024) {
            return response()->json([
                'error' => 'Le fichier dépasse la taille maximale autorisée de 2 Mo.'
            ], 413);
        }

        try {
            // Stockage dans storage/app/public/document/{idDossier}
            $uniqueName = uniqid() . '_' . $originalName;
            $path = $file->storeAs("public/document/$idDossier", $uniqueName);

            if (!$path) {
                return response()->json([
                    'error' => 'Échec lors de l’enregistrement du fichier (path vide).'
                ], 500);
            }

            // Générer l'URL publique accessible via /storage/
            $url = Storage::url(str_replace('public/', '', $path));

            // Sauvegarde dans la table documents
            DB::table('documents')->insert([
                'titre'          => $request->input('title'),
                'path'           => $path,           // chemin dans storage
                'filename'       => $originalName,   // nom original
                'extension'      => $fileExtension,
                'taille'         => $fileSizeInMb,
                'idDossier'      => $idDossier,
                'idTypeDocument' => $request->input('type_document'),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du fichier : ' . $e->getMessage());
            return response()->json([
                'error' => 'Erreur lors du téléchargement : ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => 'Document ajouté avec succès.',
            'path'    => $path,
            'url'     => $url,
        ], 201);
    }

    function getDocumentProjet($idProjet)
    {
        $getIdDossier = DB::table('projets')
            ->select('idDossier')
            ->where('idProjet', $idProjet)
            ->first();

        $idDossier = $getIdDossier ? $getIdDossier->idDossier : null;

        if ($idDossier === null) {
            return response()->json(['message' => 'Pas de dossier associé à ce projet']);
        } else {
            $documents = DB::table('v_document_dossier')
                ->where('idDossier', $idDossier)
                ->get();

            $nomDossier = DB::table('v_document_dossier')
                ->select('nomDossier')
                ->where('idDossier', $idDossier)
                ->first();

            return response()->json([
                'documents' => $documents,
                'nomDossier' => $nomDossier
            ]);
        }
    }

    public function getSectionDocument()
    {
        $sectionDocument = DB::table('section_documents')
            ->get();
        return response()->json([
            'sectionDocument' => $sectionDocument
        ]);
    }

    public function getTypeDocument($idSectionDocument)
    {
        $typeDocuments = DB::table('type_documents')
            ->where('idSectionDocument', $idSectionDocument)
            ->get();
        return response()->json([
            'typeDocuments' => $typeDocuments
        ]);
    }

    public function getNombreDocument($idDossier)
    {
        $nombreDocument = DB::table('documents')
            ->select(DB::raw('count(*) as nombreDocument'))
            ->where('idDossier', $idDossier)
            ->first();

        return response()->json([
            'nombreDocument' => $nombreDocument,
        ]);
    }

    function loadDossier()
    {
        $dossiers = DB::table('dossiers')
            ->where('dossiers.idCfp', Customer::idCustomer())
            ->groupBy('dossiers.idDossier', 'dossiers.nomDossier')
            ->orderBy('dossiers.nomDossier', 'asc')
            ->get();

        return response()->json(['dossiers' => $dossiers]);
    }

    public function getProjectsFolder(?int $idDossier = null)
    {
        try {
            $dossier = null;

            if ($idDossier) {
                $dossier = DB::table('dossiers')->where('idDossier', $idDossier)->first();
                if (!$dossier) {
                    return response()->json(['message' => 'Dossier non trouvé'], 404);
                }
            }

            $projectsQuery = DB::table('v_projet_cfps')
                ->select(
                    'idProjet',
                    'dateDebut',
                    'dateFin',
                    'module_name',
                    'etp_name',
                    'project_type',
                    'project_reference',
                    'ville',
                    'project_status',
                    'total_ht',
                    DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate')
                )
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->whereNot('module_name', 'Default module')
                ->where('headYear', Carbon::now()->format('Y'))
                ->where('project_is_trashed', 0);

            if (is_null($idDossier)) {
                $projectsQuery->whereNull('idDossier');
            } else {
                $projectsQuery->where('idDossier', $idDossier);
            }

            $projects = $projectsQuery->orderBy('dateDebut', 'asc')->get();

            $projets = $projects->map(function ($project) {
                $paymentStatus = 6;
                $totalHt = (float)($project->total_ht ?? 0);

                if ($totalHt > 0) {
                    if ($totalHt > 1000000) {
                        $paymentStatus = 5;
                    } elseif ($totalHt > 0) {
                        $paymentStatus = 4;
                    }
                }

                return [
                    'idProjet' => $project->idProjet,
                    'module_name' => $project->module_name ?? 'Module inconnu',
                    'reference' => $project->project_reference ?? 'Référence inconnue',
                    'etp_name' => $project->etp_name ?? 'Entreprise inconnue',
                    'ville' => $project->ville ?? 'Ville inconnue',
                    'project_status' => $project->project_status ?? 'Inconnu',
                    'dateDebut' => $project->dateDebut ?? '—',
                    'dateFin' => $project->dateFin ?? '—',
                    'total_ht' => $totalHt,
                    'payment_status' => $paymentStatus,
                    'fileUrl' => null,
                    'fileName' => 'Document',
                    'fileType' => 'unknown',
                ];
            });

            $entreprises = $projects->pluck('etp_name')->unique()->filter()->map(fn($etp) => ['etp_name' => $etp])->values()->toArray();
            $project_types = $projects->pluck('project_type')->unique()->filter()->map(fn($pt) => ['project_type' => $pt])->values()->toArray();
            $module_names = $projects->pluck('module_name')->unique()->filter()->map(fn($m) => ['module_name' => $m])->values()->toArray();

            $dateMinProjet = $projects->min('dateDebut') ?? '—';
            $dateMaxProjet = $projects->max('dateFin') ?? '—';

            $responseData = [
                'idDossier' => $idDossier,
                'nomDossier' => $dossier ? ($dossier->nomDossier ?? 'Nom inconnu') : 'Sans dossier',
                'entreprises' => $entreprises,
                'status' => $idDossier ? $this->dossierService->getPaymentStatusDossierDetail($idDossier) : 6,
                'montantTotal' => $idDossier ? ($this->dossierService->getMontantTotalDossierDetail($idDossier)->montantTotal ?? 0) : 0,
                'dateMinProjet' => $dateMinProjet,
                'dateMaxProjet' => $dateMaxProjet,
                'project_types' => $project_types,
                'villes' => $projects->pluck('ville')->unique()->filter()->values()->toArray(),
                'module_names' => $module_names,
                'projet_count' => $projets->count(),
                'apprenants' => $idDossier ?  $this->dossierService->getApprenantCountDossierDetail($idDossier) : 0,
                'nombreDocument' => $dossier->nombre_document ?? 0,
                'status_label' => $dossier->status_label ?? 'En_preparation',
                'projets' => $projets,
                'projectDates' => $projects->groupBy('headDate')->map(function ($group) {
                    return [
                        'headDate' => $group->first()->headDate,
                        'projet_nb' => $group->count(),
                    ];
                })->values(),
            ];

            return response()->json($responseData);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    public function getNombreProjet($idDossier)
    {
        return response()->json([
            'projet_count' => $this->dossierService->getNbProjetDossierDetail($idDossier)
        ]);
    }

    public function getOneDocumentByFolder()
    {
        $folderdocuments = DB::table('documents as d1')
            ->join(
                DB::raw('(SELECT idDossier, MAX(created_at) as latestDocument FROM documents GROUP BY idDossier) as d2'),
                function ($join) {
                    $join->on('d1.idDossier', '=', 'd2.idDossier')
                        ->on('d1.created_at', '=', 'd2.latestDocument');
                }
            )
            ->leftJoin('dossiers as ds', 'd1.idDossier', '=', 'ds.idDossier')
            ->where('ds.idCfp', '=', Customer::idCustomer())
            ->select('d1.*', 'ds.nomDossier', DB::raw("DATE_FORMAT(d1.created_at, '%b %d, %Y, %l:%i %p') as date"))
            ->get();

        return response()->json([
            'folderdocuments' => $folderdocuments
        ]);
    }

    public function moveProjet(Request $request)
    {
        $idProjet = $request->input('idProjet');
        $idDossier = $request->input('idDossier');

        DB::table('projets')
            ->where('idProjet', $idProjet)
            ->update(['idDossier' => $idDossier]);

        return response()->json(['message' => 'Projet déplacé avec succès.']);
    }

    public function getNote($idDossier)
    {
        try {
            $nomDossier = DB::table('v_document_dossier')
                ->select('nomDossier', 'idDossier')
                ->where('idDossier', $idDossier)
                ->first();

            $note = DB::table('dossiers')->where('idDossier', $idDossier)->value('note');
            return response()->json(['success' => true, 'note' => $note, 'nomDossier' => $nomDossier]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la récupération de la note.'], 500);
        }
    }

    public function updateNote($idDossier, Request $request)
    {
        $request->validate([
            'note' => 'nullable|string'
        ]);

        try {
            DB::table('dossiers')
                ->where('idDossier', $idDossier)
                ->update(['note' => $request->note]);

            return response()->json(['success' => true, 'message' => 'Note mise à jour avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la mise à jour.']);
        }
    }

    public function getSelectedDossier($idProjet)
    {
        $dossiersProject = DB::table('dossiers AS d')
            ->select('p.idProjet', 'p.idDossier', 'd.nomDossier')
            ->join('projets AS p', 'd.idDossier', '=', 'p.idDossier')
            ->where('p.idProjet', $idProjet);

        if ($dossiersProject->exists()) {
            return response()->json([
                'status' => 200,
                'dossiersProject' => $dossiersProject->first()
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ], 404);
        }
    }

    public function projectIsPaid($idProjet)
    {
        $isPaid = DB::table('invoice_details as ID')
            ->select('I.invoice_status')
            ->join('invoices as I', 'I.idInvoice', '=', 'ID.idInvoice')
            ->join('invoice_payments as IP', 'IP.invoice_id', '=', 'ID.idInvoice')
            ->where('ID.idProjet', $idProjet)
            ->whereNotExists(function ($query) {
                $query->select('IL.id')
                    ->from('invoice_deleted as IL')
                    ->whereRaw('IL.idInvoice = ID.idInvoice');
            })
            ->first();
        $status = $isPaid->invoice_status ?? null;
        return response()->json([
            'status' => $status
        ]);
    }

    public function folderIsPaid(Request $request)
    {
        $idProjets = DB::table('projets')
            ->where('idDossier', $request->idDossier)
            ->pluck('idProjet');

        $status = 6;

        foreach ($idProjets as $projet) {
            switch ($this->projectIsPaidFolder($projet)) {
                case 0:
                    $status = 6;
                case 6:
                    $status = 6;
                    break;
                case 5:
                    $status = 5;
                    break;
                case 4:
                    $status = 4;
                    break;
                default:
                    break;
            }
        }

        if ($idProjets = null) {
            $status = 6;
        }

        return response()->json([
            'status' => $status
        ]);
    }

    public function projectIsPaidFolder($idProjet)
    {
        $isPaid = DB::table('invoice_details as ID')
            ->select('I.invoice_status')
            ->join('invoices as I', 'I.idInvoice', '=', 'ID.idInvoice')
            ->join('invoice_payments as IP', 'IP.invoice_id', '=', 'ID.idInvoice')
            ->where('ID.idProjet', $idProjet)
            ->whereNotExists(function ($query) {
                $query->select('IL.id')
                    ->from('invoice_deleted as IL')
                    ->whereRaw('IL.idInvoice = ID.idInvoice');
            })
            ->first();
        return $isPaid->invoice_status ?? 0;
    }

    function getNombreApprenant($idProjet)
    {
        return DB::table('detail_apprenants')
            ->where('idProjet', $idProjet)
            ->count();
    }

    public function getEtpProjectInter($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null) {
            $etp = DB::table('v_projet_cfps')
                ->select('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
                ->where('idProjet', $idProjet)
                ->orderBy('etp_name', 'asc')
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

    private function getDocumentFile($idDocument)
    {
        $document = DB::table('documents')
            ->where('idDocument', $idDocument)
            ->first();

        if (!$document) {
            throw new \Exception('Document non trouvé.', 404);
        }

        $storagePath = $document->path;

        if (!Storage::disk('do')->exists($storagePath)) {
            throw new \Exception('Fichier non trouvé sur le serveur.', 404);
        }

        return [
            'document' => $document,
            'content' => Storage::disk('do')->get($storagePath),
            'mimeType' => Storage::disk('do')->mimeType($storagePath) ?: 'application/octet-stream',
            'filename' => $document->filename,
            'size' => Storage::disk('do')->size($storagePath),
        ];
    }

    public function downloadDocument($idDocument)
    {
        try {
            $document = DB::table('documents')->where('idDocument', $idDocument)->first();

            if (!$document) {
                return response()->json(['error' => 'Document non trouvé.'], 404);
            }

            $storagePath = $document->path ?? '';

            if (empty($storagePath)) {
                return response()->json(['error' => 'Le chemin du fichier est vide.'], 400);
            }
            if (!Storage::disk('do')->exists($storagePath)) {
                return response()->json(['error' => 'Fichier non trouvé sur le serveur.'], 404);
            }

            return Storage::disk('do')->download($storagePath, $document->filename ?? basename($storagePath));
        } catch (\Exception $e) {
            Log::error('Erreur téléchargement document: ' . $e->getMessage());

            return response()->json([
                'error' => 'Erreur lors du téléchargement : ' . $e->getMessage()
            ], 500);
        }
    }


    public function viewDocument($idDocument)
    {
        try {
            $fileData = $this->getDocumentFile($idDocument);

            $headers = [
                'Content-Type' => $fileData['mimeType'],
                'Content-Disposition' => 'inline; filename="' . $fileData['filename'] . '"',
                'Content-Length' => $fileData['size'],
                'X-Content-Type-Options' => 'nosniff',
                'Access-Control-Allow-Origin' => 'http://localhost:3000',
                'Access-Control-Allow-Credentials' => 'true',
            ];

            if (str_starts_with($fileData['mimeType'], 'image/') || $fileData['mimeType'] === 'application/pdf') {
                $headers['Cache-Control'] = 'public, max-age=3600';
            } else {
                $headers['Cache-Control'] = 'no-store, no-cache, must-revalidate';
            }

            return response($fileData['content'], 200, $headers);
        } catch (\Exception $e) {
            Log::error('Erreur prévisualisation document: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
        }
    }

    public function searchGlobal(Request $request)
    {
        try {
            $searchTerm = $request->input('q', '');
            $idCfp = Customer::idCustomer();

            $query = DB::table('dossiers')
                ->leftJoin('projets', 'dossiers.idDossier', '=', 'projets.idDossier')
                ->leftJoin('v_projet_cfps', 'projets.idProjet', '=', 'v_projet_cfps.idProjet')
                ->leftJoin('modules', 'projets.idModule', '=', 'modules.idModule')
                ->where('dossiers.idCfp', $idCfp)
                ->groupBy('dossiers.idDossier', 'dossiers.nomDossier')
                ->select(
                    'dossiers.idDossier',
                    'dossiers.nomDossier',
                    DB::raw('GROUP_CONCAT(DISTINCT v_projet_cfps.etp_name) as entreprises'),
                    DB::raw('GROUP_CONCAT(DISTINCT v_projet_cfps.ville) as villes'),
                    DB::raw('GROUP_CONCAT(DISTINCT v_projet_cfps.project_type) as project_types'),
                    DB::raw('GROUP_CONCAT(DISTINCT v_projet_cfps.module_name) as module_names'),
                    DB::raw('MIN(projets.dateDebut) as dateMinProjet'),
                    DB::raw('MAX(projets.dateFin) as dateMaxProjet'),
                    DB::raw('COUNT(DISTINCT projets.idProjet) as projet_count'),
                    DB::raw('SUM(projets.total_ht) as montantTotal')
                );

            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('dossiers.nomDossier', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('v_projet_cfps.etp_name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('v_projet_cfps.ville', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('v_projet_cfps.project_type', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('v_projet_cfps.module_name', 'LIKE', "%{$searchTerm}%");
                });
            }

            if ($request->has('year')) {
                $query->whereYear('projets.dateDebut', $request->input('year'));
            }

            if ($request->has('month')) {
                $query->whereMonth('projets.dateDebut', $request->input('month'));
            }

            $dossiers = $query->paginate(15);
            $results = collect($dossiers->items())->map(function ($dossier) {

                return [
                    'idDossier' => $dossier->idDossier,
                    'nomDossier' => $dossier->nomDossier,
                    'entreprises' => $dossier->entreprises ? explode(',', $dossier->entreprises) : [],
                    'villes' => $dossier->villes ? explode(',', $dossier->villes) : [],
                    'project_types' => $dossier->project_types ? explode(',', $dossier->project_types) : [],
                    'module_names' => $dossier->module_names ? explode(',', $dossier->module_names) : [],
                    'dateMinProjet' => $dossier->dateMinProjet,
                    'dateMaxProjet' => $dossier->dateMaxProjet,
                    'projet_count' => $dossier->projet_count,
                    'montantTotal' => $dossier->montantTotal,
                    'nombreDocument' => DB::table('documents')
                        ->where('idDossier', $dossier->idDossier)
                        ->count(),
                    'status' => $this->dossierService->getPaymentStatusDossierDetail($dossier->idDossier)
                ];
            });

            return response()->json([
                'success' => true,
                'dossiers' => $results,
                'pagination' => [
                    'current_page' => $dossiers->currentPage(),
                    'last_page' => $dossiers->lastPage(),
                    'per_page' => $dossiers->perPage(),
                    'total' => $dossiers->total()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur recherche globale: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
