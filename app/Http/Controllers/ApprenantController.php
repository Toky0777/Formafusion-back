<?php

namespace App\Http\Controllers;

use App\Imports\ExcelApprenants;
use App\Mail\AddApprenant;
use App\Models\Customer;
use App\Models\Employe;
use App\Models\RoleUser;
use App\Models\User;
use App\Services\ApprenantService;
use App\Services\EmployeService;
use App\Services\EntrepriseService;
use App\Services\UserService;
use App\Traits\GetQuery;
use App\Traits\StoreQuery;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Services\BrevoService;

use App\Services\LearnerCourseService;
use Illuminate\Support\Facades\Log;

class ApprenantController extends Controller
{
    use GetQuery, StoreQuery;


    // public function getSessionProject($idProjet)
    // {
    //     $countSession = DB::table('v_seances')
    //         ->select('idSeance', 'dateSeance', 'heureDebut', 'id_google_seance', 'heureFin', 'idSalle', 'idProjet', 'salle_name', 'salle_quartier', 'project_title', 'project_description', 'idModule', 'module_name', 'ville')
    //         ->where('idProjet', $idProjet)
    //         ->get();

    //     return count($countSession);
    // }

    // public function getFormProject($idProjet)
    // {
    //     $forms = DB::table('v_formateur_cfps')
    //         ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
    //         ->groupBy('idFormateur', 'name', 'firstName', 'photoForm', 'initialNameForm')
    //         ->where('idProjet', $idProjet)->get();

    //     return $forms->toArray();
    // }

    // public function getApprenantProject($idProjet, $idCfp_inter)
    // {
    //     if ($idCfp_inter == null) {
    //         $apprs = DB::table('v_list_apprenants')
    //             ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_photo', 'emp_matricule', 'emp_phone', 'etp_name')
    //             ->where('idProjet', $idProjet)
    //             ->orderBy('emp_name', 'asc')
    //             ->get();
    //     } elseif ($idCfp_inter != null) {
    //         $apprs = DB::table('v_list_apprenant_inter_added')
    //             ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_fonction', 'emp_email', 'emp_photo', 'emp_matricule', 'etp_name', 'idEtp')
    //             ->where('idProjet', $idProjet)
    //             ->orderBy('emp_name', 'asc')
    //             ->get();
    //     }

    //     return count($apprs);
    // }

    // public function getSessionHour($idProjet)
    // {
    //     $countSessionHour = DB::table('v_seances')
    //         ->selectRaw('SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))) as sumHourSession')
    //         ->where('idProjet', $idProjet)
    //         ->first();

    //     return $countSessionHour->sumHourSession;
    // }

    // public function getNote($idProjet)
    // {
    //     $checkEvaluation[] = DB::table('eval_chauds')->select('idProjet')->get();
    //     $checkEvaluationCount = count($checkEvaluation);

    //     if ($checkEvaluationCount > 0) {
    //         $notationProjet[] = DB::table('v_evaluation_alls')
    //             ->select('idProjet', 'idEmploye', 'generalApreciate')
    //             ->where('idProjet', $idProjet)
    //             ->groupBy('idProjet', 'idEmploye')
    //             ->get();

    //         $generalNotation = DB::table('v_general_note_evaluation')
    //             ->select(DB::raw('SUM(generalApreciate) as generalNote'))
    //             ->where('idProjet', $idProjet)
    //             ->first();

    //         $countNotationProjet = count($notationProjet);

    //         if ($countNotationProjet > 0) {
    //             $noteGeneral = $generalNotation->generalNote / $countNotationProjet;
    //             return array_merge([$noteGeneral], [$countNotationProjet]);
    //         } else {
    //             $noteGeneral = 0;
    //             return array_merge([$noteGeneral], [$countNotationProjet]);
    //         }
    //     } else {
    //         $countNotationProjet = 0;
    //         $noteGeneral = 0;
    //         return array_merge([$noteGeneral], [$countNotationProjet]);
    //     }
    // }

    // public function getEtpProjectInter($idProjet, $idCfp_inter)
    // {
    //     if ($idCfp_inter == null) {
    //         $etp = DB::table('v_projet_cfps')
    //             ->select('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
    //             ->where('idProjet', $idProjet)
    //             ->orderBy('etp_name', 'asc')
    //             ->get();
    //     } elseif ($idCfp_inter != null) {
    //         $etp = DB::table('v_list_entreprise_inter')
    //             ->select('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
    //             ->where('idProjet', $idProjet)
    //             ->where('etp_name', '!=', 'null')
    //             ->orderBy('etp_name', 'asc')
    //             ->groupBy('idEtp')
    //             ->get();
    //     }

    //     return $etp->toArray();
    // }


    // public function getRestauration($idProjet)
    // {
    //     $restaurations = DB::table('project_restaurations')
    //         ->select('idRestauration', 'paidBy')
    //         ->where('idProjet', $idProjet)
    //         ->get()
    //         ->toArray();
    //     return $restaurations;
    // }


    // public function checkEval($idProjet)
    // {
    //     $query = DB::table('eval_chauds')->where('idProjet', $idProjet);

    //     if ($query) {
    //         return $query->count();
    //     } else {
    //         return null;
    //     }
    // }

    // public function checkEmg($idProjet)
    // {
    //     $query = DB::table('emargements')->where('idProjet', $idProjet);

    //     if ($query) {
    //         return $query->count();
    //     } else {
    //         return null;
    //     }
    // }

    // private function averageEvalApprenant($idProjet)
    // {
    //     return DB::table('eval_apprenant')
    //         ->select(DB::raw('AVG(avant) as avg_avant'), DB::raw('AVG(apres) as avg_apres'))
    //         ->where('idProjet', $idProjet)
    //         ->first() ?? 0;
    // }

    // private function getApprListProjet($idProjet)
    // {
    //     $apprIntras = DB::table('v_list_apprenants')
    //         ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_photo', 'etp_name')
    //         ->where('idProjet', $idProjet)
    //         ->orderBy('emp_name', 'asc')
    //         ->get()
    //         ->toArray();

    //     $apprenantInters = DB::table('v_list_apprenant_inter_added')
    //         ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_photo', 'etp_name')
    //         ->where('idProjet', $idProjet)
    //         ->orderBy('emp_name', 'asc')
    //         ->get()
    //         ->toArray();

    //     $apprs = array_merge($apprIntras, $apprenantInters);

    //     // return response()->json(['apprs' => $apprs]);
    //     return $apprs;
    // }

    // private function getPlaceAvailable($idProjet)
    // {
    //     $place_validated = DB::table('inter_entreprises')->where('idProjet', $idProjet)->where('isActiveInter', 1)->sum('nbPlaceReserved');
    //     $place_project = DB::table('inters')->where('idProjet', $idProjet)->value('nbPlace');
    //     $place_available = $place_project - $place_validated;
    //     return $place_available;
    // }

    // private function getNbPlaceReserved($idProjet)
    // {
    //     $place_reserved = DB::table('inter_entreprises')->where('idProjet', $idProjet)->count();
    //     return $place_reserved;
    // }


