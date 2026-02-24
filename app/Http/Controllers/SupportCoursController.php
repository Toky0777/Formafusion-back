<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class SupportCoursController extends Controller
{


    public function supportEmp()
    {
        $userId = Auth::user()->id;

        // Récupérer les projets de l'employé
        $projects = DB::table('v_projet_emps')
            ->select(
                'idProjet',
                'project_reference',
                'dateDebut',
                'dateFin',
                'module_name',
                'module_image',
                'ville',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'idModule'
            )
            ->where('idEmploye', $userId)
            // ->where('headYear', Carbon::now()->format('Y'))
            ->orderBy('dateDebut', 'asc')
            ->get();

        $moduleIds = $projects->pluck('idModule')->unique()->toArray();

        $moduleRessources = DB::table('module_ressources')
            ->select('idModuleRessource', 'taille', 'module_ressource_name', 'module_ressource_extension', 'idModule')
            ->whereIn('idModule', $moduleIds)
            ->get()
            ->groupBy('idModule');

        $projets = [];
        foreach ($projects as $project) {
            $projets[] = [
                'idProjet' => $project->idProjet,
                'project_reference' => $project->project_reference,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'module_image' => $project->module_image,
                'ville' => $project->ville,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'idModule' => $project->idModule,
                'module_ressources' => $moduleRessources->get($project->idModule, [])
            ];
        }

        return response()->json([
            'projets' => $projets,
        ]);

    }

    public function getModuleRessource($idModule)
    {
        return DB::table('module_ressources')
            ->select('idModuleRessource', 'taille', 'module_ressource_name', 'module_ressource_extension', 'idModule')
            ->where('idModule', $idModule)
            ->get();
    }


    public function download(int $idModuleRessource)
    {
        $file = DB::table('module_ressources')
            ->select('file_path', 'module_ressource_name')
            ->where('idModuleRessource', $idModuleRessource)
            ->first();

        if ($file) {
            $disk = Storage::disk('do');

            if ($disk->exists($file->file_path)) {
                return new StreamedResponse(function () use ($disk, $file) {
                    echo $disk->get($file->file_path);
                }, 200, [
                    'Content-Type' => $disk->mimeType($file->file_path),
                    'Content-Disposition' => 'attachment; filename="' . $file->module_ressource_name . '"',
                ]);
            }
        }

        abort(404);
    }

    

    public function downloadAllModuleRessources($idProjet)
    {
        // Récupérer toutes les ressources du projet
        $ressources = DB::table('module_ressources')
            ->join('projets', 'projets.idModule', '=', 'module_ressources.idModule')
            ->where('projets.idProjet', $idProjet)
            ->select('module_ressources.file_path', 'module_ressources.module_ressource_name')
            ->get();

        if ($ressources->isEmpty()) {
            abort(204, 'Aucune ressource trouvée.');
        }

        $zipFileName = 'supports_projet_' . $idProjet . '.zip';
        $tmpZipPath = storage_path('app/tmp/' . $zipFileName);

        // Créer le dossier temporaire s'il n'existe pas
        if (!file_exists(storage_path('app/tmp'))) {
            mkdir(storage_path('app/tmp'), 0777, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($tmpZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $disk = Storage::disk('do');
            foreach ($ressources as $ressource) {
                if ($disk->exists($ressource->file_path)) {
                    $zip->addFromString(
                        $ressource->module_ressource_name,
                        $disk->get($ressource->file_path)
                    );
                }
            }
            $zip->close();
        } else {
            abort(500, 'Impossible de créer l’archive ZIP.');
        }

        return response()->download($tmpZipPath)->deleteFileAfterSend(true);
    }
    public function getProgramme()
    {
        if (!Auth::check()) {
            abort(401, 'Vous devez être authentifié pour accéder à cette ressource.');
        }

        $userId = Auth::user()->id;
        $projects = $this->getProjectsForUser($userId);

        return response()->json([
            'projects' =>$projects
        ]);
    }
       private function getProjectsForUser($userId)
    {
        return DB::table('v_projet_emps AS projet')
            ->leftJoin('module_ressources', 'module_ressources.idModule', '=', 'projet.idModule')
            ->select(
                'projet.idProjet',
                'projet.dateDebut',
                'projet.dateFin',
                'projet.cfp_name',
                'projet.module_name',
                'projet.project_description',
                DB::raw('COUNT(module_ressources.idModuleRessource) as nb_module_ressources')
            )
            ->where('projet.idEmploye', $userId)
            ->groupBy(
                'projet.idProjet',
                'projet.dateDebut',
                'projet.dateFin',
                'projet.cfp_name',
                'projet.module_name',
                'projet.project_description'
            )
            ->orderBy('dateDebut', 'asc')
            ->get();
    }

    public function show($idProjet)
    {
        $projects = $this->getProjectById($idProjet);
        $module_ressources = $this->getModuleRessourcesByProject($idProjet);
        $sumHourSession = $this->getSumHourSession($idProjet);

        return response()->json([
           'projects'=>$projects ,
           'module_ressources' =>$module_ressources,
           'sumHourSession' =>$sumHourSession
        ]);
    }
    private function getProjectById($idProjet)
    {
        return DB::table('v_projet_emps')
            ->select(
                'idProjet',
                'dateDebut',
                'dateFin',
                'cfp_name',
                'module_name',
                'project_description',
                'modalite'
            )
            ->where('idProjet', $idProjet)
            ->groupBy(
                'idProjet',
                'dateDebut',
                'dateFin',
                'cfp_name',
                'module_name',
                'project_description'
            )
            ->orderBy('dateDebut', 'asc')
            ->first();
    }
    private function getModuleRessourcesByProject($idProjet)
    {
        return DB::table('module_ressources')
            ->join('projets', 'projets.idModule', '=', 'module_ressources.idModule')
            ->where('projets.idProjet', $idProjet)
            ->select(
                'idModuleRessource',
                'taille',
                'module_ressource_name as name',
                'module_ressource_extension as ext',
                'module_ressources.idModule',
                'module_ressources.file_path'
            )
            ->get();
    }

    private function getSumHourSession($idProjet)
    {
        return DB::table('v_seances')
            ->where('idProjet', $idProjet)
            ->selectRaw('SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))) as sumHourSession')
            ->value('sumHourSession');
    }
}
