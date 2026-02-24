<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\UtilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\DossierService;

class DossierControllerEtp extends Controller
{
    protected $utilService;
    protected $dossierService;

    public function __construct(UtilService $utilService, DossierService $dossierService)
    {
        $this->utilService = $utilService;
        $this->dossierService = $dossierService;
    }

    public function showByIdEtp(Request $request)
    {
        $year = $request->input('year', date('Y'));

        // Ordre des statuts
        $statusOrder = [
            'En préparation',
            'En cours',
            'Planifié',
            'Terminé',
            'Annulé',
            'Reporté',
            'Cloturé'
        ];

        // Récupération des dossiers avec les champs supplémentaires
        $dossiers = DB::table('dossiers')
            ->leftJoin('v_projet_cfps', 'dossiers.idDossier', '=', 'v_projet_cfps.idDossier')
            ->leftJoin('projets', function ($join) {
                $join->on('dossiers.idDossier', '=', 'projets.idDossier')
                    ->where('projets.project_is_trashed', 0);
            })
            ->leftJoin('documents', 'dossiers.idDossier', '=', 'documents.idDossier')
            ->where('v_projet_cfps.idEtp', Customer::idCustomer())
            ->whereYear('dossiers.created_at', $year)
            ->select(
                'dossiers.idDossier',
                'dossiers.nomDossier',
                DB::raw('GROUP_CONCAT(DISTINCT v_projet_cfps.project_type) as project_types'),
                DB::raw('GROUP_CONCAT(DISTINCT v_projet_cfps.module_name) as module_names'),
                DB::raw('GROUP_CONCAT(DISTINCT v_projet_cfps.ville) as villes'),
                DB::raw('GROUP_CONCAT(DISTINCT v_projet_cfps.cfp_name) as cpfs'),
                DB::raw('COUNT(DISTINCT documents.idDocument) as nombreDocument'),
                DB::raw('COUNT(DISTINCT projets.idProjet) as projet_count'),
                DB::raw('MIN(projets.dateDebut) as dateMinProjet'),
                DB::raw('MAX(projets.dateFin) as dateMaxProjet'),
                DB::raw('SUM(DISTINCT projets.total_ht) as montantTotal'),
                DB::raw(
                    'MIN(CASE ' .
                        implode(' ', array_map(function ($status, $index) {
                            return "WHEN v_projet_cfps.project_status = '$status' AND v_projet_cfps.project_is_trashed = 0 THEN $index";
                        }, $statusOrder, array_keys($statusOrder)))
                        . ' END) as statusIndex'
                )
            )
            ->groupBy('dossiers.idDossier', 'dossiers.nomDossier')
            ->orderBy('dossiers.nomDossier', 'asc')
            ->get()
            ->map(function ($dossier) use ($statusOrder) {
                $dossier->minStatus = isset($statusOrder[$dossier->statusIndex]) ? $statusOrder[$dossier->statusIndex] : null;
                unset($dossier->statusIndex);

                // Format arrays
                $dossier->project_types = $dossier->project_types ? explode(',', $dossier->project_types) : [];
                $dossier->module_names = $dossier->module_names ? explode(',', $dossier->module_names) : [];
                $dossier->villes = $dossier->villes ? explode(',', $dossier->villes) : [];
                $dossier->entreprises = $dossier->cpfs ? explode(',', $dossier->cpfs) : [];
                unset($dossier->cpfs);

                return $dossier;
            });

        if ($dossiers->isEmpty()) {
            return response()->json(['message' => 'Aucun dossier trouvé pour cet utilisateur.']);
        }

        return response()->json([
            'message' => 'Dossiers récupérés avec succès.',
            'dossiers' => $dossiers,
        ]);
    }


