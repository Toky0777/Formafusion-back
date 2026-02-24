<?php


namespace App\Http\Controllers;

use App\Models\Customer;
use App\Traits\PresenceQuery;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PresenceController extends Controller
{

    use PresenceQuery;

    public function index()
    {
        $projects = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'dateFin', 'module_name', 'project_status')
            ->where('idCfp', Customer::idCustomer())
            ->where('project_status', ['Terminé', 'Cloturé', 'En cours'])
            ->orderBy('dateDebut', 'desc')
            ->get();
        $projectsID = $projects->pluck('idProjet')->unique()->toArray();


        $allFormateurs = $this->getAllFormateursForProjects($projectsID);
        $allEtps = $this->getAllEtpsForProjects($projectsID);
        $allPourcentages = $this->getAllPourcentagesForProjects($projectsID);



        $results = [];

        foreach ($projects as $project) {
            $idProjet = $project->idProjet;
            $results[] = [
                'idProjet' => $project->idProjet,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'dateDebut' => $project->dateDebut,
                'module_name' => $project->module_name,
                'project_status' => $project->project_status,
                'formateurs' => $allFormateurs[$idProjet] ?? [],
                'entreprises' => $allEtps[$idProjet] ?? [],
                'pourcentage' => $allPourcentages[$idProjet]['pourcentage'] ?? [],
                'isCompleted' => $allPourcentages[$idProjet]['isCompleted'] ?? [],
                'nbApprenant' => $allPourcentages[$idProjet]['nbApprenant'] ?? [],
            ];
        }

        return response()->json([
            'status' => 200,
            'projects' => $results
        ], 200);
    }

    public function showEmg($idProjet)
    {
        $projects = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'dateFin', 'module_name', 'project_status')
            ->where('idCfp', Customer::idCustomer())
            ->where('project_status', ['Terminé', 'Cloturé', 'En cours'])
            ->where('idProjet', $idProjet)
            ->orderBy('dateDebut', 'desc')
            ->first();


        $allPourcentages = $this->getAllPourcentagesForProjects([$idProjet]);
        $now = Carbon::now()->toDateString();
        $getSeance = DB::table('v_emargement_appr')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', $now)
            ->groupBy('idSeance')
            ->select('idSeance', 'idProjet', 'heureDebut', 'heureFin', 'dateSeance', 'isPresent', 'idEmploye')
            ->get();
        $getPresence = DB::table('v_emargement_appr')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', $now)
            ->select('idSeance', 'dateSeance', 'idProjet', 'isPresent', 'idEmploye')
            ->get();


        $apprs = $this->getApprForProject($idProjet);

        $projectData = $allPourcentages[$idProjet] ?? [];
        $pourcentage = $projectData['pourcentage'] ?? [];

        $percentPresent = $pourcentage['present'] ?? "0";
        $percentPartiel = $pourcentage['partiel'] ?? "0";
        $percentAbsent = $pourcentage['absent'] ?? "0";

        return response()->json([
            'projects' => $projects,
            'apprs' => $apprs,
            'getSeance' => $getSeance,
            'getPresence' => $getPresence,
            'percentPresent' => $percentPresent,
            'percentPartiel' => $percentPartiel,
            'percentAbsent' => $percentAbsent,
        ]);
    }
}