    public function getProjectListEmp()
    {
        if (!Auth::check()) {
            abort(401, 'Vous devez être authentifié pour accéder à cette ressource.');
        }


        $userId = Auth::user()->id;

        $projects = DB::table('v_projet_emps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'cfp_name', 'module_name', 'etp_name', 'ville', 'project_status', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name')
            ->where('idEmploye', $userId)
            ->orderBy('dateDebut', 'asc')
            ->get();
        $projets = [];
        foreach ($projects as $project) {
            $projets[] = [
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'formateurs' => $this->getFormProject($project->idProjet),
                'apprCount' => $this->getApprenantProject($project->idProjet, $project->idCfp_inter),
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'general_note' => $this->getNote($project->idProjet),
                'idProjet' => $project->idProjet,
                'idCfp_inter' => $project->idCfp_inter,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'idEtp' => $project->idEtp,
                'ville' => $project->ville,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'modalite' => $project->modalite,
                'project_description' => $project->project_description,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'cfp_name' => $project->cfp_name,
                'etp_initial_name' => $project->etp_initial_name,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'ville' => $project->ville,
                'etp_name_in_situ' => $project->etp_name,
                'idModule' => $project->idModule,
                'restaurations' => $this->getRestauration($project->idProjet),
                'checkEval' => $this->checkEval($project->idProjet),
                'checkEmg' => $this->checkEmg($project->idProjet),
                'avg_before' => $this->averageEvalApprenant($project->idProjet)->avg_avant,
                'avg_after' => $this->averageEvalApprenant($project->idProjet)->avg_apres,
                'apprs' => $this->getApprListProjet($project->idProjet),
                'sub_name' => $project->sub_name,
                'idSubContractor' => $project->idSubContractor,
                'idCfp' => $project->idCfp,
                'cfp_name' => $project->cfp_name,
            ];
        }
        $projectDates = DB::table('v_projet_emps')
            ->select(DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'))
            ->groupBy('headDate')
            ->orderBy('dateDebut', 'asc')
            ->where('idEmploye', $userId) // Condition pour filtrer par utilisateur
            // ->where('headYear', Carbon::now()->format('Y'))
            ->get();

        $projetCount = DB::table('v_projet_emps')
            ->where('idEmploye', $userId) // Condition pour filtrer par utilisateur
            // ->where('headYear', Carbon::now()->format('Y'))
            ->count();

        return response()->json([
            'projets' => $projets,
            'projetCount' => $projetCount,
            'projectDates' => $projectDates,
            'userId' => $userId
        ]);
    }

    public function getProjectListEmpBystatus(Request $request)
    {
        if (!Auth::check()) {
            abort(401, 'Vous devez être authentifié pour accéder à cette ressource.');
        }

        $status = $request->query('status', 'Terminé');
        $userId = Auth::id();

        // Récupération des projets avec sélection explicite des colonnes
        $projects = DB::table('v_projet_emps')
            ->select([
                'idProjet',
                'dateDebut',
                'idEtp',
                'dateFin',
                'cfp_name',
                'module_name',
                'etp_name',
                'ville',
                'project_status',
                'project_description',
                'project_type',
                'paiement',
                'module_image',
                'etp_logo',
                'etp_initial_name',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'idCfp_inter',
                'modalite',
                'total_ht',
                'total_ttc',
                'idModule',
                'project_inter_privacy',
                'sub_name',
                'idSubContractor',
                'idCfp',
                'cfp_name',
                DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate')
            ])
            ->where('idEmploye', $userId)
            ->where('project_status', $status)
            ->orderBy('dateDebut', 'asc')
            ->get();

        // Préparation des données des projets
        $projets = $projects->map(function ($project) {
            // Récupération des données liées en une seule fois
            $sessionCount = $this->getSessionProject($project->idProjet);
            $formateurs = $this->getFormProject($project->idProjet);
            $apprCount = $this->getApprenantProject($project->idProjet, $project->idCfp_inter);
            $totalSessionHour = $this->getSessionHour($project->idProjet);
            $generalNote = $this->getNote($project->idProjet);
            $etpProjectInter = $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter);
            $restaurations = $this->getRestauration($project->idProjet);
            $averageEval = $this->averageEvalApprenant($project->idProjet);
            $apprs = $this->getApprListProjet($project->idProjet);

            return [
                'idProjet' => $project->idProjet,
                'idCfp_inter' => $project->idCfp_inter,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'etp_name' => $etpProjectInter,
                'idEtp' => $project->idEtp,
                'ville' => $project->ville,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'modalite' => $project->modalite,
                'project_description' => $project->project_description,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'cfp_name' => $project->cfp_name,
                'etp_initial_name' => $project->etp_initial_name,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'etp_name_in_situ' => $project->etp_name,
                'idModule' => $project->idModule,
                'sub_name' => $project->sub_name,
                'idSubContractor' => $project->idSubContractor,
                'idCfp' => $project->idCfp,

                // Données calculées
                'seanceCount' => $sessionCount,
                'formateurs' => $formateurs,
                'apprCount' => $apprCount,
                'totalSessionHour' => $totalSessionHour,
                'general_note' => $generalNote,
                'restaurations' => $restaurations,
                'checkEval' => $this->checkEval($project->idProjet),
                'checkEmg' => $this->checkEmg($project->idProjet),
                'avg_before' => $averageEval->avg_avant ?? null,
                'avg_after' => $averageEval->avg_apres ?? null,
                'apprs' => $apprs,
            ];
        })->toArray();

        // Récupération des dates de projet groupées
        $projectDates = DB::table('v_projet_emps')
            ->select(DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'))
            ->where('idEmploye', $userId)
            ->groupBy('headDate')
            ->orderBy('dateDebut', 'asc')
            ->get();

        // Comptage des projets
        $projetCount = DB::table('v_projet_emps')
            ->where('idEmploye', $userId)
            ->count();

        return response()->json([
            'projets' => $projets,
            'projetCount' => $projetCount,
            'projectDates' => $projectDates,
            'userId' => $userId
        ]);
    }

    public function getPresenceUnique($idProjet, $idEmploye)
    {
        $data = DB::table('emargements')
            ->selectRaw('COUNT(*) as total, SUM(isPresent) as somme')
            ->where('idProjet', $idProjet)
            ->where('idEmploye', $idEmploye)
            ->first();

        if (!$data || $data->total == 0) {
            return response()->json(['checking' => 0]);
        }

        $total = (int)$data->total;
        $sum = (int)$data->somme;

        $present = $total * 3;
        $absent = $total;

        return response()->json([
            'checking' => $sum === $present ? 3 : ($sum < $present && $sum !== $absent ? 2 : ($sum === $absent ? 1 : 0))
        ]);
    }

    public function getProjetEmp($statut)
    {
        $projets = DB::table('v_projet_emps')
            ->select('idProjet', 'idEmploye', 'dateDebut', 'dateFin', 'isActiveProjet', 'formation', 'moduleName', 'customerName')
            ->where('idEmploye', Auth::user()->id)
            ->where('isActiveProjet', 1)
            ->where('statut', $statut)
            ->get();

        $countProjetE = DB::table('v_projet_emps')
            ->select('idProjet')
            ->where('idEmploye', Auth::user()->id)
            ->where('statut', "En cours")
            ->where('isActiveProjet', 1)
            ->count();

        $countProjetP = DB::table('v_projet_emps')
            ->select('idProjet')
            ->where('idEmploye', Auth::user()->id)
            ->where('statut', "Prévisionnel")
            ->where('isActiveProjet', 1)
            ->count();

        $countProjetB = DB::table('v_projet_emps')
            ->select('idProjet')
            ->where('idEmploye', Auth::user()->id)
            ->where('statut', "Brouillant")
            ->where('isActiveProjet', 1)
            ->count();

        $countProjetT = DB::table('v_projet_emps')
            ->select('idProjet')
            ->where('idEmploye', Auth::user()->id)
            ->where('statut', "Terminée")
            ->where('isActiveProjet', 1)
            ->count();

        return response()->json([
            'projets' => $projets,
            'countProjetE' => $countProjetE,
            'countProjetP' => $countProjetP,
            'countProjetB' => $countProjetB,
            'countProjetT' => $countProjetT,
        ]);
    }

    public function detailEmp($idProjet)
    {
        $userId = Auth::id(); // Plus court que Auth::user()->id

        // Récupération du projet et des infos de l'entreprise en une seule requête
        $projet = DB::table('v_projet_emps as p')
            ->leftJoin('customers as c', 'c.idCustomer', '=', 'p.idCustomer')
            ->select(
                'p.*',
                'c.logo as customer_logo',
                'c.customerName as customer_name'
            )
            ->where('p.idProjet', $idProjet)
            ->where('p.idEmploye', $userId)
            ->first();

        if (!$projet) {
            abort(404, "Projet non trouvé !");
        }

        // Formatage des dates
        $deb = Carbon::parse($projet->dateDebut)->locale('fr')->translatedFormat('l j F Y');
        $fin = Carbon::parse($projet->dateFin)->locale('fr')->translatedFormat('l j F Y');

        // Chargement des données associées en une seule requête quand possible
        $villes = DB::table('villes')->select('idVille', 'ville')->get();
        $paiements = DB::table('paiements')->select('idPaiement', 'paiement')->get();
        $modules = DB::table('mdls')
            ->select('idModule', 'moduleName AS module_name')
            ->where('moduleName', '!=', 'Default module')
            ->orderBy('moduleName', 'asc')
            ->get();

        // Récupération des séances
        $seances = DB::table('v_seances')
            ->select('idSeance', 'dateSeance', 'heureDebut', 'heureFin', 'idProjet', 'idModule', 'intervalle_raw')
            ->where('idProjet', $idProjet)
            ->orderBy('dateSeance', 'asc')
            ->get();

        // Comptage des séances par date
        $countDate = DB::table('v_seances')
            ->where('idProjet', $idProjet)
            ->select('dateSeance', DB::raw('COUNT(*) as count'))
            ->groupBy('dateSeance')
            ->get();

        // Calcul du total des heures de session
        $totalSession = DB::table('v_seances')
            ->where('idProjet', $idProjet)
            ->selectRaw('SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))) as sumHourSession')
            ->first();

        // Récupération des éléments du projet
        $materiels = DB::table('prestation_modules')->select('idPrestation', 'prestation_name', 'idModule')->get();
        $prerequis = DB::table('prerequis_modules')->select('idPrerequis', 'prerequis_name', 'idModule')->get();
        $objectifs = DB::table('objectif_modules')->select('idObjectif', 'objectif', 'idModule')->get();

        // Vérification de l'évaluation
        $checkEvaluation = DB::table('eval_chauds')->where('idProjet', $idProjet)->exists();

        if ($checkEvaluation) {
            // Récupération des notes si une évaluation existe
            $notationProjet = DB::table('v_evaluation_alls')
                ->where('idProjet', $idProjet)
                ->select('idProjet', 'idEmploye', 'generalApreciate')
                ->get();

            $generalNotation = DB::table('v_general_note_evaluation')
                ->where('idProjet', $idProjet)
                ->select(DB::raw('SUM(generalApreciate) as generalNote'))
                ->first();

            $countNotationProjet = $notationProjet->count();
            $noteGeneral = $countNotationProjet > 0 ? $generalNotation->generalNote / $countNotationProjet : 0;
        } else {
            $countNotationProjet = 0;
            $noteGeneral = 0;
        }

        // Récupération des images
        $imagesMomentums = DB::table('images')
            ->where(['idProjet' => $idProjet, 'idTypeImage' => 1])
            ->pluck('url');

        // Récupération des places disponibles
        $nbPlace = DB::table('inters')->where('idProjet', $idProjet)->value('nbPlace');
        $place_available = $this->getPlaceAvailable($idProjet) ?? null;
        $place_reserved = $this->getNbPlaceReserved($idProjet) ?? null;

        // Récupération des restaurations
        $restaurations = DB::table('project_restaurations')
            ->where('idProjet', $idProjet)
            ->pluck('idRestauration');

        // Récupération des ressources de module
        $module_ressources = DB::table('module_ressources')
            ->join('projets', 'projets.idModule', '=', 'module_ressources.idModule')
            ->where('projets.idProjet', $idProjet)
            ->select('idModuleRessource', 'taille', 'module_ressource_name', 'module_ressource_extension', 'module_ressources.idModule')
            ->get();

        $eval_type = DB::table('questions')
            ->select('idQuestion', 'question', 'idTypeQuestion')
            ->groupBy('idTypeQuestion')
            ->get();

        return response()->json([
            'module_ressources' => $module_ressources,
            'restaurations' => $restaurations,
            'imagesMomentums' => $imagesMomentums,
            'projet' => $projet,
            'villes' => $villes,
            'paiements' => $paiements,
            'seances' => $seances,
            'modules' => $modules,
            'materiels' => $materiels,
            'objectifs' => $objectifs,
            'totalSession' => $totalSession,
            'countDate' => $countDate,
            'prerequis' => $prerequis,
            'countNotationProjet' => $countNotationProjet,
            'noteGeneral' => $noteGeneral,
            'nbPlace' => $nbPlace,
            'place_available' => $place_available,
            'place_reserved' => $place_reserved,
            'deb' => $deb,
            'fin' => $fin,
            'userId' => $userId,
            'eval_type' => $eval_type
        ]);
    }

    public function getApprAddedInter($idProjet)
    {
        $apprs = DB::table('v_list_apprenants_inter')
            ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_fonction', 'emp_email', 'emp_photo', 'emp_matricule', 'etp_name')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();

        $getSeance = DB::table('v_emargement_appr_inter')
            ->select('idSeance', 'idProjet', 'heureDebut', 'heureFin', 'dateSeance', 'isPresent', 'idEmploye')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', Carbon::now()->toDateString()) // Ajoute la condition pour la date
            ->groupBy('idSeance')
            ->get();

        $getPresence = DB::table('v_emargement_appr_inter')
            ->select('idSeance', 'idProjet', 'isPresent', 'idEmploye')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', Carbon::now()->toDateString()) // Ajoute la condition pour la date
            ->get();

        $countDate = DB::table('v_seances')
            ->select('idProjet', 'dateSeance', 'idSeance', DB::raw('COUNT(*) as count'))
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', Carbon::now()->toDateString()) // Ajoute la condition pour la date
            ->groupBy('dateSeance')
            ->get();


        $getAppr = DB::table('v_emargement_appr_inter')
            ->select('idProjet', 'idEmploye', 'name', 'firstName', 'photo')
            ->where('idProjet', $idProjet)
            ->groupBy('idEmploye')
            ->get();


        $getEmargement = DB::table('emargements')
            ->select('idProjet', 'idSeance', 'isPresent')
            ->where('idProjet', $idProjet)
            ->groupBy('idSeance')
            ->get();

        $present = DB::table('emargements')
            ->select('idProjet', 'idSeance', 'isPresent')
            ->where('idProjet', $idProjet)
            ->where('isPresent', 3)
            ->get();

        $partiel = DB::table('emargements')
            ->select('idProjet', 'idSeance', 'isPresent')
            ->where('idProjet', $idProjet)
            ->where('isPresent', 2)
            ->get();

        $absent = DB::table('emargements')
            ->select('idProjet', 'idSeance', 'isPresent')
            ->where('idProjet', $idProjet)
            ->where('isPresent', 1)
            ->get();

        $nonDefini = DB::table('emargements')
            ->select('idProjet', 'idSeance', 'isPresent')
            ->where('idProjet', $idProjet)
            ->where('isPresent', 0)
            ->get();

        $countAppr = count($getAppr);

        $countPresent = count($present);
        $countPartiel = count($partiel);
        $countAbsent = count($absent) + count($nonDefini);

        $countEmargement = count($getEmargement);
        $divide = $countAppr * $countEmargement;

        if ($divide > 0) {
            $percentPresent = number_format(($countPresent / $divide) * 100, 1, ',', ' ');
            $percentPartiel = number_format(($countPartiel / $divide) * 100, 1, ',', ' ');
            $percentAbsent = number_format(($countAbsent / $divide) * 100, 1, ',', ' ');
        } else {
            $percentPresent = 0;
            $percentPartiel = 0;
            $percentAbsent = 0;
        }

        $getIdAppr = DB::table('v_emargement_appr_inter')
            ->select('idProjet', 'idEmploye', 'idSeance', 'name', 'firstName', 'photo')
            ->where('idProjet', $idProjet)
            ->get();

        return response()->json([
            'apprs' => $apprs,
            'getSeance' => $getSeance,
            'getPresence' => $getPresence,
            'getAppr' => $getAppr,
            'getIdAppr' => $getIdAppr,
            'countDate' => $countDate,
            'countAppr' => $countAppr,
            'countEmargement' => $countEmargement,
            'percentPresent' => $percentPresent . '%',
            'percentPartiel' => $percentPartiel . '%',
            'percentAbsent' => $percentAbsent . '%',
        ]);
    }
    public function getEtpAdded($idProjet)
    {
        $etps = DB::table('v_list_entreprise_inter')->select('idEtp', 'etp_logo', 'etp_name', 'mail', 'idProjet')->where('idProjet', $idProjet)->get();

        return response()->json(['etps' => $etps]);
    }

    //Programme Pedagogique
    public function getApprenantProjetInter($idProjet)
    {
        $idEtpParent = $this->getIdEtpAdded($idProjet);

        $queryEtp = DB::table('v_union_list_entreprise_inter')
            ->select('idEtp', 'etp_name');

        $query = DB::table('v_union_list_apprenant_inter')
            ->select('*')
            ->where('role_id', 4)
            ->where('user_is_in_service', 1);

        if (isset($idEtpParent)) {
            $a1 = (clone $query)->whereIn('idEntrepriseParent', $idEtpParent)->orderBy('etp_name', 'asc')->get()->toArray();
            $a2 = (clone $query)->where('idProjet', $idProjet)->orderBy('etp_name', 'asc')->get()->toArray();

            $e1 = (clone $queryEtp)->whereIn('idEtpParent', $idEtpParent)->orderBy('etp_name', 'asc')->get()->toArray();
            $e2 = (clone $queryEtp)->where('idProjet', $idProjet)->orderBy('etp_name', 'asc')->get()->toArray();

            $apprs = array_merge($a1, $a2);
            $etps = array_merge($e1, $e2);
        } else {
            $apprs = $query->where('idProjet', $idProjet)->orderBy('etp_name', 'asc')->get();
            $etps = $queryEtp->where('idProjet', $idProjet)->orderBy('etp_name', 'asc')->get();
        }

        return response()->json([
            'apprs' => $apprs,
            'etps' => $etps,
        ]);
    }
    public function addApprenantInter($idProjet, $idApprenant, $idEtp)
    {
        $checkAppr = DB::table('apprenants')->where('idEmploye', $idApprenant)->get();
        $check = DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->where('idEtp', $idEtp)->where('idEmploye', $idApprenant)->get();

        if (count($checkAppr) < 1 && count($check) < 1) {
            try {
                DB::transaction(function () use ($idProjet, $idApprenant, $idEtp) {
                    DB::table('apprenants')->insert([
                        'idEmploye' => $idApprenant
                    ]);

                    DB::table('detail_apprenant_inters')->insert([
                        'idProjet' => $idProjet,
                        'idEmploye' => $idApprenant,
                        'idEtp' => $idEtp,
                        'id_cfp_appr' => $this->idCfp()
                    ]);
                });
                return response()->json(['success' => 'Succès']);
            } catch (\Throwable $th) {
                return response()->json(['error' => 'Erreur inconnue !']);
            }
        } elseif (count($checkAppr) >= 1 && count($check) < 1) {
            try {
                DB::table('detail_apprenant_inters')->insert([
                    'idProjet' => $idProjet,
                    'idEmploye' => $idApprenant,
                    'idEtp' => $idEtp,
                    'id_cfp_appr' => $this->idCfp()
                ]);
                return response()->json(['success' => 'Succès']);
            } catch (\Throwable $th) {
                return response()->json(['error' => 'Erreur inconnue !']);
            }
        } elseif (count($checkAppr) >= 1 && count($check) >= 1) {
            return response()->json(['error' => 'Employée déjà inscrit à la session']);
        }
    }
    public function programProject($idModule)
    {
        $programmes = DB::table('programmes')->select('program_title', 'program_description', 'idModule')->where('idModule', $idModule)->get();

        return response()->json(['programmes' => $programmes]);
    }

    public function showmomentum($idProjet, Request $request)
    {
        $idProjet = $request->idProjet;

        $images = DB::table('images')
            ->select('idProjet', 'idImages', 'url', 'nomImage')
            ->where('idProjet', $idProjet)
            ->where('idTypeImage', 1)
            ->get();


        if ($images->isEmpty()) {
            return redirect()->route('emps.detailEmp.index', ['idProjet' => $idProjet]);
        }
        return view('employes.projets.pages.photo_momentum', compact('images', 'idProjet'));
    }

    public function uploadPhotoMomentum(Request $request)
    {
        // Ajuster les paramètres PHP
        ini_set('upload_max_filesize', '5M');
        ini_set('post_max_size', '50M');
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '300');
        ini_set('max_input_time', '300');

        $driver = new Driver();
        $manager = new ImageManager($driver);

        $validate = Validator::make($request->all(), [
            'myFile.*' => 'required|image|max:5120', // Validation Laravel (taille en KB)
        ]);

        if ($validate->fails()) {
            return back()->with(['error' => $validate->messages()]);
        }

        $files = $request->file('myFile');
        $idProjet = $request->idProjet;
        $maxFileSize = 5 * 1024 * 1024; // 5 MB
        $urls = [];

        if ($files) {
            foreach ($files as $file) {
                if ($file->getSize() > $maxFileSize) {
                    return response()->json(['error' => 'L\'un des fichiers est trop grand. La taille maximale autorisée est de 5 MB par fichier.']);
                }

                try {
                    $image = $manager->read($file)->toWebp(25);

                    $disk = Storage::disk('do');
                    $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.webp';
                    $path = 'img/momentum/' . $idProjet . '/' . $filename;

                    $disk->put($path, $image->__toString());

                    $url = $disk->url($path);
                    $urls[] = $url;

                    DB::table('images')->insert([
                        'idTypeImage' => 1,
                        'idProjet' => $idProjet,
                        'url' => $url,
                        'path' => $path,
                        'nomImage' => $filename,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erreur lors du traitement de l\'image : ' . $e->getMessage(), [
                        'file' => $file->getClientOriginalName(),
                        'idProjet' => $idProjet,
                    ]);

                    return back()->with('error', 'Une erreur est survenue lors du traitement de l\'image. Vérifiez les logs pour plus de détails.');
                }
            }

            return back()->with('success', 'Photos téléchargées avec succès');
        }

        return back()->with(['error' => 'Aucun fichier n\'a été téléchargé.']);
    }
    public function destroyPhoto($idProjet, $idImages)
    {
        // Récupérer l'image spécifique en fonction de l'idProjet et de l'idImages
        $image = DB::table('images')
            ->where('idImages', $idImages)
            ->where('idProjet', $idProjet)
            ->first();

        if ($image) {
            $filePath = $image->path;

            // Supprimer le fichier du stockage
            Storage::disk('do')->delete($filePath);

            // Supprimer l'image de la base de données
            DB::table('images')
                ->where('idImages', $idImages)
                ->where('idProjet', $idProjet)
                ->delete();

            return response()->json(['success' => true, 'message' => 'Image supprimée avec succès.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Image non trouvée.']);
        }
    }

    public function getSessionProject($idProjet)
    {
        $countSession = DB::table('v_seances')
            ->select('idSeance', 'dateSeance', 'heureDebut', 'id_google_seance', 'heureFin', 'idSalle', 'idProjet', 'salle_name', 'salle_quartier', 'project_title', 'project_description', 'idModule', 'module_name', 'ville')
            ->where('idProjet', $idProjet)
            ->get();

        return count($countSession);
    }

    public function getFormProject($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
            ->groupBy('idFormateur', 'name', 'firstName', 'photoForm', 'initialNameForm')
            ->where('idProjet', $idProjet)->get();

        return $forms->toArray();
    }

    public function getApprenantProject($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null) {
            $apprs = DB::table('v_list_apprenants')
                ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_photo', 'emp_matricule', 'emp_phone', 'etp_name')
                ->where('idProjet', $idProjet)
                ->orderBy('emp_name', 'asc')
                ->get();
        } elseif ($idCfp_inter != null) {
            $apprs = DB::table('v_list_apprenant_inter_added')
                ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_fonction', 'emp_email', 'emp_photo', 'emp_matricule', 'etp_name', 'idEtp')
                ->where('idProjet', $idProjet)
                ->orderBy('emp_name', 'asc')
                ->get();
        }

        return count($apprs);
    }

