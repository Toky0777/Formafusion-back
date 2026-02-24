<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\FolderService;
use App\Traits\CheckQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FolderController extends Controller
{
    use CheckQuery;

    protected $folder;

    public function __construct(FolderService $fld)
    {
        $this->folder = $fld;
    }
    private function getFirstImageFolder($id)
    {
        $projectIds = $this->getProjectByFolder($id);

        $image = DB::table('images')
            ->select('url')
            ->whereIn('idProjet', $projectIds)
            ->first();

        return $image->url ?? null;
    }
    private function getProjectByFolder($id)
    {
        $projects = DB::table('projets')
            ->where('idDossier', $id)
            ->pluck('idProjet');
        return $projects;
    }
    public function index($year)
    {

        // $roleId = $this->checkRoleUser(Auth::user()->id)->role_id;
        $roleId = $this->checkRoleUser(Auth::user()->id)->role_id;

        if ($roleId == 3 || $roleId == 6) {
            $intras = $this->folder->folderIntras($year)->get();
            $inters = $this->folder->folderInters($year)->get();
        } elseif ($roleId == 4) {
            $intras = $this->folder->folderIntras($year)->get();
            $inters = $this->folder->folderInters($year)->get();
        }

        $folders = array_merge($intras->toArray(), $inters->toArray());

        $folders = $intras->merge($inters);

        foreach ($folders as $folder) {
            $id = $folder->idDossier; // car c’est un objet Eloquent
            $folder->image = $this->getFirstImageFolder($id);
        }
        if (count($folders) <= 0) {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'count_folders' => $folders->count(),
            'folders' => $folders
        ]);
    }

    public function foldersFomateurs($year)
    {
        $folders = $this->folder->foldersByformateur($year)->get();

        foreach ($folders as $folder) {
            $id = $folder->idDossier; // car c’est un objet Eloquent
            $folder->image = $this->getFirstImageFolder($id);
        }
        if (count($folders) <= 0) {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }

        return response()->json([
            'status' => 200,
            'count_folders' => $folders->count(),
            'folders' => $folders
        ]);
    }

    public function getFoldersFomateurs()
    {
        $folders = $this->folder->foldersByformateur(now())->get();

        foreach ($folders as $folder) {
            $id = $folder->idDossier; // car c’est un objet Eloquent
            $folder->image = $this->getFirstImageFolder($id);
        }
        if (count($folders) <= 0) {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }

        return response()->json([
            'status' => 200,
            'count_folders' => $folders->count(),
            'folders' => $folders
        ]);
    }


    private function getFirstImageProject($id)
    {
        $image = DB::table('images')->select('url')->where('idProjet', $id)->first();

        return $image->url ?? null;
    }
    public function getProjects($idDossier)
    {
        $intras = $this->folder->projectIntras($idDossier)->get();
        $inters = $this->folder->projectInters($idDossier)->get();

        $projects = array_merge($intras->toArray(), $inters->toArray());
        $projects = $intras->merge($inters);
        foreach ($projects as $project) {
            $id = $project->idProjet;
            $project->image = $this->getFirstImageProject($id);
        }
        if (count($projects) <= 0) {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ]);
        }

        return response()->json([
            'status' => 200,
            'count_projects' => $projects->count(),
            'projects' => $projects
        ]);
    }
    public function getProjectsFormateurs($idDossier)
    {
        $projects = $this->folder->projectByFormateur($idDossier)->get();
        foreach ($projects as $project) {
            $id = $project->idProjet;
            $project->image = $this->getFirstImageProject($id);
        }
        // if (count($projects) <= 0) {
        //     return response()->json([
        //         'status' => 404,
        //         'message' => 'Aucun résultat !'
        //     ]);
        // }

        return response()->json([
            'status' => 200,
            'count_projects' => $projects->count(),
            'projects' => $projects
        ]);
    }

    public function getAllFolders()
    {
        $folders = $this->folder->getAllFoldersFormateur()->get();

        return response()->json([
            'status' => 200,
            'folders' => $folders
        ]);
    }
}
