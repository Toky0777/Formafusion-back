<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Customer;
use App\Services\UtilService;
use App\Services\ProjetService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Projet;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ProjectRequest;
use App\Traits\HasModule;
use App\Traits\HasCustomer;
use App\Traits\HasProjectMaterial;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProjetController extends Controller
{
    protected $utilService;
    protected $project;

    use HasModule, HasCustomer, HasProjectMaterial;

    public function __construct(UtilService $utilService, ProjetService $prj)
    {
        $this->utilService = $utilService;
        $this->project = $prj;
    }

    public function avisEmp()
    {
        return view('employes.projets.pages.avis');
    }

    public static function getTotalHT($idProjet)
    {
        try {
            $TotalHT = DB::table('projets')
                ->select('total_ht')
                ->where('idProjet', $idProjet)
                ->first();

            // Si $TotalHT est null ou si la colonne total_ht est null, retourner 0
            if ($TotalHT === null || $TotalHT->total_ht === null) {
                return 0;
            }

            return (float) $TotalHT->total_ht;
        } catch (Exception $e) {
            // Vous pouvez également gérer cette exception en journalisant l'erreur ou en retournant une valeur par défaut
            return 0;
        }
    }

    public function formAssign($idProjet, $idFormateur)
    {
        $check = DB::table('project_forms')
            ->select('idProjet', 'idFormateur')
            ->where('idProjet', $idProjet)
            ->where('idFormateur', $idFormateur)
            ->count();

        if ($check <= 0) {
            $insert = DB::table('project_forms')->insert([
                'idProjet' => $idProjet,
                'idFormateur' => $idFormateur
            ]);

            if ($insert) {
                return response()->json(['success' => 'Succès']);
            } else {
                return response()->json(['error' => 'Erreur inconnue !']);
            }
        } else {
            return response()->json(['error' => 'Formateur déjas inscrit au projet !']);
        }
    }


    public function getVille()
    {
        $villes = DB::table('ville_codeds')
            ->select('id as idVille', 'ville_name as ville')
            ->orderBy('ville', 'asc')
            ->get();

        if (count($villes) <= 0) {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        } else {
            return response()->json([
                'status' => 200,
                'villes' => $villes
            ]);
        }
    }

    public function formRemove($idProjet, $idFormateur)
    {
        $query = DB::table('project_forms')->where('idFormateur', $idFormateur)->where('idProjet', $idProjet);

        if ($query->first()) {
            try {
                $query->delete();

                return response()->json([
                    'status' => 200,
                    'message' => 'succès'
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'status' => 400,
                    'message' => 'erreur inconnue !'
                ]);
            }
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Formateur introuvable !'
            ], 204);
        }
    }

    public function fraisAssign($idProjet, $idFrais, $isEtp)
    {
        // Validate input
        if (!is_numeric($idProjet) || !is_numeric($idFrais)) {
            return response()->json(['error' => 'Invalid input data'], 400);
        }

        try {
            DB::table('fraisprojet')->insert([
                'idProjet' => $idProjet,
                'idFrais' => $idFrais,
                'montant' => 0,
                'description' => null,
                'isEtp' => $isEtp,
                'idPayeur' => Auth::user()->id
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Frais ajouté avec succes'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => 'Erreur inconnue !'
            ], 400);
        }
    }

    public function fraisTotalEtp($idProjet)
    {
        try {
            $fraisTotalHT = 0; // Initialiser à 0

            $fraisTotal = DB::table('fraisprojet')
                ->select(DB::raw('SUM(montant) as fraisTotal'))
                ->where('idProjet', $idProjet)
                ->where('isEtp', 1)
                ->first();

            if ($fraisTotal !== null && $fraisTotal->fraisTotal !== null) {
                $fraisTotalHT = (float) $fraisTotal->fraisTotal;
            } else {
                Log::info('Aucun frais trouvé pour le projet ID: ' . $idProjet);
            }

            $total_ht = ProjetController::getTotalHT($idProjet);
            if ($total_ht === null) {
                $total_ht = 0;
            }
            $fraisTotalHT += (float) $total_ht;

            $fraisTotalTTC = $fraisTotalHT * 1.20;

            DB::table('projets')
                ->where('idProjet', $idProjet)
                ->update(['total_ht_etp' => $fraisTotalHT, 'total_ttc_etp' => $fraisTotalTTC]);

            return response()->json(['message' => 'Frais total mis à jour avec succès', 'total_ht_etp' => $fraisTotalHT, 'total_ttc_etp' => $fraisTotalTTC]);
        } catch (Exception $e) {
            Log::error('Erreur dans fraisTotalEtp: ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue', 'details' => $e->getMessage()]);
        }
    }

    public function updateFrais(Request $request)
    {
        // Validation stricte
        $validated = $request->validate([
            'description' => 'nullable|string|max:200',
            'montant' => 'required|numeric|min:0',
            'idFraisProjet' => 'required|exists:fraisprojet,idFraisProjet'
        ]);

        $idFraisProjet = $validated['idFraisProjet'];
        $montant = $validated['montant'];
        $description = $validated['description'] ?? '';

        $query = DB::table('fraisprojet')->where('idFraisProjet', $idFraisProjet);

        if (!$query->exists()) {
            return response()->json([
                'status' => 204,
                'message' => 'Élément introuvable !'
            ], 204);
        }

        try {
            $query->update([
                'montant' => $montant,
                'description' => $description
            ]);

            // Mettre à jour les totaux du projet
            $idProjet = ProjetController::getIdProjetByIdFraisProjet($idFraisProjet);
            ProjetController::fraisTotal($idProjet);
            ProjetController::fraisTotalEtp($idProjet);

            return response()->json([
                'status' => 200,
                'message' => 'Frais modifié avec succès'
            ]);
        } catch (\Exception $e) {
            // Log de l’erreur pour débogage
            Log::error('Erreur updateFrais: ' . $e->getMessage(), [
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => 500,
                'message' => 'Erreur serveur : ' . $e->getMessage()
            ], 500);
        }
    }

    public function getIdProjetByIdFraisProjet($idFraisProjet)
    {
        try {
            $idProjet = DB::table('fraisprojet')
                ->select('idProjet')
                ->where('idFraisProjet', $idFraisProjet)
                ->first();

            if ($idProjet) {
                return response()->json(['idProjet' => $idProjet->idProjet]);
            } else {
                return response()->json(['error' => 'ID Projet non trouvé']);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue', 'details' => $e->getMessage()]);
        }
    }

    public function fraisRemove($idProjet, $idFraisProjet)
    {
        $query = DB::table('fraisprojet')->where('idFraisProjet', $idFraisProjet)->where('idProjet', $idProjet);

        if ($query->first()) {
            try {
                $query->delete();
                $idProjet = ProjetController::getIdProjetByIdFraisProjet($idFraisProjet);
                ProjetController::fraisTotal($idProjet);

                return response()->json([
                    'status' => 200,
                    'message' => 'Frais supprimé avec succes'
                ]);
            } catch (Exception $e) {
                return response()->json(['error' => 'Erreur !']);
            }
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Element introuvable !'
            ], 204);
        }
    }

    public function fraisTotal($idProjet)
    {
        try {

            $sub = DB::table('v_projet_cfps')
                ->select('idSubContractor')
                ->where('idProjet', $idProjet)
                ->first();

            $idCfp = Customer::idCustomer();

            if ((isset($sub->idSubContractor) && $sub->idSubContractor != $idCfp) || !isset($sub->idSubContractor)) {
                $isEtp = 0;
            } elseif (isset($sub->idSubContractor) && $sub->idSubContractor == $idCfp) {
                $isEtp = 2;
            }

            $fraisTotal = DB::table('fraisprojet')
                ->select(DB::raw('SUM(montant) as fraisTotal'))
                ->where('idProjet', $idProjet)
                ->where('isEtp', $isEtp)
                ->first();
            if ($fraisTotal === null) {
                return response()->json(['error' => 'Aucun frais trouvé pour ce projet'], 204);
            }

            $taxe = DB::table('projets')
                ->select('taxe')
                ->where('idProjet', $idProjet)
                ->first();

            $fraisTotalHT = $fraisTotal->fraisTotal;
            $fraisTotalTTC = $fraisTotalHT * (1 + ($taxe->taxe / 100));
            if ($isEtp == 0) {
                DB::table('projets')
                    ->where('idProjet', $idProjet)
                    ->update(['total_ht' => $fraisTotalHT, 'total_ttc' => $fraisTotalTTC]);
            } elseif ($isEtp == 2) {
                DB::table('projets')
                    ->where('idProjet', $idProjet)
                    ->update(['total_ht_sub_contractor' => $fraisTotalHT, 'total_ttc_sub_contractor' => $fraisTotalTTC]);
            }

            return response()->json(['message' => 'Frais total mis à jour avec succès']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue', 'details' => $e->getMessage()]);
        }
    }

    public function removeEtpFraisProjet($idProjet, $idEtp)
    {
        $query = DB::table('fraisprojet')->where('idPayeur', $idEtp)->where('idProjet', $idProjet);

        if ($query->first()) {
            try {
                $query->delete();

                return response()->json([
                    'status' => 200,
                    'message' => 'Succes'
                ]);
            } catch (Exception $e) {
                return response()->json(['error' => 'Erreur !']);
            }
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Element introuvable !'
            ], 204);
        }
    }

    public function updateTaxe(Request $request, $idProjet)
    {
        try {
            DB::beginTransaction();

            $taxe = $request->input('taxe');

            $project = DB::table('projets')
                ->select('total_ht')
                ->where('idProjet', $idProjet)
                ->first();

            if (!$project) {
                return response()->json([
                    'status' => 204,
                    'message' => 'Projet introuvable.'
                ], 204);
            }

            $projectHt = $project->total_ht ?? 0;

            $dataUpdate = ['taxe' => $taxe];

            if ($projectHt > 0) {
                $dataUpdate['total_ttc'] = $projectHt - ($projectHt * $taxe / 100);
            }

            DB::table('projets')
                ->where('idProjet', $idProjet)
                ->update($dataUpdate);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Taxe mise à jour avec succès.'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la mise à jour de la taxe.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function updateFinancement(Request $req, $idProjet)
    {
        $req->validate([
            'idPaiement' => 'required|exists:paiements,idPaiement'
        ]);

        try {
            $projectType = $this->getProjectType($idProjet);
            $projet = DB::table('projets')->where('idProjet', $idProjet)->exists();
            if (!$projet) {
                return response()->json([
                    'status' => 204,
                    'message' => 'Projet non trouvé'
                ], 204);
            }

            if ($projectType == 1) {
                $updated = DB::table('intras')
                    ->where('idProjet', $idProjet)
                    ->update(['idPaiement' => $req->idPaiement]);
            } else {
                $updated = DB::table('inters')
                    ->where('idProjet', $idProjet)
                    ->update(['idPaiement' => $req->idPaiement]);
            }

            if ($updated === 0) {
                return response()->json([
                    'status' => 204,
                    'message' => 'Aucun enregistrement trouvé pour mise à jour'
                ], 204);
            }

            return response()->json([
                'status' => 200,
                'success' => 'Opération effectuée avec succès',
                'data' => ['idPaiement' => $req->idPaiement]
            ]);
        } catch (Exception $th) {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur serveur: ' . $th->getMessage()
            ], 500);
        }
    }

    public function getModalite()
    {
        $modalites = DB::table('modalites')
            ->select('idModalite', 'modalite')
            ->orderBy('idModalite', 'asc')
            ->get();

        if (count($modalites) <= 0) {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        } else {
            return response()->json([
                'status' => 200,
                'modalites' => $modalites
            ]);
        }
    }

    public function updateModalite(Request $req, $idProjet)
    {
        $req->validate([
            'idModalite' => 'required|exists:modalites,idModalite'
        ]);

        $query = DB::table('projets')->where('idCustomer', Customer::idCustomer())->where('idProjet', $idProjet);

        if ($query->exists()) {
            $query->update(['idModalite' => $req->idModalite]);

            return response()->json([
                'status' => 200,
                'message' => 'Succès'
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Element introuvable !'
            ], 204);
        }
    }


    public function moduleAssign($idProjet, $idModule)
    {
        $query = DB::table('projets')->where('idCustomer', Customer::idCustomer())->where('idProjet', $idProjet);

        if ($query->exists()) {
            $query->update(['idModule' => $idModule]);

            $module = DB::table('mdls')
                ->select('moduleName as name', 'module_image as image', 'idModule as id')
                ->where('idModule', $idModule)
                ->where('moduleName', '<>', 'Default module')
                ->first();

            return response()->json([
                'status' => 200,
                'message' => 'Succès',
                'module' => $module
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Element introuvable !'
            ], 204);
        }
    }

    public function getMoluleAssigne($idProjet)
    {
        $mdl = DB::table('v_projet_cfps')->select('idProjet', 'idModule', 'module_name', 'module_description', 'module_image')->where('idProjet', $idProjet);

        if ($mdl->first()) {
            return response()->json([
                'status' => 200,
                'module' => $mdl->first()
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'ENtreprise introuvable !'
            ], 204);
        }
    }

    public function store(ProjectRequest $req)
    {
        $idModule = $this->defaultModule(Customer::idCustomer())->idModule;

        try {
            $projet = $this->project->store(
                Customer::idCustomer(),
                $req->reference,
                $req->title,
                $req->description,
                $req->project_reserve ?? 0,
                $req->idModalite ?? 1,
                $idModule,
                $req->idTypeProjet,
                $req->date_debut ?? Carbon::now(),
                $req->date_fin ?? Carbon::now()
            );


            return response()->json([
                'status' => 200,
                'idProjet' => $projet,
                'message' => 'Projet crée avec succès'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => 'Erreur inconnue !'
            ], 400);
        }
    }

    public function getAllProject(Request $request)
    {
        $status = $request->query('status');
        $showAll = $request->query('showAll', false);

        $showAll = filter_var($showAll, FILTER_VALIDATE_BOOLEAN);

        return response()->json([
            'status' => 200,
            'projet_counts' => [
                'all' => $this->countProjectByStatus(''),
                'en_cours' => $this->countProjectByStatus("En cours"),
                'en_preparations' => $this->countProjectByStatus("En préparation"),
                'planifies' => $this->countProjectByStatus("Planifié"),
                'termines' => $this->countProjectByStatus("Terminé"),
                'clotures' => $this->countProjectByStatus("Cloturé"),
                'annules' => $this->countProjectByStatus("Annulé"),
                'reportes' => $this->countProjectByStatus("Reporté"),
                'reserves' => $this->countProjectByStatus("Réservé"),
                'archives' => $this->countProjectByStatus("Archivé"),
                'supprimes' => $this->countProjectByStatus("Supprimé")
            ],
            'projets' => $this->getAllProjectByStatus("Terminé", $showAll)
        ]);
    }

    public function getAllProjectStatus(Request $request)
    {
        $status = $request->query('status');
        $etpIds = $request->query('etp_ids');
        $types = $request->query('types');
        $financial = $request->query('financial');
        $courseIds = $request->query('course_ids');
        $cityIds = $request->query('city_ids');
        $periodes = $request->query('periodes');
        $trainerIds = $request->query('trainer_ids');
        $key = $request->query('key');

        return response()->json([
            'status' => $status,
            'projets' => $this->getAllProjectByStatus($status, $etpIds, $types, $financial, $courseIds, $cityIds, $periodes, $trainerIds, $key),
        ]);
    }

    public function getAllProjectByStatus($status, $etpIds, $types, $financial, $courseIds, $cityIds, $periodes, $trainerIds, $key)
    {
        $baseQuery = DB::table('v_projects')
            ->select(
                'idProjet',
                'dateDebut',
                'dateFin',
                'module_name',
                'li_name',
                'ville',
                'project_status',
                'project_reference',
                'project_description',
                'project_type',
                'paiement',
                DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'),
                'module_image',
                'idSalle',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'modalite',
                'total_ht',
                'total_ttc',
                'idModule',
                'sub_name',
                'idSubContractor',
                'idCfp',
                'total_ht_sub_contractor',
                'idTypeProjet',
                'idBc',
                'numero_bc'
            )
            ->where(function ($query) {
                $customer = Customer::idCustomer();
                $query->where('idCfp', $customer)
                    ->orWhere('idSubContractor', $customer);
            })
            ->where('module_name', '!=', 'Default module')
            ->where('project_status', $status);

        if (!empty($key)) {
            $baseQuery->where('module_name', 'like', "%{$key}%");
        }

        $query = $baseQuery
            ->orderByDesc('dateDebut')
            ->groupBy('idProjet')
            ->paginate(9);


        $query->getCollection()->transform(function ($project) use ($status) {
            // Calculer le pricing une seule fois
            $pricing = isset($project->idSubContractor) && $project->idSubContractor == Customer::idCustomer()
                ? $project->total_ht_sub_contractor
                : $project->total_ht;

            // Stocker les résultats dans des variables
            $idProjet = $project->idProjet;
            $ville = $project->ville;

            // Obtenir les données nécessaires
            $nomDossier = $this->getNomDossier($idProjet);
            $nombreDocument = $this->getNombreDocument($idProjet);
            $sessionCount = $this->getSessionProject($idProjet);
            $formateurs = $this->getFormProject($idProjet);
            $learners = $this->project->getLearnerByProject($project->idTypeProjet, $project->idProjet);
            $totalPrice = $this->getProjectTotalPrice($idProjet);
            $sessionHour = $this->getSessionHour($idProjet);
            $note = $this->getNote($idProjet);
            $entreprises = $this->project->getEntrepriseByProject($project->idTypeProjet, $project->idProjet);
            $restaurations = $this->getRestauration($idProjet);
            $checkEmg = $this->checkEmg($idProjet);
            $checkEval = $this->checkEval($idProjet);
            $avgBefore = $this->averageEvalApprenant($idProjet)->avg_avant;
            $avgAfter = $this->averageEvalApprenant($idProjet)->avg_apres;
            $isPaid = $this->projectIsPaid($idProjet);
            $materials = $this->project->getProjectMaterials($idProjet);
            $invoice = $this->getInvoiceProject($idProjet);

            // Ajouter les informations dans le tableau
            return [
                'project_reference' => $project->project_reference,
                'dossier' => $nomDossier,
                'nbDocument' => $nombreDocument,
                'seanceCount' => $sessionCount,
                'formateurs' => $formateurs,
                'learners' => $learners,
                'total_learners' => count($learners),
                'projectTotalPrice' => $totalPrice,
                'totalSessionHour' => $sessionHour,
                'general_note' => $note,
                'idProjet' => $idProjet,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'entreprises' => $entreprises,
                'ville' => $ville,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'paiement' => $project->paiement,
                'modalite' => $project->modalite,
                'project_description' => $project->project_description,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'li_name' => $project->li_name,
                'total_ht' => $this->utilService->formatPrice($pricing),
                'total_ttc' => $project->total_ttc,
                'idModule' => $project->idModule,
                'restaurations' => $restaurations,
                'checkEmg' => $checkEmg,
                'checkEval' => $checkEval,
                'avg_before' => $avgBefore,
                'avg_after' => $avgAfter,
                'sub_name' => $project->sub_name,
                'idSubContractor' => $project->idSubContractor,
                'idCfp' => $project->idCfp,
                'idUser' => Customer::idCustomer(),
                'isPaid' => $isPaid,
                'materials' => $materials,
                'invoice' => $invoice,
                'type_project' => $project->idTypeProjet,
                'idBc' => $project->idBc,
                'numero_bc' => $project->numero_bc
            ];
        });

        return $query;
    }

    public function getProjetByIdFormateur($idFormateur)
    {
        $projet = DB::table('v_projet_form')
            ->select(
                'idProjet',
                'referenceEtp',
                'project_name',
                'dateDebut',
                'dateFin',
                'project_reference',
                'project_title',
                'project_description',
                'project_status',
                'module_name',
            )
            ->where('idFormateur', $idFormateur)
            ->get();

        return response()->json([
            'status' => 200,
            'projet' => $projet
        ]);
    }

    public function countProjectByStatus($status)
    {
        return DB::table('v_projet_cfps')
            ->select('idProjet')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                });
            })
            ->where('module_name', '!=', 'Default module')
            ->where('project_status', $status)
            ->count();
    }

    public function index(string $status, Request $request)
    {
        $validStatuses = ['Cloturé', 'En cours', 'Terminé'];

        // Phase 1 : Validation du status
        if (!in_array($status, $validStatuses)) {
            return response()->json([
                'status' => 200,
                'projets' => [],
                'filtre' => [
                    'type_projets' => [],
                    'lieux' => [],
                    'entreprises' => [],
                    'modules' => [],
                    'formateurs' => [],
                    'mois' => [],
                ],
            ]);
        }
        $filters = $request->all(); // récupère tous les filtres envoyés en query/body
        $projets = $this->getStatus($status, $filters);

        // Phase 3 : Réponse JSON
        return response()->json([
            'status' => 200,
            'projets' => $projets['projets'],
            'pagination' => $projets['pagination'],
        ]);
    }


    public function detailProjetCfpPdf($idProjet)
    {
        $idCfp = Customer::idCustomer();

        $projet = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'dateFin', 'project_title', 'etp_name', 'ville', 'project_status', 'project_description',  'project_type', 'paiement', 'idPaiement', 'project_reference', 'idModalite', 'modalite', 'idEtp', 'etp_initial_name', 'etp_logo', 'idModule', 'module_name', 'module_image', 'project_price_pedagogique', 'project_price_annexe', 'module_description', 'salle_name', 'salle_rue', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'idCfp', 'modalite', 'idModule', 'idSubContractor',)
            ->where('idProjet', $idProjet)
            ->first();

        $apprenantInter = DB::table('v_list_apprenant_inter_added')
            ->select('*')
            ->where('idProjet', $idProjet)
            ->get();

        $etp = DB::table('v_projet_cfps')->select('idProjet', 'idEtp', 'etp_initial_name', 'etp_name', 'etp_logo', 'etp_email')->where('idProjet', $projet->idProjet)->first();

        $forms = DB::table('v_formateur_cfps')
            ->select('idProjet', 'idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'email AS form_email', 'initialNameForm AS form_initial_name', 'form_phone')
            ->groupBy('idProjet', 'idFormateur', 'name', 'firstName', 'photoForm', 'email', 'initialNameForm')
            ->where('idProjet', $projet->idProjet)
            ->get();

        $apprs = DB::table('v_list_apprenants as L')
            ->leftJoin('eval_apprenant as E', function ($join) use ($idProjet) {
                $join->on('E.idEmploye', '=', 'L.idEmploye')
                    ->where('E.idProjet', '=', $idProjet);
            })
            ->select(
                'L.idEmploye',
                'emp_initial_name',
                'emp_name',
                'emp_firstname',
                'emp_fonction',
                'emp_email',
                'emp_photo',
                'emp_matricule',
                'etp_name',
                'idEtp',
                'E.avant as avant',
                'E.apres as apres'
            )
            ->where('L.idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();
        // dd($apprs);

        $villes = DB::table('villes')->select('idVille', 'ville')->get();
        $paiements = DB::table('paiements')->select('idPaiement', 'paiement')->get();

        $seances = DB::table('v_seances')
            ->select('idSeance', 'dateSeance', 'heureDebut', 'heureFin', 'idProjet', 'idModule', DB::raw("TIME_FORMAT(SEC_TO_TIME(TIME_TO_SEC(intervalle_raw)), '%H:%i') AS intervalle_raw"))
            ->where('idProjet', $idProjet)
            ->orderBy('dateSeance', 'asc')
            ->get();

        $debSession = DB::table('v_seances')
            ->select('dateSeance as dateDebut')
            ->where('idProjet', $idProjet)
            ->orderBy('dateDebut', 'asc')
            ->pluck('dateDebut')
            ->first();

        $finSession = DB::table('v_seances')
            ->select('dateSeance as dateFin')
            ->where('idProjet', $idProjet)
            ->orderBy('dateFin', 'desc')
            ->pluck('dateFin')
            ->first();

        $countDate = DB::table('v_seances')
            ->select('idProjet', 'dateSeance', 'idSeance', DB::raw('COUNT(*) as count'))
            ->where('idProjet', $idProjet)
            ->groupBy('dateSeance')
            ->get();

        $totalSession = DB::table('v_seances')
            ->selectRaw("IFNULL(TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))), '%H:%i'), '00:00') as sumHourSession")
            ->where('idProjet', $idProjet)
            ->groupBy('idProjet')
            ->first();


        $deb =  Carbon::parse($projet->dateDebut)->locale('fr')->translatedFormat('l j F Y');
        $fin =  Carbon::parse($projet->dateFin)->locale('fr')->translatedFormat('l j F Y');

        $modules = DB::table('mdls')
            ->select('idModule', 'moduleName AS module_name')
            ->where('moduleName', '!=', 'Default module')
            ->where('idCustomer', Customer::idCustomer())
            ->orderBy('moduleName', 'asc')
            ->get();

        // Matériel - Prérequis - Objectif pour le projet
        $materiels = DB::table('prestation_modules')
            ->select('idPrestation', 'prestation_name', 'idModule')
            ->get();

        $prerequis = DB::table('prerequis_modules')
            ->select('idPrerequis', 'prerequis_name', 'idModule')
            ->get();

        $objectifs = DB::table('objectif_modules')->select('idObjectif', 'objectif', 'idModule')->get();

        $emargements = DB::table('emargements')
            ->select('idProjet', 'idEmploye', 'idSeance', 'isPresent')
            ->where('idProjet', $idProjet)
            ->get();

        $eval_content = DB::table('questions')
            ->select('idQuestion', 'question', 'idTypeQuestion')
            ->get();

        $eval_type = DB::table('questions')
            ->select('idQuestion', 'question', 'idTypeQuestion')
            ->groupBy('idTypeQuestion')
            ->get();

        $modalites = DB::table('modalites')->select('idModalite', 'modalite')->get();

        $checkEvaluation = DB::table('eval_chauds')->select('idProjet')->get();
        $checkEvaluationCount = count($checkEvaluation);

        if ($checkEvaluationCount > 0) {
            $notationProjet = DB::table('v_evaluation_alls')
                ->select('idProjet', 'idEmploye', 'generalApreciate')
                ->where('idProjet', $idProjet)
                ->groupBy('idProjet', 'idEmploye')
                ->get();

            $generalNotation = DB::table('v_general_note_evaluation')
                ->select(DB::raw('SUM(generalApreciate) as generalNote'))
                ->where('idProjet', $idProjet)
                ->first();

            $countNotationProjet = count($notationProjet);

            if ($countNotationProjet > 0) {
                $noteGeneral = $generalNotation->generalNote / $countNotationProjet;
            } else {
                $noteGeneral = 0;
            }
        } else {
            $countNotationProjet = 0;
            $noteGeneral = 0;
        }

        $imagesMomentums = DB::table('images')
            ->select('url', 'idImages')
            ->where('idProjet', $idProjet)
            ->where('idTypeImage', 1)
            ->get();

        $nbPl = DB::table('inters')->select('nbPlace')->where('idProjet', $idProjet)->first();
        $place_available = $this->getPlaceAvailable($idProjet) ?? null;
        $place_reserved = $this->getNbPlaceReserved($idProjet) ?? null;
        $nbPlace = $nbPl->nbPlace ?? null;

        $restaurations = DB::table('project_restaurations AS pr')
            ->select('pr.idRestauration', 'rst.typeRestauration')
            ->join('restaurations AS rst', 'pr.idRestauration', 'rst.idRestauration')
            ->where('idProjet', $idProjet)
            ->get();

        $pdf = Pdf::loadView('CFP.projets.detailProjetCfpPdf', compact(['restaurations', 'imagesMomentums', 'projet', 'villes', 'paiements', 'seances', 'modules', 'materiels', 'objectifs', 'totalSession', 'countDate', 'emargements', 'apprenantInter', 'modalites', 'prerequis', 'eval_content', 'eval_type', 'countNotationProjet', 'noteGeneral', 'nbPlace', 'place_available', 'place_reserved', 'idCfp', 'deb', 'fin', 'etp', 'forms', 'apprs']));


        $pdfContent = $pdf->output();


        $base64Pdf = base64_encode($pdfContent);


        return response()->json([
            'fileName' => $projet->module_name . '.pdf',
            'fileData' => $base64Pdf
        ]);
    }

    public function getProjects($idCustomer): mixed
    {
        $query = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'etp_name', 'li_name', 'ville', 'project_status', 'project_reference', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin', 'total_ht_sub_contractor')
            ->where(function ($query) use ($idCustomer) {
                $query->where(function ($query) use ($idCustomer) {
                    $query->where('idCfp', $idCustomer)
                        ->orWhere('idCfp_inter', $idCustomer)
                        ->orWhere('idSubContractor', $idCustomer);
                });
            })
            ->where('module_name', '!=', 'Default module')
            ->groupBy('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'etp_name', 'li_name', 'ville', 'project_status', 'project_reference', 'project_description', 'project_type', 'paiement', 'module_image', 'etp_logo', 'etp_initial_name', 'idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin', 'total_ht_sub_contractor');

        return $query;
    }

    private function getStatus(string $status, array $filters = [])
    {
        $userId = Auth::id();
        $roleId = DB::table('role_users')
            ->where('user_id', $userId)
            ->value('role_id');

        // Sélection de la source de projets selon le rôle
        if ($roleId == 3) {
            // CFP
            $projects = $this->project->indexByCfp(null, Customer::idCustomer(), $status, $filters);
            $getEtpMethod = 'getEtpProjectInter';
        } else  if ($roleId == 5) {
            // Formateur
            $projects = $this->project->indexByFormateur($userId, $status, $filters);
            $getEtpMethod = 'getEtpProjectInterByFormateur';
        } else if ($roleId == 4) {
            $projects = $this->project->indexByApprenant($userId, $status, $filters);
            $getEtpMethod = 'getEtpProjectInterByApprenant';
        }

        $projets = [];
        foreach ($projects as $project) {
            $idProjet = $project->idProjet;
            $idCfpInter = $project->idCfp_inter;

            $apprs = $this->getApprListProjet($idProjet);
            $etpName = $this->$getEtpMethod($idProjet, $idCfpInter);
            //$sessionHour = $this->getSessionHour($idProjet);
            $formateurs = $this->getFormProject($idProjet);

            $projets[] = [
                'formateurs' => $formateurs,
                //'totalSessionHour' => $sessionHour,
                'idProjet' => $idProjet,
                'idCfp_inter' => $idCfpInter,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'etp_name' => $etpName,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'modalite' => $project->modalite,
                'project_description' => $project->project_description,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'etp_initial_name' => $project->etp_initial_name,
                'idModule' => $project->idModule,
                'apprs' => $apprs,
                'li_name' => $project->li_name,
                'type_project' => $project->idTypeProjet
            ];
        }

        return [
            'projets' => $projets,
            'pagination' => method_exists($projects, 'links') ? $projects->toArray() : null,
        ];
    }

    public function show($idProjet)
    {
        $projet = DB::table('v_projects')->where('idProjet', $idProjet)->first();

        if (!$projet) {
            return response()->json(
                [
                    "message" => "Erreur lors de la recuperation de donnee"
                ],
                400
            );
        }

        $seances = DB::table('v_seances')
            ->where('idProjet', $idProjet)
            ->orderBy('dateSeance', 'asc')
            ->get([
                'idSeance',
                'dateSeance',
                'heureDebut',
                'heureFin',
                'idProjet',
                'idModule',
                DB::raw("TIME_FORMAT(SEC_TO_TIME(TIME_TO_SEC(intervalle_raw)), '%H:%i') AS intervalle_raw")
            ]);

        $datesSession = $seances->pluck('dateSeance');
        $deb = Carbon::parse($datesSession->first())->locale('fr')->translatedFormat('l j F Y');
        $fin = Carbon::parse($datesSession->last())->locale('fr')->translatedFormat('l j F Y');

        $totalSession = DB::table('v_seances')
            ->where('idProjet', $idProjet)
            ->selectRaw("IFNULL(TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))), '%H:%i'), '00:00') as sumHourSession")
            ->value('sumHourSession');

        $generalData = DB::table('v_seances')
            ->where('idProjet', $idProjet)
            ->groupBy('idProjet')
            ->selectRaw("COUNT(DISTINCT dateSeance) as countDate")
            ->first();

        $modules = DB::table('mdls')
            ->where('moduleName', '!=', 'Default module')
            ->where('idCustomer', Customer::idCustomer())
            ->orderBy('moduleName', 'asc')
            ->get(['idModule', 'moduleName AS module_name']);

        $evaluations = DB::table('v_evaluation_alls as eva')
            ->leftJoin('v_general_note_evaluation as gen', 'gen.idProjet', '=', 'eva.idProjet')
            ->where('eva.idProjet', $idProjet)
            ->groupBy('eva.idProjet')
            ->selectRaw("
                COUNT(DISTINCT eva.idEmploye) as countNotationProjet,
                IFNULL(SUM(gen.generalApreciate), 0) as generalNote,
                IFNULL(AVG(eva.generalApreciate), 0) as noteGeneral
            ")
            ->first() ?? (object) [
                'countNotationProjet' => 0,
                'generalNote' => 0,
                'noteGeneral' => 0
            ];

        $objectifs = DB::table('objectif_modules')->select('idObjectif', 'objectif', 'idModule')->get();

        $prerequis = DB::table('prerequis_modules')
            ->select('idPrerequis', 'prerequis_name', 'idModule')
            ->get();

        $placeData = DB::table('inters')
            ->where('idProjet', $idProjet)
            ->select(['nbPlace'])
            ->first();

        $nbPlace = $placeData->nbPlace ?? null;
        $place_available = $this->getPlaceAvailable($idProjet) ?? null;
        $place_reserved = $this->getNbPlaceReserved($idProjet) ?? null;

        $apprenantInter = DB::table('v_list_apprenant_inter_added')->where('idProjet', $idProjet)->get();
        $villes = DB::table('villes')->get(['idVille', 'ville']);
        $paiements = DB::table('paiements')->get(['idPaiement', 'paiement']);
        $modalites = DB::table('modalites')->get(['idModalite', 'modalite']);

        $restaurations = DB::table('project_restaurations AS pr')
            ->join('restaurations AS rst', 'pr.idRestauration', '=', 'rst.idRestauration')
            ->where('idProjet', $idProjet)
            ->get(['pr.idRestauration', 'rst.typeRestauration', 'pr.paidBy']);

        $dossier = DB::table('dossiers AS d')
            ->join('projets AS p', 'd.idDossier', '=', 'p.idDossier')
            ->where('p.idProjet', $idProjet)
            ->first(['nomDossier', 'd.idDossier']);

        $nomDossier = $dossier->nomDossier ?? null;
        $idDossier = $dossier->idDossier ?? null;

        $imagesMomentums = DB::table('images')
            ->where('idProjet', $idProjet)
            ->where('idTypeImage', 1)
            ->get(['nomImage', 'idImages']);

        $module_ressources = DB::table('module_ressources AS mr')
            ->join('mdls AS m', 'mr.idModule', 'm.idModule')
            ->join('projets AS p', 'p.idModule', 'm.idModule')
            ->where('p.idProjet', $idProjet)
            ->get(['idModuleRessource', 'taille', 'module_ressource_name', 'file_path', 'module_ressource_extension', 'mr.idModule']);

        $idCfp = Customer::idCustomer();

        $entreprises = $this->project->getEntrepriseByProject($projet->idTypeProjet, $projet->idProjet);

        $etpIds = [];

        foreach ($entreprises as $e) {
            $etpIds[] = $e->idEtp;
        }

        $purchaseOrders = DB::table('bon_commandes as bc')
            ->select([
                'bc.idBC',
                'bc.numero as numero_bc',
                'bc.idCfp',
                'bc.date as date_bc',
                'bc.montant as montant_bc',
                'bcd.idDocument as id_document_bc',
            ])
            ->join('invoices as i', 'bc.idDevis', '=', 'i.idInvoice')
            ->join('customers as cu', 'i.idEntreprise', '=', 'cu.idCustomer')
            ->join('bc_status as bcs', 'bc.idStatus', '=', 'bcs.idStatus')
            ->leftJoin('bc_documents as bcd', 'bc.idBC', '=', 'bcd.idBC')
            ->where('bc.idCfp', $idCfp)
            ->when($etpIds, function ($q) use ($etpIds) {
                $q->whereIn('i.idEntreprise', $etpIds);
            })
            ->get();

        $trainers = DB::table('v_formateur_cfps')
            ->select('idProjet', 'idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'email AS form_email', 'initialNameForm AS form_initial_name', 'form_phone')
            ->groupBy('idProjet', 'idFormateur', 'name', 'firstName', 'photoForm', 'email', 'initialNameForm')
            ->where('idProjet', $idProjet)
            ->get();

        $projectType = $this->getProjectType($idProjet);
        $learnersAdded = [];
        if ($projectType === 1) {
            $learnersAdded = $this->getLeanerByProjectIntra($idProjet);
        } elseif ($projectType === 2) {
            $learnersAdded = $this->getLeanerByProjectInter($idProjet);
        } elseif ($projectType === 4) {
            $learnersAdded = $this->getLeanerByProjectParticular($idProjet);
        }

        $materials = $this->getProjectMaterials($projet->idProjet);

        return response()->json([
            "idCfp" => $idCfp,
            "objectifs" => $objectifs,
            "prerequis" => $prerequis,
            "module_ressources" => $module_ressources,
            "restaurations" => $restaurations,
            "dossier" => $dossier,
            "imagesMomentums" => $imagesMomentums,
            "projet" => $projet,
            "villes" => $villes,
            "paiements" => $paiements,
            "seances" => $seances,
            "modules" => $modules,
            "totalSession" => $totalSession,
            "generalData" => $generalData,
            "apprenantInter" => $apprenantInter,
            "modalites" => $modalites,
            "evaluations" => $evaluations,
            "nbPlace" => $nbPlace,
            "place_available" => $place_available,
            "place_reserved" => $place_reserved,
            "nomDossier" => $nomDossier,
            "idDossier" => $idDossier,
            "deb" => $deb,
            "fin" => $fin,
            "purchase_orders" => $purchaseOrders,
            "entreprises" => $entreprises,
            "trainers" => $trainers,
            "learners" => $learnersAdded,
            "materials" => $materials
        ]);
    }


    public function getFraisByIdprojet($idProjet)
    {
        DB::enableQueryLog(); // Active le log SQL pour débugger les requêtes

        // ✅ Récupération unique du projet
        $projet = DB::table('v_projet_cfps')->where('idProjet', $idProjet)->first();

        if (!$projet) {
            return response()->json(
                [
                    "message" => "Erreur lors de la recuperation de donnee"
                ],
                400
            );
        }


        // ✅ Récupération des sessions et leur durée en une seule requête
        $seances = DB::table('v_seances')
            ->where('idProjet', $idProjet)
            ->orderBy('dateSeance', 'asc')
            ->get([
                'idSeance',
                'dateSeance',
                'heureDebut',
                'heureFin',
                'idProjet',
                'idModule',
                DB::raw("TIME_FORMAT(SEC_TO_TIME(TIME_TO_SEC(intervalle_raw)), '%H:%i') AS intervalle_raw")
            ]);

        // ✅ Obtenir les dates de sessions en une seule requête
        $datesSession = $seances->pluck('dateSeance');
        $deb = Carbon::parse($datesSession->first())->locale('fr')->translatedFormat('l j F Y');
        $fin = Carbon::parse($datesSession->last())->locale('fr')->translatedFormat('l j F Y');

        // ✅ Calcul du total des heures en une requête
        $totalSession = DB::table('v_seances')
            ->where('idProjet', $idProjet)
            ->selectRaw("IFNULL(TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))), '%H:%i'), '00:00') as sumHourSession")
            ->value('sumHourSession');

        // ✅ Regroupement des autres données générales
        $generalData = DB::table('v_seances')
            ->where('idProjet', $idProjet)
            ->groupBy('idProjet')
            ->selectRaw("COUNT(DISTINCT dateSeance) as countDate")
            ->first();

        //  Récupération des modules sans répéter les appels SQL
        $modules = DB::table('mdls')
            ->where('moduleName', '!=', 'Default module')
            ->where('idCustomer', Customer::idCustomer())
            ->orderBy('moduleName', 'asc')
            ->get(['idModule', 'moduleName AS module_name']);

        // ✅ Récupération des infos d’évaluation en une seule requête
        $evaluations = DB::table('v_evaluation_alls')
            ->where('idProjet', $idProjet)
            ->groupBy('idProjet')
            ->selectRaw("COUNT(idEmploye) as countNotationProjet, IFNULL(AVG(generalApreciate), 0) as noteGeneral")
            ->first() ?? (object) ['countNotationProjet' => 0, 'noteGeneral' => 0];


        // ✅ Récupération des places
        $placeData = DB::table('inters')
            ->where('idProjet', $idProjet)
            ->select(['nbPlace'])
            ->first();

        $nbPlace = $placeData->nbPlace ?? null;
        $place_available = $this->getPlaceAvailable($idProjet) ?? null;
        $place_reserved = $this->getNbPlaceReserved($idProjet) ?? null;

        // ✅ Récupération des autres données en une seule fois
        $apprenantInter = DB::table('v_list_apprenant_inter_added')->where('idProjet', $idProjet)->get();
        $villes = DB::table('villes')->get(['idVille', 'ville']);
        $paiements = DB::table('paiements')->get(['idPaiement', 'paiement']);
        $modalites = DB::table('modalites')->get(['idModalite', 'modalite']);

        // ✅ Récupération des restaurations avec JOIN
        $restaurations = DB::table('project_restaurations AS pr')
            ->join('restaurations AS rst', 'pr.idRestauration', '=', 'rst.idRestauration')
            ->where('idProjet', $idProjet)
            ->get(['pr.idRestauration', 'rst.typeRestauration', 'pr.paidBy']);

        // ✅ Récupération du dossier lié au projet
        $dossier = DB::table('dossiers AS d')
            ->join('projets AS p', 'd.idDossier', '=', 'p.idDossier')
            ->where('p.idProjet', $idProjet)
            ->first(['nomDossier', 'd.idDossier']);

        $nomDossier = $dossier->nomDossier ?? null;
        $idDossier = $dossier->idDossier ?? null;

        //  Récupération des images d'vénements
        $imagesMomentums = DB::table('images')
            ->where('idProjet', $idProjet)
            ->where('idTypeImage', 1)
            ->get(['nomImage', 'idImages']);

        // ✅ Récupération des ressources des modules en une seule requête
        $module_ressources = DB::table('module_ressources AS mr')
            ->join('mdls AS m', 'mr.idModule', 'm.idModule')
            ->join('projets AS p', 'p.idModule', 'm.idModule')
            ->where('p.idProjet', $idProjet)
            ->get(['idModuleRessource', 'taille', 'module_ressource_name', 'file_path', 'module_ressource_extension', 'mr.idModule']);

        $idCfp = Customer::idCustomer();

        return response()->json([
            "idCfp" => $idCfp,
            // "objectifs" => $objectifs,
            // "materiels" => $materiels,
            // "prerequis" => $prerequis,
            "module_ressources" => $module_ressources,
            "restaurations" => $restaurations,
            "dossier" => $dossier,
            "imagesMomentums" => $imagesMomentums,
            "projet" => $projet,
            "villes" => $villes,
            "paiements" => $paiements,
            "seances" => $seances,
            "modules" => $modules,
            "totalSession" => $totalSession,
            "generalData" => $generalData,
            "apprenantInter" => $apprenantInter,
            "modalites" => $modalites,
            "evaluations" => $evaluations,
            "nbPlace" => $nbPlace,
            "place_available" => $place_available,
            "place_reserved" => $place_reserved,
            "nomDossier" => $nomDossier,
            "idDossier" => $idDossier,
            "deb" => $deb,
            "fin" => $fin,
        ]);
    }

    public function getInformationBaseProject($projectId)
    {
        $project = DB::table('projets as P')
            ->join('mdls as M', 'M.idModule', '=', 'P.idModule')
            ->select(
                'P.idProjet',
                'P.project_reference',
                'P.project_type',
                'P.modalite',
                'P.project_description',
                'P.dateDebut',
                'P.dateFin',
                'M.moduleName'
            )
            ->where('P.idProjet', $projectId)
            ->first();

        return response()->json([
            'data' => $project
        ], 200);
    }


    public function getFiltre(string $status)
    {
        $validStatuses = ['Cloturé', 'En cours', 'Terminé'];

        if (!in_array($status, $validStatuses)) {
            // Retourne structure vide si status invalide
            return response()->json([
                'status' => 200,
                'filtre' => [
                    'type_projets' => [],
                    'lieux' => [],
                    'entreprises' => [],
                    'modules' => [],
                    'formateurs' => [],
                    'mois' => [],
                ],
            ]);
        }

        $projets = $this->getFilterByStatus($status);

        return response()->json([
            'status' => 200,
            'filtre' => [
                'type_projets' => $projets['type_projets'],
                'lieux' => $projets['lieux'],
                'entreprises' => $projets['entreprises'],
                'modules' => $projets['modules'],
                'formateurs' => $projets['formateurs'],
                'mois' => $projets['mois'],
            ],
        ]);
    }

    public function getPartAdded($idProjet)
    {
        $parts = DB::table('particulier_projet')
            ->join('users', 'particulier_projet.idParticulier', '=', 'users.id')
            ->where('particulier_projet.idProjet', $idProjet)
            ->select('users.id as idParticulier', 'users.name as part_name', 'users.firstName as part_firstname', 'users.email as part_email', 'users.photo as part_photo')
            ->where('idProjet', $idProjet)
            ->get();

        if (count($parts) <= 0) {
            return response([
                'status' => 204,
                'message' => "Aucun résultat trouvé !"
            ]);
        }

        return response([
            'status' => 200,
            'particuliers' => $parts
        ]);
    }

    private function getFilterByStatus(string $status): array
    {
        $lieux = collect();
        $entreprises = collect();
        $modules = collect();
        $formateursUniques = collect();
        $mois = collect();

        $type_projets = DB::table('type_projets')->get();

        $roleId = DB::table('role_users')
            ->where('user_id', Auth::id())
            ->value('role_id');
        $projets = match ($roleId) {
            3 => $this->project->indexFilter(Customer::idCustomer(), $status),
            5 => $this->project->indexFilterByFormateur($status, Auth::id()),
            4 => $this->project->indexFilterByApprenant($status, Auth::id()),
            default => [],
        };

        foreach ($projets as $pj) {
            $idProjet = $pj->idProjet;
            $idCfpInter = $pj->idCfp_inter;
            $etpName = match ($roleId) {
                3 =>  $this->getEtpProjectInter($idProjet, $idCfpInter),
                5 =>  $this->getEtpProjectInterByFormateur($idProjet, $idCfpInter),
                4 =>  $this->getEtpProjectInterByApprenant($idProjet, $idCfpInter),
                default => []
            };
            $formateurs = $this->getFormProject($idProjet);
            $lieux->push($pj->li_name);
            foreach ($etpName as $etp) {
                $entreprises->push((object)[
                    'idEtp' => $etp->idEtp,
                    'etp_name' => $etp->etp_name
                ]);
            }

            // Modules uniques
            $modules->push([
                'idModule' => $pj->idModule,
                'module_name' => $pj->module_name,
                'module_image' => $pj->module_image,
            ]);

            // Formateurs uniques
            foreach ($formateurs as $form) {
                $formateursUniques->push($form);
            }

            // Mois uniques basés sur dateDebut
            $date = Carbon::parse($pj->dateDebut);
            $mois->push([
                'id' => $date->format('Y-m'),
                'label' => $date->format('F Y')
            ]);
        }

        return [
            'type_projets' => $type_projets,
            'lieux' => $lieux->unique()->values()->all(),
            'entreprises' => $entreprises->unique('idEtp')->values()->all(),
            'modules' => $modules->unique('idModule')->values()->all(),
            'formateurs' => $formateursUniques->unique('idFormateur')->values()->all(),
            'mois' => $mois->unique('id')->values()->all(),
        ];
    }

    public static function getEtpProjectInter($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null || $idCfp_inter == 'null') {
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
    public static function getEtpProjectInterByFormateur($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null || $idCfp_inter == 'null') {
            $etp = DB::table('v_projet_cfps')
                ->select('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
                ->where('idProjet', $idProjet)
                // ->whereNot('idEtp', Customer::idCustomer())
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
    public static function getEtpProjectInterByApprenant($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null || $idCfp_inter == 'null') {
            $etp = DB::table('v_projet_cfps')
                ->select('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
                ->where('idProjet', $idProjet)
                // ->whereNot('idEtp', Customer::idCustomer())
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

    public function getFormProject($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
            ->groupBy('idFormateur', 'name', 'firstName', 'photoForm', 'initialNameForm')
            ->where('idProjet', $idProjet)->get();

        return $forms->toArray();
    }

    public function getApprenantProject($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null) {
            $apprs = DB::table('v_list_apprenants')
                ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_photo', 'emp_matricule', 'emp_phone', 'etp_name')
                ->where('idProjet', $idProjet)
                ->orderBy('emp_name', 'asc')
                ->count();
        } elseif ($idCfp_inter != null) {
            $apprs_inter = DB::table('v_list_apprenant_inter_added')
                ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_fonction', 'emp_email', 'emp_photo', 'emp_matricule', 'etp_name', 'idEtp')
                ->where('idProjet', $idProjet)
                ->orderBy('emp_name', 'asc')
                ->count();

            $parts = $this->getParticulierProject($idProjet, $idCfp_inter);

            $apprs = $apprs_inter + $parts;
        }

        return $apprs;
    }

    public function getCountProject()
    {
        $req = DB::table('role_users')
            ->select('role_id', 'user_id')
            ->where('user_id', Auth::user()->id)
            ->first();

        [$projetEnCours, $projetTermines, $projetClotures] = match ($req->role_id) {
            3 => [
                $this->project->countByStatus(Customer::idCustomer(), "En cours"),
                $this->project->countByStatus(Customer::idCustomer(), "Terminé"),
                $this->project->countByStatus(Customer::idCustomer(), "Cloturé"),
            ],
            5 => [
                $this->project->countByStatusByFormateur("En cours", Auth::user()->id),
                $this->project->countByStatusByFormateur("Termin", Auth::user()->id),
                $this->project->countByStatusByFormateur("Clotur", Auth::user()->id),
            ],
            4 => [
                $this->project->countByStatusByApprenant("En cours", Auth::user()->id),
                $this->project->countByStatusByApprenant("Terminé", Auth::user()->id),
                $this->project->countByStatusByApprenant("Cloturé", Auth::user()->id),
            ],
            default => throw new \Exception('Rôle non reconnu'),
        };


        return response()->json([
            'status' => 200,
            'projet_counts' => [
                'en_cours' => $projetEnCours,
                'termines' => $projetTermines,
                'clotures' => $projetClotures,
            ]
        ]);
    }

    public static function getApprListProjet($idProjet)
    {
        $apprIntras = DB::table('v_list_apprenants')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_photo', 'etp_name', 'emp_initial_name')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get()
            ->toArray();

        $apprenantInters = DB::table('v_list_apprenant_inter_added')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_photo', 'etp_name')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get()
            ->toArray();

        $apprs = array_merge($apprIntras, $apprenantInters);

        // return response()->json(['apprs' => $apprs]);
        return $apprs;
    }

    public function getDataPresence($idProjet)
    {
        //  Récupération unique du projet
        $projet = DB::table('v_projet_cfps')->where('idProjet', $idProjet)->first();

        if (!$projet) {
            abort(204, 'Projet non trouvé');
        }


        // ✅ Récupération des sessions et leur durée en une seule requête
        $seances = DB::table('v_seances')
            ->where('idProjet', $idProjet)
            ->orderBy('dateSeance', 'asc')
            ->get([
                'idSeance',
                'dateSeance',
                'heureDebut',
                'heureFin',
                'idProjet',
                'idModule',
                DB::raw("TIME_FORMAT(SEC_TO_TIME(TIME_TO_SEC(intervalle_raw)), '%H:%i') AS intervalle_raw")
            ]);

        // ✅ Obtenir les dates de sessions en une seule requête
        $datesSession = $seances->pluck('dateSeance');
        $dateDebut = DB::table('v_projet_cfps')->where('idProjet', $idProjet)->value('dateDebut');
        $dateFin = DB::table('v_projet_cfps')->where('idProjet', $idProjet)->value('dateFin');

        $deb = $datesSession->first() ? Carbon::parse($datesSession->first())->locale('fr')->translatedFormat('l j F Y') : Carbon::parse($dateDebut)->locale('fr')->translatedFormat('l j F Y');
        $fin = $datesSession->last() ? Carbon::parse($datesSession->last())->locale('fr')->translatedFormat('l j F Y') : Carbon::parse($dateFin)->locale('fr')->translatedFormat('l j F Y');

        //  Calcul du total des heures en une requête
        $totalSession = DB::table('v_seances')
            ->where('idProjet', $idProjet)
            ->selectRaw("IFNULL(TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))), '%H:%i'), '00:00') as sumHourSession")
            ->value('sumHourSession');

        //  Regroupement des autres donnes générales
        $generalData = DB::table('v_seances')
            ->where('idProjet', $idProjet)
            ->groupBy('idProjet')
            ->selectRaw("COUNT(DISTINCT dateSeance) as countDate")
            ->first();

        // ✅ Récupération des modules sans répéter les appels SQL
        $modules = DB::table('mdls')
            ->where('moduleName', '!=', 'Default module')
            // ->where('idCustomer', Customer::idCustomer())
            ->orderBy('moduleName', 'asc')
            ->get(['idModule', 'moduleName AS module_name']);


        // ✅ Récupération des autres données en une seule fois
        $apprenantInter = DB::table('v_list_apprenant_inter_added')->where('idProjet', $idProjet)->get();
        $villes = DB::table('villes')->get(['idVille', 'ville']);
        $paiements = DB::table('paiements')->get(['idPaiement', 'paiement']);
        $modalites = DB::table('modalites')->get(['idModalite', 'modalite']);

        // ✅ Récupération du dossier lié au projet
        $dossier = DB::table('dossiers AS d')
            ->join('projets AS p', 'd.idDossier', '=', 'p.idDossier')
            ->where('p.idProjet', $idProjet)
            ->first(['nomDossier', 'd.idDossier']);

        $nomDossier = $dossier->nomDossier ?? null;
        $idDossier = $dossier->idDossier ?? null;


        return response()->json([
            'projet' => $projet,
            'seances' => $seances,
            'date_debut' => $deb,
            'date_fin' => $fin,
            'total_session' => $totalSession,
            'general_data' => $generalData,
            'modules' => $modules,


            'apprenants' => $apprenantInter,
            'villes' => $villes,
            'paiements' => $paiements,
            'modalites' => $modalites,

            'dossier' => [
                'nomDossier' => $nomDossier,
                'idDossier' => $idDossier,
            ],
        ]);
    }

    public function getIdFormateur()
    {
        $allId = [];
        // $allId = DB::select("SELECT idFormateur FROM `v_formateur_cfps` WHERE idCfp = ? GROUP BY idFormateur ", [Customer::idCustomer()] );       
        $allId = DB::table('v_formateur_cfps')
            ->select('idFormateur')
            ->where('idCfp', Customer::idCustomer())
            ->groupBy('idFormateur')
            ->get();

        return $allId;
    }


    public function getDropdownItem()
    {
        $status = DB::table('v_projet_cfps')
            ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('module_name', '!=', 'Default module')
            ->where('project_is_trashed', 0)
            ->groupBy('project_status')
            ->orderBy('project_status', 'asc')
            ->get();

        $etps = DB::table('v_union_projets')
            ->select(DB::raw('COUNT(v_union_projets.idProjet) AS projet_nb'), DB::raw('COALESCE(idEtp, idEtp_inter) AS idEtp'), DB::raw('customers.customerName AS etp_name'), 'psc.idSubContractor', 'sub.customerName AS sub_name')
            ->leftJoin('entreprises', function ($join) {
                $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
            })
            ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
            ->leftJoin('project_forms', 'v_union_projets.idProjet', '=', 'project_forms.idProjet')
            ->leftJoin('formateurs', 'formateurs.idFormateur', '=', 'project_forms.idFormateur')
            ->leftJoin('project_sub_contracts AS psc', 'v_union_projets.idProjet', '=', 'psc.idProjet')
            ->leftJoin('customers AS sub', 'psc.idSubContractor', '=', 'sub.idCustomer')
            ->where('headDate', '!=', null)

            ->where(function ($query) {
                $query->where('idCfp_intra', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('psc.idSubContractor', Customer::idCustomer());
            })
            ->where('project_is_trashed', 0)
            ->groupBy(DB::raw('COALESCE(idEtp, idEtp_inter)'))
            ->orderBy('etp_name', 'asc')
            ->get();

        $filteredEtps = $etps->filter(function ($etp) {
            return $etp->etp_name !== null && $etp->etp_name !== '';
        });

        //fonction pour selectionne tous les id des formateurs...        
        $queries  = $this->getIdFormateur();
        $formateurs = [];
        foreach ($queries as $key => $queryInfo) {
            $query = DB::table('v_formateur_cfps')
                ->select('idProjet', 'idFormateur', 'project_type', 'idEtp', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
                ->where('idFormateur', $queryInfo->idFormateur)
                ->groupBy('idProjet', 'idFormateur', 'project_type', 'idEtp');
            $forms = $query->get();
            $project_count = count($forms);
            $formateurs[$key] = [
                'idFormateur' => $forms[0]->idFormateur,
                'form_name' => $forms[0]->form_name,
                'form_firstname' => $forms[0]->form_firstname,
                'projet_nb' => $project_count
            ];
        }
        //ajout mois...
        $months = DB::table('v_projet_cfps')
            ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
            //->select('headDate', 'headMonthDebut')
            ->groupBy('headDate')
            ->orderBy('dateDebut', 'asc')
            ->where('dateDebut', '!=', 'null')
            ->where('project_is_trashed', 0)
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            ->get();
        // dd($months);

        $types = DB::table('v_projet_cfps')
            ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('module_name', '!=', 'Default module')
            ->where('project_is_trashed', 0)
            ->orderBy('project_type', 'asc')
            ->groupBy('project_type')
            ->get();

        $periodePrev3 = DB::table('v_projet_cfps')
            ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('module_name', '!=', 'Default module')
            // ->where('p_id_periode', "prev_3_month")
            ->whereRaw("p_id_periode COLLATE utf8mb4_unicode_ci = 'prev_3_month'")
            ->where('project_is_trashed', 0)
            ->groupBy('p_id_periode')
            ->first();

        $periodePrev6 = DB::table('v_projet_cfps')
            ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('module_name', '!=', 'Default module')
            ->where('project_is_trashed', 0)
            ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
            ->first();

        $periodePrev12 = DB::table('v_projet_cfps')
            ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('module_name', '!=', 'Default module')
            ->where('project_is_trashed', 0)
            ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
            ->first();

        $periodeNext3 = DB::table('v_projet_cfps')
            ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('module_name', '!=', 'Default module')
            ->where('p_id_periode', "next_3_month")
            ->where('project_is_trashed', 0)
            ->groupBy('p_id_periode')
            ->first();

        $periodeNext6 = DB::table('v_projet_cfps')
            ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('module_name', '!=', 'Default module')
            ->where('project_is_trashed', 0)
            ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
            ->first();

        $periodeNext12 = DB::table('v_projet_cfps')
            ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('module_name', '!=', 'Default module')
            ->where('project_is_trashed', 0)
            ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
            ->first();

        $modules = DB::table('v_projet_cfps')
            ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('module_name', '!=', 'Default module')
            ->where('module_name', '!=', 'Default module')
            ->where('project_is_trashed', 0)
            ->orderBy('module_name', 'asc')
            ->groupBy('idModule', 'module_name')
            ->get();

        $villes = DB::table('v_projet_cfps')
            ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('module_name', '!=', 'Default module')
            ->where('project_is_trashed', 0)
            ->orderBy('ville', 'asc')
            ->groupBy('idVille', 'ville')
            ->get();

        $financements = DB::table('v_projet_cfps')
            ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('module_name', '!=', 'Default module')
            ->where('project_is_trashed', 0)
            ->orderBy('paiement', 'asc')
            ->groupBy('idPaiement', 'paiement')
            ->get();

        return response()->json([
            'status' => $status,
            'etps' => [...$filteredEtps],
            'types' => $types,
            'formateurs' => $formateurs,
            'periodePrev3' => $periodePrev3,
            'periodePrev6' => $periodePrev6,
            'periodePrev12' => $periodePrev12,
            'periodeNext3' => $periodeNext3,
            'periodeNext6' => $periodeNext6,
            'periodeNext12' => $periodeNext12,
            'modules' => $modules,
            'villes' => $villes,
            'financements' => $financements,
            'months' => $months,
        ]);
    }

    public function filterItem(Request $req)
    {
        $idStatus = explode(',', $req->idStatut);
        $idEtps = explode(',', $req->idEtp);
        $idTypes = explode(',', $req->idType);
        $idPeriodes = $req->idPeriode;
        $idModules = explode(',', $req->idModule);
        $idVilles = explode(',', $req->idVille);
        $idFinancements = explode(',', $req->idFinancement);
        $idFormateurs = explode(',', $req->idFormateur);
        $idMois = explode(',', $req->idMois);

        if ($req->idStatut == 'Terminé') {
            $order = 'desc';
        } else {
            $order = 'asc';
        }

        if ((isset($idEtps) && $idEtps[0] > 0) || (isset($idFormateurs) && $idFormateurs[0] > 0)) {
            $query = DB::table('v_union_projets')
                ->select('v_union_projets.idProjet', 'project_reference', 'dateDebut', 'dateFin', 'module_name', 'etp_name', 'ville', 'project_status', 'project_type', 'paiement', 'headDate', 'headMonthDebut', 'headMonthFin', 'headYear', 'headDayDebut', 'headDayFin', 'module_image', 'etp_logo', 'etp_initial_name', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'project_description', 'total_ht', 'total_ttc', 'idModule', 'psc.idSubContractor', 'sub.customerName AS sub_name', 'ce.customerName AS cfp_name', 'it.project_inter_privacy', 'total_ht_sub_contractor')
                ->join('project_forms', 'v_union_projets.idProjet', '=', 'project_forms.idProjet')
                ->join('formateurs', 'formateurs.idFormateur', '=', 'project_forms.idFormateur')
                ->leftJoin('project_sub_contracts AS psc', 'v_union_projets.idProjet', '=', 'psc.idProjet')
                ->leftJoin('customers AS sub', 'psc.idSubContractor', '=', 'sub.idCustomer')
                ->leftJoin('customers AS ce', 'v_union_projets.idCfp_intra', '=', 'ce.idCustomer')
                ->leftJoin('inters AS it', 'v_union_projets.idProjet', '=', 'it.idProjet')
                ->where(function ($query) {
                    $query->where('idCfp_intra', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('psc.idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->groupBy('v_union_projets.idProjet')
                ->orderBy('dateDebut', $order);
        } else {
            $query = DB::table('v_projet_cfps')
                ->select('idProjet', 'dateDebut', 'project_reference', 'idEtp', 'dateFin', 'cfp_name', 'module_name', 'etp_name', 'ville', 'project_status', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'idSalle', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin', 'total_ht_sub_contractor')
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->groupBy('idProjet')
                ->orderBy('dateDebut', $order);
        }

        $queryDate = DB::table('v_projet_cfps')
            ->select('headDate', 'headMonthDebut')
            ->groupBy('headDate')
            ->orderBy('dateDebut', $order)
            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer());
            })
            ->where('module_name', '!=', 'Default module')
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('project_is_trashed', 0);

        // Fonction pour appliquer les conditions communes à query et queryDate
        function applyCommonFilters($query, $idStatus, $idEtps, $idTypes, $idModules, $idVilles, $idFinancements, $idPeriodes)
        {
            // Appliquer le statut des projets
            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
            }

            // Appliquer les autres filtres si définis
            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
            }

            if ($idTypes[0] != null) {
                $query->whereIn('project_type', $idTypes);
            }

            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
            }

            if ($idVilles[0] != null) {
                $query->whereIn('idVille', $idVilles);
            }

            if ($idFinancements[0] != null) {
                $query->whereIn('idPaiement', $idFinancements);
            }

            // Appliquer les périodes si définies
            if ($idPeriodes != null) {
                switch ($idPeriodes) {
                    case 'prev_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        break;
                    case 'prev_6_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                        break;
                    case 'prev_12_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                        break;
                    case 'next_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        break;
                    case 'next_6_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        break;
                    case 'next_12_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                        break;
                    default:
                        $query->where('p_id_periode', $idPeriodes);
                        break;
                }
            }
        }

        if ($idStatus[0] != null) {
            // Commencer la requête principale
            $query->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->orWhere('idCfp_inter', Customer::idCustomer())
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('project_status', $idStatus);

            // Appliquer les conditions communes
            applyCommonFilters($query, $idStatus, $idEtps, $idTypes, $idModules, $idVilles, $idFinancements, $idPeriodes);

            // Requête pour récupérer les dates
            $queryDate = DB::table('v_projet_cfps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0);

            // Appliquer les conditions communes à queryDate aussi
            applyCommonFilters($queryDate, $idStatus, $idEtps, $idTypes, $idModules, $idVilles, $idFinancements, $idPeriodes);
        }

        /****** MODIFIE 2 ******/

        if ($idEtps[0] != null) {

            $query->where(function ($expr) use ($idEtps) {
                $expr->wherein('idEtp', $idEtps)
                    ->orWherein('idEtp_inter', $idEtps);
            });
            $projectDates = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                //->whereIn('idEtp', $idEtps)
                ->get();
        }

        // Requête principale
        if ($idTypes[0] != null) {
            $query->whereIn('project_type', $idTypes);

            $queryDate = DB::table('v_projet_cfps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0);

            // Appliquer les conditions communes à query et queryDate
            applyCommonFilters($query, $idStatus, $idEtps, $idTypes, $idModules, $idVilles, $idFinancements, $idPeriodes);
            applyCommonFilters($queryDate, $idStatus, $idEtps, $idTypes, $idModules, $idVilles, $idFinancements, $idPeriodes);
        }

        if ($idPeriodes != null) {
            switch ($idPeriodes) {
                case 'prev_3_month':
                    $query->where('p_id_periode', $idPeriodes);

                    $queryDate = DB::table('v_projet_cfps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', $order)
                        ->where(function ($query) {
                            $query->where('idCfp', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('module_name', '!=', 'Default module')
                        ->where('project_is_trashed', 0)
                        ->where('p_id_periode', $idPeriodes);

                    break;
                case 'prev_6_month':
                    $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                    $queryDate = DB::table('v_projet_cfps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', $order)
                        ->where(function ($query) {
                            $query->where('idCfp', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('module_name', '!=', 'Default module')
                        ->where('project_is_trashed', 0)
                        ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                    break;
                case 'prev_12_month':
                    $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                    $queryDate = DB::table('v_projet_cfps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', $order)
                        ->where(function ($query) {
                            $query->where('idCfp', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('module_name', '!=', 'Default module')
                        ->where('project_is_trashed', 0)
                        ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                    break;
                case 'next_3_month':
                    $query->where('p_id_periode', $idPeriodes);

                    $queryDate = DB::table('v_projet_cfps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', $order)
                        ->where(function ($query) {
                            $query->where('idCfp', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('module_name', '!=', 'Default module')
                        ->where('project_is_trashed', 0)
                        ->where('p_id_periode', $idPeriodes);

                    break;
                case 'next_6_month':
                    $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);

                    $queryDate = DB::table('v_projet_cfps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', $order)
                        ->where(function ($query) {
                            $query->where('idCfp', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('module_name', '!=', 'Default module')
                        ->where('project_is_trashed', 0)
                        ->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                    break;
                case 'next_12_month':
                    $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                    $queryDate = DB::table('v_projet_cfps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', $order)
                        ->where(function ($query) {
                            $query->where('idCfp', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('module_name', '!=', 'Default module')
                        ->where('project_is_trashed', 0)
                        ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                    break;

                default:
                    $query->where('p_id_periode', $idPeriodes);

                    $queryDate = DB::table('v_projet_cfps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', $order)
                        ->where(function ($query) {
                            $query->where('idCfp', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('module_name', '!=', 'Default module')
                        ->where('project_is_trashed', 0)
                        ->where('p_id_periode', $idPeriodes);
                    break;
            }

            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
                $queryDate->whereIn('project_status', $idStatus);
            }

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
                $queryDate->whereIn('idEtp', $idEtps);
            }

            if ($idTypes[0] != null) {
                $query->whereIn('project_type', $idTypes);
                $queryDate->whereIn('project_type', $idTypes);
            }

            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
                $queryDate->whereIn('idModule', $idModules);
            }

            if ($idVilles[0] != null) {
                $query->whereIn('idVille', $idVilles);
                $queryDate->whereIn('idVille', $idVilles);
            }

            if ($idFinancements[0] != null) {
                $query->whereIn('idPaiement', $idFinancements);
                $queryDate->whereIn('idPaiement', $idFinancements);
            }
        }

        function applyFilters($query, $idStatus, $idEtps, $idTypes, $idPeriodes, $idVilles, $idFinancements)
        {
            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
            }

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
            }

            if ($idTypes[0] != null) {
                $query->whereIn('project_type', $idTypes);
            }

            if ($idPeriodes != null) {
                switch ($idPeriodes) {
                    case 'prev_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        break;
                    case 'prev_6_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                        break;
                    case 'prev_12_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                        break;
                    case 'next_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        break;
                    case 'next_6_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        break;
                    case 'next_12_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                        break;
                    default:
                        $query->where('p_id_periode', $idPeriodes);
                        break;
                }
            }

            if ($idVilles[0] != null) {
                $query->whereIn('idVille', $idVilles);
            }

            if ($idFinancements[0] != null) {
                $query->whereIn('idPaiement', $idFinancements);
            }
        }

        if ($idModules[0] != null) {
            $query->whereIn('idModule', $idModules);
            $queryDate = DB::table('v_projet_cfps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('idModule', $idModules);

            applyFilters($query, $idStatus, $idEtps, $idTypes, $idPeriodes, $idVilles, $idFinancements);
            applyFilters($queryDate, $idStatus, $idEtps, $idTypes, $idPeriodes, $idVilles, $idFinancements);
        }


        if ($idVilles[0] != null) {
            $query->whereIn('idVille', $idVilles);

            $queryDate = DB::table('v_projet_cfps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('idVille', $idVilles);

            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
                $queryDate->whereIn('project_status', $idStatus);
            }

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
                $queryDate->whereIn('idEtp', $idEtps);
            }

            if ($idTypes[0] != null) {
                $query->whereIn('project_type', $idTypes);
                $queryDate->whereIn('project_type', $idTypes);
            }

            if ($idPeriodes != null) {
                switch ($idPeriodes) {
                    case 'prev_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'prev_6_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                        break;
                    case 'prev_12_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                        break;
                    case 'next_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'next_6_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        break;
                    case 'next_12_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                        break;

                    default:
                        $query->where('p_id_periode', $idPeriodes);

                        $queryDate = DB::table('v_projet_cfps')
                            ->select('headDate', 'headMonthDebut')
                            ->groupBy('headDate')
                            ->orderBy('dateDebut', $order)
                            ->where(function ($query) {
                                $query->where('idCfp', Customer::idCustomer())
                                    ->orWhere('idCfp_inter', Customer::idCustomer());
                            })
                            // ->where('headYear', Carbon::now()->format('Y'))
                            ->where('module_name', '!=', 'Default module')
                            ->where('project_is_trashed', 0)
                            ->where('p_id_periode', $idPeriodes);
                        break;
                }
            }

            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
                $queryDate->whereIn('idModule', $idModules);
            }

            if ($idFinancements[0] != null) {
                $query->whereIn('idPaiement', $idFinancements);
                $queryDate->whereIn('idPaiement', $idFinancements);
            }
        }

        if ($idFinancements[0] != null) {
            $query->whereIn('idPaiement', $idFinancements);

            $queryDate = DB::table('v_projet_cfps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('idPaiement', $idFinancements);

            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
                $queryDate->whereIn('project_status', $idStatus);
            }

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
                $queryDate->whereIn('idEtp', $idEtps);
            }

            if ($idTypes[0] != null) {
                $query->whereIn('project_type', $idTypes);
                $queryDate->whereIn('project_type', $idTypes);
            }

            if ($idPeriodes != null) {
                switch ($idPeriodes) {
                    case 'prev_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'prev_6_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                        break;
                    case 'prev_12_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                        break;
                    case 'next_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'next_6_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        break;
                    case 'next_12_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                        break;

                    default:
                        $query->where('p_id_periode', $idPeriodes);

                        $queryDate = DB::table('v_projet_cfps')
                            ->select('headDate', 'headMonthDebut')
                            ->groupBy('headDate')
                            ->orderBy('dateDebut', $order)
                            ->where(function ($query) {
                                $query->where('idCfp', Customer::idCustomer())
                                    ->orWhere('idCfp_inter', Customer::idCustomer());
                            })
                            // ->where('headYear', Carbon::now()->format('Y'))
                            ->where('module_name', '!=', 'Default module')
                            ->where('project_is_trashed', 0)
                            ->where('p_id_periode', $idPeriodes);
                        break;
                }
            }

            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
                $queryDate->whereIn('idModule', $idModules);
            }

            if ($idVilles[0] != null) {
                $query->whereIn('idVille', $idVilles);
                $queryDate->whereIn('idVille', $idVilles);
            }
        }
        /**MODIFIE 2 */
        if ($idFormateurs[0] != null) {
            $query->whereIn('formateurs.idFormateur', $idFormateurs)
                ->groupBy('idProjet');


            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
                $queryDate->whereIn('project_status', $idStatus);
            }

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
                $queryDate->whereIn('idEtp', $idEtps);
            }

            if ($idTypes[0] != null) {
                $query->whereIn('project_type', $idTypes);
                $queryDate->whereIn('project_type', $idTypes);
            }

            if ($idPeriodes != null) {
                switch ($idPeriodes) {
                    case 'prev_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'prev_6_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                        break;
                    case 'prev_12_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                        break;
                    case 'next_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'next_6_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        break;
                    case 'next_12_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                        break;

                    default:
                        $query->where('p_id_periode', $idPeriodes);

                        $queryDate = DB::table('v_projet_cfps')
                            ->select('headDate', 'headMonthDebut')
                            ->groupBy('headDate')
                            ->orderBy('dateDebut', $order)
                            ->where(function ($query) {
                                $query->where('idCfp', Customer::idCustomer())
                                    ->orWhere('idCfp_inter', Customer::idCustomer());
                            })
                            // ->where('headYear', Carbon::now()->format('Y'))
                            ->where('module_name', '!=', 'Default module')
                            ->where('project_is_trashed', 0)
                            ->where('p_id_periode', $idPeriodes);
                        break;
                }
            }


            $projectDates = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                //->whereIn('idPaiement', $idFinancements)
                ->get();
        }

        /******** NOUVEAU ITEM **** */
        if ($idMois[0] != null) {
            $query->whereIn(DB::raw('MONTH(dateDebut)'), $idMois)
                ->groupBy('idProjet');


            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
                $queryDate->whereIn('project_status', $idStatus);
            }

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
                $queryDate->whereIn('idEtp', $idEtps);
            }

            if ($idTypes[0] != null) {
                $query->whereIn('project_type', $idTypes);
                $queryDate->whereIn('project_type', $idTypes);
            }

            if ($idPeriodes != null) {
                switch ($idPeriodes) {
                    case 'prev_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'prev_6_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                        break;
                    case 'prev_12_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                        break;
                    case 'next_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'next_6_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        break;
                    case 'next_12_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                        break;

                    default:
                        $query->where('p_id_periode', $idPeriodes);

                        $queryDate = DB::table('v_projet_cfps')
                            ->select('headDate', 'headMonthDebut')
                            ->groupBy('headDate')
                            ->orderBy('dateDebut', $order)
                            ->where(function ($query) {
                                $query->where('idCfp', Customer::idCustomer())
                                    ->orWhere('idCfp_inter', Customer::idCustomer());
                            })
                            // ->where('headYear', Carbon::now()->format('Y'))
                            ->where('module_name', '!=', 'Default module')
                            ->where('project_is_trashed', 0)
                            ->where('p_id_periode', $idPeriodes);
                        break;
                }
            }


            $projectDates = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')

                ->get();
            //dd($projectDates);
        }

        $projects = $query->get();
        $projectDates = $queryDate->get();

        $projets = [];
        $projets = [];

        foreach ($projects as $project) {
            // Calcul du prix une seule fois
            $pricing = isset($project->idSubContractor) && $project->idSubContractor == Customer::idCustomer()
                ? $project->total_ht_sub_contractor
                : $project->total_ht;

            // Récupération des informations qui sont réutilisées plusieurs fois
            $idProjet = $project->idProjet;
            $idCfpInter = $project->idCfp_inter;

            // Appels aux méthodes
            $nomDossier = $this->getNomDossier($idProjet);
            $nombreDocument = $this->getNombreDocument($idProjet);
            $sessionCount = $this->getSessionProject($idProjet);
            $formateurs = $this->getFormProject($idProjet);
            $apprenantCount = $this->getApprenantProject($idProjet, $idCfpInter);
            $totalPrice = $this->getProjectTotalPrice($idProjet);
            $sessionHour = $this->getSessionHour($idProjet);
            $note = $this->getNote($idProjet);
            $partCount = $this->getParticulierProject($idProjet, $idCfpInter);
            $etpName = $this->getEtpProjectInter($idProjet, $idCfpInter);
            $restaurations = $this->getRestauration($idProjet);
            $checkEmg = $this->checkEmg($idProjet);
            $checkEval = $this->checkEval($idProjet);
            $avgBefore = $this->averageEvalApprenant($idProjet)->avg_avant;
            $avgAfter = $this->averageEvalApprenant($idProjet)->avg_apres;
            $apprs = $this->getApprListProjet($idProjet);
            $isPaid = $this->projectIsPaid($idProjet);

            // Ajout des données dans le tableau
            $projets[] = [
                'dossier' => $nomDossier,
                'project_reference' => $project->project_reference,
                'nbDocument' => $nombreDocument,
                'seanceCount' => $sessionCount,
                'formateurs' => $formateurs,
                'apprCount' => $apprenantCount,
                'projectTotalPrice' => $totalPrice,
                'idProjet' => $idProjet,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'etp_name' => $etpName,
                'ville' => $project->ville,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'paiement' => $project->paiement,
                'modalite' => $project->modalite,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'etp_initial_name' => $project->etp_initial_name,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'ville' => $project->ville,
                'headYear' => $project->headYear,
                'headMonthDebut' => $project->headMonthDebut,
                'headMonthFin' => $project->headMonthFin,
                'headDayDebut' => $project->headDayDebut,
                'headDayFin' => $project->headDayFin,
                'project_description' => $project->project_description,
                'total_ht' => $this->utilService->formatPrice($pricing),
                'total_ttc' => $project->total_ttc,
                'totalSessionHour' => $sessionHour,
                'general_note' => $note,
                'idModule' => $project->idModule,
                'restaurations' => $restaurations,
                'apprs' => $apprs,
                'sub_name' => $project->sub_name,
                'idSubContractor' => $project->idSubContractor,
                'idUser' => Customer::idCustomer(),
                'cfp_name' => $project->cfp_name,
                'project_inter_privacy' => $project->project_inter_privacy,
                'idCfp_inter' => $idCfpInter,
                'isPaid' => $isPaid,
            ];
        }


        return response()->json([
            'status' => 200,
            'projets' => $projets,
            'projectDates' => $projectDates
        ]);
    }


    public function filterItems(Request $req)
    {
        $idStatus = explode(',', $req->idStatut);
        $idEtps = explode(',', $req->idEtp);
        $idTypes = explode(',', $req->idType);
        $idPeriodes = $req->idPeriode;
        $idModules = explode(',', $req->idModule);
        $idVilles = explode(',', $req->idVille);
        $idFinancements = explode(',', $req->idFinancement);
        $idFormateurs = explode(',', $req->idFormateur);
        $idMois = explode(',', $req->idMois);
        $searchTerm = $req->search;

        if ($req->idStatut == 'Terminé') {
            $order = 'desc';
        } else {
            $order = 'asc';
        }

        if ((isset($idEtps) && $idEtps[0] > 0) || (isset($idFormateurs) && $idFormateurs[0] > 0)) {
            $query = DB::table('v_union_projets')
                ->select(
                    'v_union_projets.idProjet',
                    'v_union_projets.project_reference',
                    'v_union_projets.dateDebut',
                    'v_union_projets.dateFin',
                    'v_union_projets.module_name',
                    'v_union_projets.etp_name',
                    'v_union_projets.ville',
                    'v_union_projets.project_status',
                    'v_union_projets.project_type',
                    'v_union_projets.idPaiement',
                    'v_union_projets.paiement',
                    'v_union_projets.headDate',
                    'v_union_projets.headMonthDebut',
                    'v_union_projets.headMonthFin',
                    'v_union_projets.headYear',
                    'v_union_projets.headDayDebut',
                    'v_union_projets.headDayFin',
                    'v_union_projets.module_image',
                    'v_union_projets.etp_logo',
                    'v_union_projets.etp_initial_name',
                    'v_union_projets.salle_name',
                    'v_union_projets.salle_quartier',
                    'v_union_projets.salle_code_postal',
                    'v_union_projets.ville',
                    'v_union_projets.idCfp_inter',
                    'v_union_projets.modalite',
                    'v_union_projets.project_description',
                    'v_union_projets.total_ht',
                    'v_union_projets.total_ttc',
                    'v_union_projets.idModule',
                    'psc.idSubContractor',
                    'sub.customerName AS sub_name',
                    'ce.customerName AS cfp_name',
                    'it.project_inter_privacy',
                    'v_union_projets.total_ht_sub_contractor'
                )
                ->leftJoin('project_forms', 'v_union_projets.idProjet', '=', 'project_forms.idProjet')
                ->leftJoin('formateurs as f', 'f.idFormateur', '=', 'project_forms.idFormateur')
                ->leftJoin('project_sub_contracts AS psc', 'v_union_projets.idProjet', '=', 'psc.idProjet')
                ->leftJoin('customers AS sub', 'psc.idSubContractor', '=', 'sub.idCustomer')
                ->leftJoin('customers AS ce', 'v_union_projets.idCfp_intra', '=', 'ce.idCustomer')
                ->leftJoin('inters AS it', 'v_union_projets.idProjet', '=', 'it.idProjet')
                ->where(function ($query) {
                    $query->where('idCfp_intra', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('psc.idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->groupBy('v_union_projets.idProjet')
                ->orderBy('dateDebut', $order);
        } else {
            $query = DB::table('v_projet_cfps')
                ->select(
                    'v_projet_cfps.idProjet',
                    'v_projet_cfps.dateDebut',
                    'v_projet_cfps.project_reference',
                    'v_projet_cfps.idEtp',
                    'v_projet_cfps.dateFin',
                    'v_projet_cfps.cfp_name',
                    'v_projet_cfps.module_name',
                    'v_projet_cfps.etp_name',
                    'v_projet_cfps.ville',
                    'v_projet_cfps.project_status',
                    'v_projet_cfps.project_description',
                    'v_projet_cfps.project_type',
                    'v_projet_cfps.paiement',
                    DB::raw('DATE_FORMAT(v_projet_cfps.dateDebut, "%M, %Y") AS headDate'),
                    'v_projet_cfps.module_image',
                    'v_projet_cfps.etp_logo',
                    'v_projet_cfps.etp_initial_name',
                    'v_projet_cfps.idSalle',
                    'v_projet_cfps.salle_name',
                    'v_projet_cfps.salle_quartier',
                    'v_projet_cfps.salle_code_postal',
                    'v_projet_cfps.ville',
                    'v_projet_cfps.idCfp_inter',
                    'v_projet_cfps.modalite',
                    'v_projet_cfps.total_ht',
                    'v_projet_cfps.total_ttc',
                    'v_projet_cfps.idModule',
                    'v_projet_cfps.project_inter_privacy',
                    'v_projet_cfps.sub_name',
                    'v_projet_cfps.idSubContractor',
                    'v_projet_cfps.idCfp',
                    'v_projet_cfps.headYear',
                    'v_projet_cfps.headMonthDebut',
                    'v_projet_cfps.headMonthFin',
                    'v_projet_cfps.headDayDebut',
                    'v_projet_cfps.headDayFin',
                    'v_projet_cfps.total_ht_sub_contractor'
                )
                ->where(function ($query) {
                    $query->where('v_projet_cfps.idCfp', Customer::idCustomer())
                        ->orWhere('v_projet_cfps.idCfp_inter', Customer::idCustomer())
                        ->orWhere('v_projet_cfps.idSubContractor', Customer::idCustomer());
                })
                ->where('v_projet_cfps.module_name', '!=', 'Default module')
                ->where('v_projet_cfps.project_is_trashed', 0)
                ->groupBy('v_projet_cfps.idProjet')
                ->orderBy('v_projet_cfps.dateDebut', $order);

            if ($idFormateurs[0] != null) {
                $query->leftJoin('project_forms', 'v_projet_cfps.idProjet', '=', 'project_forms.idProjet')
                    ->leftJoin('formateurs as f', 'f.idFormateur', '=', 'project_forms.idFormateur') // Utiliser l'alias 'f'
                    ->whereIn('project_forms.idFormateur', $idFormateurs); // Utiliser project_forms.idFormateur au lieu de formateurs.idFormateur
            }
        }

        $searchTerm = trim($searchTerm);
        if ($searchTerm !== '') {
            $like = '%' . mb_strtolower($searchTerm, 'UTF-8') . '%';

            $query->where(function ($q) use ($like) {
                $q->whereRaw('LOWER(project_reference) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(module_name) LIKE ?', [$like]);
            });
        }

        if ($idStatus[0] != null) {
            $query->whereIn('project_status', $idStatus);

            $etps = DB::table('v_union_projets')
                ->select(DB::raw('COUNT(v_union_projets.idProjet) AS projet_nb'), DB::raw('COALESCE(idEtp, idEtp_inter) AS idEtp'), DB::raw('customers.customerName AS etp_name'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->leftJoin('project_sub_contracts AS psc', 'v_union_projets.idProjet', '=', 'psc.idProjet')
                ->leftJoin('customers AS sub', 'psc.idSubContractor', '=', 'sub.idCustomer')
                ->leftJoin('customers AS ce', 'v_union_projets.idCfp_intra', '=', 'ce.idCustomer')
                ->where(function ($query) {
                    $query->where('idCfp_intra', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->whereIn('project_status', $idStatus)
                ->groupBy(DB::raw('COALESCE(idEtp, idEtp_inter)'))
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_projet_cfps as v')
                ->select('v.project_type', DB::raw('COUNT(v.idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('v.idCfp', Customer::idCustomer())
                        ->orWhere('v.idCfp_inter', Customer::idCustomer())
                        ->orWhere('v.idSubContractor', Customer::idCustomer());
                })
                ->where('v.project_is_trashed', 0)
                ->whereIn('v.project_status', $idStatus)
                ->groupBy('v.project_type')
                ->orderBy('v.project_type', 'asc')
                ->get();


            $periodePrev3 = DB::table('v_projet_cfps')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "prev_3_month")
                ->where('project_is_trashed', 0)
                ->whereIn('project_status', $idStatus)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_projet_cfps')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('project_status', $idStatus)
                ->first();

            $periodePrev12 = DB::table('v_projet_cfps')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('project_status', $idStatus)
                ->first();

            $periodeNext3 = DB::table('v_projet_cfps')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "next_3_month")
                ->where('project_is_trashed', 0)
                ->whereIn('project_status', $idStatus)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_projet_cfps')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->whereIn('project_status', $idStatus)
                ->first();

            $periodeNext12 = DB::table('v_projet_cfps')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->whereIn('project_status', $idStatus)
                ->first();

            $modules = DB::table('v_projet_cfps')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('project_status', $idStatus)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_projet_cfps')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('project_status', $idStatus)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            $financements = DB::table('v_projet_cfps')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('project_status', $idStatus)
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();

            $forms = DB::table('v_formateur_cfps')
                ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idCfp', Customer::idCustomer())
                ->whereIn('project_status', $idStatus)
                ->groupBy('idFormateur')
                ->get();
            //dd($forms);
            $months = DB::table('v_projet_cfps')
                ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('project_status', $idStatus)
                ->get();

            $projectDates = DB::table('v_projet_cfps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('project_status', $idStatus)
                ->get();
        }

        if ($idEtps[0] != null) {
            $query->where(function ($expr) use ($idEtps) {
                $expr->wherein('idEtp', $idEtps)
                    ->orWherein('idEtp_inter', $idEtps);
            });
            //dd($query);    
            $status = DB::table('v_union_projets')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                //->whereIn('idEtp', $idEtps)
                //projets(Intra et Inter)
                //->where('idTypeprojet','!=',3)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();

            //dd($status);
            $types = DB::table('v_union_projets')

                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            // dd($types);

            $periodePrev3 = DB::table('v_union_projets')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "prev_3_month")
                //->whereIn('idEtp', $idEtps)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('idEtp', $idEtps)
                ->first();

            $periodePrev12 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('idEtp', $idEtps)
                ->first();

            $periodeNext3 = DB::table('v_union_projets')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "next_3_month")
                // ->whereIn('idEtp', $idEtps)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_union_projets')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                //->whereIn('idEtp', $idEtps)
                ->first();

            $periodeNext12 = DB::table('v_union_projets')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                //->whereIn('idEtp', $idEtps)
                ->first();

            $modules = DB::table('v_union_projets')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_union_projets')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            $financements = DB::table('v_union_projets')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();

            $months = DB::table('v_union_projets')
                ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))

                ->orderBy('dateDebut', $order)
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->groupBy('headDate')
                ->get();
            // dd($months);

            $forms = DB::table('v_formateur_cfps')
                ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer());
                    // ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->groupBy('idFormateur')
                //->orderBy('form_firstname','asc')
                ->get();


            $projectDates = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) use ($idEtps) {
                    $query->whereIn('idEtp', $idEtps)
                        ->orWhereIn('idEtp_inter', $idEtps);
                })
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                //->whereIn('idEtp', $idEtps)
                ->get();
            //dd($projectDates);
        }

        if ($idTypes[0] != null) {
            $query->whereIn('project_type', $idTypes);

            $status = DB::table('v_projet_cfps')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->whereIn('project_type', $idTypes)
                ->where('project_is_trashed', 0)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();


            $etps = DB::table('v_union_projets')
                ->select(DB::raw('COUNT(idProjet) AS projet_nb'), DB::raw('COALESCE(idEtp, idEtp_inter) AS idEtp'), DB::raw('customers.customerName AS etp_name'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->whereIn('project_type', $idTypes)
                ->groupBy(DB::raw('COALESCE(idEtp, idEtp_inter)'))
                ->orderBy('etp_name', 'asc')
                ->get();

            $periodePrev3 = DB::table('v_projet_cfps')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "prev_3_month")
                ->where('project_is_trashed', 0)
                ->whereIn('project_type', $idTypes)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_projet_cfps')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('project_type', $idTypes)
                ->first();

            $periodePrev12 = DB::table('v_projet_cfps')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('project_type', $idTypes)
                ->first();

            $periodeNext3 = DB::table('v_projet_cfps')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "next_3_month")
                ->where('project_is_trashed', 0)
                ->whereIn('project_type', $idTypes)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_projet_cfps')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->whereIn('project_type', $idTypes)
                ->first();

            $periodeNext12 = DB::table('v_projet_cfps')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->whereIn('project_type', $idTypes)
                ->first();

            $modules = DB::table('v_projet_cfps')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('project_type', $idTypes)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_projet_cfps')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('project_type', $idTypes)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            $financements = DB::table('v_projet_cfps')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('project_type', $idTypes)
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();


            $forms = DB::table('v_formateur_cfps')
                ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idCfp', Customer::idCustomer())
                ->whereIn('project_type', $idTypes)
                ->groupBy('idFormateur')
                ->get();

            $months = DB::table('v_projet_cfps')
                ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('project_type', $idTypes)
                ->get();

            //dd($months);

            $projectDates = DB::table('v_projet_cfps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('project_type', $idTypes)
                ->get();
        }

        if ($idPeriodes != null) {
            switch ($idPeriodes) {
                case 'prev_3_month':
                    $query->where('p_id_periode', $idPeriodes);

                    $projectDates = DB::table('v_projet_cfps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', $order)
                        ->where(function ($query) {
                            $query->where('idCfp', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('module_name', '!=', 'Default module')
                        ->where('p_id_periode', $idPeriodes)
                        ->where('project_is_trashed', 0)
                        ->get();

                    break;
                case 'prev_6_month':
                    $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                    $projectDates = DB::table('v_projet_cfps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', $order)
                        ->where(function ($query) {
                            $query->where('idCfp', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('module_name', '!=', 'Default module')
                        ->where('project_is_trashed', 0)
                        ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                        ->get();

                    break;
                case 'prev_12_month':
                    $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                    $projectDates = DB::table('v_projet_cfps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', $order)
                        ->where(function ($query) {
                            $query->where('idCfp', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('module_name', '!=', 'Default module')
                        ->where('project_is_trashed', 0)
                        ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                        ->get();

                    break;
                case 'next_3_month':
                    $query->where('p_id_periode', $idPeriodes);

                    $projectDates = DB::table('v_projet_cfps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', $order)
                        ->where(function ($query) {
                            $query->where('idCfp', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('module_name', '!=', 'Default module')
                        ->where('p_id_periode', $idPeriodes)
                        ->where('project_is_trashed', 0)
                        ->get();

                    break;
                case 'next_6_month':
                    $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);

                    $projectDates = DB::table('v_projet_cfps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', $order)
                        ->where(function ($query) {
                            $query->where('idCfp', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('module_name', '!=', 'Default module')
                        ->where('project_is_trashed', 0)
                        ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                        ->get();
                    break;
                case 'next_12_month':
                    $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                    $projectDates = DB::table('v_projet_cfps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', $order)
                        ->where(function ($query) {
                            $query->where('idCfp', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('module_name', '!=', 'Default module')
                        ->where('project_is_trashed', 0)
                        ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                        ->get();

                    break;

                default:
                    $query->where('p_id_periode', $idPeriodes);

                    $projectDates = DB::table('v_projet_cfps')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate')
                        ->orderBy('dateDebut', $order)
                        ->where(function ($query) {
                            $query->where('idCfp', Customer::idCustomer())
                                ->orWhere('idCfp_inter', Customer::idCustomer());
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('module_name', '!=', 'Default module')
                        ->where('p_id_periode', $idPeriodes)
                        ->where('project_is_trashed', 0)
                        ->get();

                    break;
            }

            $status = DB::table('v_projet_cfps')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('p_id_periode', $idPeriodes)
                ->where('project_is_trashed', 0)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();


            $etps = DB::table('v_union_projets')
                ->select(DB::raw('COALESCE(idEtp, idEtp_inter) AS idEtp'), DB::raw('customers.customerName AS etp_name'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->where('p_id_periode', $idPeriodes)
                ->groupBy(DB::raw('COALESCE(idEtp, idEtp_inter)'))
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_projet_cfps')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('p_id_periode', $idPeriodes)
                ->where('project_is_trashed', 0)
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $modules = DB::table('v_projet_cfps')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('p_id_periode', $idPeriodes)
                ->where('project_is_trashed', 0)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_projet_cfps')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('p_id_periode', $idPeriodes)
                ->where('project_is_trashed', 0)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            $financements = DB::table('v_projet_cfps')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('p_id_periode', $idPeriodes)
                ->where('project_is_trashed', 0)
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();

            $forms =  DB::table('v_formateur_cfps')
                ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idCfp', Customer::idCustomer())
                ->where('p_id_periode', $idPeriodes)
                ->groupBy('idFormateur')
                ->get();

            $months = DB::table('v_projet_cfps')
                ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->where('p_id_periode', $idPeriodes)
                ->get();
        }

        if ($idModules[0] != null) {
            $query->whereIn('idModule', $idModules);

            $status = DB::table('v_projet_cfps')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('idModule', $idModules)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();

            $etps = DB::table('v_union_projets')
                ->select(DB::raw('COALESCE(idEtp, idEtp_inter) AS idEtp'), DB::raw('customers.customerName AS etp_name'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->whereIn('idModule', $idModules)
                ->groupBy(DB::raw('COALESCE(idEtp, idEtp_inter)'))
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_projet_cfps')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('idModule', $idModules)
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $periodePrev3 = DB::table('v_projet_cfps')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "prev_3_month")
                ->where('project_is_trashed', 0)
                ->whereIn('idModule', $idModules)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_projet_cfps')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('idModule', $idModules)
                ->first();

            $periodePrev12 = DB::table('v_projet_cfps')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('idModule', $idModules)
                ->first();

            $periodeNext3 = DB::table('v_projet_cfps')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "next_3_month")
                ->where('project_is_trashed', 0)
                ->whereIn('idModule', $idModules)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_projet_cfps')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->whereIn('idModule', $idModules)
                ->first();

            $periodeNext12 = DB::table('v_projet_cfps')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->whereIn('idModule', $idModules)
                ->first();

            $villes = DB::table('v_projet_cfps')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('idModule', $idModules)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            $financements = DB::table('v_projet_cfps')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('idModule', $idModules)
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();

            $forms = DB::table('v_formateur_cfps')
                ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idCfp', Customer::idCustomer())
                ->whereIn('idModule', $idModules)
                ->groupBy('idFormateur')
                ->get();

            $months = DB::table('v_projet_cfps')
                ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('idModule', $idModules)
                ->get();

            $projectDates = DB::table('v_projet_cfps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('idModule', $idModules)
                ->get();
        }

        if ($idVilles[0] != null) {
            $query->whereIn('idVille', $idVilles);

            $status = DB::table('v_projet_cfps')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('idVille', $idVilles)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();


            $etps = DB::table('v_union_projets')
                ->select(DB::raw('COALESCE(idEtp, idEtp_inter) AS idEtp'), DB::raw('customers.customerName AS etp_name'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->whereIn('idVille', $idVilles)
                ->groupBy(DB::raw('COALESCE(idEtp, idEtp_inter)'))
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_projet_cfps')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('idVille', $idVilles)
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $periodePrev3 = DB::table('v_projet_cfps')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "prev_3_month")
                ->where('project_is_trashed', 0)
                ->whereIn('idVille', $idVilles)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_projet_cfps')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('idVille', $idVilles)
                ->first();

            $periodePrev12 = DB::table('v_projet_cfps')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('idVille', $idVilles)
                ->first();

            $periodeNext3 = DB::table('v_projet_cfps')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "next_3_month")
                ->where('project_is_trashed', 0)
                ->whereIn('idVille', $idVilles)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_projet_cfps')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->whereIn('idVille', $idVilles)
                ->first();

            $periodeNext12 = DB::table('v_projet_cfps')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->whereIn('idVille', $idVilles)
                ->first();

            $modules = DB::table('v_projet_cfps')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('idVille', $idVilles)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $financements = DB::table('v_projet_cfps')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('idVille', $idVilles)
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();

            $forms = DB::table('v_formateur_cfps')
                ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idCfp', Customer::idCustomer())
                ->whereIn('idVille', $idVilles)
                ->groupBy('idFormateur')
                ->get();


            $months = DB::table('v_projet_cfps')
                ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('idVille', $idVilles)
                ->get();

            $projectDates = DB::table('v_projet_cfps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('idVille', $idVilles)
                ->get();
        }

        if ($idFinancements[0] != null) {
            $query->whereIn('idPaiement', $idFinancements);

            $status = DB::table('v_projet_cfps')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('idPaiement', $idFinancements)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();


            $etps = DB::table('v_union_projets')
                ->select(DB::raw('COALESCE(idEtp, idEtp_inter) AS idEtp'), DB::raw('customers.customerName AS etp_name'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->whereIn('idPaiement', $idFinancements)
                ->groupBy(DB::raw('COALESCE(idEtp, idEtp_inter)'))
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_projet_cfps')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('idPaiement', $idFinancements)
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $periodePrev3 = DB::table('v_projet_cfps')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "prev_3_month")
                ->where('project_is_trashed', 0)
                ->whereIn('idPaiement', $idFinancements)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_projet_cfps')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('idPaiement', $idFinancements)
                ->first();

            $periodePrev12 = DB::table('v_projet_cfps')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('idPaiement', $idFinancements)
                ->first();

            $periodeNext3 = DB::table('v_projet_cfps')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "next_3_month")
                ->where('project_is_trashed', 0)
                ->whereIn('idPaiement', $idFinancements)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_projet_cfps')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->whereIn('idPaiement', $idFinancements)
                ->first();

            $periodeNext12 = DB::table('v_projet_cfps')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->whereIn('idPaiement', $idFinancements)
                ->first();

            $modules = DB::table('v_projet_cfps')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('idPaiement', $idFinancements)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_projet_cfps')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('idPaiement', $idFinancements)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            $projectDates = DB::table('v_projet_cfps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('idPaiement', $idFinancements)
                ->get();

            $forms = DB::table('v_union_projets')
                ->select('formateurs.idFormateur', 'users.name AS form_name', 'users.firstName AS form_firstname', DB::raw('COUNT(v_union_projets.idProjet) AS projet_nb'))
                ->join('project_forms', 'v_union_projets.idProjet', '=', 'project_forms.idProjet')
                ->join('formateurs', 'formateurs.idFormateur', '=', 'project_forms.idFormateur')
                ->join('users', 'users.id', '=', 'formateurs.idFormateur')

                ->where(function ($query) {
                    $query->where('v_union_projets.idCfp_intra', Customer::idCustomer())
                        ->orWhere('v_union_projets.idCfp_inter', Customer::idCustomer());
                })
                ->whereIn('idPaiement', $idFinancements)
                ->groupBy('idFormateur')
                ->get();

            $months = DB::table('v_projet_cfps')
                ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn('idPaiement', $idFinancements)
                ->get();
        }

        if ($idFormateurs[0] != null) {
            $query->leftJoin('project_forms as pf_main', 'v_union_projets.idProjet', '=', 'pf_main.idProjet')
                ->leftJoin('formateurs', 'formateurs.idFormateur', '=', 'pf_main.idFormateur')
                ->whereIn('pf_main.idFormateur', $idFormateurs);

            $status = DB::table('v_projet_form')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->whereIn('idFormateur', $idFormateurs)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();

            $etps = DB::table('v_union_projets')
                ->select(
                    DB::raw('COUNT(v_union_projets.idProjet) AS projet_nb'),
                    DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter) AS idEtp'),
                    DB::raw('customers.customerName AS etp_name')
                )
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('project_forms as pf', 'pf.idProjet', '=', 'v_union_projets.idProjet')
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->whereIn('pf.idFormateur', $idFormateurs)
                ->groupBy(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'))
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_projet_form')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->whereIn('idFormateur', $idFormateurs)
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            // ✅ CORRECTION : Utiliser v_union_projets.idProjet partout
            $periodePrev3 = DB::table('v_union_projets')
                ->select('v_union_projets.idProjet', 'p_id_periode', DB::raw('COUNT(v_union_projets.idProjet) AS projet_nb'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->leftJoin('project_forms as pf_periode', 'v_union_projets.idProjet', '=', 'pf_periode.idProjet')
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "prev_3_month")
                ->whereIn('pf_periode.idFormateur', $idFormateurs)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'),  DB::raw('COUNT(v_union_projets.idProjet) AS projet_nb'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->leftJoin('project_forms as pf_periode', 'v_union_projets.idProjet', '=', 'pf_periode.idProjet')
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->where('module_name', '!=', 'Default module')
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('pf_periode.idFormateur', $idFormateurs)
                ->first();

            $periodePrev12 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'),  DB::raw('COUNT(v_union_projets.idProjet) AS projet_nb'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->leftJoin('project_forms as pf_periode', 'v_union_projets.idProjet', '=', 'pf_periode.idProjet')
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->where('module_name', '!=', 'Default module')
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('pf_periode.idFormateur', $idFormateurs)
                ->first();

            $periodeNext3 = DB::table('v_union_projets')
                ->select('p_id_periode', DB::raw('COUNT(v_union_projets.idProjet) AS projet_nb'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->leftJoin('project_forms as pf_periode', 'v_union_projets.idProjet', '=', 'pf_periode.idProjet')
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "next_3_month")
                ->whereIn('pf_periode.idFormateur', $idFormateurs)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_union_projets')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(v_union_projets.idProjet) AS projet_nb'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->leftJoin('project_forms as pf_periode', 'v_union_projets.idProjet', '=', 'pf_periode.idProjet')
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->where('module_name', '!=', 'Default module')
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->whereIn('pf_periode.idFormateur', $idFormateurs)
                ->first();

            $periodeNext12 = DB::table('v_union_projets')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(v_union_projets.idProjet) AS projet_nb'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->leftJoin('project_forms as pf_periode', 'v_union_projets.idProjet', '=', 'pf_periode.idProjet')
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->where('module_name', '!=', 'Default module')
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->whereIn('pf_periode.idFormateur', $idFormateurs)
                ->first();

            $modules = DB::table('v_projet_form')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->whereIn('idFormateur', $idFormateurs)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_projet_form')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->whereIn('idFormateur', $idFormateurs)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            $financements = DB::table('v_union_projets')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();

            $months = DB::table('v_projet_form')
                ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->whereIn('idFormateur', $idFormateurs)
                ->groupBy('headDate')
                ->get();

            $projectDates = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->where('module_name', '!=', 'Default module')
                ->get();
        }

        if ($idMois[0] != null) {

            $query->whereIn(DB::raw('MONTH(dateDebut)'), $idMois);

            $status = DB::table('v_projet_cfps')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn(DB::raw('MONTH(dateDebut)'), $idMois)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();


            $etps = DB::table('v_union_projets')
                ->select(DB::raw('COUNT(idProjet) AS projet_nb'), DB::raw('COALESCE(idEtp, idEtp_inter) AS idEtp'), DB::raw('customers.customerName AS etp_name'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->whereIn(DB::raw('MONTH(dateDebut)'), $idMois)
                ->groupBy(DB::raw('COALESCE(idEtp, idEtp_inter)'))
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_projet_cfps')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn(DB::raw('MONTH(dateDebut)'), $idMois)
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $periodePrev3 = DB::table('v_projet_cfps')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "prev_3_month")
                ->where('project_is_trashed', 0)
                ->whereIn(DB::raw('MONTH(dateDebut)'), $idMois)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_projet_cfps')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn(DB::raw('MONTH(dateDebut)'), $idMois)
                ->first();

            $periodePrev12 = DB::table('v_projet_cfps')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn(DB::raw('MONTH(dateDebut)'), $idMois)
                ->first();

            $periodeNext3 = DB::table('v_projet_cfps')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "next_3_month")
                ->where('project_is_trashed', 0)
                ->whereIn(DB::raw('MONTH(dateDebut)'), $idMois)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_projet_cfps')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->whereIn(DB::raw('MONTH(dateDebut)'), $idMois)
                ->first();

            $periodeNext12 = DB::table('v_projet_cfps')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->whereIn(DB::raw('MONTH(dateDebut)'), $idMois)
                ->first();

            $modules = DB::table('v_projet_cfps')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn(DB::raw('MONTH(dateDebut)'), $idMois)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_projet_cfps')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn(DB::raw('MONTH(dateDebut)'), $idMois)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            $financements = DB::table('v_projet_cfps')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->whereIn(DB::raw('MONTH(dateDebut)'), $idMois)
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();

            //    dd($financements);

            $forms = DB::table('v_formateur_cfps')
                ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idCfp', Customer::idCustomer())
                ->whereIn(DB::raw('MONTH(dateDebut)'), $idMois)
                ->groupBy('idFormateur')
                ->get();


            $projectDates = DB::table('v_projet_cfps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn(DB::raw('MONTH(dateDebut)'), $idMois)
                ->get();
            // dd($projectDates);
        }

        if ($idStatus[0] == null && $idEtps[0] == null && $idTypes[0] == null && $idPeriodes == null && $idModules[0] == null && $idVilles[0] == null && $idFinancements[0] == null && $idFormateurs[0] == null && $idMois[0] == null) {
            $status = DB::table('v_projet_cfps')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();


            $etps = DB::table('v_union_projets')
                ->select(DB::raw('COALESCE(idEtp, idEtp_inter) AS idEtp'), DB::raw('customers.customerName AS etp_name'))
                ->leftJoin('entreprises', function ($join) {
                    $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
                })
                ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
                ->where(function ($query) {
                    $query->where('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idCfp_intra', Customer::idCustomer());
                })
                ->groupBy(DB::raw('COALESCE(idEtp, idEtp_inter)'))
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_projet_cfps')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $periodePrev3 = DB::table('v_projet_cfps')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "prev_3_month")
                ->where('project_is_trashed', 0)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_projet_cfps')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->first();

            $periodePrev12 = DB::table('v_projet_cfps')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->first();

            $periodeNext3 = DB::table('v_projet_cfps')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('p_id_periode', "next_3_month")
                ->where('project_is_trashed', 0)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_projet_cfps')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->first();

            $periodeNext12 = DB::table('v_projet_cfps')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->first();

            $modules = DB::table('v_projet_cfps')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_projet_cfps')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            $financements = DB::table('v_projet_cfps')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();

            $forms = DB::table('v_formateur_cfps')
                ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idCfp', Customer::idCustomer())
                ->whereIn('idEtp', $idEtps)
                ->groupBy('idFormateur')
                ->get();

            $months = DB::table('v_projet_cfps')
                ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                ->where('project_is_trashed', 0)
                ->get();

            $projectDates = DB::table('v_projet_cfps')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate')
                ->orderBy('dateDebut', $order)
                ->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->where('project_is_trashed', 0)
                ->get();
        }

        $projects = $query->get();
        $projets = [];  // Tableau pour stocker les projets

        foreach ($projects as $project) {
            // Calculer le prix une seule fois
            $pricing = isset($project->idSubContractor) && $project->idSubContractor == Customer::idCustomer()
                ? $project->total_ht_sub_contractor
                : $project->total_ht;

            // Stocker les variables dans des variables locales pour éviter des appels répétés
            $idProjet = $project->idProjet;
            $idCfpInter = $project->idCfp_inter;
            // $idEtp = $project->idEtp;
            $ville = $project->ville;

            // Appels aux méthodes pour récupérer les données nécessaires
            $nomDossier = $this->getNomDossier($idProjet);
            $nombreDocument = $this->getNombreDocument($idProjet);
            $sessionCount = $this->getSessionProject($idProjet);
            $formateurs = $this->getFormProject($idProjet);
            $apprenantCount = $this->getApprenantProject($idProjet, $project->idCfp_inter);
            $totalPrice = $this->getProjectTotalPrice($idProjet);
            $sessionHour = $this->getSessionHour($idProjet);
            $note = $this->getNote($idProjet);
            $partCount = $this->getParticulierProject($idProjet, $idCfpInter);
            $etpName = $this->getEtpProjectInter($idProjet, $idCfpInter);
            $restaurations = $this->getRestauration($idProjet);
            $checkEmg = $this->checkEmg($idProjet);
            $checkEval = $this->checkEval($idProjet);
            $avgBefore = $this->averageEvalApprenant($idProjet)->avg_avant;
            $avgAfter = $this->averageEvalApprenant($idProjet)->avg_apres;
            $apprs = $this->getApprListProjet($idProjet);
            $isPaid = $this->projectIsPaid($idProjet);

            // Ajouter les informations dans le tableau final
            $projets[] = [
                'dossier' => $nomDossier,
                'project_reference' => $project->project_reference,
                'nbDocument' => $nombreDocument,
                'seanceCount' => $sessionCount,
                'formateurs' => $formateurs,
                'apprCount' => $apprenantCount,
                'projectTotalPrice' => $totalPrice,
                'idProjet' => $idProjet,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'etp_name' => $etpName,
                'ville' => $ville,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'paiement' => $project->paiement,
                'modalite' => $project->modalite,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'etp_initial_name' => $project->etp_initial_name,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'ville' => $ville,
                'headYear' => $project->headYear,
                'headMonthDebut' => $project->headMonthDebut,
                'headMonthFin' => $project->headMonthFin,
                'headDayDebut' => $project->headDayDebut,
                'headDayFin' => $project->headDayFin,
                'project_description' => $project->project_description,
                'total_ht' => $this->utilService->formatPrice($pricing),
                'total_ttc' => $project->total_ttc,
                'totalSessionHour' => $sessionHour,
                'general_note' => $note,
                'idModule' => $project->idModule,
                'restaurations' => $restaurations,
                'apprs' => $apprs,
                'sub_name' => $project->sub_name,
                'idSubContractor' => $project->idSubContractor,
                'idUser' => Customer::idCustomer(),
                'cfp_name' => $project->cfp_name,
                'project_inter_privacy' => $project->project_inter_privacy,
                'idCfp_inter' => $idCfpInter,
                'isPaid' => $isPaid,
            ];
        }


        if ($idStatus[0] != null) {
            return response()->json([
                'projets' => $projets,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'financements' => $financements,
                'formateurs' => $forms,
                'months' => $months,
                'projectDates' => $projectDates
            ]);
        } elseif ($idEtps[0] != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'financements' => $financements,
                'formateurs' => $forms,
                'months' => $months,
                'projectDates' => $projectDates
            ]);
        } elseif ($idTypes[0] != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'financements' => $financements,
                'formateurs' => $forms,
                'months' => $months,
                'projectDates' => $projectDates
            ]);
        } elseif ($idPeriodes != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'types' => $types,
                'modules' => $modules,
                'villes' => $villes,
                'financements' => $financements,
                'formateurs' => $forms,
                'months' => $months,
                'projectDates' => $projectDates
            ]);
        } elseif ($idModules[0] != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'villes' => $villes,
                'financements' => $financements,
                'formateurs' => $forms,
                'months' => $months,
                'projectDates' => $projectDates
            ]);
        } elseif ($idVilles[0] != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'financements' => $financements,
                'formateurs' => $forms,
                'months' => $months,
                'projectDates' => $projectDates
            ]);
        } elseif ($idFinancements[0] != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'formateurs' => $forms,
                'months' => $months,
                'projectDates' => $projectDates
            ]);
        } elseif ($idFormateurs[0] != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'financements' => $financements,
                'months' => $months,
                'projectDates' => $projectDates
            ]);
        } elseif ($idMois[0] != null) {
            //dd($financements);

            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'financements' => $financements,
                'formateurs' => $forms,
                'projectDates' => $projectDates
            ]);
        } else {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'financements' => $financements,
                'formateurs' => $forms,
                'months' => $months,
                'projectDates' => $projectDates
            ]);
        }
    }

    public function getNomDossier($idProjet)
    {
        $dossier = DB::table('dossiers')
            ->select('dossiers.idDossier', 'nomDossier')
            ->join('projets', 'dossiers.idDossier', 'projets.idDossier')
            ->where('idProjet', $idProjet)
            ->get();

        return $dossier;
    }

    public function getInvoiceProject($idProjet)
    {
        $invoice = DB::table('invoice_details')
            ->join('invoices', 'invoices.idInvoice', '=', 'invoice_details.idInvoice')
            ->join('invoice_status', 'invoice_status.idInvoiceStatus', '=', 'invoices.invoice_status')
            ->select('invoice_status.invoice_status_name', 'invoices.invoice_number', 'invoices.idInvoice')
            ->where('invoice_details.idProjet', $idProjet)
            ->first();

        return $invoice;
    }

    public function getNombreDocument($idProjet)
    {
        $nbDocument = DB::table('documents as d')
            ->join('dossiers as ds', 'ds.idDossier', '=', 'd.idDossier')
            ->join('projets as p', 'ds.idDossier', '=', 'p.idDossier')
            ->select(DB::raw('COUNT(d.idDocument) as document_count'))
            ->where('p.idProjet', $idProjet)
            ->first();

        return $nbDocument->document_count;
    }

    public function getSessionProject($idProjet)
    {
        $countSession = DB::table('v_seances')
            ->select('idSeance')
            ->where('idProjet', $idProjet)
            ->get();

        return count($countSession);
    }


    public function getProjectMaterials($idProjet)
    {
        $materials = DB::table('project_materials as PM')
            ->join('projets as P', 'PM.project_id', '=', 'P.idProjet')
            ->join('mdls', 'P.idModule', '=', 'mdls.idModule')
            ->join('materials as MTL', 'PM.material_id', '=', 'MTL.id')
            ->select(
                'PM.project_id',
                'PM.material_id',
                'MTL.name as material_name',
                'MTL.stock_number',
                'MTL.customer_id as cfp_id',
                'PM.number',
                'PM.created_at',
                'P.dateDebut as project_start_date',
                'P.dateFin as project_end_date',
                'P.idModule as module_id',
                'mdls.moduleName as module_name',
                'mdls.description as module_description',
                'mdls.module_image'
            )
            ->where('P.idCustomer', Customer::idCustomer())
            ->where('PM.project_id', $idProjet)
            ->get();

        return $materials;
    }


    public function getProjectTotalPrice($idProjet)
    {
        $projectPrice = DB::table('v_projet_cfps')
            ->select(DB::raw('SUM(project_price_pedagogique + project_price_annexe) AS project_total_price'))
            ->where('idProjet', $idProjet)
            ->first();

        return $projectPrice->project_total_price;
    }

    public function getSessionHour($idProjet)
    {
        $countSessionHour = DB::table('v_seances')
            ->selectRaw("IFNULL(TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))), '%H:%i'), '0') as sumHourSession")
            ->where('idProjet', $idProjet)
            ->first();

        return $countSessionHour->sumHourSession;
    }

    public function getNote($idProjet)
    {
        $checkEvaluation = DB::table('eval_chauds')->select('idProjet')->get();
        $checkEvaluationCount = count($checkEvaluation);

        if ($checkEvaluationCount > 0) {
            $notationProjet = DB::table('v_evaluation_alls')
                ->select('idProjet', 'idEmploye', 'generalApreciate')
                ->where('idProjet', $idProjet)
                ->groupBy('idProjet', 'idEmploye')
                ->get();

            $generalNotation = DB::table('v_general_note_evaluation')
                ->select(DB::raw('SUM(generalApreciate) as generalNote'))
                ->where('idProjet', $idProjet)
                ->first();

            $countNotationProjet = count($notationProjet);

            if ($countNotationProjet > 0) {
                $noteGeneral = $generalNotation->generalNote / $countNotationProjet;
                return array_merge([$noteGeneral], [$countNotationProjet]);
            } else {
                $noteGeneral = 0;
                return array_merge([$noteGeneral], [$countNotationProjet]);
            }
        } else {
            $countNotationProjet = 0;
            $noteGeneral = 0;
            return array_merge([$noteGeneral], [$countNotationProjet]);
        }
    }

    public function getNotesBatch(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([], 200);
        }

        $results = [];

        foreach ($ids as $idProjet) {
            $checkEvaluation = DB::table('eval_chauds')->select('idProjet')->get();
            $checkEvaluationCount = count($checkEvaluation);

            if ($checkEvaluationCount > 0) {
                $notationProjet = DB::table('v_evaluation_alls')
                    ->select('idProjet', 'idEmploye', 'generalApreciate')
                    ->where('idProjet', $idProjet)
                    ->groupBy('idProjet', 'idEmploye')
                    ->get();

                $generalNotation = DB::table('v_general_note_evaluation')
                    ->select(DB::raw('SUM(generalApreciate) as generalNote'))
                    ->where('idProjet', $idProjet)
                    ->first();

                $countNotationProjet = count($notationProjet);
                $noteGeneral = $countNotationProjet > 0
                    ? $generalNotation->generalNote / $countNotationProjet
                    : 0;
            } else {
                $countNotationProjet = 0;
                $noteGeneral = 0;
            }

            $results[$idProjet] = [$noteGeneral, $countNotationProjet];
        }

        return response()->json($results);
    }

    public function uploadPhotoMomentum(Request $request)
    {
        // Ajuster les paramètres PHP
        ini_set('upload_max_filesize', '5M');
        ini_set('post_max_size', '50M');
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '300');
        ini_set('max_input_time', '300');

        $driver = new Driver();
        $manager = new ImageManager($driver);

        $request->validate([
            'myFile.*' => 'required|image|max:5120'
        ]);

        $files = $request->file('myFile');
        $idProjet = $request->idProjet;
        $maxFileSize = 5 * 1024 * 1024; // 5 MB
        $urls = [];

        if ($files) {
            foreach ($files as $file) {
                if ($file->getSize() > $maxFileSize) {
                    return response()->json(['error' => 'L\'un des fichiers est trop grand. La taille maximale autorisée est de 5 MB par fichier.']);
                }

                try {
                    $image = $manager->read($file)->toWebp(25);

                    $disk = Storage::disk('do');
                    $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.webp';
                    $path = 'img/momentum/' . $idProjet . '/' . $filename;

                    $disk->put($path, $image->__toString());

                    $url = $disk->url($path);
                    $urls[] = $url;

                    DB::table('images')->insert([
                        'idTypeImage' => 1,
                        'idProjet' => $idProjet,
                        'url' => $url,
                        'path' => $path,
                        'nomImage' => $filename,
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Upload impossible !'
                    ]);

                    // Log::error('Erreur lors du traitement de l\'image : ' . $e->getMessage(), [
                    //     'file' => $file->getClientOriginalName(),
                    //     'idProjet' => $idProjet,
                    // ]);
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Photos téléchargées avec succès'
            ]);
        }

        return response()->json([
            'status' => 400,
            'message' => 'Aucun fichier n\'a été téléchargé !'
        ]);
    }

    public function getParticulierProject($idProjet, $idCfp_inter)
    {
        $parts = []; // Initialiser $parts comme un tableau vide

        if ($idCfp_inter != null) {
            $parts = DB::table('v_particuliers_projet')
                ->select('idParticulier')
                ->where('idProjet', $idProjet)
                ->orderBy('part_name', 'asc')
                ->get();
        }
        return count($parts);
    }

    public function getRestauration($idProjet)
    {
        $restaurations = DB::table('project_restaurations')
            ->select('idRestauration', 'paidBy')
            ->where('idProjet', $idProjet)
            ->get()
            ->toArray();
        return $restaurations;
    }

    public function checkEmg($idProjet)
    {
        $query = DB::table('emargements')->where('idProjet', $idProjet);

        if ($query) {
            return $query->count();
        } else {
            return null;
        }
    }

    public function checkEval($idProjet)
    {
        $query = DB::table('eval_chauds')->where('idProjet', $idProjet);

        if ($query) {
            return $query->count();
        } else {
            return null;
        }
    }

    private function averageEvalApprenant($idProjet)
    {
        return DB::table('eval_apprenant')
            ->select(DB::raw('AVG(avant) as avg_avant'), DB::raw('AVG(apres) as avg_apres'))
            ->where('idProjet', $idProjet)
            ->first() ?? 0;
    }

    private function projectIsPaid($id)
    {
        $isPaid = DB::table('invoice_details as ID')
            ->select('IS.invoice_status_name', 'IS.idInvoiceStatus')
            ->join('invoices as I', 'I.idInvoice', '=', 'ID.idInvoice')
            ->join('invoice_status as IS', 'IS.idInvoiceStatus', '=', 'I.invoice_status')
            ->where('ID.idProjet', $id)
            ->whereNotExists(function ($query) {
                $query->select('IL.id')
                    ->from('invoice_deleted as IL')
                    ->whereRaw('IL.idInvoice = ID.idInvoice');
            })
            ->first();

        return $isPaid ? (array) $isPaid : [];
    }

    public function getProjectStatus($status)
    {
        $projects = $this->project->indexStatus(Customer::idCustomer(), $status);

        $projets = [];
        $customerId = Customer::idCustomer(); // Calculé une seule fois

        foreach ($projects as $project) {
            // Calculer le pricing une seule fois
            $pricing = isset($project->idSubContractor) && $project->idSubContractor == $customerId
                ? $project->total_ht_sub_contractor
                : $project->total_ht;

            // Stocker les résultats dans des variables
            $idProjet = $project->idProjet;
            $idCfpInter = $project->idCfp_inter;
            $idEtp = $project->idEtp ?? null;
            $ville = $project->ville;

            // Obtenir les données nécessaires (même logique qu'avant)
            $nomDossier = $this->getNomDossier($idProjet);
            $nombreDocument = $this->getNombreDocument($idProjet);
            $sessionCount = $this->getSessionProject($idProjet);
            $materials = $this->getProjectMaterials($idProjet);
            $formateurs = $this->getFormProject($idProjet);
            $apprenantCount = $this->getApprenantProject($idProjet, $idCfpInter);
            $totalPrice = $this->getProjectTotalPrice($idProjet);
            $sessionHour = $this->getSessionHour($idProjet);
            $note = $this->getNote($idProjet);
            $partCount = $this->getParticulierProject($idProjet, $idCfpInter);
            $etpName = $this->getEtpProjectInter($idProjet, $idCfpInter);
            $restaurations = $this->getRestauration($idProjet);
            $checkEmg = $this->checkEmg($idProjet);
            $checkEval = $this->checkEval($idProjet);
            $avgBefore = $this->averageEvalApprenant($idProjet)->avg_avant ?? null;
            $avgAfter = $this->averageEvalApprenant($idProjet)->avg_apres ?? null;
            $apprs = $this->getApprListProjet($idProjet);
            $isPaid = $this->projectIsPaid($idProjet);

            // Ajouter les informations dans le tableau (même structure qu'avant)
            $projets[] = [
                'project_reference' => $project->project_reference,
                'dossier' => $nomDossier,
                'nbDocument' => $nombreDocument,
                'seanceCount' => $sessionCount,
                'formateurs' => $formateurs,
                'apprCount' => $apprenantCount,
                'projectTotalPrice' => $totalPrice,
                'totalSessionHour' => $sessionHour,
                'general_note' => $note,
                'idProjet' => $idProjet,
                'idCfp_inter' => $idCfpInter,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'partCount' => $partCount,
                'etp_name' => $etpName,
                'idEtp' => $idEtp,
                'ville' => $ville,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'paiement' => $project->paiement,
                'modalite' => $project->modalite,
                'project_description' => $project->project_description,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'etp_initial_name' => $project->etp_initial_name,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'li_name' => $project->li_name,
                'etp_name_in_situ' => $project->etp_name,
                'total_ht' => $this->utilService->formatPrice($pricing),
                'total_ttc' => $project->total_ttc,
                'idModule' => $project->idModule,
                'restaurations' => $restaurations,
                'project_inter_privacy' => $project->project_inter_privacy,
                'checkEmg' => $checkEmg,
                'checkEval' => $checkEval,
                'avg_before' => $avgBefore,
                'avg_after' => $avgAfter,
                'apprs' => $apprs,
                'sub_name' => $project->sub_name,
                'idSubContractor' => $project->idSubContractor,
                'idCfp' => $project->idCfp,
                'cfp_name' => $project->cfp_name,
                'idUser' => $customerId,
                'isPaid' => $isPaid,
                'materials' => $materials,
            ];
        }

        return $projets;
    }

    public function getProjectByStatus(Request $request)
    {
        $status = $request->status;

        $projects = $this->getStatus($status);
        return response()->json([
            'status' => 200,
            'projets' => $projects['projets']
        ]);
    }

    public function getProjectForEvaluation(Request $request)
    {
        $perPage = (int)$request->input('per_page', 10);
        $page = max(1, (int)$request->input('page', 1));

        $statuses = ['En cours', 'Terminé', 'Cloturé'];
        $keys = ['en_cours', 'termines', 'clotures'];

        $projets = [];
        $counts = [];

        // Calculer le total une seule fois (plus léger)
        $totalProjects = $this->getProjectStatus('Terminé'); // Juste pour le count
        $totalItems = count($totalProjects);
        $totalPages = ceil($totalItems / $perPage);

        // Limiter le nombre de pages pour éviter les appels hors limites
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        foreach ($statuses as $index => $status) {
            // Récupérer seulement les projets nécessaires
            $result = $this->getProjectStatus($status);
            $counts[$keys[$index]] = count($result);

            // Pagination simple
            $offset = ($page - 1) * $perPage;
            $pagedResult = array_slice($result, $offset, $perPage);

            $groupedByMonth = $this->groupProjectsByMonth($pagedResult);
            $projets[$keys[$index]] = $groupedByMonth;
        }

        return response()->json([
            'status' => 200,
            'projet_counts' => $counts,
            'projets' => $projets,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalItems,
                'total_pages' => $totalPages,
            ]
        ]);
    }

    private function groupProjectsByMonth($projects)
    {
        if (empty($projects)) {
            return [];
        }

        $grouped = [];
        $monthsNames = [
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

        foreach ($projects as $project) {
            // Extraire le mois et l'année de la date de début
            $dateDebut = new \DateTime($project['dateDebut']);
            $monthNumber = (int)$dateDebut->format('n'); // 1-12
            $year = $dateDebut->format('Y');

            // Créer la cl du mois (ex: "Janvier 2024")
            $monthKey = $monthsNames[$monthNumber] . ' ' . $year;

            // Ajouter les données du mois au projet pour le frontend
            $project['month'] = $monthKey;
            $project['monthNumber'] = $monthNumber;
            $project['year'] = $year;

            // Grouper par mois
            if (!isset($grouped[$monthKey])) {
                $grouped[$monthKey] = [
                    'month' => $monthKey,
                    'monthNumber' => $monthNumber,
                    'year' => $year,
                    'projects' => []
                ];
            }

            $grouped[$monthKey]['projects'][] = $project;
        }

        // Trier les mois par ordre chronologique décroissant (plus récent en premier)
        return $this->sortMonthsChronologically($grouped);
    }

    private function getPlaceAvailable($idProjet)
    {
        $place_validated = DB::table('inter_entreprises')->where('idProjet', $idProjet)->where('isActiveInter', 1)->sum('nbPlaceReserved');
        $place_project = DB::table('inters')->where('idProjet', $idProjet)->value('nbPlace');
        $place_available = $place_project - $place_validated;
        return $place_available;
    }

    private function getNbPlaceReserved($idProjet)
    {
        $place_reserved = DB::table('inter_entreprises')->where('idProjet', $idProjet)->where('isActiveInter', 1)->sum('nbPlaceReserved');
        return $place_reserved;
    }

    public function getFormAdded($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idProjet', 'idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'email AS form_email', 'initialNameForm AS form_initial_name', 'form_phone')
            ->groupBy('idProjet', 'idFormateur', 'name', 'firstName', 'photoForm', 'email', 'initialNameForm')
            ->where('idProjet', $idProjet)
            ->get();

        return response()->json([
            'status' => 200,
            'forms' => $forms
        ]);
    }

    public function fraisdetails($idProjet, $isEtp)
    {
        $fraisdetails = DB::table('fraisprojet')
            ->select('idFraisProjet', 'fraisprojet.idProjet', 'fraisprojet.idFrais', 'frais', 'description', 'montant', 'taxe', 'isEtp')
            ->join('projets', 'projets.idProjet', 'fraisprojet.idProjet')
            ->join('frais', 'frais.idFrais', 'fraisprojet.idFrais')
            ->where('fraisprojet.idProjet', $idProjet)
            ->where('fraisprojet.isEtp', $isEtp)
            ->orderBy('fraisprojet.idFraisProjet', 'desc')
            ->get();

        return response()->json([
            'status' => 200,
            'fraisdetails' => $fraisdetails
        ]);
    }

    public function getEtpAssign($idProjet)
    {
        $etp = DB::table('v_projet_cfps')->select('idProjet', 'idEtp', 'etp_initial_name', 'etp_name', 'etp_logo', 'etp_email')->where('idProjet', $idProjet);

        if ($etp->exists()) {
            return response()->json([
                'status' => 200,
                'entreprise' => $etp->first()
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'ENtreprise introuvable !'
            ], 204);
        }
    }

    public function getProgramme($idModule)
    {
        $programmes = DB::table('programmes')->select('program_title', 'program_description', 'idModule')->where('idModule', $idModule)->get();

        if (count($programmes) <= 0) {
            return response()->json([
                'status' => 204,
                'message' => 'aucun élement touvé !'
            ], 204);
        } else {
            return response()->json([
                'status' => 200,
                'programmes' => $programmes
            ]);
        }
    }

    public function getSalleAdded($idProjet)
    {
        $idSalleProjet = DB::table('v_projet_cfps')
            ->select('idProjet', 'idSalle')
            ->where('idProjet', $idProjet)
            ->first();

        $salle = DB::table('v_list_salles')
            ->select('idSalle', 'salle_name', 'salle_rue', 'salle_quartier', 'vi_code_postal', 'ville', 'salle_image', 'lieu_name', 'idLieu')
            ->where(function ($query) use ($idSalleProjet) {
                $query->where('idSalle', $idSalleProjet->idSalle)
                    ->where('salle_name', '!=', 'null');
            });

        if ($salle->exists()) {
            return response()->json([
                'status' => 200,
                'salle' => $salle->first()
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Element introuvable !'
            ], 204);
        }
    }

    public function duplicate($idProjet)
    {

        $project =  Projet::where('idProjet', $idProjet)->first();


        $newProject = Projet::create([
            'referenceEtp' => $project->referenceEtp,
            'project_reference' => $project->project_reference,
            'project_title' => $project->project_title,
            'projectName' => $project->projectName,
            'dateDebut' => $project->dateDebut,
            'dateFin' => $project->dateFin,
            'dateFin' => $project->dateFin,
            'lieu' => $project->lieu,
            'idVilleCoded' => 1,
            'idModule' => $project->idModule,
            'idCustomer' => $project->idCustomer,
            'idModalite' => $project->idModalite,
            'idTypeProjet' => $project->idTypeProjet,
            'idSalle' => $project->idSalle,
            'project_description' => $project->project_description,
            'project_num_fmfp' => $project->project_num_fmfp,
            'project_is_active' => 0,
            'project_is_reserved' => 0,
            'project_is_cancelled' => 0,
            'project_is_repported' => 0,
            'project_is_trashed' => 0,
            'project_price_pedagogique' => 0,
            'project_price_annexe' => 0,
            'total_ht' => 0,
            'total_ttc' => 0,
        ]);

        if (!$newProject) {
            return response()->json(['error' => 'Erreur inconnue !']);
        }

        $new_idProjet = Projet::latest()->first()->idProjet;

        if ($project->idTypeProjet === 1) {

            $intra = DB::table('intras')->where('idProjet', $idProjet)->first();
            $insert_intra = DB::table('intras')->insert([
                'idProjet' => $new_idProjet,
                'idPaiement' => $intra->idPaiement,
                'idEtp' => $intra->idEtp,
                'idCfp' => $intra->idCfp
            ]);
            if (!$insert_intra) {
                DB::table('projets')->where('idProjet', $new_idProjet)->delete();
                return response()->json(['error' => 'Erreur inconnue !']);
            }
        }

        if ($project->idTypeProjet === 2) {

            $inters = DB::table('inters')->where('idProjet', $idProjet)->first();
            $insert_inters = DB::table('inters')->insert([
                'idProjet' => $new_idProjet,
                'idPaiement' => $inters->idPaiement,
                'idCfp' => $inters->idCfp
            ]);
            if (!$insert_inters) {
                DB::table('projets')->where('idProjet', $new_idProjet)->delete();
                return response()->json(['error' => 'Erreur inconnue !']);
            }

            $inter_entreprises = DB::table('inter_entreprises')->where('idProjet', $idProjet)->get();
            foreach ($inter_entreprises as $inter_entreprise) {
                $insert_inter_entreprise = DB::table('inter_entreprises')->insert([
                    'idProjet' => $new_idProjet,
                    'idEtp' => $inter_entreprise->idEtp
                ]);
                if (!$insert_inter_entreprise) {
                    DB::table('projets')->where('idProjet', $new_idProjet)->delete();
                    return response()->json(['error' => 'Erreur inconnue !']);
                }
            }
        }

        return response()->json([
            'status' => 200,
            'message' => 'Projet dupliqué avec succès'
        ]);
    }

    public function deassignEtp($projectId, $etpId)
    {
        DB::beginTransaction();

        try {
            $projectType = $this->getProjectType($projectId);

            if ($projectType === 1) {

                // Remove apprenants
                DB::table('detail_apprenants')
                    ->where('idProjet', $projectId)
                    ->delete();

                // Remove Evaluation
                DB::table('eval_chauds')
                    ->join('detail_apprenants', 'eval_chauds.idEmploye', '=', 'detail_apprenants.idEmploye')
                    ->where('eval_chauds.idProjet', $projectId)
                    ->delete();

                // Remove presence
                DB::table('emargements')
                    ->join('detail_apprenants', 'emargements.idEmploye', '=', 'detail_apprenants.idEmploye')
                    ->where('emargements.idProjet', $projectId)
                    ->delete();
            } else {

                // Remove apprenants inter
                DB::table('detail_apprenant_inters')
                    ->where('idProjet', $projectId)
                    ->where('idEtp', $etpId)
                    ->delete();

                // Remove relation inter entreprise
                DB::table('inter_entreprises')
                    ->where('idProjet', $projectId)
                    ->where('idEtp', $etpId)
                    ->delete();

                // Remove evaluation
                DB::table('eval_chauds')
                    ->join('detail_apprenant_inters', 'eval_chauds.idEmploye', '=', 'detail_apprenant_inters.idEmploye')
                    ->where('eval_chauds.idProjet', $projectId)
                    ->where('detail_apprenant_inters.idEtp', $etpId)
                    ->delete();

                // Remove presence
                DB::table('emargements')
                    ->join('detail_apprenant_inters', 'emargements.idEmploye', '=', 'detail_apprenant_inters.idEmploye')
                    ->where('emargements.idProjet', $projectId)
                    ->where('detail_apprenant_inters.idEtp', $etpId)
                    ->delete();
            }

            DB::commit();

            return response()->json('Entreprise successfully deleted', 200);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Error deleting entreprise',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function etpAssignInter($idProjet, $idEtp)
    {
        $idEtpGrp = DB::table('etp_groupeds')->where('idEntrepriseParent', $idEtp)->pluck('idEntreprise')->toArray();
        $idEtpParentGrp = DB::table('etp_groupeds')->where('idEntreprise', $idEtp)->pluck('idEntrepriseParent')->toArray();

        $allIdEtp = [];

        if ($idEtpGrp == []) {
            $allIdEtp = array_merge($idEtpParentGrp, [$idEtp]);
        } else {
            $allIdEtp = array_merge($idEtpGrp, [$idEtp]);
        }

        $check = DB::table('inter_entreprises')->where('idProjet', $idProjet)->whereIn('idEtp', $allIdEtp)->get();
        $checkAppr = DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->whereIn('idEtp', $allIdEtp)->get();

        if (count($check) < 1 && count($checkAppr) < 1) {

            $insert = DB::table('inter_entreprises')->insert([
                'idProjet' => $idProjet,
                'idEtp' => $idEtp,
            ]);

            return response()->json([
                'success' => 'Entreprise ajoutée avec succès !'
            ]);
        } elseif (count($check) < 1 && count($checkAppr) >= 1) {

            DB::beginTransaction();
            DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->where('idEtp', $idEtp)->delete();
            DB::table('inter_entreprises')->insert([
                'idProjet' => $idProjet,
                'idEtp' => $idEtp,
            ]);
            DB::commit();

            return response()->json([
                'success' => 'Entreprise ajouté avec succès !'
            ]);
        } elseif (count($check) >= 1) {
            return response()->json([
                'error' => 'Cette entreprise ou une entreprise parent est déjà assignée à ce projet.'
            ]);
        }
    }

    public function etpAssign($idProjet, $idEtp)
    {
        $checkEval = DB::table('eval_chauds')
            ->join('detail_apprenants', 'eval_chauds.idEmploye', '=', 'detail_apprenants.idEmploye')
            ->select('eval_chauds.*')
            ->where('eval_chauds.idProjet', $idProjet)
            ->get();

        $checkPresence = DB::table('emargements')
            ->join('detail_apprenants', 'emargements.idEmploye', '=', 'detail_apprenants.idEmploye')
            ->select('emargements.*')
            ->where('emargements.idProjet', $idProjet)
            ->get();

        try {
            if (count($checkEval) > 0 && count($checkPresence) > 0) {
                DB::beginTransaction();

                //Remove Evaluation
                DB::table('eval_chauds')
                    ->join('detail_apprenants', 'eval_chauds.idEmploye', '=', 'detail_apprenants.idEmploye')
                    ->select('eval_chauds.*')
                    ->where('eval_chauds.idProjet', $idProjet)
                    ->delete();

                //Remove presence
                DB::table('emargements')
                    ->join('detail_apprenants', 'emargements.idEmploye', '=', 'detail_apprenants.idEmploye')
                    ->select('emargements.*')
                    ->where('emargements.idProjet', $idProjet)
                    ->delete();

                DB::table('detail_apprenants')->where('idProjet', $idProjet)->delete();

                DB::table('projets')
                    ->join('intras', 'intras.idProjet', 'projets.idProjet')
                    ->where('projets.idProjet', $idProjet)
                    ->update(['idEtp' => $idEtp]);

                DB::commit();
            } elseif (count($checkEval) > 0 && count($checkPresence) <= 0) {
                DB::beginTransaction();

                //Remove Evaluation
                DB::table('eval_chauds')
                    ->join('detail_apprenants', 'eval_chauds.idEmploye', '=', 'detail_apprenants.idEmploye')
                    ->select('eval_chauds.*')
                    ->where('eval_chauds.idProjet', $idProjet)
                    ->delete();

                DB::table('detail_apprenants')->where('idProjet', $idProjet)->delete();

                DB::table('projets')
                    ->join('intras', 'intras.idProjet', 'projets.idProjet')
                    ->where('projets.idProjet', $idProjet)
                    ->update(['idEtp' => $idEtp]);
                DB::commit();
            } elseif (count($checkEval) <= 0 && count($checkPresence) > 0) {
                DB::beginTransaction();

                //Remove presence
                DB::table('emargements')
                    ->join('detail_apprenants', 'emargements.idEmploye', '=', 'detail_apprenants.idEmploye')
                    ->select('emargements.*')
                    ->where('emargements.idProjet', $idProjet)
                    ->delete();

                DB::table('detail_apprenants')->where('idProjet', $idProjet)->delete();

                DB::table('projets')
                    ->join('intras', 'intras.idProjet', 'projets.idProjet')
                    ->where('projets.idProjet', $idProjet)
                    ->update(['idEtp' => $idEtp]);
                DB::commit();
            } else {
                DB::beginTransaction();
                DB::table('detail_apprenants')->where('idProjet', $idProjet)->delete();

                DB::table('projets')
                    ->join('intras', 'intras.idProjet', 'projets.idProjet')
                    ->where('projets.idProjet', $idProjet)
                    ->update(['idEtp' => $idEtp]);
                DB::commit();
            }
            return response()->json([
                'status' => 200,
                'message' => 'Succès'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 400,
                'message' => 'Erreur inconnue !'
            ]);
        }
    }

    public function addEtpInProject($idProjet, $idEtp)
    {
        try {
            DB::beginTransaction();

            $projectType = $this->getProjectType($idProjet);

            if ($projectType === 1) {
                $projectIsExists = DB::table('intras')->where('idProjet', $idProjet)->exists();
                if ($projectIsExists) {
                    DB::table('intras')
                        ->where('idProjet', $idProjet)
                        ->update([
                            'idEtp' => $idEtp
                        ]);
                } else {
                    DB::table('intras')->insert(['idEtp' => $idEtp, 'idProjet' => $idProjet, 'idCfp' => Customer::idCustomer()]);
                }

                $learners = DB::table('detail_apprenants')
                    ->where('idProjet', $idProjet);

                if ($learners->exists()) {
                    $learners->delete();
                }

                $evaluation = DB::table('eval_chauds')
                    ->where('idProjet', $idProjet);

                if ($evaluation->exists()) {
                    $evaluation->delete();
                }

                $emargements = DB::table('emargements')
                    ->join('detail_apprenants', 'emargements.idEmploye', '=', 'detail_apprenants.idEmploye')
                    ->where('detail_apprenants.idProjet', $idProjet);

                if ($emargements->exists()) {
                    $emargements->delete();
                }
            } else {
                DB::table('inter_entreprises')->insert(
                    ['idEtp' => $idEtp, 'idProjet' => $idProjet]
                );
            }

            DB::commit();

            $entreprise = DB::table('customers')
                ->select('customerName', 'logo', 'customerEmail', 'idCustomer')
                ->where('idCustomer', $idEtp)
                ->first();

            return response()->json([
                'status' => 200,
                'message' => 'Succès',
                'entreprise' => $entreprise,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 400,
                'message' => $th->getMessage()
            ]);
        }
    }


    public function cancel($idProjet)
    {
        $query = $this->project->getProject(Customer::idCustomer())->where('idProjet', $idProjet);

        if ($query->exists()) {
            $query->update([
                'project_is_active' => 0,
                'project_is_reserved' => 0,
                'project_is_repported' => 0,
                'project_is_trashed' => 0,
                'project_is_cancelled' => 1,
                'project_is_closed' => 0,
                'project_is_archived' => 0
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Projet annulé avec succes.'
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Projet introuvable !'
            ], 204);
        }
    }

    public function repport(Request $req, $idProjet)
    {
        $req->validate([
            'dateDebut' => 'required|date',
            'dateFin' => 'required|date|after_or_equal:dateDebut'
        ]);

        $query = $this->project->getProject(Customer::idCustomer())->where('idProjet', $idProjet);

        if ($query->exists()) {
            $query->update([
                'dateDebut' => $req->dateDebut,
                'dateFin' => $req->dateFin,
                'project_is_repported' => 1,
                'project_is_active' => 0,
                'project_is_reserved' => 0,
                'project_is_trashed' => 0,
                'project_is_cancelled' => 0,
                'project_is_closed' => 0,
                'project_is_archived' => 0
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Projet repporté avec succes'
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Projet introuvable !'
            ], 204);
        }
    }

    public function close($idProjet)
    {
        $query = $this->project->getProject(Customer::idCustomer())->where('idProjet', $idProjet);

        if ($query->exists()) {
            $query->update([
                'project_is_active' => 0,
                'project_is_reserved' => 0,
                'project_is_repported' => 0,
                'project_is_trashed' => 0,
                'project_is_cancelled' => 0,
                'project_is_archived' => 0,
                'project_is_closed' => 1
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Projet cloturé avec succes'
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Projet introuvable !'
            ], 204);
        }
    }

    public function trash($idProjet)
    {
        $query = DB::table('projets')->where('idCustomer', Customer::idCustomer())->where('idProjet', $idProjet);

        if ($query->exists()) {
            $query->update(['project_is_trashed' => 1]);

            return response()->json([
                'status' => 200,
                'message' => 'Succès'
            ], 200);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Element introuvable !'
            ], 204);
        }
    }

    public function makeArchive($id)
    {
        $query = DB::table('projets')->where('idCustomer', Customer::idCustomer())->where('idProjet', $id);

        if ($query->first()) {
            $query->update([
                'project_is_trashed' => 0,
                'project_is_active' => 0,
                'project_is_reserved' => 0,
                'project_is_repported' => 0,
                'project_is_cancelled' => 0,
                'project_is_closed' => 0,
                'project_is_archived' => 1
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Succès'
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Projet introuvable !'
            ], 404);
        }
    }

    public function confirm($idProjet)
    {
        $query = DB::table('projets')->where('idCustomer', Customer::idCustomer())->where('idProjet', $idProjet);

        if ($query->exists()) {
            $query->update([
                'project_is_active' => 1,
                'project_is_reserved' => 0,
                'project_is_repported' => 0,
                'project_is_trashed' => 0,
                'project_is_cancelled' => 0,
                'project_is_closed' => 0,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Projet confirmé avec succes'
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Projet introuvable !'
            ], 204);
        }
    }

    // soft delete
    public function destroy($idProjet)
    {
        $query = $this->project->getProject(Customer::idCustomer())->where('idProjet', $idProjet);

        if ($query->exists()) {
            $query->update([
                'project_is_trashed' => 1,
                'project_is_active' => 0,
                'project_is_reserved' => 0,
                'project_is_repported' => 0,
                'project_is_cancelled' => 0,
                'project_is_closed' => 0,
                'project_is_archived' => 0
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Projet supprimé avec succes'
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Projet introuvable !'
            ], 204);
        }
    }


    public function getProjetsByIdEtp($idEtp)
    {
        // Récupérer les projets de l'entreprise
        $projets = DB::table('v_projet_all')
            ->where('idEtp', $idEtp)
            ->get();

        // Récupérer les employés avec role_id = 6 liés à l'entreprise
        $referent = DB::table('v_employe_alls')
            ->where('customerName', $projets->first()?->etp_name) // récupère le nom de l'entreprise à partir du premier projet
            ->where('role_id', 6)
            ->get();

        return response()->json([
            'projets' => $projets,
            'referent' => $referent
        ]);
    }


    public function restore($idProjet)
    {
        $projet = $this->project->getProject(Customer::idCustomer())->where('idProjet', $idProjet)->where('project_is_trashed', 1);

        if ($projet->exists()) {
            $projet->update([
                'project_is_trashed' => 0
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Projet restauré avec succes'
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Projet introuvable !'
            ], 204);
        }
    }

    public function updateDate(Request $req, $idProjet)
    {
        $req->validate([
            'dateDebut' => 'nullable|date',
            'dateFin' => 'nullable|date|after_or_equal:dateDebut',
        ]);

        $data = [];

        if ($req->filled('dateDebut')) {
            $data['dateDebut'] = $req->dateDebut;
        }

        if ($req->filled('dateFin')) {
            $data['dateFin'] = $req->dateFin;
        }

        // 👇 Teste ici si ça matche bien un projet
        $projet = DB::table('projets')->where('idProjet', $idProjet)->first();

        if (!$projet) {
            return response()->json([
                'status' => 204,
                'message' => 'Projet introuvable',
            ]);
        }

        if (!empty($data)) {
            DB::table('projets')->where('idProjet', $idProjet)->update($data);

            return response()->json([
                'status' => 200,
                'message' => 'Dates mises à jour avec succès',
                'updated' => $data,
            ]);
        }

        return response()->json([
            'status' => 400,
            'message' => 'Aucune date fournie',
        ]);
    }

    public function updateModule(Request $req, $idProjet)
    {
        $req->validate([
            'idModule' => 'required|exists:mdls,idModule'
        ]);

        DB::table('projets')->where('idProjet', $idProjet)->update([
            'idModule' => $req->idModule
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Opération effectuée avec succès'
        ]);
    }

    public function updateNumeroBc(Request $req, $idProjet)
    {
        $req->validate([
            'project_reference' => 'nullable|string|max:255'
        ]);

        $updated = DB::table('projets')
            ->where('idProjet', $idProjet)
            ->update([
                'project_reference' => $req->project_reference
            ]);

        if ($updated) {
            return response()->json([
                'status' => 200,
                'message' => 'Opération effectuée avec succès'
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Projet introuvable ou inchangé'
            ]);
        }
    }


    public function updateProjet(Request $req, $idProjet)
    {
        $validate = Validator::make($req->all(), [
            'nbPlace' => 'numeric'
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->messages()]);
        } else {
            try {
                if ($req->project_type == 'Inter') {
                    DB::table('inters')->where('idProjet', $idProjet)->update([
                        'nbPlace' => $req->nbPlace,
                    ]);
                }

                DB::table('projets')->where('idProjet', $idProjet)->update([
                    'project_reference' => $req->project_reference,
                    'idBC' => $req->idBC,
                    'project_title' => $req->project_title,
                    'project_description' => $req->project_description
                ]);

                return response()->json(['success' => 'Modifié avec succès !']);
            } catch (Exception $e) {
                return response()->json(['error' => 'Erreur inconnue !']);
            };
        }
    }

    public function getMiniCV($idFormateur)
    {
        $form = DB::table('users')
            ->select('id', 'name', 'email', 'firstName', 'phone', 'photo')
            ->where('id', $idFormateur);

        if ($form->exists()) {
            // Expériences
            $exp = DB::table('experiences')
                ->select('id', 'idFormateur', 'Lieu_de_stage', 'Fonction', 'Date_debut', 'Date_fin', 'Lieu')
                ->where('idFormateur', $idFormateur)
                ->get();

            // Diplômes
            $dp = DB::table('diplomes')
                ->select('id', 'idFormateur', 'Ecole', 'Diplome', 'Domaine', 'Date_debut', 'Date_fin')
                ->where('idFormateur', $idFormateur)
                ->get();

            // Compétences
            $cpc = DB::table('competences')
                ->select('id', 'idFormateur', 'Competence', 'note')
                ->where('idFormateur', $idFormateur)
                ->get();

            // Langues
            $lg = DB::table('langues')
                ->select('id', 'idFormateur', 'Langue', 'note')
                ->where('idFormateur', $idFormateur)
                ->get();

            $speciality = DB::table('formateurs')->select('form_titre')->where('idFormateur', $idFormateur)->first();

            return response()->json([
                'status' => 200,
                'form' => $form->first(),
                'experiences' => $exp,
                'diplomes' => $dp,
                'competences' => $cpc,
                'langues' => $lg,
                'speciality' => $speciality
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'formateur introuvable !'
            ], 204);
        }
    }

    public function fraisdetailsEtp($idProjet, $isEtp)
    {
        $isEtp = [0, 1];
        $fraisdetails = DB::table('fraisprojet')
            ->select('idFraisProjet', 'fraisprojet.idProjet', 'fraisprojet.idFrais', 'frais', 'description', 'montant', 'taxe', 'isEtp')
            ->join('projets', 'projets.idProjet', 'fraisprojet.idProjet')
            ->join('frais', 'frais.idFrais', 'fraisprojet.idFrais')
            ->where('fraisprojet.idProjet', $idProjet)
            ->whereIn('fraisprojet.isEtp', $isEtp)
            ->orderBy('fraisprojet.idFraisProjet', 'desc')
            ->get();

        return response()->json(['fraisdetails' => $fraisdetails]);
    }

    private function sortMonthsChronologically($grouped)
    {
        // Convertir en array pour le tri
        $monthsArray = array_values($grouped);

        // Trier par année décroissante puis par mois décroissant
        usort($monthsArray, function ($a, $b) {
            // Comparer d'abord par année
            if ($a['year'] !== $b['year']) {
                return (int)$b['year'] - (int)$a['year']; // Ordre décroissant
            }
            // Si même année, comparer par mois
            return (int)$b['monthNumber'] - (int)$a['monthNumber']; // Ordre décroissant
        });

        return $monthsArray;
    }

    public function create(Request $request)
    {
        $projectType = $request->project_type;
        $title = $request->title;
        $description = $request->description;
        $entreprisesId = $request->enterprise_id ?? [];
        $particularsId = $request->individual_id ?? [];
        $modalitiId = $request->modality;
        $purchaseOrderId = $request->purchase_order_id;
        $folderId = $request->folder_id;
        $dateBegin = $request->date_begin;
        $dateEnd = $request->date_end;
        $courseId = $request->course_id;

        if ($projectType == "Inter") {
            return $this->createProjectInter($title, $description, $entreprisesId, $modalitiId, $purchaseOrderId, $folderId, $dateBegin, $dateEnd, $courseId);
        } elseif ($projectType == "Intra") {
            return $this->project->createProjectIntra($title, $description, $entreprisesId, $modalitiId, $purchaseOrderId, $folderId, $dateBegin, $dateEnd, $courseId, null);
        } elseif ($projectType == "Individual") {
            return $this->createProjectParticular($title, $description, $particularsId, $modalitiId, $dateBegin, $dateEnd, $courseId);
        }

        return response()->json(['error' => 'Invalid project type'], 400);
    }

    private function createProjectInter($title, $description, $entreprisesId, $modalitiId, $purchaseOrderId, $folderId, $dateBegin, $dateEnd, $courseId)
    {
        try {
            $projectId = DB::table('projets')->insertGetId([
                'project_title' => $title,
                'project_description' => $description,
                'dateDebut' => $dateBegin,
                'dateFin' => $dateEnd,
                'idModalite' => $modalitiId,
                'idModule' => $courseId,
                'idCustomer' => Customer::idCustomer(),
                'idTypeProjet' => 2,
                'idDossier' => $folderId,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('inters')->insert([
                'idCfp' => Customer::idCustomer(),
                'idProjet' => $projectId
            ]);

            foreach ($entreprisesId as $id) {
                DB::table('inter_entreprises')->insert([
                    'idEtp' => $id,
                    'idProjet' => $projectId
                ]);
            }

            return response()->json(['success' => true, 'project_id' => $projectId]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function createProjectParticular($title, $description, $particularsId, $modalitiId, $dateBegin, $dateEnd, $courseId)
    {
        try {
            $projectId = DB::table('projets')->insertGetId([
                'project_title' => $title,
                'project_description' => $description,
                'dateDebut' => $dateBegin,
                'dateFin' => $dateEnd,
                'idModalite' => $modalitiId,
                'idCustomer' => Customer::idCustomer(),
                'idModule' => $courseId,
                'idTypeProjet' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            foreach ($particularsId as $id) {
                DB::table('particulier_projet')->insert([
                    'idParticulier' => $id,
                    'idProjet' => $projectId,
                    'date_attribution' => now()
                ]);
            }

            return response()->json(['success' => true, 'project_id' => $projectId]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updatePurchaseOrder($projectId, Request $request)
    {
        try {
            DB::table('projets')
                ->where('idProjet', $projectId)
                ->update(['idBc' => $request->orderId]);

            $purchaseOrder = DB::table('bon_commandes')
                ->select('numero', 'idBc')
                ->where('idBc', $request->orderId)
                ->first();

            return response()->json([
                'success' => 'Project updated succesfully',
                'order' => $purchaseOrder
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function geePurchaseOrderByProject($projectId)
    {
        try {
            $purchaseOrder = DB::table('projets as P')
                ->join('bon_commandes as B', 'B.idBc', 'P.idBc')
                ->select('B.numero', 'B.idBc')
                ->where('P.idProjet', $projectId)
                ->first();

            return response()->json($purchaseOrder, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAllParticular()
    {
        $particulars = DB::table('cfp_particuliers as P')
            ->join('users as U', 'U.id', 'P.idParticulier')
            ->select('U.name', 'U.firstName', 'U.photo', 'U.id')
            ->where('P.idCfp', Customer::idCustomer())
            ->get();

        return response()->json($particulars, 200);
    }

    public function getEntrepriseByProject($id)
    {
        $projectType = $this->getProjectType($id);
        $entreprises = $this->project->getEntrepriseByProject($projectType, $id);

        return response()->json($entreprises, 200);
    }

    public function getLearnerByEntreprise($id)
    {
        $learners = DB::table('employes as E')
            ->join('users as U', 'U.id', 'E.idEmploye')
            ->join('role_users as R', 'R.user_id', 'U.id')
            ->select('U.name', 'U.firstName', 'U.photo', 'U.id')
            ->where('E.idCustomer', $id)
            ->where('R.role_id', 4)
            ->get();

        return response()->json($learners, 200);
    }

    public function getLearnerByKey($id, Request $request)
    {
        $key = $request->key;

        $projectType = $this->getProjectType($id);

        $learners = [];
        if ($projectType === 4) {
            $learners = DB::table('cfp_particuliers as P')
                ->join('users as U', 'U.id', 'P.idParticulier')
                ->select('U.name', 'U.firstName', 'U.photo', 'U.id')
                ->where('P.idCfp', Customer::idCustomer())
                ->where(function ($query) use ($key) {
                    $query->where('U.name', 'like', "%$key%")
                        ->orWhere('U.firstName', 'like', "%$key%")
                        ->orWhere(DB::raw('CONCAT(U.name, U.firstName)'), 'like', "%$key%");
                })
                ->get();
        } else {
            $etpAssigned = $this->project->getEntrepriseByProject($projectType, $id);

            $etpIds = [];

            foreach ($etpAssigned as $e) {
                $etpIds[] = $e->idEtp;
            }

            $learners = DB::table('employes as E')
                ->join('users as U', 'U.id', 'E.idEmploye')
                ->join('role_users as R', 'R.user_id', 'U.id')
                ->select('U.name', 'U.firstName', 'U.photo', 'U.id')
                ->whereIn('E.idCustomer', $etpIds)
                ->where('R.role_id', 4)
                ->where(function ($query) use ($key) {
                    $query->where('U.name', 'like', "%$key%")
                        ->orWhere('U.firstName', 'like', "%$key%")
                        ->orWhere(DB::raw('CONCAT(U.name, U.firstName)'), 'like', "%$key%");
                })
                ->get();
        }
        return response()->json($learners, 200);
    }

    public function getLearnerAddedByProject($id)
    {
        $projectType = $this->getProjectType($id);
        $learnersAdded = [];
        if ($projectType === 1) {
            $learnersAdded = $this->getLeanerByProjectIntra($id);
        } elseif ($projectType === 2) {
            $learnersAdded = $this->getLeanerByProjectInter($id);
        } elseif ($projectType === 4) {
            $learnersAdded = $this->getLeanerByProjectParticular($id);
        }

        return response()->json($learnersAdded, 200);
    }

    public function getProjectType($projectId)
    {
        $project = DB::table('projets')->select('idTypeProjet')->where('idProjet', $projectId)->first();

        return $project->idTypeProjet;
    }

    public function getLeanerByProjectIntra($projectId)
    {
        return DB::table('detail_apprenants as D')
            ->join('users as U', 'U.id', 'D.idEmploye')
            ->select('U.name', 'U.firstName', 'U.photo', 'U.id')
            ->where('D.idProjet', $projectId)
            ->get();
    }

    public function getLeanerByProjectInter($projectId)
    {
        return DB::table('detail_apprenant_inters as D')
            ->join('users as U', 'U.id', 'D.idEmploye')
            ->select('U.name', 'U.firstName', 'U.photo', 'U.id')
            ->where('D.idProjet', $projectId)
            ->get();
    }

    public function getLeanerByProjectParticular($projectId)
    {
        return DB::table('particulier_projet as P')
            ->join('users as U', 'U.id', 'P.idParticulier')
            ->select('U.name', 'U.firstName', 'U.photo', 'U.id')
            ->where('P.idProjet', $projectId)
            ->get();
    }

    public function getEntrepriseAssigned($projectId)
    {
        $projectType = $this->getProjectType($projectId);
        $entreprises = $this->project->getEntrepriseByProject($projectType, $projectId);

        return response()->json($entreprises, 200);
    }

    public function getTotalCost($projectId)
    {
        $cost = DB::table('projets')
            ->select('total_ht', 'total_ttc')
            ->where('idProjet', $projectId)
            ->first();

        return response()->json($cost, 200);
    }

    public function assignLearnerToProject($projectId, $employeeId)
    {
        $projectType = $this->getProjectType($projectId);
        if ($projectType === 1) {
            DB::table('detail_apprenants')->insert([
                'idProjet' => $projectId,
                'idEmploye' => $employeeId
            ]);
        } elseif ($projectType === 2) {
            $etpId = DB::table('employes')->select('idCustomer')->where('idEmploye', $employeeId)->first();
            DB::table('detail_apprenant_inters')->insert([
                'idProjet' => $projectId,
                'idEmploye' => $employeeId,
                'idEtp' => $etpId->idCustomer
            ]);
        } elseif ($projectType === 4) {
            DB::table('particulier_projet')->insert([
                'idProjet' => $projectId,
                'idParticulier' => $employeeId
            ]);
        }

        return response()->json('Learner successfully added', 200);
    }

    public function deassignLearnerToProject($projectId, $employeeId)
    {
        $projectType = $this->getProjectType($projectId);
        if ($projectType === 1) {

            DB::table('detail_apprenants')
                ->where([
                    ['idProjet', '=', $projectId],
                    ['idEmploye', '=', $employeeId],
                ])
                ->delete();
        } elseif ($projectType === 2) {

            DB::table('detail_apprenant_inters')
                ->where([
                    ['idProjet', '=', $projectId],
                    ['idEmploye', '=', $employeeId],
                ])
                ->delete();
        } elseif ($projectType === 4) {

            DB::table('particulier_projet')
                ->where([
                    ['idProjet', '=', $projectId],
                    ['idParticulier', '=', $employeeId],
                ])
                ->delete();
        }
        return response()->json('Learner successfully added', 200);
    }

    public function getLearnerByProject($id)
    {
        $projectType = $this->getProjectType($id);
        $learners = [];
        if ($projectType === 1) {
            $learners = $this->getLeanerByProjectIntra($id);
        } elseif ($projectType === 2) {
            $learners = $this->getLeanerByProjectInter($id);
        } elseif ($projectType === 4) {
            $learners = $this->getLeanerByProjectParticular($id);
        }

        return response()->json($learners, 200);
    }
}