    public function getSessionHour($idProjet)
    {
        $countSessionHour = DB::table('v_seances')
            ->selectRaw('SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))) as sumHourSession')
            ->where('idProjet', $idProjet)
            ->first();

        return $countSessionHour->sumHourSession;
    }

    public function getNote($idProjet)
    {
        $checkEvaluation[] = DB::table('eval_chauds')->select('idProjet')->get();
        $checkEvaluationCount = count($checkEvaluation);

        if ($checkEvaluationCount > 0) {
            $notationProjet[] = DB::table('v_evaluation_alls')
                ->select('idProjet', 'idEmploye', 'generalApreciate')
                ->where('idProjet', $idProjet)
                ->groupBy('idProjet', 'idEmploye')
                ->get();

            $generalNotation = DB::table('v_general_note_evaluation')
                ->select(DB::raw('SUM(generalApreciate) as generalNote'))
                ->where('idProjet', $idProjet)
                ->first();

            $countNotationProjet = count($notationProjet);

            if ($countNotationProjet > 0) {
                $noteGeneral = $generalNotation->generalNote / $countNotationProjet;
                return array_merge([$noteGeneral], [$countNotationProjet]);
            } else {
                $noteGeneral = 0;
                return array_merge([$noteGeneral], [$countNotationProjet]);
            }
        } else {
            $countNotationProjet = 0;
            $noteGeneral = 0;
            return array_merge([$noteGeneral], [$countNotationProjet]);
        }
    }

