<?php

namespace App\Services;

use App\Interfaces\DossierInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DossierService implements DossierInterface
{
    public function createDossier(string $dossier, int $idCfp): int
    {
        $originalDossier = $dossier;
        $counter = 1;

        while (DB::table('dossiers')->where('nomDossier', $dossier)->where('idCfp', $idCfp)->exists()) {
            $dossier = $originalDossier . "($counter)";
            $counter++;
        }

        return DB::table('dossiers')->insertGetId([
            'nomDossier' => $dossier,
            'idCfp' => $idCfp,
        ]);
    }

    public function getDossiersByCfpAndYear(int $cfpId, int $year, int $month)
    {
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

        // Requête avec la vue 'v_projet_cfps'
        $dossiers = DB::table('dossiers')
            ->leftJoin('v_projet_cfps', 'dossiers.idDossier', '=', 'v_projet_cfps.idDossier')
            ->where('dossiers.idCfp', $cfpId)
            ->whereYear('dossiers.created_at', $year)
            ->whereMonth('dossiers.created_at', $month)
            ->select(
                'dossiers.idDossier',
                'dossiers.nomDossier',
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
            ->get();

        // Traitement des statuts pour chaque dossier
        return $dossiers->map(function ($dossier) use ($statusOrder) {
            $dossier->minStatus = isset($statusOrder[$dossier->statusIndex]) ? $statusOrder[$dossier->statusIndex] : null;
            unset($dossier->statusIndex);
            return $dossier;
        });
    }

    public function getProjetsForDossier($idDossier)
    {
        return DB::table('v_projet_cfps')
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
            ->where('idDossier', $idDossier)
            ->where('project_is_trashed', 0)
            ->where('module_name', '!=', 'Default module')
            ->orderBy('dateDebut', 'asc')
            ->get()
            ->map(function ($project) {
                // CORRECTION: Utiliser getPaymentStatusByProjet
                $paymentStatus = $this->getPaymentStatusByProjet($project->idProjet);

                Log::info("Projet {$project->idProjet} - Statut de paiement: {$paymentStatus}");

                return [
                    'idProjet' => $project->idProjet,
                    'module_name' => $project->module_name ?? 'Module inconnu',
                    'reference' => $project->project_reference ?? '-',
                    'etp_name' => $project->etp_name ?? 'Entreprise inconnue',
                    'ville' => $project->ville ?? 'Ville inconnue',
                    'project_status' => $project->project_status ?? 'Inconnu',
                    'dateDebut' => $project->dateDebut ?? '—',
                    'dateFin' => $project->dateFin ?? '—',
                    'total_ht' => (float)($project->total_ht ?? 0),
                    'payment_status' => $paymentStatus,
                    'participants' => $this->getNombreApprenantsForProject($project->idProjet),
                ];
            });
    }

    private function getNombreApprenantsForProject($idProjet)
    {
        return DB::table('detail_apprenants')
            ->where('idProjet', $idProjet)
            ->count();
    }

    public function getMinStatus($idDossier)
    {
        return $this->getPaymentStatusDossierDetail($idDossier);
    }

    public function getAllDossiersByCfpAndYear(int $idCfp, $year)
    {
        // Ordre des statuts des projets
        $statusOrder = [
            'En préparation',
            'En cours',
            'Planifié',
            'Terminé',
            'Annulé',
            'Reporté',
            'Cloturé'
        ];

        if ($year) {
            $dossiers = DB::table('dossiers')
                ->where('idCfp', $idCfp)
                ->whereYear('created_at', $year)
                ->orderBy('nomDossier', 'asc')
                ->get();
        } else {
            $dossiers = DB::table('dossiers')
                ->where('idCfp', $idCfp)
                ->orderBy('nomDossier', 'asc')
                ->get();
        }

        return $dossiers->map(function ($dossier) use ($idCfp, $statusOrder) {
            // Statut du projet
            $minStatusIndex = DB::table('dossiers')
                ->leftJoin('v_projet_cfps', 'dossiers.idDossier', '=', 'v_projet_cfps.idDossier')
                ->where('dossiers.idDossier', $dossier->idDossier)
                ->select(
                    DB::raw(
                        'MIN(CASE ' .
                            implode(' ', array_map(function ($status, $index) {
                                return "WHEN v_projet_cfps.project_status = '$status' AND v_projet_cfps.project_is_trashed = 0 THEN $index";
                            }, $statusOrder, array_keys($statusOrder)))
                            . ' END) as statusIndex'
                    )
                )
                ->groupBy('dossiers.idDossier')
                ->pluck('statusIndex')
                ->first();

            // Détails supplémentaires
            $paymentStatus = $this->getPaymentStatusDossierDetail($dossier->idDossier);
            $project_types = $this->getProjectTypesDossierDetail($dossier->idDossier, $idCfp)->pluck('project_type')->toArray();
            $module_names = $this->getModuleNamesDossierDetail($dossier->idDossier, $idCfp)->pluck('module_name')->toArray();
            $villes = $this->getVillesDossierDetail($dossier->idDossier, $idCfp)->pluck('ville')->toArray();
            $nombreDocument = $this->getNombreDocumentDossierDetail($dossier->idDossier)->first() ?? 0;
            $projet_count = $this->getNbProjetDossierDetail($dossier->idDossier)->first() ?? 0;
            $apprenants = $this->getApprenantCountDossierDetail($dossier->idDossier);
            $dateMinProjet = $this->getDateMinProjetDossierDetail($dossier->idDossier)->first();
            $dateMaxProjet = $this->getDateMaxProjetDossierDetail($dossier->idDossier)->first();
            $montantTotal = $this->getMontantTotalDossierDetail($dossier->idDossier)->montantTotal ?? 0;

            // 🔥 Récupération des entreprises associées
            $entreprises = $this->getEntreprisesDossierDetail($dossier->idDossier, $idCfp);

            return (object) [
                'idDossier' => $dossier->idDossier,
                'nomDossier' => $dossier->nomDossier,
                'status' => isset($minStatusIndex) ? $statusOrder[$minStatusIndex] : null,
                'paymentStatus' => $paymentStatus,
                'project_types' => $project_types,
                'module_names' => $module_names,
                'villes' => $villes,
                'nombreDocument' => $nombreDocument,
                'projet_count' => $projet_count,
                'apprenants' => $apprenants,
                'dateMinProjet' => $dateMinProjet,
                'dateMaxProjet' => $dateMaxProjet,
                'montantTotal' => $montantTotal,
                'entreprises' => $entreprises,
            ];
        });
    }

    public function dossierExists($nomDossier)
    {
        return DB::table('dossiers')->where('nomDossier', $nomDossier)->exists();
    }

    public function updateDossier($idDossier, $nouveauNom)
    {
        return DB::table('dossiers')
            ->where('idDossier', $idDossier)
            ->update(['nomDossier' => $nouveauNom]);
    }

    public function deleteFiles($idDossier)
    {
        $filePaths = DB::table('documents')
            ->where('idDossier', $idDossier)
            ->pluck('path');

        foreach ($filePaths as $filePath) {
            if (Storage::disk('do')->exists($filePath)) {
                Storage::disk('do')->delete($filePath);
            }
        }

        DB::table('documents')->where('idDossier', $idDossier)->delete();
    }

    public function deleteRelatedProjets($idDossier)
    {
        DB::table('projets')
            ->where('idDossier', $idDossier)
            ->update(['idDossier' => null]);
    }

    public function deleteDossier($idDossier)
    {
        return DB::table('dossiers')->where('idDossier', $idDossier)->delete();
    }

    public function getNomDossier($idDossier)
    {
        return DB::table('dossiers')
            ->where('idDossier', $idDossier)
            ->value('nomDossier');
    }

    public function getEntreprisesDossierDetail($idDossier, $idCfp)
    {
        return DB::table('v_projet_cfps')
            ->distinct()
            ->select('etp_name', 'idEtp')
            ->where(function ($query) use ($idCfp) {
                $query->where('idCfp', $idCfp)
                    ->orWhere('idCfp_inter', $idCfp)
                    ->orWhere('idSubContractor', $idCfp);
            })
            ->where('idDossier', $idDossier)
            ->where('project_is_trashed', 0)
            ->get();
    }

    public function getMontantTotalDossierDetail($idDossier)
    {
        $montants = DB::table('projets as P')
            ->leftJoin('intras as I', 'P.idProjet', '=', 'I.idProjet')
            ->leftJoin('inters as Inter', 'P.idProjet', '=', 'Inter.idProjet')
            ->where('P.idDossier', $idDossier)
            ->where('P.project_is_trashed', 0)
            ->select(
                DB::raw('SUM(CASE WHEN I.idProjet IS NOT NULL THEN P.total_ttc ELSE 0 END) as montant_intra'),
                DB::raw('SUM(CASE WHEN Inter.idProjet IS NOT NULL THEN P.total_ttc ELSE 0 END) as montant_inter'),
                DB::raw('SUM(P.total_ttc) as montant_total')
            )
            ->first();

        return (object)[
            'montantTotal' => $montants->montant_total ?? 0,
            'montantIntra' => $montants->montant_intra ?? 0,
            'montantInter' => $montants->montant_inter ?? 0
        ];
    }

    public function getProjectTypesDossierDetail($idDossier, $idCfp)
    {
        return DB::table('v_projet_cfps')
            ->distinct()
            ->select('project_type')
            ->where('idDossier', $idDossier)
            ->where('project_is_trashed', 0)
            ->get();
    }

    public function getModuleNamesDossierDetail($idDossier, $idCfp)
    {
        return DB::table('v_projet_cfps')
            ->distinct()
            ->select('module_name')
            ->where('idDossier', $idDossier)
            ->where('project_is_trashed', 0)
            ->where('module_name', '!=', 'Default module')
            ->get();
    }

    public function getVillesDossierDetail($idDossier, $idCfp)
    {
        return DB::table('v_projet_cfps')
            ->distinct()
            ->select('ville')
            ->where('idDossier', $idDossier)
            ->where('project_is_trashed', 0)
            ->get();
    }

    public function getDateMinProjetDossierDetail($idDossier)
    {
        return DB::table('projets')
            ->select(DB::raw('min(dateDebut) as dateDebut'))
            ->where('project_is_trashed', 0)
            ->where('idDossier', $idDossier)
            ->pluck('dateDebut');
    }

    public function getDateMaxProjetDossierDetail($idDossier)
    {
        return DB::table('projets')
            ->select(DB::raw('max(dateFin) as dateFin'))
            ->where('project_is_trashed', 0)
            ->where('idDossier', $idDossier)
            ->pluck('dateFin');
    }

    public function getNombreDocumentDossierDetail($idDossier)
    {
        return DB::table('documents')
            ->select(DB::raw('count(*) as nombreDocument'))
            ->where('idDossier', $idDossier)
            ->pluck('nombreDocument');
    }

    public function getNbProjetDossierDetail($idDossier)
    {
        return DB::table('v_projet_cfps')
            ->select(DB::raw('COUNT(idDossier) as projet_count'))
            ->where('idDossier', $idDossier)
            ->where('project_is_trashed', 0)
            ->pluck('projet_count');
    }

    public function getApprenantCountDossierDetail($idDossier)
    {
        // Compter les apprenants des projets intra
        $intraCount = DB::table('detail_apprenants as DA')
            ->join('projets as P', 'DA.idProjet', '=', 'P.idProjet')
            ->join('intras as I', 'P.idProjet', '=', 'I.idProjet')
            ->where('P.idDossier', $idDossier)
            ->where('P.project_is_trashed', 0)
            ->count('DA.idEmploye');

        // Compter les apprenants des projets inter
        $interCount = DB::table('detail_apprenants as DA')
            ->join('projets as P', 'DA.idProjet', '=', 'P.idProjet')
            ->join('inters as I', 'P.idProjet', '=', 'I.idProjet')
            ->where('P.idDossier', $idDossier)
            ->where('P.project_is_trashed', 0)
            ->count('DA.idEmploye');

        return [
            'total' => $intraCount + $interCount,
            'intra' => $intraCount,
            'inter' => $interCount
        ];
    }

    public function getPaymentStatusDossierDetail($idDossier)
    {
        // Récupérer tous les projets du dossier
        $projects = DB::table('projets')
            ->where('idDossier', $idDossier)
            ->where('project_is_trashed', 0)
            ->pluck('idProjet');

        if ($projects->isEmpty()) {
            return 6; // "Non payé" si aucun projet
        }

        $statusCounts = [
            'preparation' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'cancelled' => 0
        ];

        foreach ($projects as $idProjet) {
            $status = $this->getProjectStatus($idProjet);

            switch ($status) {
                case 'En préparation':
                case 'preparation':
                    $statusCounts['preparation']++;
                    break;
                case 'En cours':
                case 'in_progress':
                    $statusCounts['in_progress']++;
                    break;
                case 'Terminé':
                case 'completed':
                    $statusCounts['completed']++;
                    break;
                case 'Annulé':
                case 'cancelled':
                    $statusCounts['cancelled']++;
                    break;
            }
        }

        // Logique de priorité des statuts
        if ($statusCounts['preparation'] > 0) {
            return 1; // "En préparation"
        } elseif ($statusCounts['in_progress'] > 0) {
            return 2; // "En cours"
        } elseif ($statusCounts['completed'] > 0) {
            return 3; // "Terminé"
        } elseif ($statusCounts['cancelled'] > 0) {
            return 4; // "Annulé"
        }

        return 1; // Par défaut "En préparation"
    }

    private function getProjectStatus($idProjet)
    {
        return DB::table('v_projet_cfps')
            ->where('idProjet', $idProjet)
            ->value('project_status');
    }

    public function getPaymentStatusByProjet(int $idProjet)
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

        $status = $isPaid->invoice_status ?? 6;

        Log::info("Statut paiement projet {$idProjet}: {$status}");

        return $status;
    }
}
