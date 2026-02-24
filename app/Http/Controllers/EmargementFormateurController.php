<?php

namespace App\Http\Controllers;

use App\Services\ProjetService;
use Illuminate\Support\Facades\DB;
use App\Traits\PresenceQuery;

class EmargementFormateurController extends Controller
{
    use PresenceQuery;

    protected $projet;

    public function __construct(ProjetService $prj)
    {
        $this->projet = $prj;
    }

    public function index()
    {
        $projects = DB::table('v_projet_form')
            ->select('idProjet', 'idTypeProjet', 'dateDebut', 'dateFin', 'project_reference', 'idFormateur', 'idCfp_inter', 'idCfp', 'project_type', 'module_name', 'project_status')
            ->whereIn('project_status', ['Terminé', 'Cloturé', 'En cours'])
            ->where('idFormateur', auth()->user()->id)
            ->get();

        $projectsID = $projects->pluck('idProjet')->unique()->toArray();

        $allPourcentages = $this->getAllPourcentagesForProjectsFormateur($projectsID);


        $results = [];

        foreach ($projects as $p) {
            $idProjet = $p->idProjet;
            $results[] = [
                'idProjet' => $p->idProjet,
                'dateDebut' => $p->dateDebut,
                'dateFin' => $p->dateFin,
                'module_name' => $p->module_name,
                'entreprises' => $this->projet->getEntrepriseByProject($p->idTypeProjet, $p->idProjet),
                'project_status' => $p->project_status,
                'pourcentage' => $allPourcentages[$idProjet]['pourcentage'] ?? [],
                'isCompleted' => $allPourcentages[$idProjet]['isCompleted'] ?? [],
                'nbApprenant' => $allPourcentages[$idProjet]['nbApprenant'] ?? [],
            ];
        }


        return response()->json(['projects' => $results]);
    }


    public function showEmgFormateur($idProjet)
    {
        $projet = DB::table('projets')
            ->select('idTypeProjet')
            ->where('idProjet', $idProjet)
            ->first();

        if ($projet->idTypeProjet === 1) {
            $emargement = $this->getAttendanceByProject($idProjet);
        } else {
            $emargement = $this->getAttendanceByProjectInter($idProjet);
        }

        return response()->json($emargement, 200);
    }

    public function getDataPresence($idProjet)
    {
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
        return response()->json([

            'seances' => $seances,

        ]);
    }
}