    public function getEtpProjectInter($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null) {
            $etp = DB::table('v_projet_cfps')
                ->select('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
                ->where('idProjet', $idProjet)
                ->orderBy('etp_name', 'asc')
                ->get();
        } elseif ($idCfp_inter != null) {
            $etp = DB::table('v_list_entreprise_inter')
                ->select('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
                ->where('idProjet', $idProjet)
                ->where('etp_name', '!=', 'null')
                ->orderBy('etp_name', 'asc')
                ->groupBy('idEtp')
                ->get();
        }

        return $etp->toArray();
    }


    public function getRestauration($idProjet)
    {
        $restaurations = DB::table('project_restaurations')
            ->select('idRestauration', 'paidBy')
            ->where('idProjet', $idProjet)
            ->get()
            ->toArray();
        return $restaurations;
    }




    public function checkEval($idProjet)
    {
        $query = DB::table('eval_chauds')->where('idProjet', $idProjet);

        if ($query) {
            return $query->count();
        } else {
            return null;
        }
    }



    public function checkEmg($idProjet)
    {
        $query = DB::table('emargements')->where('idProjet', $idProjet);

        if ($query) {
            return $query->count();
        } else {
            return null;
        }
    }

    private function averageEvalApprenant($idProjet)
    {
        return DB::table('eval_apprenant')
            ->select(DB::raw('AVG(avant) as avg_avant'), DB::raw('AVG(apres) as avg_apres'))
            ->where('idProjet', $idProjet)
            ->first() ?? 0;
    }

    private function getApprListProjet($idProjet)
    {
        $apprIntras = DB::table('v_list_apprenants')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_photo', 'etp_name')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get()
            ->toArray();

        $apprenantInters = DB::table('v_list_apprenant_inter_added')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_photo', 'etp_name')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get()
            ->toArray();

        $apprs = array_merge($apprIntras, $apprenantInters);

        // return response()->json(['apprs' => $apprs]);
        return $apprs;
    }



    private function getPlaceAvailable($idProjet)
    {
        $place_validated = DB::table('inter_entreprises')->where('idProjet', $idProjet)->where('isActiveInter', 1)->sum('nbPlaceReserved');
        $place_project = DB::table('inters')->where('idProjet', $idProjet)->value('nbPlace');
        $place_available = $place_project - $place_validated;
        return $place_available;
    }

    private function getNbPlaceReserved($idProjet)
    {
        $place_reserved = DB::table('inter_entreprises')->where('idProjet', $idProjet)->count();
        return $place_reserved;
    }


    public function indexEmp()
    {
        return view('employes.projets.index');
    }
    public function getIdCustomer()
    {
        $userId = Auth::user()->id;
        return response()->json(['idCustomer' => $userId]);
    }
    public function getApprenantAddedInter($idProjet)
    {
        $apprs = DB::table('v_list_apprenant_inter_added')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_fonction', 'emp_email', 'emp_photo', 'emp_matricule', 'etp_name', 'idEtp', 'idProjet')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();

        $array_apprs = DB::table('v_list_apprenant_inter_added')
            ->select('idEtp')
            ->where('idProjet', $idProjet)
            ->pluck('idEtp')
            ->toArray();

        //dd($array_apprs);

        $getEtps = DB::table('entreprises AS e')
            ->select('cst.idCustomer AS idEtp', 'cst.customerName AS etp_name')
            ->join('customers AS cst', 'e.idCustomer', 'cst.idCustomer')
            ->whereIn('cst.idCustomer', $array_apprs)
            ->groupBy('etp_name')
            ->orderBy('etp_name', 'asc')
            ->get();

        return response()->json([
            'apprs' => $apprs,
            'getEtps' => $getEtps
        ]);
    }
    //Seance Employe
    public function getAllSeance($idProjet)
    {
        $seances = DB::table('v_seance_appr')
            ->select('idSeance', 'idFormateur', 'dateSeance', 'id_google_seance', 'heureDebut', 'heureFin', 'idSalle', 'idProjet', 'salle_name', 'salle_quartier', 'project_title', 'project_description', 'idModule', 'module_name', 'ville')
            ->where('idEmploye', Auth::user()->id)
            ->where('idProjet', $idProjet)
            ->get();
        if (count($seances) > 0) {

            foreach ($seances as $seance) {
                $events[] =  [
                    'idSeance' => $seance->idSeance,      //<===== idSeance
                    'idCfp' => $seance->idCfp,
                    'idEtp' => $this->getFieldsProject($seance->idProjet)->idEtp,
                    'end' => $seance->dateSeance . "T" . $seance->heureFin,
                    'start' => $seance->dateSeance . "T" . $seance->heureDebut,
                    'idProjet' => $seance->idProjet,
                    'idSalle' => $seance->idSalle,
                    'idModule' => $seance->idModule,
                    'text' => $seance->project_title,
                    'description' => $seance->project_description,
                    'idCalendar' => $seance->id_google_seance,      //id reliant à Google calendar
                    'salle' => $seance->salle_name,
                    'module' => $seance->module_name,
                    'ville' => $seance->ville,
                    'formateurs' => $this->getFormProject($seance->idProjet),
                    //'apprCount' => $this->getApprenantProject($seance->idProjet),
                    'apprCountIntra' => $this->getApprenantProjectIntra($seance->idProjet),
                    'apprCountInter' => $this->getApprenantProjectInter($seance->idProjet),
                    'imgModule' => $this->getFieldsProject($seance->idProjet)->module_image,
                    'statut' => $this->getFieldsProject($seance->idProjet)->project_status,
                    'nameEtp' => $this->getFieldsProject($seance->idProjet)->etp_name,
                    'nameEtps' => $this->getEtpProjectInter($seance->idProjet, $seance->idCfp),
                    'paiementEtp' => $this->getFieldsProject($seance->idProjet)->paiement,
                    'typeProjet' => $this->getFieldsProject($seance->idProjet)->project_type,

                ];
            }
        } else {
            return response()->json(['pas de donnée']);
        }
        return response()->json(['seances' => $seances]);
    }
    public function getAllSalle()
    {
        $salles = DB::table('villes')
            ->join('salles', 'salles.idVille', 'villes.idVille')
            ->select('salles.idSalle', 'salle_name', 'salle_quartier', 'salle_rue', 'salle_code_postal', 'villes.ville')
            ->where('salles.idCustomer', $this->idCustomer())
            ->where('salle_name', '!=', 'null')
            ->get();

        return response()->json(['salles' => $salles]);
    }
    public function getSalleAdded($idProjet)
    {
        $salle = DB::table('v_projet_emps')
            ->select('idSalle', 'salle_name', 'salle_rue', 'salle_quartier', 'salle_code_postal', 'ville')
            ->where(function ($query) use ($idProjet) {
                $query->where('idProjet', $idProjet)
                    ->where('salle_name', '!=', 'null');
            })
            ->first();

        return response()->json(['salle' => $salle]);
    }
    public function moduleRessource($idModule)
    {
        $module_ressources = DB::table('module_ressources')
            ->select('idModuleRessource', 'module_ressource_name', 'module_ressource_extension', 'idModule')
            ->where('idModule', $idModule)
            ->get();

        return response()->json(['module_ressources' => $module_ressources]);
    }
    public function getMiniCV($idFormateur)
    {
        try {
            if (!Auth::check()) {
                throw new Exception('User is not authenticated.');
            }

            // Vérifier que l'utilisateur a accès aux informations demandées
            $userId = Auth::user()->id;

            $form = DB::table('users')
                ->select('id', 'name', 'email', 'firstName', 'phone', 'photo')
                ->where('id', $idFormateur)
                ->first();

            // Expériences
            $exp = DB::table('experiences')
                ->select('id', 'idFormateur', 'Lieu_de_stage', 'Fonction', 'Date_debut', 'Date_fin', 'Lieu')
                ->where('idFormateur', $idFormateur)
                ->get();

            // Diplômes
            $dp = DB::table('diplomes')
                ->select('id', 'idFormateur', 'Ecole', 'Diplome', 'Domaine', 'Date_debut', 'Date_fin')
                ->where('idFormateur', $idFormateur)
                ->get();

            // Compétences
            $cpc = DB::table('competences')
                ->select('id', 'idFormateur', 'Competence', 'note')
                ->where('idFormateur', $idFormateur)
                ->get();

            // Langues
            $lg = DB::table('langues')
                ->select('id', 'idFormateur', 'Langue', 'note')
                ->where('idFormateur', $idFormateur)
                ->get();

            $speciality = DB::table('formateurs')->select('form_titre')->where('idFormateur', $idFormateur)->first();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => ['message' => $e->getMessage()]], 500);
        }