    public function getDossierDetailEtp($idDossier)
    {
        $dossier = DB::table('dossiers')->where('idDossier', $idDossier)->first();

        $projets = DB::table('v_projet_cfps')
            ->where('idDossier', $idDossier)
            ->where('project_is_trashed', 0)
            ->get()
            ->map(function ($projet) {
                $projet->payment_status = $this->dossierService->getPaymentStatusByProjet($projet->idProjet);
                return $projet;
            });

        $cpfs = DB::table('v_projet_cfps')
            ->distinct()
            ->select('cfp_name')
            ->where('idDossier', $idDossier)
            ->where('project_is_trashed', 0)
            ->get();

        $montantTotal = DB::table('projets')
            ->select(DB::raw('sum(total_ht) as montantTotal'))
            ->where('project_is_trashed', 0)
            ->where('idDOssier', $idDossier)
            ->first();

        $project_types = DB::table('v_projet_cfps')
            ->distinct()
            ->select('project_type')
            ->where('project_is_trashed', 0)
            ->where('idDossier', $idDossier)
            ->get();

        $module_names = DB::table('v_projet_cfps')
            ->distinct()
            ->select('module_name')
            ->where('project_is_trashed', 0)
            ->where('idDossier', $idDossier)
            ->get();

        $villes = DB::table('v_projet_cfps')
            ->distinct()
            ->select('ville')
            ->where('project_is_trashed', 0)
            ->where('idDossier', $idDossier)
            ->get();

        $dateMinProjet = DB::table('projets')
            ->select(DB::raw('min(dateDebut) as dateDebut'))
            ->where('project_is_trashed', 0)
            ->where('idDossier', $idDossier)
            ->get();

        $dateMaxProjet = DB::table('projets')
            ->select(DB::raw('max(dateFin) as dateFin'))
            ->where('project_is_trashed', 0)
            ->where('idDossier', $idDossier)
            ->get();

        $nombreDocument = DB::table('documents')
            ->select(DB::raw('count(*) as nombreDocument'))
            ->where('idDossier', $idDossier)
            ->first();

        $nbProjet = DB::table('projets')
            ->select(DB::raw('COUNT(projets.idDossier) as projet_count'))
            ->where('project_is_trashed', 0)
            ->where('idDossier', $idDossier)
            ->first();
        // Raiso ny project_status rehetra an'ilay dossier
        $statuses = DB::table('v_union_projets')
            ->select('project_status')
            ->where('idDossier', $idDossier)
            ->where('project_is_trashed', 0)
            ->pluck('project_status')
            ->toArray();

        // Raiso ny status farany (na azonao amboarina araka izay tianao)
        $status = count($statuses) ? end($statuses) : 'En préparation';
        $paymentStatus = $this->dossierService->getPaymentStatusDossierDetail($idDossier);

        return response()->json([
            'nomDossier' => $dossier->nomDossier ?? 'Nom inconnu',
            'cpfs' => $cpfs,
            'montantTotal' => $montantTotal->montantTotal,
            'project_types' => $project_types,
            'dateMinProjet' => $dateMinProjet,
            'dateMaxProjet' => $dateMaxProjet,
            'villes' => $villes,
            'module_names' => $module_names,
            'projet_count' => $nbProjet->projet_count,
            'nombreDocument' => $nombreDocument->nombreDocument,
            'status' => $status,
            'projets' => $projets, // ✅ Ampidirina eto
            'payment_status' => $paymentStatus, // ✅ Ajout ici
        ]);
    }

    function getFichierEtp($idDossier)
    {
        $projects = DB::table('v_union_projets')
            ->select(
                'idProjet',
                'dateDebut',
                'dateFin',
                'ville',
                'project_status',
                'module_name',
                'idTypeprojet',
                'idCfp_intra',
                'etp_name',
                'total_ht',
                'project_type',
                'project_reference'
            )
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->where('idDossier', $idDossier)
            ->where('project_is_trashed', 0)
            ->orderBy('dateDebut', 'asc')
            ->get();

        $projets = [];
        $totalHtSum = 0;
        $totalNbApprenants = 0;

        foreach ($projects as $project) {
            $totalHtSum += $project->total_ht;

            $totalNbApprenants += $this->getNombreApprenant($project->idProjet);

            $projets[] = [
                'nbApprenant' => $this->getNombreApprenant($project->idProjet),
                'idProjet' => $project->idProjet,
                'ville' => $project->ville,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'total_ht' => $this->utilService->formatPrice($project->total_ht),
                'module_name' => $project->module_name,
                'nameCfp' => ($project->idTypeprojet == 1) ? $this->getNameCfpIntra($project->idCfp_intra) : null,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'project_reference' => $project->project_reference
            ];
        }

        $documents = DB::table('v_document_dossier')
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
            'total_ht_sum' => $this->utilService->formatPrice($totalHtSum),
            'totalNbApprenants' => $totalNbApprenants
        ]);
    }

    function getNombreApprenant($idProjet)
    {
        return DB::table('detail_apprenants')
            ->where('idProjet', $idProjet)
            ->count();
    }

    public function getNameCfpIntra($idCfp)
    {
        $nameCfp = DB::table('v_collaboration_etp_cfps')
            ->select('etp_name')
            ->where('idCfp', $idCfp)
            ->first();
        return $nameCfp->etp_name;
    }
}
