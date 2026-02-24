<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GalleryEmpController extends Controller
{
    public function getAllGallery()
    {
        // Récupère les années de création des projets du client connecté
        $dataFolders = DB::table('projets')
            ->select(DB::raw('YEAR(created_at) as year'))
            // ->where('idCfp', $idCfp) // Ligne commentée, à activer si besoin
            ->where('idCustomer', Customer::idCustomer())
            ->groupBy(DB::raw('YEAR(created_at)'))
            ->get();

        // Retourner les données en format JSON
        return response()->json([
            'success' => true,
            'data' => $dataFolders
        ]);
    }

    public function getAllFolder(Request $request)
    {

        $userId = Auth::user()->id;

        $folders = DB::table('v_projet_emps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'cfp_name', 'project_title', 'idDossier')
            ->where('idEmploye', $userId)
            ->orderBy('dateDebut', 'asc')
            ->groupBy('idDossier')
            ->get();

        $data = [];
        /** @var \stdClass $folder */
        foreach ($folders as $folder) {
            $idDossier = $folder->idDossier;

            $data[] = [
                'idDossier' => $idDossier,
                'nomDossier' => $folder->project_title,
                'image' => $this->getFirstImage($idDossier),
                'countImage' => $this->countImageByFolder($idDossier)
            ];
        }

        $allFolder = view('employes.gallery.folderListEmp', [
            'data' => $data,
            'year' => $request->year
        ])->render();

        return response()->json($allFolder);
    }

    public function allImage(Request $request)
    {
        $projectIds = $this->getProjectByFolder($request->id);

        $images = DB::table('images')
            ->select('url')
            ->whereIn('idProjet', $projectIds)
            ->get();

        $minAndMaxDate = DB::table('dossiers as D')
            ->join('projets as P', 'P.idDossier', 'D.idDossier')
            ->select(DB::raw('MIN(P.dateDebut) as minDate'), DB::raw('MAX(P.dateFin) as maxDate'))
            ->where('D.idDossier', $request->id)
            ->first();

        $dossier = DB::table('dossiers')
            ->select('nomDossier', DB::raw('YEAR(created_at) as year'))
            ->where('idDossier', $request->id)
            ->first();

        $images = view('employes.gallery.imageListEmp', [
            'data' => $images,
            'date' => $minAndMaxDate,
            'nomDossier' => $dossier->nomDossier,
            'year' => $dossier->year
        ])->render();

        return response()->json($images);
    }

    /****** PRIVATE FUNCTION ****/

    private function getIdCfpByEmploye($idEmploye)
    {
        $cfp = DB::table('c_emps')
            ->where('idEmploye', $idEmploye)
            ->pluck('id_cfp');
        return $cfp;
    }

    private function getFirstImage($id)
    {
        $projectIds = $this->getProjectByFolder($id);

        $image = DB::table('images')->select('url')->whereIn('idProjet', $projectIds)->first();

        return $image->url ?? null;
    }

    private function getProjectByFolder($id)
    {
        $projects = DB::table('projets')
            ->where('idDossier', $id)
            ->pluck('idProjet');
        return $projects;
    }

    private function countImageByFolder($id)
    {
        $projectIds = $this->getProjectByFolder($id);

        $imageCount = DB::table('images')->select('idImages')->whereIn('idProjet', $projectIds)->get();

        return count($imageCount);
    }

    /****** END FUNCTION****/
}