        // Retourner les données au format JSON
        return response()->json([
            'form' => $form,
            'experiences' => $exp,
            'diplomes' => $dp,
            'competences' => $cpc,
            'langues' => $lg,
            'speciality' => $speciality
        ]);
    }
    public function getModule($idModule)
    {
        $details = DB::table('mdls')
            ->select('mdls.idModule', 'reference', 'moduleName', 'description', 'module_image', 'minApprenant', 'maxApprenant', 'dureeJ', 'dureeH', 'nomDomaine', 'prix', 'prixGroupe')
            ->join('domaine_formations', 'domaine_formations.idDomaine', 'mdls.idDomaine')
            ->join('modules', 'modules.idModule', 'mdls.idModule')
            ->where('mdls.idModule', $idModule)
            ->first();

        $objectifs = DB::table('objectif_modules')
            ->select('objectif', 'idObjectif')
            ->where('idModule', $idModule)
            ->get();

        $programmes = DB::table('programmes')->select('idProgramme', 'program_title', 'program_description')->where('idModule', $idModule)->get();

        return response()->json([
            'details' => $details,
            'objectifs' => $objectifs,
            'programmes' => $programmes,
        ]);
    }
    public function getEtp(Request $request)
    {
        $referents = DB::table('users')
            ->select('users.*', 'employes.idCustomer')
            ->join('employes', 'users.id', '=', 'employes.idEmploye')
            ->join('role_users', 'users.id', '=', 'role_users.user_id')
            ->where('employes.idCustomer', $request->idEtp)
            ->whereIn('role_users.role_id', [3, 6, 8, 9])
            ->get();

        $customer = DB::table('customers')
            ->select('customers.*')
            ->where('idCustomer', $request->idEtp)
            ->first();
        return response()->json(['customer' => $customer, 'referents' => $referents]);
    }
    public function getSession(Request $request)
    {
        $sessions = DB::table('seances')
            ->select('seances.*')
            ->where('idProjet', $request->idProjet)
            ->get();

        $module = DB::table('projets')
            ->join('mdls', 'projets.idModule', 'mdls.idModule')
            ->select('mdls.*')
            ->where('idProjet', $request->idProjet)
            ->first();

        $totalSessionHours = DB::table('v_seances')
            ->selectRaw('SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))) as sumHourSession')
            ->where('idProjet', $request->idProjet)
            ->first();

        return response()->json(['sessions' => $sessions, 'module' => $module, 'totalSessionHours' => $totalSessionHours]);
    }
    public function DrawerApprenant(Request $request)
    {
        $apprenants = DB::table('detail_apprenants')
            ->join('users', 'detail_apprenants.idEmploye', '=', 'users.id')
            ->join('employes', 'users.id', '=', 'employes.idEmploye')
            ->join('fonctions', 'employes.idFonction', '=', 'fonctions.idFonction')
            ->join('customers', 'employes.idCustomer', '=', 'customers.idCustomer')
            ->select('users.name', 'users.email', 'users.matricule', 'users.firstName', 'users.phone', 'users.photo', 'fonctions.fonction', 'customers.customerName')
            ->where('idProjet', $request->idProjet)
            ->get();

        $module = DB::table('projets')
            ->join('mdls', 'projets.idModule', 'mdls.idModule')
            ->select('mdls.*')
            ->where('idProjet', $request->idProjet)
            ->first();

        return response()->json(['apprenants' => $apprenants, 'module' => $module]);
    }
    public function DrawerDoc(Request $request)
    {
        $module = DB::table('projets')
            ->join('mdls', 'projets.idModule', 'mdls.idModule')
            ->select('mdls.*')
            ->where('idProjet', $request->idProjet)
            ->first();

        $documents = DB::table('v_document_dossier')
            ->where('idProjet', $request->idProjet)
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'module' => $module,
            'documents' => $documents
        ]);
    }
    public function getAllForms()
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'photoForm AS form_photo', 'name AS form_name', 'firstName AS form_first_name', 'email AS form_email', 'initialNameForm AS form_initial_name')
            ->groupBy('idFormateur', 'photoForm', 'name', 'firstName', 'email', 'initialNameForm')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json(['forms'  => $forms]);
    }
    public function getFormAdded($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idProjet', 'idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'email AS form_email', 'initialNameForm AS form_initial_name', 'form_phone')
            ->groupBy('idProjet', 'idFormateur', 'name', 'firstName', 'photoForm', 'email', 'initialNameForm')
            ->where('idProjet', $idProjet)
            ->get();

        return response()->json(['forms' => $forms]);
    }
    public function getAllEtps()
    {
        $etps = DB::table('v_collaboration_cfp_etps')
            ->select('idEtp', 'etp_name', 'etp_email', 'etp_initial_name', 'etp_logo')
            ->orderBy('etp_name', 'asc')
            ->get();

        return response()->json(['etps' => $etps]);
    }
    public function getEtpAssign($idProjet)
    {
        $etp = DB::table('v_projet_emps')->select('idProjet', 'idEtp', 'etp_initial_name', 'etp_name', 'etp_logo')->where('idProjet', $idProjet)->first();

        return response()->json(['etp' => $etp]);
    }



    //FIN METHODE EVALUATION

    public function idCfp()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idCustomer;
    }

    public function getEtps(EntrepriseService $etps)
    {
        $entreprises = $etps->index(Customer::idCustomer())->get();

        if (count($entreprises) <= 0) {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'entreprises' => $entreprises
        ]);
    }

    public function getApprenantProjets($idEtp)
    {
        $checkEtp = DB::table('entreprises')->select('idCustomer', 'idTypeEtp')->where('idCustomer', $idEtp)->first();

        if ($checkEtp) {
            if ($checkEtp->idTypeEtp == 1 || $checkEtp->idTypeEtp == 3) {
                $apprs = DB::table('v_apprenant_etp_alls')
                    ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_fonction', 'emp_email', 'emp_photo', 'emp_matricule', 'idEtp', 'etp_name')
                    ->where('role_id', 4)
                    ->where('idEtp', $idEtp)
                    ->where('user_is_in_service', 1)
                    ->orderBy('emp_name', 'asc')
                    ->get();

                $etps = DB::table('entreprises AS et')
                    ->select('et.idCustomer AS idEtp', 'cst.customerName AS etp_name')
                    ->join('customers AS cst', 'et.idCustomer', 'cst.idCustomer')
                    ->where('et.idCustomer', $idEtp)
                    ->get();

                return response()->json([
                    'apprs' => $apprs,
                    'etps' => $etps
                ]);
            } elseif ($checkEtp->idTypeEtp == 2) {
                $apprs = DB::table('v_union_emp_grps')
                    ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_photo', 'emp_matricule', 'idEntrepriseParent', 'etp_name', 'etp_name_parent', 'idEntreprise AS idEtp')
                    ->where('role_id', 4)
                    ->where('idEntrepriseParent', $idEtp)
                    ->where('user_is_in_service', 1)
                    ->orderBy('emp_name', 'asc')
                    ->get();

                $etpGroupeds = DB::table('etp_groupeds AS egd')
                    ->select('egd.idEntreprise AS idEtp', 'cst.customerName AS etp_name')
                    ->join('customers AS cst', 'egd.idEntreprise', 'cst.idCustomer')
                    ->where('egd.idEntrepriseParent', $idEtp)
                    ->get();

                $etpGroupe = DB::table('etp_groupes as eg')
                    ->select('eg.idEntreprise as idEtp', 'c.customerName as etp_name')
                    ->join('customers as c', 'eg.idEntreprise', 'c.idCustomer')
                    ->where('eg.idEntreprise', $idEtp)
                    ->get();

                $etps = array_merge($etpGroupeds->toArray(), $etpGroupe->toArray());

                return response()->json([
                    'apprs' => $apprs ?? [],
                    'etps' => $etps ?? []
                ]);
            } else {
                return response(['error' => 'Erreur inconnue !']);
            }
        } else {
            return response(['error' => 'Entreprise introuvable !']);
        }
    }
    public function getProjectTrainer()
    {
        $userId = Auth::user()->id;

        $query = "
                SELECT *
                FROM v_trainer_learners
                WHERE idProjet IN (
                    SELECT idProjet
                    FROM project_forms
                    WHERE idFormateur = ?
                )
                GROUP BY idEmploye
                ORDER BY idEmploye ASC
            ";



        $learners = DB::select($query, [$userId]);

        $data = [];
        foreach ($learners as $learner) {
            $data[] = [
                "idProjet" => $learner->idProjet,
                "project_title" => $learner->project_title,
                "project_name" => $learner->project_name,
                "idEmploye" => $learner->idEmploye,
                "emp_name" => $learner->emp_name,
                "emp_firstname" => $learner->emp_firstname,
                "emp_email" => $learner->emp_email,
                "emp_photo" => $learner->emp_photo,
                "module_name" => $learner->module_name,
                "etp_name" => $learner->etp_name,
                "isAddedByTrainer" => $this->checkIsAddedByTrainer($learner->idEmploye)
            ];
        }

        return response()->json([
            'apprenants' => $data,
        ]);
    }

    public function checkIsAddedByTrainer($learnerId)
    {
        return DB::table('f_emps')
            ->where('idEmploye', $learnerId)
            ->where('id_formateur', Auth::id())
            ->exists();
    }

    public function getApprenantAdded($idProjet)
    {
        $now = Carbon::now()->toDateString();

        // Entreprises (établissements) des apprenants
        $getEtps = DB::table('detail_apprenants AS da')
            ->join('employes AS emp', 'da.idEmploye', '=', 'emp.idEmploye')
            ->join('customers AS cst', 'emp.idCustomer', '=', 'cst.idCustomer')
            ->where('da.idProjet', $idProjet)
            ->groupBy('cst.idCustomer', 'cst.customerName')
            ->orderBy('cst.customerName', 'asc')
            ->select('da.idProjet', 'da.idEmploye', 'cst.idCustomer AS idEtp', 'cst.customerName AS etp_name', 'cst.logo AS etp_logo')
            ->get();

        // Récupération des séances et présences
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

        // Nombre total de dates de séance
        $countDate = DB::table('v_seances')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', $now)
            ->groupBy('dateSeance')
            ->select('idProjet', 'idSeance', DB::raw('COUNT(*) as count'), 'dateSeance')
            ->get();

        // Récupération des identités des apprenants
        $getAppr = DB::table('v_emargement_appr')
            ->where('idProjet', $idProjet)
            ->groupBy('idEmploye')
            ->select('idProjet', 'idEmploye', 'name', 'firstName', 'photo')
            ->get();

        // Comptage des apprenants et des présences
        $countAppr = $getAppr->count();
        $countEmargement = DB::table('emargements')
            ->where('idProjet', $idProjet)
            ->groupBy('idSeance')
            ->count();

        // Comptage des statuts de présence
        $statuts = DB::table('emargements')
            ->where('idProjet', $idProjet)
            ->whereIn('isPresent', [0, 1, 2, 3])
            ->select('isPresent', DB::raw('COUNT(*) as count'))
            ->groupBy('isPresent')
            ->pluck('count', 'isPresent');

        $countPresent = $statuts[3] ?? 0;
        $countPartiel = $statuts[2] ?? 0;
        $countAbsent = ($statuts[1] ?? 0) + ($statuts[0] ?? 0);

        // Calcul des pourcentages
        $divide = $countAppr * $countEmargement;
        $percentPresent = $divide > 0 ? number_format(($countPresent / $divide) * 100, 1, ',', ' ') : 0;
        $percentPartiel = $divide > 0 ? number_format(($countPartiel / $divide) * 100, 1, ',', ' ') : 0;
        $percentAbsent = $divide > 0 ? number_format(($countAbsent / $divide) * 100, 1, ',', ' ') : 0;

        // Récupération des apprenants avec leurs évaluations
        $apprs = DB::table('v_list_apprenants as L')
            ->leftJoin('eval_apprenant as E', function ($join) use ($idProjet) {
                $join->on('E.idEmploye', '=', 'L.idEmploye')
                    ->where('E.idProjet', '=', $idProjet);
            })
            ->select(
                'L.idEmploye',
                'emp_initial_name',
                'emp_name',
                'emp_firstname',
                'emp_fonction',
                'emp_email',
                'emp_photo',
                'emp_matricule',
                'etp_name',
                'idEtp',
                'E.avant',
                'E.apres'
            )
            ->where('L.idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();

        $arrayApprs = $apprs->pluck('idEmploye')->toArray();

        if (!$arrayApprs || count($arrayApprs) === 0) {
            return response()->json([]);
        }

        $data = DB::table('emargements')
            ->selectRaw('idEmploye, COUNT(*) as total, SUM(isPresent) as somme')
            ->where('idProjet', $idProjet)
            ->whereIn('idEmploye', $arrayApprs)
            ->groupBy('idEmploye')
            ->get();

        $results = $data->map(function ($item) {
            $total = (int) $item->total;
            $sum = (int) $item->somme;
            $present = $total * 3;
            $absent = $total;

            return [
                'idEmploye' => $item->idEmploye,
                'checking' => $sum === $present ? 3 : ($sum < $present && $sum !== $absent ? 2 : ($sum === $absent ? 1 : 0)),
                'color' => $sum === $present ? 'bg-green-500' : ($sum < $present && $sum !== $absent ? 'bg-red-500' : ($sum === $absent ? 1 : 'bg-amber-500'))
            ];
        });

        // Joindre les résultats aux apprenants
        $apprs = $apprs->map(function ($appr) use ($results, $idProjet) {
            // Chercher les résultats de présence
            $result = $results->firstWhere('idEmploye', $appr->idEmploye);

            // Ajouter les valeurs de présence à l'apprenant
            $appr->checking = $result['checking'] ?? 0;
            $appr->color = $result['color'] ?? 'bg-gray-500';

            // Appel à la méthode checkEval de EvaluationController
            $evaluationController = app(EvaluationController::class);
            $evaluationResponse = $evaluationController->checkEval($idProjet, $appr->idEmploye);

            // Décoder la réponse JSON
            $evaluationData = json_decode($evaluationResponse->getContent(), true);

            // Ajouter les données de l'évaluation à l'apprenant
            $appr->evaluation = $evaluationData['one'] ?? null;

            return $appr;
        });

        // dd($apprs);

        return response()->json([
            'apprs' => $apprs,
            'getEtps' => $getEtps,
            'getSeance' => $getSeance,
            'getPresence' => $getPresence,
            'getAppr' => $getAppr,
            'countDate' => $countDate,
            'countAppr' => $countAppr,
            'countEmargement' => $countEmargement,
            'percentPresent' => $percentPresent . '%',
            'percentPartiel' => $percentPartiel . '%',
            'percentAbsent' => $percentAbsent . '%',
        ]);
    }

    public function addApprenant($idProjet, $idApprenant)
    {
        $checkAppr = DB::table('apprenants')->where('idEmploye', $idApprenant)->get();
        $check = DB::table('detail_apprenants')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->get();

        if (count($checkAppr) < 1 && count($check) < 1) {
            try {
                DB::beginTransaction();
                DB::table('apprenants')->insert([
                    'idEmploye' => $idApprenant
                ]);

                DB::table('detail_apprenants')->insert([
                    'idProjet' => $idProjet,
                    'idEmploye' => $idApprenant,
                    'id_cfp_appr' => $this->idCfp()
                ]);
                DB::commit();
                return response()->json(['success' => 'Apprenant ajouté avec succès']);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Erreur inconnue !']);
            }
        } elseif (count($checkAppr) >= 1 && count($check) < 1) {
            DB::table('detail_apprenants')->insert([
                'idProjet' => $idProjet,
                'idEmploye' => $idApprenant,
                'id_cfp_appr' => $this->idCfp()
            ]);

            return response()->json(['success' => 'Apprenant ajouté avec succès']);
        } elseif (count($checkAppr) >= 1 && count($check) >= 1) {
            return response()->json(['error' => 'Employée déjà inscrit à la session']);
        }
    }

    public function index()
    {
        $apprs = DB::table('v_apprenant_union')
            ->select('idEmploye', 'emp_initial_name', 'emp_fonction', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'idEtp', 'ville', 'idVille', 'idCfp')
            ->where('idCfp', $this->idCfp())
            ->distinct()
            ->orderByDesc('idEmploye')
            ->get();

        $villes = DB::table('villes')
            ->select('idVille', 'ville')
            ->orderBy('ville', 'asc')
            ->get();

        $countAppr = DB::table('v_apprenant_etp_alls')
            ->where(function ($query) {
                $query->where('idCfp', $this->idCfp())
                    ->where('id_cfp', $this->idCfp())
                    ->orWhere('id_cfp_appr', $this->idCfp());
            })
            ->get();

        $apprsArray = DB::table('v_apprenant_union')
            ->select('idEmploye', 'emp_initial_name', 'emp_fonction', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'idEtp', 'ville', 'idVille', 'idCfp')
            ->where('idCfp', $this->idCfp())
            ->distinct()
            ->orderByDesc('idEmploye')
            ->pluck('idEmploye');

        $countApprs = count($countAppr);
        $idCustomer = Customer::idCustomer();
        $userId = Auth::user()->id;

        $moduleQuery = DB::table('v_apprenant_union')
            ->select('idEmploye', 'idModule', 'module_name')
            ->where('idCfp', $this->idCfp())
            ->where('idModule', '!=', 'null')
            ->whereIn('idEmploye', $apprsArray)
            ->get();

        $authenticatedUser = Customer::idCustomer();
        $userNow = Customer::findOrFail($authenticatedUser);
        $mysubscriptions = $userNow->planSubscriptions()->first();
        if ($mysubscriptions && $mysubscriptions->ended()) {
            $nextSubscription = $userNow->planSubscriptions()->where('starts_at', '>=', $mysubscriptions->ends_at)->first();
            if ($nextSubscription) {
                $mysubscriptions->delete();
            }
        }

        return response()->json([
            'status' => 200,
            'apprenants' => $apprs,
            'countApprs' => $countApprs,
            'idCustomer' => $idCustomer,
            'userId' => $userId,
            'moduleQuery' => $moduleQuery,
            'villes' => $villes
        ]);
    }

    public function addEmp(Request $req, UserService $usr, EmployeService $employe)
    {
        $validate = Validator::make($req->all(), [
            'idEntreprise' => 'required|exists:customers,idCustomer',
            'emp_name' => 'required|min:2|max:200'
        ]);

        if ($validate->fails()) {
            return response([
                'status' => 422,
                "message" => $validate->messages()
            ]);
        }

        if ($req->emp_email) {
            if (User::where('email', $req->emp_email)->exists()) {
                return response()->json([
                    'status' => 409,
                    'message' => 'Cet email est déjà utilisé.'
                ], 409);
            }
        }


        try {
            $employeeId = DB::transaction(function () use ($req, $usr, $employe) {
                $user = $usr->store(NULL, $req->emp_name, $req->emp_firstname, $req->emp_email, $req->emp_phone, Hash::make("0000@#"));
                $employe->store($user->id, 1, $req->idEntreprise, 1, $this->getIdFonction($req->idEntreprise));
                $this->roleUser(4, $user->id, 1, 1, 1);
                $this->storeCEmp($user->id, Customer::idCustomer());

                if (isset($req->emp_email)) {
                    $htmlContent = (new AddApprenant(Customer::getCustomer(Customer::idCustomer())->customer_name, $req->emp_email))->render();

                    app(BrevoService::class)->sendEmail(
                        $req->emp_email,
                        "Attribution de badge",
                        $htmlContent
                    );
                }

                return $user->id;
            });

            $idCfp_inter = null;

            if (isset($req->idProjet)) {
                $idCfp_inter = $this->getIdCfpInter($req->idProjet);
            }

            return response([
                'idCfp_inter' => $idCfp_inter,
                'status' => 200,
                'message' => "Employé ajouté avec succès",
                'employee_id' => $employeeId
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getApprenants()
    {
        $apprs = DB::table('v_apprenant_etp_alls')
            ->select('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->where('idCfp', $this->idCfp())
            ->where('id_cfp', $this->idCfp())
            ->where('role_id', 4)
            ->groupBy('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->orderBy('emp_name', 'asc')
            ->get();

        if (count($apprs) <= 0) {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'apprenants' => $apprs
        ]);
    }


    public function removeApprenant($idProjet, $idApprenant)
    {
        $eval = DB::table('eval_chauds')->select('idProjet')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->get();
        $presence = DB::table('emargements')->select('idProjet')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->get();

        $checkEval = count($eval);
        $checkPresence = count($presence);

        try {
            if ($checkEval > 0 && $checkPresence > 0) {
                $delete = DB::transaction(function () use ($idProjet, $idApprenant) {
                    DB::table('detail_apprenants')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                    DB::table('eval_chauds')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                    DB::table('emargements')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                });
            } elseif ($checkEval > 0) {
                $delete = DB::transaction(function () use ($idProjet, $idApprenant) {
                    DB::table('detail_apprenants')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                    DB::table('eval_chauds')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                });
            } elseif ($checkPresence > 0) {
                $delete = DB::transaction(function () use ($idProjet, $idApprenant) {
                    DB::table('detail_apprenants')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                    DB::table('emargements')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
                });
            } else {
                $delete = DB::table('detail_apprenants')->where('idProjet', $idProjet)->where('idEmploye', $idApprenant)->delete();
            }
            return response()->json(['success' => 'Apprenant a été supprimé avec succès']);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Erreur inconnue !']);
        }
    }
    public function edit($idApprenant)
    {
        $appr = DB::table('v_apprenant_etp_alls')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_email', 'emp_phone', 'emp_matricule', 'emp_fonction', 'user_addr_lot', 'user_addr_quartier', 'user_addr_code_postal', 'emp_initial_name', 'emp_photo', 'etp_name', 'idVille', 'ville', 'idEtp', 'idVille')
            ->where('idEmploye', $idApprenant);

        $etps = DB::table('v_collaboration_cfp_etps')
            ->select('idEtp', 'etp_name')
            ->groupBy('idEtp', 'etp_name')
            ->where('idCfp', Customer::idCustomer())
            ->get();

        $villes = DB::table('villes')
            ->select('idVille', 'ville')
            ->orderBy('ville', 'asc')
            ->get();

        if ($appr->exists()) {
            return response()->json([
                'status' => 200,
                'apprenant' => $appr->first(),
                'etps' => $etps,
                'villes' => $villes
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Apprenant introuvable !'
            ], 404);
        }
    }

    public function update(Request $req, $id)
    {
        $validate = Validator::make($req->all(), [
            'emp_name' => 'min:2|max:100',
            'idVille' => 'exists:villes,idVille',
            'idEntreprise' => 'required'
        ]);

        $user = User::where('email', $req->emp_email)->whereNot('id', $id)->first();

        if ($user) {
            return response()->json([
                'status' => 409,
                'message' => "Cette adresse e-mail est déjà prise."
            ], 409);
        }

        if ($validate->fails()) {
            return response()->json(['error' => $validate->messages()]);
        } else {
            $user = User::find($id);

            if (!$user) {
                return response([
                    'status' => 404,
                    'message' => 'Apprenant introuvable !'
                ]);
            }

            DB::transaction(function () use ($req, $id, $user) {
                $user->update([
                    'name' => $req->emp_name,
                    'firstName' => $req->emp_firstname,
                    'matricule' => $req->emp_matricule,
                    'email' => $req->emp_email,
                    'phone' => $req->emp_phone,
                    'user_addr_lot' => $req->emp_lot,
                    'user_addr_quartier' => $req->emp_qrt,
                    'user_addr_code_postal' => $req->emp_cp,
                    'idVille' => $req->idVille
                ]);

                DB::table('employes')->where('idEmploye', $id)->update([
                    'idCustomer' => $req->idEntreprise
                ]);
            });

            $etp = DB::table('customers')
                ->select('idCustomer', 'customerName')
                ->where('idCustomer', $req->idEntreprise)->first();

            return response()->json([
                'status' => 200,
                'message' => "Succès",
                'apprenant' => [
                    'emp_name' => $req->emp_name,
                    'emp_firstname' => $req->emp_firstname,
                    'emp_email' => $req->emp_email,
                    'emp_phone' => $req->emp_phone,
                    'etp_name' => $etp->customerName
                ]
            ]);
        }
    }


    public function searchName(string $name)
    {
        $apprenants = DB::table('v_apprenant_etp_alls')
            ->select('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->where('idCfp', Customer::idCustomer())
            ->where('id_cfp', Customer::idCustomer())
            ->where('role_id', '=', 4)
            ->where(function ($query) use ($name) {
                $query->where('emp_name', 'LIKE', '%' . $name . '%')
                    ->orWhere('emp_firstname', 'LIKE', '%' . $name . '%');
            })
            ->groupBy('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->get();

        if (count($apprenants) > 0) {
            return response()->json(
                [
                    'status' => 200,
                    'apprenants' => $apprenants
                ]
            );
        } else {
            return response([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }
    }

    public function getEtpFilter()
    {
        $etps = DB::table('v_apprenant_etp_alls')
            ->select('idEtp', 'etp_name')
            ->where('idCfp', Customer::idCustomer())
            ->where('id_cfp', Customer::idCustomer())
            ->groupBy('idEtp', 'etp_name')
            ->get();

        if (count($etps) > 0) {
            return response()->json(
                [
                    'status' => 200,
                    'entreprises' => $etps
                ]
            );
        } else {
            return response([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }
    }

    public function getEmpFiltered($idEtp)
    {
        $apprenants = DB::table('v_apprenant_etp_alls')
            ->select('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->where('idCfp', $this->idCfp())
            ->where('id_cfp', $this->idCfp())
            ->where('role_id', 4)
            ->where('idEtp', $idEtp)
            ->groupBy('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->get();

        if (count($apprenants) > 0) {
            return response()->json(
                [
                    'status' => 200,
                    'apprenants' => $apprenants
                ]
            );
        } else {
            return response([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }
    }

    public function updateImageAppr(Request $req, $idApprenant)
    {
        $appr = DB::table('users')->select('photo')->where('id', $idApprenant)->first();

        $driver = new Driver();

        $manager = new ImageManager($driver);

        if ($appr != null) {

            if (!empty($module->module_image)) {
                Storage::disk('do')->delete('img/employes/' . $appr->photo);
            }

            $image_parts = explode(";base64,", $req->image);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $image = $manager->read($image_base64)->toWebp(25);

            $imageName = uniqid() . '.webp';
            $filePath = 'img/employes/' . $imageName;

            // Upload the image to DigitalOcean Space
            Storage::disk('do')->put($filePath, $image, 'public');

            // Update the database with the new image name
            DB::table('users')->where('id', $idApprenant)->update([
                'photo' => $imageName,
            ]);
            return response()->json([
                'status' => 200,
                'success' => 'Image Uploaded Successfully',
                'imageName' =>  $imageName
            ]);
        }
    }

    public function getDropdownItem()
    {
        $etps = DB::table('v_apprenant_union')
            ->select('idEtp', 'etp_name', DB::raw('COUNT(DISTINCT idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->orderBy('etp_name', 'asc')
            ->groupBy('idEtp', 'etp_name')
            ->get();

        $villes = DB::table('v_apprenant_union')
            ->select('project_id_ville', 'project_ville as ville', 'project_code_postal', DB::raw('COUNT(DISTINCT idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->orderBy('project_ville', 'asc')
            ->where('project_status', 'Terminé')
            ->groupBy('project_id_ville')
            ->get();

        $status = DB::table('v_apprenant_union')
            ->select('project_status', DB::raw('COUNT(DISTINCT idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->whereNot('project_status', 'Supprimé')
            ->where('project_status', '!=', 'null')
            ->orderBy('project_status', 'asc')
            ->groupBy('project_status')
            ->get();

        $modalites = DB::table('v_apprenant_union')
            ->select('project_modality', DB::raw('COUNT(DISTINCT idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('project_modality', '!=', 'null')
            ->orderBy('project_modality', 'asc')
            ->groupBy('project_modality')
            ->get();

        $modules = DB::table('v_apprenant_union')
            ->select('idModule', 'module_name', DB::raw('COUNT(DISTINCT idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('idModule', '!=', 'null')
            ->orderBy('module_name', 'asc')
            ->groupBy('idModule')
            ->get();

        $periodePrev3 = DB::table('v_apprenant_union')
            ->select('idProjet', 'p_id_periode', DB::raw('COUNT(DISTINCT idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('dateDebut', '!=', 'null')
            ->where('p_id_periode', "prev_3_month")
            ->groupBy('p_id_periode')
            ->first();

        $periodePrev6 = DB::table('v_apprenant_union')
            ->select('idProjet', 'p_id_periode', DB::raw('COUNT(DISTINCT idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('dateDebut', '!=', 'null')
            ->where('p_id_periode', "prev_6_month")
            ->groupBy('p_id_periode')
            ->first();

        $periodePrev12 = DB::table('v_apprenant_union')
            ->select('idProjet', 'p_id_periode', DB::raw('COUNT(DISTINCT idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('dateDebut', '!=', 'null')
            ->where('p_id_periode', "prev_12_month")
            ->groupBy('p_id_periode')
            ->first();

        $periodeNext3 = DB::table('v_apprenant_union')
            ->select('p_id_periode', DB::raw('COUNT(DISTINCT idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('dateDebut', '!=', 'null')
            ->where('p_id_periode', "next_3_month")
            ->groupBy('p_id_periode')
            ->first();

        $periodeNext6 = DB::table('v_apprenant_union')
            ->select('p_id_periode', DB::raw('COUNT(DISTINCT idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('dateDebut', '!=', 'null')
            ->where('p_id_periode', "next_6_month")
            ->groupBy('p_id_periode')
            ->first();

        $periodeNext12 = DB::table('v_apprenant_union')
            ->select('p_id_periode', DB::raw('COUNT(DISTINCT idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('dateDebut', '!=', 'null')
            ->where('p_id_periode', "next_12_month")
            ->groupBy('p_id_periode')
            ->first();

        return response()->json([
            'etps' => $etps,
            'villes' => $villes,
            'status' => $status,
            'modalites' => $modalites,
            'modules' => $modules,
            'periodePrev3' => $periodePrev3,
            'periodePrev6' => $periodePrev6,
            'periodePrev12' => $periodePrev12,
            'periodeNext3' => $periodeNext3,
            'periodeNext6' => $periodeNext6,
            'periodeNext12' => $periodeNext12
        ]);
    }

    public function filterItems(Request $req)
    {
        $idEtps      = array_filter(explode(',', $req->query('idEtp', '')));
        $idFonctions = array_filter(explode(',', $req->query('idFonction', '')));
        $idModules   = array_filter(explode(',', $req->query('idModule', '')));
        $idStatus    = array_filter(explode(',', $req->query('idStatut', '')));
        $idModalites = array_filter(explode(',', $req->query('idModalite', '')));
        $idVilles    = array_filter(explode(',', $req->query('idVille', '')));
        $idPeriodes  = $req->query('idPeriode', '');

        $query = DB::table('v_apprenant_union')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_initial_name', 'emp_photo', 'emp_matricule', 'emp_phone', 'emp_email', 'emp_fonction', 'idEtp', 'etp_name')
            ->where('idCfp', $this->idCfp());

        // Appliquer tous les filtres combinés (AND)
        if (count($idEtps))      $query->whereIn('idEtp', $idEtps);
        if (count($idModules))   $query->whereIn('idModule', $idModules);
        if (count($idStatus))    $query->whereIn('project_status', $idStatus);
        if (count($idModalites)) $query->whereIn('project_modality', $idModalites);
        if (count($idVilles))    $query->whereIn('project_id_ville', $idVilles);
        if ($idPeriodes)         $query->where('p_id_periode', $idPeriodes);
        if (count($idFonctions)) $query->whereIn('emp_fonction', $idFonctions);

        $query->groupBy('idEmploye');
        $apprs = $query->get();

        // Pour les dropdowns, il faut aussi appliquer les mêmes filtres si tu veux des stats cohérentes
        $etpQuery = DB::table('v_apprenant_union')
            ->select('idEtp', 'etp_name', DB::raw('COUNT(DISTINCT idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp());
        if (count($idModules))   $etpQuery->whereIn('idModule', $idModules);
        if (count($idStatus))    $etpQuery->whereIn('project_status', $idStatus);
        if (count($idModalites)) $etpQuery->whereIn('project_modality', $idModalites);
        if (count($idVilles))    $etpQuery->whereIn('project_id_ville', $idVilles);
        if ($idPeriodes)         $etpQuery->where('p_id_periode', $idPeriodes);
        if (count($idFonctions)) $etpQuery->whereIn('emp_fonction', $idFonctions);
        $etps = $etpQuery->orderBy('etp_name', 'asc')->groupBy('idEtp', 'etp_name')->get();

        $statusQuery = DB::table('v_apprenant_union')
            ->select('project_status', DB::raw('COUNT(DISTINCT idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('project_status', '!=', 'null');
        if (count($idEtps))      $statusQuery->whereIn('idEtp', $idEtps);
        if (count($idModules))   $statusQuery->whereIn('idModule', $idModules);
        if (count($idModalites)) $statusQuery->whereIn('project_modality', $idModalites);
        if (count($idVilles))    $statusQuery->whereIn('project_id_ville', $idVilles);
        if ($idPeriodes)         $statusQuery->where('p_id_periode', $idPeriodes);
        if (count($idFonctions)) $statusQuery->whereIn('emp_fonction', $idFonctions);
        $status = $statusQuery->orderBy('project_status', 'asc')->groupBy('project_status')->get();

        $modaliteQuery = DB::table('v_apprenant_union')
            ->select('project_modality', DB::raw('COUNT(DISTINCT idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('project_modality', '!=', 'null');
        if (count($idEtps))      $modaliteQuery->whereIn('idEtp', $idEtps);
        if (count($idModules))   $modaliteQuery->whereIn('idModule', $idModules);
        if (count($idStatus))    $modaliteQuery->whereIn('project_status', $idStatus);
        if (count($idVilles))    $modaliteQuery->whereIn('project_id_ville', $idVilles);
        if ($idPeriodes)         $modaliteQuery->where('p_id_periode', $idPeriodes);
        if (count($idFonctions)) $modaliteQuery->whereIn('emp_fonction', $idFonctions);
        $modalites = $modaliteQuery->orderBy('project_modality', 'asc')->groupBy('project_modality')->get();

        $moduleQuery = DB::table('v_apprenant_union')
            ->select('idModule', 'module_name', DB::raw('COUNT(DISTINCT idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->where('idModule', '!=', 'null');
        if (count($idEtps))      $moduleQuery->whereIn('idEtp', $idEtps);
        if (count($idStatus))    $moduleQuery->whereIn('project_status', $idStatus);
        if (count($idModalites)) $moduleQuery->whereIn('project_modality', $idModalites);
        if (count($idVilles))    $moduleQuery->whereIn('project_id_ville', $idVilles);
        if ($idPeriodes)         $moduleQuery->where('p_id_periode', $idPeriodes);
        if (count($idFonctions)) $moduleQuery->whereIn('emp_fonction', $idFonctions);
        $modules = $moduleQuery->orderBy('module_name', 'asc')->groupBy('idModule')->get();

        $villeQuery = DB::table('v_apprenant_union')
            ->select('project_id_ville', 'project_ville as ville', DB::raw('COUNT(DISTINCT idEmploye) AS emp_nb'))
            ->where('idCfp', $this->idCfp())
            ->whereNotNull('project_id_ville');
        if (count($idEtps))      $villeQuery->whereIn('idEtp', $idEtps);
        if (count($idModules))   $villeQuery->whereIn('idModule', $idModules);
        if (count($idStatus))    $villeQuery->whereIn('project_status', $idStatus);
        if (count($idModalites)) $villeQuery->whereIn('project_modality', $idModalites);
        if ($idPeriodes)         $villeQuery->where('p_id_periode', $idPeriodes);
        if (count($idFonctions)) $villeQuery->whereIn('emp_fonction', $idFonctions);
        $villes = $villeQuery->orderBy('project_ville', 'asc')->groupBy('project_id_ville', 'project_ville')->get();

        // Pour les périodes, tu peux aussi appliquer les filtres (sauf p_id_periode bien sûr)
        $periodeQueries = [];
        foreach (['prev_3_month', 'prev_6_month', 'prev_12_month', 'next_3_month', 'next_6_month', 'next_12_month'] as $periode) {
            $q = DB::table('v_apprenant_union')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(DISTINCT idEmploye) AS emp_nb'))
                ->where('idCfp', $this->idCfp())
                ->where('dateDebut', '!=', 'null')
                ->where('p_id_periode', $periode);
            if (count($idEtps))      $q->whereIn('idEtp', $idEtps);
            if (count($idModules))   $q->whereIn('idModule', $idModules);
            if (count($idStatus))    $q->whereIn('project_status', $idStatus);
            if (count($idModalites)) $q->whereIn('project_modality', $idModalites);
            if (count($idVilles))    $q->whereIn('project_id_ville', $idVilles);
            if (count($idFonctions)) $q->whereIn('emp_fonction', $idFonctions);
            $periodeQueries[$periode] = $q->groupBy('p_id_periode')->first();
        }

        return response()->json([
            'idModules'     => $idModules,
            'etps'          => $etps,
            'status'        => $status,
            'villes'        => $villes,
            'modalites'     => $modalites,
            'modules'       => $modules,
            'periodes'      => $idPeriodes,
            'apprs'         => $apprs,
            'periodePrev3'  => $periodeQueries['prev_3_month'],
            'periodePrev6'  => $periodeQueries['prev_6_month'],
            'periodePrev12' => $periodeQueries['prev_12_month'],
            'periodeNext3'  => $periodeQueries['next_3_month'],
            'periodeNext6'  => $periodeQueries['next_6_month'],
            'periodeNext12' => $periodeQueries['next_12_month'],
        ]);
    }

    public function filterItem(Request $req)
    {
        $idEtps = explode(',', $req->idEtp);
        $idFonctions = explode(',', $req->idFonction);
        $idPeriodes = $req->idPeriode;
        $idModules = explode(',', $req->idModule);
        $idVilles = explode(',', $req->idVille);
        $idModalites = explode(',', $req->idModalite);
        $idStatus = explode(',', $req->idStatut);

        $query = DB::table('v_apprenant_union')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_initial_name', 'emp_photo', 'emp_matricule', 'emp_phone', 'emp_email', 'emp_fonction', 'idEtp', 'etp_name')
            ->where('idCfp', $this->idCfp());

        if ($idEtps[0] != null) {
            $query->whereIn('idEtp', $idEtps);
        }


        if ($idPeriodes != null) {
            $query->where('p_id_periode', $idPeriodes);
        }

        if ($idModules[0] != null) {
            $query->whereIn('idModule', $idModules);
        }

        if ($idVilles[0] != null) {
            $query->whereIn('project_id_ville', $idVilles);
        }

        if ($idModalites[0] != null) {
            $query->whereIn('project_modality', $idModalites);
        }

        if ($idStatus[0] != null) {
            $query->whereIn('project_status', $idStatus);
        }

        $query->groupBy('idEmploye');

        $apprs = $query->orderBy('idEmploye', 'DESC')->get();

        return response()->json(['apprs' => $apprs]);
    }

    public function addEmpExcel(Request $request)
    {
        // Validation des entrées
        $validate = Validator::make($request->all(), [
            'idEntrepriseExcel' => 'required',
            'data' => 'required|file|mimes:xls,xlsx',
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->messages()], 400);
        }

        // Rcupération des données nécessaires
        $idEntreprise = $request->idEntrepriseExcel;
        $file = $request->file('data');
        $data = Excel::toArray(new ExcelApprenants, $file);

        try {
            // Parcourir les lignes du fichier
            foreach ($data[0] as $row) {
                if (!empty($row['nom'])) {
                    DB::beginTransaction();

                    $existingUser = DB::table('users')
                        ->where('email', $row['e_mail'])
                        ->first();

                    if ($existingUser) {
                        return response()->json([
                            'error' => "L'utilisateur avec l'email '{$row['e_mail']}' existe déjà."
                        ], 400);
                    }

                    // Création de l'utilisateur
                    $user = new User();
                    $user->name = $row['nom'];
                    $user->firstName = $row['prenom'];
                    $user->email = $row['e_mail'];
                    $user->phone = $row['telephone'];
                    $user->password = Hash::make('0000@#');
                    $user->save();

                    // check si fonction existe
                    $check = DB::table('fonctions')
                        ->select('idFonction', 'fonction')
                        ->where('fonction', 'like', $row['fonction'])
                        ->where('idCustomer', $this->idCfp())
                        ->first();

                    if (!$check) {
                        $idFonction = DB::table('fonctions')->insertGetId([
                            'fonction' => $row['fonction'],
                            'idCustomer' => $this->idCfp()
                        ]);
                    } else {
                        $idFonction = $check->idFonction;
                    }

                    $emp = new Employe();
                    $emp->idEmploye = $user->id;
                    $emp->idSexe = 1;
                    $emp->idNiveau = 6;
                    $emp->idCustomer = $idEntreprise;
                    $emp->idFonction = $idFonction;
                    $emp->save();

                    // Ajout dans la table pivot `c_emps`
                    DB::table('c_emps')->insert([
                        'idEmploye' => $user->id,
                        'id_cfp' => $this->idCfp()
                    ]);

                    // Attribution du rôle
                    RoleUser::create([
                        'role_id' => 4,
                        'user_id' => $user->id,
                        'isActive' => 0,
                        'hasRole' => 1
                    ]);

                    DB::commit();
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Employé(s) ajouté(s) avec succès !'
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur inconnue : ' . $e->getMessage()], 500);
        }
    }

    // suppression Apprenant from CFP
    public function destroy($id)
    {
        $query = DB::table('c_emps')->where('idEmploye', $id)->where('id_cfp', Customer::idCustomer());

        if (!isset($query)) {
            $query = DB::table('employes')->where('idEmploye', $id);
        }

        if ($query->first()) {
            $checkProject = DB::table('detail_apprenants')->where('idEmploye', $id)->count();
            $checkProjectInter = DB::table('detail_apprenant_inters')->where('idEmploye', $id)->count();

            if ($checkProject > 0 || $checkProjectInter > 0) {
                return response()->json([
                    'message' => 'Suppression impossible !'
                ]);
            } else {
                try {
                    DB::transaction(function () use ($query, $id) {
                        DB::table('apprenants')->where('idEmploye', $id)->delete();
                        DB::table('c_emps')->where('idEmploye', $id)->delete();
                        DB::table('employes')->where('idEmploye', $id)->delete();
                        DB::table('role_users')->where('user_id', $id)->delete();
                        DB::table('employes')->where('idEmploye', $id)->delete();
                        DB::table('users')->where('id', $id)->delete();
                        DB::table('c_emps')->where('idEmploye', $id)->delete();
                        $query->delete();
                    });

                    return response()->json([
                        'status' => 200,
                        'message' => 'Employés supprimé avec succès'
                    ]);
                } catch (Exception $e) {
                    return response(['error' => 'Erreur inconnue !']);
                }
            }
        } else
            return response()->json([
                'status' => 404,
                'message' => "Employé introuvable"
            ]);
    }


    //FORMATEUR
    public function indexFormAppr(LearnerCourseService $learner)
    {
        $apprenants = $this->getFApprenants($this->getIdEntreprises(Auth::user()->id));

        if (count($apprenants) <= 0) {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }

        $apprs = [];

        foreach ($apprenants as $appr) {
            $apprs[] = [
                'idEmploye' => $appr->idEmploye,
                'idEtp' => $appr->idEtp,
                'etp_name' => $appr->etp_name,
                'etp_email' => $appr->etp_email,
                'emp_matricule' => $appr->emp_matricule,
                'emp_initial_name' => $appr->emp_initial_name,
                'emp_name' => $appr->emp_name,
                'emp_firstname' => $appr->emp_firstname,
                'emp_email' => $appr->emp_email,
                'emp_photo' => $appr->emp_photo,
                'emp_phone' => $appr->emp_phone,
                'emp_courses' => $learner->getLearnerCourseForm($appr->idEmploye, Auth::user()->id)
            ];
        }

        return response()->json([
            'status' => 200,
            'apprenants' => $apprs
        ]);
    }

    // sotre apprenant from "FORMATEUR"
    public function addEmpForm(Request $req, UserService $usr, EmployeService $emp)
    {
        $validate = Validator::make($req->all(), [
            'idEntreprise' => 'required|exists:customers,idCustomer',
            'emp_name' => 'required|min:2|max:200',
            'idProjet' => 'required|exists:projets,idProjet'
        ]);

        if ($validate->fails()) {
            return response([
                'status' => 422,
                "message" => $validate->messages()
            ]);
        }

        $idProjet = $req->idProjet;

        $employeIsExist = $req->isExist;

        try {
            if ($employeIsExist == 'false') {
                DB::transaction(function () use ($usr, $emp, $req, $idProjet) {
                    $user = $usr->store($req->emp_matricule, $req->emp_name, $req->emp_firstname, $req->emp_email, $req->emp_phone, Hash::make($req->password));
                    $userId = $user->id;
                    $this->roleUser(4, $userId, 1, 1, 1);
                    $emp->store($userId, 6, $req->idEntreprise, 1, 1);
                    $this->storeFEmp($userId, Auth::user()->id);
                    $this->storeLearner($userId);
                    $typeProject = DB::table('projets')->where('idProjet', $idProjet)->value('idTypeProjet');
                    if ($typeProject == 1) {
                        DB::table('detail_apprenants')->insert([
                            'idProjet' =>  $idProjet,
                            'idEmploye' => $userId,

                        ]);
                    } else if ($typeProject == 2) {
                        DB::table('detail_apprenant_inters')->insert([
                            'idProjet' =>  $idProjet,
                            'idEmploye' => $userId,
                            'idEtp' => $req->idEntreprise
                        ]);
                    }
                });
            } else {
                $employeId = DB::table('employes as E')
                    ->join('users as U', 'U.id', 'E.idEmploye')
                    ->where('U.name', $req->emp_name)
                    ->where('U.firstName', $req->emp_firstname)
                    ->where('E.idCustomer', $req->idEntreprise)
                    ->value('id');

                $typeProject = DB::table('projets')->where('idProjet', $idProjet)->value('idTypeProjet');
                if ($typeProject == 1) {
                    DB::table('detail_apprenants')->insert([
                        'idProjet' =>  $idProjet,
                        'idEmploye' => $employeId,
                    ]);
                } else if ($typeProject == 2) {
                    DB::table('detail_apprenant_inters')->insert([
                        'idProjet' =>  $idProjet,
                        'idEmploye' => $employeId,
                        'idEtp' => $req->idEntreprise
                    ]);
                }
            }

            return response([
                'status' => 200,
                'message' => "Employé ajouté avec succès"
            ]);
        } catch (Exception $e) {
            return response([
                'status' => 500,
                'message' => "Enregistrement impossible ! " . $e->getMessage()
            ]);
        }
    }

    public function getEntreprises(EntrepriseService $entreprise)
    {
        $etps = $entreprise->getEntrepriseForm(Auth::user()->id);

        if (count($etps) < 0) {
            return response([
                'status' => 404,
                'message' => "Aucun résultat !"
            ]);
        }

        return response([
            'status' => 200,
            'etps' => $etps
        ]);
    }

    // edit apprenant from FORMATEUR
    public function editEmpForm(
        ApprenantService $apprenant,
        EntrepriseService $entreprise,
        $id
    ) {
        try {
            $appr = $apprenant->show($id);

            $etps = $entreprise->getEntrepriseForm(Auth::user()->id);
            $villes = $this->getVilles();

            return response()->json([
                'appr' => $appr,
                'etps' => $etps,
                'villes' => $villes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Apprenant introuvable'
            ], 422);
        }
    }
}
