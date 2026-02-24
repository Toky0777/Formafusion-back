<?php

namespace App\Traits;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait FolderQuery
{
    public function getFolder($key)
    {
        $folders = DB::table('dossiers as D')
            ->join('projets as P', 'P.idDossier', 'D.idDossier')
            ->select('D.idDossier', 'D.nomDossier', DB::raw('COUNT(P.idProjet) as nbProjet'))
            ->where('D.nomDossier', 'like', "%$key%")
            ->where('D.idCfp', 2)
            ->groupBy('P.idDossier')
            ->get();

        $results = [];

        foreach ($folders as $folder) {
            $results[] = [
                'idDossier' => $folder->idDossier,
                'nomDossier' => $folder->nomDossier,
                'nbProjet' => $this->getTotalProjectByFolder($folder->idDossier),
                'entreprises' => implode(', ', $this->getEntrepriseByFolder($folder->idDossier)->toArray()),
                'modules' => implode(', ', $this->getModuleByFolder($folder->idDossier)->toArray()),
                'dates' => $this->getDateByfolder($folder->idDossier),
                'typeProjets' => implode(', ', $this->getTypeProjectByFolder($folder->idDossier)->toArray()),
                'villes' => implode(', ', $this->getVilleByFolder($folder->idDossier)->toArray()),
                'status' => $this->getMinStatusByFolder($folder->idDossier),
                'prix' => $this->getTotalPriceByFolder($folder->idDossier),
                'document' => $this->getNumberDocumentByFolder($folder->idDossier)
            ];
        }

        return $results;
    }

    public function getTotalProjectByFolder($idDossier)
    {
        $projects = DB::table('projets')
            ->where('idDossier', $idDossier)
            ->pluck('idProjet');

        return count($projects) ?? 0;
    }

    public function getEntrepriseByFolder($id)
    {
        $customers = DB::table('v_projet_cfps')
            ->where('idDossier', $id)
            ->pluck('etp_name')
            ->unique();
        return $customers;
    }

    public function getModuleByFolder($id)
    {
        $modules = DB::table('v_projet_cfps')
            ->where('idDossier', $id)
            ->pluck('module_name')
            ->unique();

        return $modules;
    }

    public function getDateByfolder($id)
    {
        $dates = DB::table('v_projet_cfps')
            ->select(DB::raw('MIN(dateDebut) as dateDebut'), DB::raw('MAX(dateFin) as dateFin'))
            ->where('idDossier', $id)
            ->first();

        $dates->dateDebut = Carbon::parse($dates->dateDebut)->format('m/d/y');
        $dates->dateFin = Carbon::parse($dates->dateFin)->format('m/d/y');
        return $dates;
    }

    public function getTypeProjectByFolder($id)
    {
        $types = DB::table('v_projet_cfps')
            ->where('idDossier', $id)
            ->pluck('project_type')
            ->unique();
        return $types;
    }

    public function getTotalPriceByFolder($id)
    {
        $total = DB::table('v_projet_cfps')
            ->where('idDossier', $id)
            ->value(DB::raw('SUM(total_ht)'));

        return $total ?? 0;
    }

    public function getNumberDocumentByFolder($id)
    {
        $document = DB::table('documents')
            ->where('idDossier', $id)
            ->value(DB::raw('COUNT(idDocument)'));
        return $document;
    }

    public function getVilleByFolder($id)
    {
        $villes = DB::table('v_projet_cfps')
            ->where('idDossier', $id)
            ->pluck('ville')
            ->unique();
        return $villes;
    }

    public function getMinStatusByFolder($id)
    {
        $statusOrder = [
            'En préparation',
            'En cours',
            'Planifié',
            'Terminé',
            'Annulé',
            'Reporté',
            'Cloturé'
        ];

        $statusIndexQuery = DB::raw(
            'MIN(CASE ' .
                implode(' ', array_map(function ($status, $index) {
                    return "WHEN v_projet_cfps.project_status = '$status' THEN $index";
                }, $statusOrder, array_keys($statusOrder)))
                . ' END) as statusIndex'
        );

        $result = DB::table('v_projet_cfps')
            ->select($statusIndexQuery)
            ->where('idDossier', $id)
            ->where('project_is_trashed', 0)
            ->first();

        $minStatusIndex = $result->statusIndex;

        return $minStatusIndex !== null ? $statusOrder[$minStatusIndex] : "--";
    }
}