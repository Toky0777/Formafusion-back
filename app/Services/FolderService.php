<?php

namespace App\Services;

use App\Interfaces\FolderInterface;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FolderService implements FolderInterface
{
    public function index($year): mixed
    {
        $projects = DB::table('projets as p')
            ->select('p.idDossier', 'd.nomDossier as folder_name', 'intras.idEtp')
            ->join('dossiers as d', 'p.idDossier', 'd.idDossier')
            ->leftJoin('images as img', 'img.idProjet', 'p.idProjet')
            ->leftJoin('intras', 'p.idProjet', 'intras.idProjet')
            ->where('intras.idEtp', Customer::idCustomer())
            ->whereYear('img.created_at', $year)
            ->groupBy('p.idDossier', 'nomDossier', 'intras.idEtp')
            ->orderBy('nomDossier', 'asc')
            ->get();

        return $projects;
    }

    public function folderIntras($year): mixed
    {
        $folderIntras = DB::table('projets as p')
            ->select('p.idDossier', 'd.nomDossier as folder_name', 'intras.idEtp')
            ->join('dossiers as d', 'p.idDossier', 'd.idDossier')
            ->leftJoin('images as img', 'img.idProjet', 'p.idProjet')
            ->leftJoin('intras', 'p.idProjet', 'intras.idProjet')
            ->where('intras.idEtp', Customer::idCustomer())
            ->whereYear('img.created_at', $year)
            ->groupBy('p.idDossier', 'nomDossier', 'intras.idEtp')
            ->orderBy('nomDossier', 'asc');
        // ->get();

        return $folderIntras;
    }

    public function folderInters($year): mixed
    {
        $folderInters = DB::table('projets as p')
            ->select('p.idDossier', 'd.nomDossier as folder_name', 'ietp.idEtp')
            ->join('dossiers as d', 'p.idDossier', 'd.idDossier')
            ->leftJoin('images as img', 'img.idProjet', 'p.idProjet')
            ->leftJoin('inter_entreprises as ietp', 'p.idProjet', 'ietp.idProjet')
            ->where('ietp.idEtp', Customer::idCustomer())
            ->whereYear('img.created_at', $year)
            ->groupBy('p.idDossier', 'nomDossier', 'ietp.idEtp')
            ->orderBy('nomDossier', 'asc');
        // ->get();

        return $folderInters;
    }
    public function foldersByformateur($year): mixed
    {
        $folders = DB::table('projets as p')
            ->select('p.idDossier', 'd.nomDossier as folder_name', 'img.created_at')
            ->join('dossiers as d', 'p.idDossier', 'd.idDossier')
            ->leftjoin('project_forms as pf', 'p.idProjet', 'pf.idProjet')
            ->leftJoin('images as img', 'img.idProjet', 'p.idProjet')
            //->leftJoin('inter_entreprises as ietp', 'p.idProjet', 'ietp.idProjet')
            ->where('pf.idFormateur', Auth::user()->id)
            ->whereYear('img.created_at', $year ?? now())
            ->groupBy('p.idDossier', 'nomDossier')
            ->orderBy('nomDossier', 'asc');
        // ->get();

        return $folders;
    }

    public function projectByFormateur($idDossier): mixed
    {
        $query = DB::table('projets as p')
            ->select(
                'p.idProjet',
                'p.projectName as project_name',
                'p.project_title',
                'p.dateDebut as project_start_date',
                'p.dateFin as project_end_date',
                'p.idDossier',
                'd.nomDossier as folder_name',
                'vc.ville_name',
                'vc.vi_code_postal',
                'vc.idVille',
                'v.ville'
            )
            ->join('ville_codeds as vc', 'p.idVilleCoded', '=', 'vc.id')
            ->join('villes as v', 'vc.idVille', '=', 'v.idVille')
            ->join('dossiers as d', 'p.idDossier', '=', 'd.idDossier')
            ->join('project_forms as pf', 'p.idProjet', '=', 'pf.idProjet')
            ->where('pf.idFormateur', Auth::user()->id);

        if ($idDossier != 1) {
            $query->where('p.idDossier', $idDossier);
        }

        $query->groupBy(
            'p.idProjet',
            'p.projectName',
            'p.project_title',
            'p.dateDebut',
            'p.dateFin',
            'p.idDossier',
            'd.nomDossier',
            'vc.ville_name',
            'vc.vi_code_postal',
            'vc.idVille',
            'v.ville'
        );

        return $query;
    }

    public function projectIntras($idDossier): mixed
    {
        $projectIntras = DB::table('projets as p')
            ->select('p.idProjet', 'p.projectName as project_name', 'p.project_title', 'p.dateDebut as project_start_date', 'p.dateFin as project_end_date', 'p.idDossier', 'd.nomDossier as folder_name', 'intras.idEtp', 'vc.ville_name', 'vc.vi_code_postal', 'vc.idVille', 'v.ville')
            ->join('ville_codeds as vc', 'p.idVilleCoded', 'vc.id')
            ->join('villes as v', 'vc.idVille', 'v.idVille')
            ->join('dossiers as d', 'p.idDossier', 'd.idDossier')
            ->leftJoin('images as img', 'img.idProjet', 'p.idProjet')
            ->leftJoin('intras', 'p.idProjet', 'intras.idProjet')
            ->where('intras.idEtp', Customer::idCustomer())
            ->where('p.idDossier', $idDossier)
            ->groupBy('p.idProjet', 'p.projectName', 'p.project_title', 'p.dateDebut', 'p.dateFin', 'p.idDossier', 'd.nomDossier', 'intras.idEtp', 'vc.ville_name', 'vc.vi_code_postal', 'vc.idVille', 'v.ville');
        // ->get();

        return $projectIntras;
    }

    public function projectInters($idDossier): mixed
    {
        $projectInters = DB::table('projets as p')
            ->select('p.idProjet', 'p.projectName as project_name', 'p.project_title', 'p.dateDebut as project_start_date', 'p.dateFin as project_end_date', 'p.idDossier', 'd.nomDossier as folder_name', 'ietp.idEtp', 'vc.ville_name', 'vc.vi_code_postal', 'vc.idVille', 'v.ville')
            ->join('ville_codeds as vc', 'p.idVilleCoded', 'vc.id')
            ->join('villes as v', 'vc.idVille', 'v.idVille')
            ->join('dossiers as d', 'p.idDossier', 'd.idDossier')
            ->leftJoin('images as img', 'img.idProjet', 'p.idProjet')
            ->leftJoin('inter_entreprises as ietp', 'p.idProjet', 'ietp.idProjet')
            ->where('ietp.idEtp', Customer::idCustomer())
            ->where('p.idDossier', $idDossier)
            ->groupBy('p.idProjet', 'p.projectName', 'p.project_title', 'p.dateDebut', 'p.dateFin', 'p.idDossier', 'd.nomDossier', 'ietp.idEtp', 'vc.ville_name', 'vc.vi_code_postal', 'vc.idVille', 'v.ville');
        // ->get();

        return $projectInters;
    }
    public function getAllFolders()
    {
        $data = DB::table('dossiers as d')
            ->select(DB::raw('YEAR(d.created_at) as year'))
            ->join('projets as p', 'p.idDossier', 'd.idDossier')
            ->join('intras', 'p.idProjet', 'intras.idProjet')
            ->leftJoin('inter_entreprises as ietp', 'p.idProjet', 'ietp.idProjet')
            ->where('intras.idEtp', Customer::idCustomer())
            ->orWhere('ietp.idEtp', Customer::idCustomer())
            ->groupBy(DB::raw('YEAR(d.created_at)'))
            ->orderBy('d.created_at', 'desc');
        return $data;
    }
    public function getAllFoldersFormateur()
    {
        $data = DB::table('dossiers as d')
            ->select(DB::raw('YEAR(d.created_at) as year'))
            ->join('projets as p', 'p.idDossier', 'd.idDossier')
            ->join('intras', 'p.idProjet', 'intras.idProjet')
            ->leftJoin('inter_entreprises as ietp', 'p.idProjet', 'ietp.idProjet')
            ->groupBy(DB::raw('YEAR(d.created_at)'))
            ->orderBy('d.created_at', 'desc');
        return $data;
    }


    public function countFolder($key, $idCustomer)
    {
        return count(DB::table('dossiers as D')
            ->join('projets as P', 'P.idDossier', 'D.idDossier')
            ->select('D.idDossier')
            ->where('D.nomDossier', 'like', "%$key%")
            ->where('D.idCfp', $idCustomer)
            ->groupBy('P.idDossier')
            ->get());
    }

    public function getFolder($key, $idCustomer)
    {
        $folders = DB::table('dossiers as D')
            ->join('projets as P', 'P.idDossier', 'D.idDossier')
            ->select('D.idDossier', 'D.nomDossier', DB::raw('COUNT(P.idProjet) as nbProjet'))
            ->where('D.nomDossier', 'like', "%$key%")
            ->where('D.idCfp', $idCustomer)
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
