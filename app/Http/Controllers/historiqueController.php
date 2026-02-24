<?php

namespace App\Http\Controllers;

use App\Exports\ApprenantExcelExport;
use App\Exports\FinanceExport;
use App\Exports\FormationExcelExport;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


class historiqueController extends Controller
{


    public function idCfp()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idCustomer;
    }
    public function apprenant(Request $request)
    {
        $createdCfp = Auth::user()->created_at->format('m-d-Y');

        // Récupération des apprenants
        $all_learner = DB::table('v_apprenant_information')
            ->select(
                'idEmploye',
                'idModule',
                'emp_matricule',
                'module_name',
                'emp_name',
                'emp_firstname',
                'emp_fonction',
                'salle_name',
                'salle_quartier',
                'project_status',
                'project_type',
                'etp_name',
                'cfp_name',
                'dateDebut',
                'dateFin',
                'dureeH',
                'taux_de_presence',
                'module_image'  // Ajout de module_image
            )
            ->where('idCfp', Customer::idCustomer())
            ->get();

        // Récupération des formations
        $all_cfp_formation = DB::table('v_apprenant_information')
            ->select('idModule', 'module_name')
            ->where('idCfp', Customer::idCustomer())
            ->whereNotNull('module_name')
            ->distinct()
            ->get();

        // Filtrage des dates
        $data_filter = ['Tous les dates', 'Tous les formations'];
        $latestDate = DB::table('v_apprenant_information')->max('dateDebut');
        $earliestDate = DB::table('v_apprenant_information')->min('dateDebut');

        if (is_null($latestDate) || is_null($earliestDate)) {
            $formatedEarliestDate = Carbon::today()->format('m-d-Y');
            $formatedLatestDate = Carbon::today()->format('m-d-Y');
        } else {
            $formatedEarliestDate = Carbon::createFromFormat('Y-m-d', $earliestDate)->format('m-d-Y');
            $formatedLatestDate = Carbon::createFromFormat('Y-m-d', $latestDate)->format('m-d-Y');
        }

        // Retourner uniquement les apprenants et les formations à la vue
        return response()->json([
            'all_learner' => $all_learner,
            'all_cfp_formation' => $all_cfp_formation
        ]);
    }


    public function getLearner()
    {
        // Définit la locale Carbon une seule fois si tu en as besoin globalement
        // Carbon::setLocale('fr'); // Peut être configuré dans AppServiceProvider si c'est pour toute l'application

        $learners = DB::table('v_apprenant_etp_alls')
            ->where(function ($query) {
                // Simplifie les conditions si elles pointent toutes vers le même ID client
                // Tu devras confirmer la logique exacte de tes colonnes idCfp, id_cfp, id_cfp_appr
                $customerId = Customer::idCustomer(); // Appelle idCustomer une seule fois
                $query->where('idCfp', $customerId)
                    ->orWhere('id_cfp', $customerId)
                    ->orWhere('id_cfp_appr', $customerId);
            })
            ->whereNotNull('emp_name')
            ->orderBy('emp_name')
            ->select('idEmploye as id', DB::raw('CONCAT(emp_name, " ", COALESCE(emp_firstname, "")) as name'))
            ->get(); // Utilisez get() pour obtenir une collection, pas pluck() pour un tableau d'objets structurés

        // Convertit la collection en un tableau associatif si le frontend s'attend à ce format
        // Sinon, retourner la collection directement est souvent plus simple pour JS
        $formattedLearners = $learners->mapWithKeys(function ($item) {
            return [$item->id => $item->name];
        });

        // Si le frontend s'attend à un tableau d'objets { id: X, name: Y }
        // return response()->json($learners); 
        // Sinon, si le frontend s'attend à un objet { id1: "Name1", id2: "Name2" }
        return response()->json($formattedLearners);
    }

    public function getProjectLearner(Request $request)
    {
        // 1. Validation du paramètre de l'apprenant
        // Nous nous attendons à 'learnerId' dans la chaîne de requête (e.g., ?learnerId=123)
        $idEmploye = $request->query('learnerId');

        if (empty($idEmploye)) {
            // Retourne une erreur 400 Bad Request si l'ID est manquant
            return response()->json([
                'projets' => [],
                'message' => 'L\'ID de l\'apprenant est manquant.'
            ], 400);
        }

        // Assurez-vous que Customer::idCustomer() renvoie un ID valide
        $customerId = Customer::idCustomer();
        if (empty($customerId)) {
            // Gérer le cas où l'ID du client n'est pas disponible (utilisateur non connecté, etc.)
            return response()->json([
                'projets' => [],
                'message' => 'ID client non disponible. Veuillez vous connecter.'
            ], 403); // 403 Forbidden ou 401 Unauthorized
        }

        // 2. Récupère les IDs de projets associés à l'apprenant et au client
        $idProjects = DB::table('detail_apprenants as D')
            ->join('projets as P', 'D.idProjet', '=', 'P.idProjet')
            ->where('P.idCustomer', $customerId) // Utilisation de $customerId
            ->where('D.idEmploye', $idEmploye)
            ->pluck('D.idProjet');

        if ($idProjects->isEmpty()) {
            // Retourne un succès 200 OK avec une liste vide si aucun projet n'est trouvé
            return response()->json([
                'projets' => [],
                'message' => 'Aucun projet trouvé pour cet apprenant ou ce client.'
            ], 200);
        }

        // 3. Récupère les détails complets des projets trouvés
        $projects = DB::table('projets AS P')
            ->join('mdls as M', 'M.idModule', '=', 'P.idModule')
            ->join('module_levels as L', 'L.idLevel', '=', 'M.idLevel')
            ->join('ville_codeds', 'ville_codeds.id', 'P.idVilleCoded')
            ->join('villes as V', 'V.idVille', '=', 'ville_codeds.idVille')
            ->select(
                'P.idProjet',
                'M.moduleName',
                'V.ville',
                'P.dateDebut',
                'P.dateFin',
                'M.module_image',
                'M.idModule',
                'M.description',
                'M.dureeJ',
                'M.dureeH', // Correction pour s'assurer que c'est bien 'dureeH'
                'L.module_level_name'
            )
            ->whereIn('P.idProjet', $idProjects)
            ->get();

        // 4. Formate les résultats pour le frontend
        Carbon::setLocale('fr'); // Configure la locale une seule fois

        $results = [];
        /** @var \stdClass $project */
        foreach ($projects as $project) {
            // Assurez-vous que dateDebut est une date valide avant de la parser
            $dateDebut = null;
            try {
                $dateDebut = Carbon::parse($project->dateDebut);
            } catch (\Exception $e) {
                // Gérer l'erreur si la date est invalide, ou assigner une valeur par défaut
                \Log::warning("Date de début invalide pour le projet ID {$project->idProjet}: " . $project->dateDebut);
            }

            $results[] = [
                'idProjet' => $project->idProjet,
                'idModule' => $project->idModule,
                'module_name' => $project->moduleName,
                'date_debut' => $this->dateConverted($project->dateDebut),
                'date_fin' => $this->dateConverted($project->dateFin),
                'ville' => $project->ville,
                'dureeJ' => $project->dureeJ,
                'dureeH' => $project->dureeH,
                'module_description' => $project->description,
                'module_image' => $project->module_image,
                // Assurez-vous que $dateDebut est défini avant d'accéder à ses propriétés
                'day' => $dateDebut ? $dateDebut->day : null,
                'mois' => $dateDebut ? $dateDebut->format('M Y') : null,
                'note' => $this->getEval($project->idModule), // Assurez-vous que getEval existe et gère les cas d'erreur
                'level_name' => $project->module_level_name
            ];
        }

        // Retourne les projets formatés en JSON
        return response()->json([
            'projets' => $results,
            'message' => 'Projets récupérés avec succès.'
        ]);
    }

    private function getProjectByModule($idModule)
    {
        $projects = DB::table('v_projet_cfps')
            ->select('idProjet')
            ->where('idModule', $idModule)
            ->pluck('idProjet');
        return $projects;
    }

    private function getEval($idModule)
    {
        $projectIds = $this->getProjectByModule($idModule);

        $result = DB::table('eval_chauds')
            ->select(
                DB::raw('SUM(firstNotes.generalApreciate) as sumFirstNotes'),
                DB::raw('COUNT(DISTINCT firstNotes.idEmploye) as totalEmployees')
            )
            ->fromSub(function ($query) use ($projectIds) {
                $query->select('idEmploye', 'idProjet', 'generalApreciate')
                    ->from('eval_chauds')
                    ->whereIn('idProjet', $projectIds)
                    ->whereNotNull('generalApreciate')
                    ->groupBy('idEmploye', 'idProjet');
            }, 'firstNotes')
            ->first();

        $average = $result->totalEmployees > 0 ? $result->sumFirstNotes / $result->totalEmployees : 0;

        return [
            'totalEmployees' => $result->totalEmployees,
            'average' => round($average, 1)
        ];
    }

    private function dateConverted($date)
    {
        Carbon::setLocale('fr');
        $dateSeance = \Carbon\Carbon::parse($date);
        return  $dateSeance->translatedFormat('d M Y');
    }

    public function searchName($name)
    {
        $idCfp = Auth::user()->id;

        $apprenants = DB::table('v_apprenant_information')
            ->where('idCfp', $idCfp)
            ->where(function ($query) use ($name) {
                $query->where('emp_name', 'LIKE', '%' . $name . '%')
                    ->orWhere('emp_firstname', 'LIKE', '%' . $name . '%');
            })
            ->get();

        return response()->json(['apprenants' => $apprenants]);
    }

    // Export Apprenant List
    public function exportFinanceXl()
    {
        return Excel::download(new FinanceExport, 'Finance.xlsx');
    }
    public function exportXlApp(Request $request)
    {
        return Excel::download(new ApprenantExcelExport($request->session()->get('data')), 'Apprenant.xlsx');
    }
    public function exportPdfApp()
    {
        $all_learner = session()->get('data');
        $data_filter = session()->get('data_filter');
        $pdf = PDF::loadView('CFP.Reporting.formation.dataAppExport', compact(['all_learner', 'data_filter']))->setPaper('a4', 'landscape')->setOption(['defaultFont' => 'Helvetica']);
        return $pdf->download('reportingformation.pdf');
    }

    // Dans historiqueController
    public function getPrograms($idModule)
    {
        return DB::table('programmes')
            ->select('program_title', 'program_description', 'idModule')
            ->where('idModule', $idModule)
            ->get();
    }

    private function getModuleDomaine($idDomaine)
    {
        return DB::table('v_module_cfps')->select('idDomaine')->where('idDomaine', $idDomaine)->where('moduleStatut', 1)->count();
    }

    public function getProjectInterCfp($idProjet)
    {
        $project_cfp = DB::table('v_projet_cfps_inters')
            ->select(
                'idProjet',
                'dateDebut as dateDebut',
                'dateFin as dateFin',
                'project_title',
                'module_name as moduleName',
                'ville',
                'project_status',
                'project_description',
                'project_type',
                'logo_cfp',
                'idCfp_inter',
                'idCfp_inter'
            )
            ->where('project_status', "Planifié")
            ->where('idProjet', $idProjet)
            ->where('project_type', 'Inter')
            ->orderBy('dateDebut')
            ->first();
        return $project_cfp;
    }

    public function getModules($idDomaine, $id)
    {

        $get_mod = DB::table('v_module_cfps')
            ->select('idDomaine', 'moduleName', 'idModule', 'prix', 'dureeJ', 'dureeH', 'moduleStatut', 'module_image', 'module_is_complete', 'cfpName', 'logo', 'module_level_name')
            ->where('idCustomer', $id)
            ->whereNot('moduleName', 'Default module')
            ->where('idDomaine', $idDomaine)
            ->get();

        $modules = [];
         /** @var \stdClass gm */
        foreach ($get_mod as $gm) {

            $modules[] = [
                'idDomaine' => $gm->idDomaine,
                'idModule' => $gm->idModule,
                'module_name' => $gm->moduleName,
                'prix' => $gm->prix,
                'dureeJ' => $gm->dureeJ,
                'dureeH' => $gm->dureeH,
                'moduleStatut' => $gm->moduleStatut,
                'module_image' => $gm->module_image,
                'module_is_complete' => $gm->module_is_complete,
                'cfp_name' => $gm->cfpName,
                'logo_cfp' => $gm->logo,
                'module_level_name' => $gm->module_level_name,
                'note' => $this->getEval($gm->idModule)
            ];
        }

        return $modules;
    }

    public function getDetailFormationInter($id, $idProjet)
    {
        $module = DB::table('v_module_cfps AS M')
            ->join('customers AS C', 'C.idCustomer', '=', 'M.idCustomer')
            ->select(
                'idModule',
                'module_image',
                'reference',
                'moduleName',
                'moduleStatut',
                'M.description',
                'minApprenant',
                'dureeH',
                'dureeJ',
                'maxApprenant',
                'prix',
                'prixGroupe',
                'M.idCustomer',
                'initialName',
                'cfpName as nameCfp',
                'C.logo as etp_logo',
                'nomDomaine',
                'idDomaine',
                'module_is_complete',
                'module_subtitle',
                'module_level_name'
            )
            ->where('moduleStatut', 1)
            ->where('moduleName', '!=', 'Default module')
            ->where('idModule', $id)
            ->first();

        if (!$module) {
            return response()->json(['error' => 'Module non trouvé.'], 404);
        }

        $data = [
            'module' => $module,
            'cfp' => DB::table('customers')->where('idCustomer', $module->idCustomer)->first(),
            'cibles' => DB::table('cible_modules')->where('idModule', $id)->pluck('cible'),
            'prerequis' => DB::table('prerequis_modules')->where('idModule', $id)->pluck('prerequis_name'),
            'objectifs' => DB::table('objectif_modules')->where('idModule', $id)->get(),
            'prog' => $this->getPrograms($id),
            'note' => $this->getEval($id),
        ];

        // Domaines & nb_module
        $all_domaines = DB::table('domaine_formations')->select('idDomaine', 'nomDomaine')->orderBy('nomDomaine')->get();
        $data['domaines'] = $all_domaines->map(function ($d) {
            return [
                'idDomaine' => $d->idDomaine,
                'nomDomaine' => $d->nomDomaine,
                'nb_module' => $this->getModuleDomaine($d->idDomaine),
            ];
        });

        // Projet et sessions
        $project_cfp = $this->getProjectInterCfp($idProjet);
        if ($project_cfp) {
            $data['project'] = [
                'infos' => $project_cfp,
                'sessionsGroupedByDate' => $this->sessionsGroupedByDate($project_cfp->idProjet, $id),
                'startDate' => $this->monthConverted($project_cfp->dateDebut),
                'endDate' => $this->dateConverted($project_cfp->dateFin),
                'forms' => $this->getForms($project_cfp->idProjet),
                'ville' => $project_cfp->ville,
                'nbPlace' => $this->getNbPlace($project_cfp->idProjet),
                'availability' => $this->placeIsAvailable($project_cfp->idProjet),
            ];
        }

        // Online modules
        $get_domaines = DB::table('v_module_cfps')
            ->select('idDomaine', 'nomDomaine')
            ->where('moduleStatut', 1)
            ->where('idCustomer', $module->idCustomer)
            ->where('moduleName', '!=', 'Default module')
            ->groupBy('idDomaine')
            ->get();

        $onlineModules = [];
        /** @var \stdClass $domaine */
        foreach ($get_domaines as $domaine) {
            $modules = count($get_domaines) < 4
                ? DB::table('v_module_cfps')
                ->join('domaine_formations', 'domaine_formations.idDomaine', 'v_module_cfps.idDomaine')
                ->where('v_module_cfps.idDomaine', $domaine->idDomaine)
                ->get()
                ->map(function ($m) {
                    return [
                        'idDomaine' => $m->idDomaine,
                        'idModule' => $m->idModule,
                        'module_name' => $m->moduleName,
                        'prix' => $m->prix,
                        'dureeJ' => $m->dureeJ,
                        'dureeH' => $m->dureeH,
                        'moduleStatut' => $m->moduleStatut,
                        'module_image' => $m->module_image,
                        'module_is_complete' => $m->module_is_complete,
                        'cfp_name' => $m->cfpName,
                        'logo_cfp' => $m->logo,
                        'module_level_name' => $m->module_level_name,
                        'note' => $this->getEval($m->idModule),
                    ];
                })
                : $this->getModules($domaine->idDomaine, $module->idCustomer);

            $onlineModules[] = [
                'idDomaine' => $domaine->idDomaine,
                'nomDomaine' => $domaine->nomDomaine,
                'modules' => $modules,
            ];
        }

        $data['onlineModules'] = $onlineModules;

        return response()->json($data, 200);
    }

    public function getProjectCfp($idModule)
    {
        $project_cfp = DB::table('v_projet_cfps_inters')
            ->select(
                'idProjet',
                'dateDebut as dateDebut',
                'dateFin as dateFin',
                'project_title',
                'module_name as moduleName',
                'ville_name as ville',
                'project_status',
                'project_description',
                'project_type',
                'logo_cfp',
                'idCfp_inter',
                'idCfp_inter'
            )
            ->where('project_status', "Planifié")
            ->where('idModule', $idModule)
            ->where('project_type', 'Inter')
            ->orderBy('dateDebut')
            ->get();
        return $project_cfp;
    }


    public function exportPdf($id)
    {
        $module = DB::table('v_module_cfps AS M')
            ->join('customers AS C', 'C.idCustomer', '=', 'M.idCustomer')
            ->select(
                'idModule',
                'module_image',
                'reference',
                'moduleName',
                'moduleStatut',
                'M.description',
                'minApprenant',
                'dureeH',
                'dureeJ',
                DB::raw('COALESCE(maxApprenant, 0) as maxApprenant'),
                'prix',
                'prixGroupe',
                'M.idCustomer',
                'initialName',
                'cfpName as nameCfp',
                'C.logo as etp_logo',
                'nomDomaine',
                'idDomaine',
                'module_is_complete',
                'module_subtitle',
                'module_level_name'
            )
            ->whereNot('moduleName', 'Default module')
            ->where('idModule', $id)
            ->orderBy('moduleName', 'desc')
            ->first();

        if (!$module) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        $idCustomer = $module->idCustomer;
        $cfp = DB::table('customers')->select('*')->where('idCustomer', $idCustomer)->first();
        $cibles = DB::table('cible_modules')->where('idModule', $module->idModule)->pluck('cible');
        $prerequis = DB::table('prerequis_modules')->where('idModule', $module->idModule)->pluck('prerequis_name');
        $all_domaines = DB::table('domaine_formations')->select('idDomaine', 'nomDomaine')->orderBy('nomDomaine')->get();

        $domaines = [];
       /** @var \stdClass $doma */
        foreach ($all_domaines as $doma) {
            $domaines[] = [
                'idDomaine' => $doma->idDomaine,
                'nomDomaine' => $doma->nomDomaine,
                'nb_module' => $this->getModuleDomaine($doma->idDomaine)
            ];
        }

        $objectifs = DB::table('objectif_modules')
            ->select('idObjectif', 'objectif', 'idModule')
            ->where('idModule', $module->idModule)
            ->get();

        $projects_with_sessions = [];
        $project_cfp = $this->getProjectCfp($id);
         /** @var \stdClass $p */
        foreach ($project_cfp as $p) {
            $projects_with_sessions[$p->idProjet] = [
                'project' => $p,
                'sessionsGroupedByDate' => $this->sessionsGroupedByDate($p->idProjet, $id),
                'projectStartDate' => $this->monthConverted($p->dateDebut),
                'projectEndDate' => $this->dateConverted($p->dateFin),
                'forms' => $this->getForms($p->idProjet),
                'ville' => $p->ville,
                'nbPlace' => $this->getNbPlace($p->idProjet),
                'availability' => $this->placeIsAvailable($p->idProjet)
            ];
        }

        $prog = $this->getPrograms($id);
        $note = $this->getEval($id);

        $get_domaines = DB::table('v_module_cfps')
            ->select('idDomaine', 'nomDomaine')
            ->where('moduleStatut', 1)
            ->where('idCustomer', $idCustomer)
            ->whereNot('moduleName', "Default module")
            ->groupBy('idDomaine', 'nomDomaine')
            ->get();

        $onlineModules = [];
        /** @var \stdClass $d */
        foreach ($get_domaines as $d) {
            $modules = $this->getModules($d->idDomaine, $idCustomer);
            $onlineModules[] = [
                'idDomaine' => $d->idDomaine,
                'nomDomaine' => $d->nomDomaine,
                "modules" => $modules
            ];
        }

        // Générer PDF
        $pdf = PDF::loadView('ETP.reportings.module', [
            'module' => $module,
            'cfp' => $cfp,
            'cibles' => $cibles,
            'prerequis' => $prerequis,
            'domaines' => $domaines,
            'objectifs' => $objectifs,
            'programmes' => $prog, 
            'projects_with_sessions' => $projects_with_sessions,
            'note' => $note,
            'onlineModules' => $onlineModules
        ]);

        return $pdf->download('fiche_module_' . $id . '.pdf');
    }
}
