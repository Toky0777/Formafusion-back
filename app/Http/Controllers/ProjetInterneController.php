<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ProjetController;
use App\Models\Customer;
use App\Services\ProjetService;
use App\Services\UtilService;
use Illuminate\Support\Facades\Log;
use Laravelcm\Subscriptions\Models\Feature;
use Laravelcm\Subscriptions\Models\Subscription;
use stdClass;

class ProjetInterneController extends Controller
{
    protected $utilService;
    protected $project;

    public function __construct(UtilService $utilService,ProjetService $prj)
    {
        $this->utilService = $utilService;
        $this->project = $prj;
    }

    private function getIdEtpCfps()
    {
        $allId = [];
        $allId = DB::select("SELECT idCfp FROM `v_collaboration_etp_cfps` WHERE idEtp = ?", [Customer::idCustomer()]);

        $ids = array_map(function ($allId) {
            return (int)$allId->idCfp;
        }, $allId);

        return $ids;
    }

    private function getStatus($status)
    {
        $stats = new stdClass();

        $etp_id = Customer::idCustomer();

        $etp_is_grouped = DB::table('etp_groupeds')->where('idEntreprise', $etp_id)->exists();

        if ($etp_is_grouped) {

            $id_projets = $this->list_id_project_etpgrouped($etp_id);

            $projects = $this->getUnionProjects($id_projets, $status)->get();

            $stats = $projects->reduce(function ($carry, $item) {
                $carry[$item->project_status] = ($carry[$item->project_status] ?? 0) + 1;
                return $carry;
            }, []);

            $items = [];
            foreach ($stats as $statut => $nb) {
                $items[] = new stdClass();
                $items[count($items) - 1]->project_status = $statut;
                $items[count($items) - 1]->projet_nb = $nb;
            }

            $stats = array_filter($items, function ($item) use ($status) {
                return $item->project_status === $status;
            });

            return $stats;
        } else {  //<== Liste des projets pour une groupe d'Entreprise(ex:AXIAN) 

            $stats = DB::table('v_union_projets')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->where('project_status', $status)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();

            return $stats;
        }
    }

    public function index()
    {
        $encours = [...$this->getStatus('En cours')];
        $preparation = [...$this->getStatus('En préparation')];
        $planifier = [...$this->getStatus('Planifié')];
        $terminer = [...$this->getStatus('Terminé')];
        $cloturer = [...$this->getStatus('Cloturé')];
        $annuler = [...$this->getStatus('Annulé')];
        $reporter = [...$this->getStatus('Reporté')];

        return response()->json([
            'status' => 200,
            'projet_counts' => [
                'terminer' => $terminer, 
                'annuler' => $annuler, 
                'preparation' => $preparation, 
                'cloturer' => $cloturer, 
                'encours' => $encours, 
                'reporter' => $reporter, 
                'planifier' => $planifier
            ]
        ]);
    }

    public function getProjectList()
    {
        $projets = [];

        $etp_id = Customer::idCustomer();

        $etp_is_grouped = DB::table('etp_groupeds')->where('idEntreprise', $etp_id)->exists();

        if ($etp_is_grouped) {

            $id_projets = $this->list_id_project_etpgrouped($etp_id);

            $projects = $this->getUnionProjects($id_projets)->get();

            $projets = $this->getProjets($projects);

            $stats = $projects->reduce(function ($carry, $item) {
                $carry[$item->project_status] = ($carry[$item->project_status] ?? 0) + 1;
                return $carry;
            }, []);

            $projectDates = DB::table('v_union_projets')
                ->select('headDate')
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->orWhereIn('idProjet', $id_projets)
                ->get();

            $projetCount = count($projets);
        } else {  
            $projects = $this->getUnionProject()->get();
            $projets = $this->getProjets($projects);

            $stats = $projects->reduce(function ($carry, $item) {
                $carry[$item->project_status] = ($carry[$item->project_status] ?? 0) + 1;
                return $carry;
            }, []);

            $projectDates = DB::table('v_union_projets')
                ->select('headDate')
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->get();

            $projetCount = count($projets);
        }

        return response()->json([
            'status' => 200,
            'projets' => $projets,
            'projet_count' => $projetCount,
            'projet_dates' => $projectDates,
            'stats' => $stats,
        ]);
    }

    public function getProjectListByStatus(Request $request)
    {
        // Récupérer le status depuis la query string, ex: ?status=En cours
        $status = $request->query('status', 'Terminé');  

        $etp_id = Customer::idCustomer();
        $projets = [];

        $etp_is_grouped = DB::table('etp_groupeds')->where('idEntreprise', $etp_id)->exists();

        if ($etp_is_grouped) {
            $id_projets = $this->list_id_project_etpgrouped($etp_id);
            $query = $this->getUnionProjects($id_projets)
                ->when($status, fn($q) => $q->where('project_status', $status))
                ->get();
        } else {
            $query = $this->getUnionProject()
                ->when($status, fn($q) => $q->where('project_status', $status))
                ->get();
        }

        // Enrichir les projets (ex: comme getProjets)
        $projets = $this->getProjets($query);

        // Stats par status
        $stats = $query->reduce(function ($carry, $item) {
            $carry[$item->project_status] = ($carry[$item->project_status] ?? 0) + 1;
            return $carry;
        }, []);

        // Liste des dates groupées
        $projectDates = DB::table('v_union_projets')
            ->select('headDate')
            ->groupBy('headDate')
            ->orderBy('dateDebut', 'asc')
            ->where(function ($query) use ($etp_id) {
                $query->where('idEtp', $etp_id)
                    ->orWhere('idEtp_inter', $etp_id);
            })
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->get();

        $projetCount = count($projets);

        return response()->json([
            'status' => 200,
            'projets' => $projets,
            'projet_count' => $projetCount,
            'projet_dates' => $projectDates,
            'stats' => $stats,
        ]);
    }


    public function store(Request $req)
    {
        // LIMITEUR PAR RAPPORT AU ABONNEMENT
        $authenticatedUser = Customer::idCustomer();
        $user = Customer::findOrFail($authenticatedUser);

        //Check raha efa nanao abonnement
        $sub = Subscription::where('subscriber_id', $authenticatedUser)->first();
        if (!$sub) {
            return response()->json(['error' => 'Vous devriez vous abonner']);
        }
        //Maka subscriptionSlug
        $subscriptionSlug = Subscription::where('subscriber_id', $authenticatedUser)->first()->slug;
        //Maka idPlan sy featureSlug
        $idplan = $user->planSubscriptions()->first()->plan_id;
        $featureSlug = Feature::where('plan_id', $idplan)->where('name', '{"fr":"Projets"}')->first()->slug;

        $subscription = $user->planSubscription($subscriptionSlug);
        $usage = $subscription->usage()->byFeatureSlug($featureSlug)->first();

        //Initialisation du premier usage 0
        if (!$usage) {
            $subscription->recordFeatureUsage($featureSlug, 0, false);
        }

        if (!$subscription->canUseFeature($featureSlug)) {
            return response()->json(['error' => 'Vous avez atteint le nombre maximum de projets autorisés.']);
        }
        // FIN LIMITEUR PAR RAPPORT AU ABONNEMENT

        $validate = Validator::make($req->all(), [
            'project_title' => 'required|min:2|max:150'
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->messages()]);
        } else {
            try {
                DB::beginTransaction();
                $mdl = DB::table('mdls')->select('idModule')->where('idCustomer', Customer::idCustomer())->first();
                $salle = DB::table('v_list_salles')->select('idSalle')->where('idLieuType', 1)->first();

                DB::table('projets')->insertGetId([
                    'project_reference' => $req->project_reference,
                    'project_title' => $req->project_title,
                    'project_description' => $req->project_description,
                    'idModalite' => 1,
                    'idCustomer' => Customer::idCustomer(),
                    'idModule' => $mdl->idModule,
                    'idTypeProjet' => 3,
                    'idVilleCoded' => 1,
                    'project_is_active' => 0,
                    'idSalle' => $salle->idSalle,
                ]);

                $prj = DB::table('projets')->select('idProjet')->orderBy('idProjet', 'desc')->first();
                DB::table('internes')->insert([
                    'idProjet' => $prj->idProjet,
                    'idEtp' => Customer::idCustomer()
                ]);

                DB::commit();
                $subscription->recordFeatureUsage($featureSlug);
                return response()->json([
                    'status' => 200,
                    'message' => 'Succès',
                ]);
            } catch (Exception $e) {
                DB::rollBack();
                return response([
                    'status' => 400,
                    'message' => 'Erreur inconnue !'
                ]);
            }
        }
    }

    public function show($idProjet)
    {
        $projet = DB::table('v_union_projets')
            ->select('idProjet', 'idEtp_inter', 'idCfp_intra', 'idCfp_inter', 'dateDebut', 'dateFin', 'paiement', 'project_title', 'etp_name', 'ville', 'project_status', 'project_description',  'project_type', 'project_reference', 'idModalite', 'modalite', 'idEtp', 'etp_initial_name', 'etp_logo', 'idModule', 'module_name', 'module_image', 'module_description', 'salle_name', 'salle_rue', 'salle_quartier', 'salle_code_postal', 'ville', 'total_ht', 'total_ttc', 'total_ht_etp')
            ->where('idProjet', $idProjet);

        if($projet->exists()){
            $cfp = DB::table('customers')
            ->select('customerName AS cfp_name', 'logo AS cfp_logo', 'customerEmail AS cfp_email')
            ->whereIn('idCustomer', [$projet->first()->idCfp_inter, $projet->first()->idCfp_intra])
            ->first();

            $apprenantInter = DB::table('v_list_apprenant_inter_added')
                ->select('*')
                ->where('idProjet', $idProjet)
                ->get();

            $villes = DB::table('villes')->select('idVille', 'ville')->get();
            $paiements = DB::table('paiements')->select('idPaiement', 'paiement')->get();

            $seances = DB::table('v_seances')
                ->select('idSeance', 'dateSeance', 'heureDebut', 'heureFin', 'idProjet', 'idModule', 'intervalle_raw')
                ->where('idProjet', $idProjet)
                ->orderBy('dateSeance', 'asc')
                ->get();

            $countDate = DB::table('v_seances')
                ->select('idProjet', 'dateSeance', DB::raw('COUNT(*) as count'))
                ->where('idProjet', $idProjet)
                ->groupBy('dateSeance')
                ->get();

            $totalSession = DB::table('v_seances')
                ->selectRaw('SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))) as sumHourSession')
                ->where('idProjet', $idProjet)
                ->groupBy('idProjet')
                ->first();

            $modules = DB::table('mdls')
                ->select('idModule', 'moduleName AS module_name')
                ->where('moduleName', '!=', 'Default module')
                ->where('idCustomer', Customer::idCustomer())
                ->orderBy('moduleName', 'asc')
                ->get();

            $apprs = DB::table('v_list_apprenants')
                ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_fonction', 'emp_email', 'emp_photo', 'emp_matricule', 'etp_name')
                ->where('idProjet', $idProjet)
                ->where('idEtp', Customer::idCustomer())
                ->orderBy('emp_name', 'asc')
                ->get();

            $getSeance = DB::table('v_emargement_appr')
                ->select('idSeance', 'idProjet', 'heureDebut', 'heureFin', 'dateSeance')
                ->where('idProjet', $idProjet)
                ->groupBy('idSeance')
                ->get();

            $getAppr = DB::table('v_emargement_appr')
                ->select('idProjet', 'idEmploye', 'name', 'firstName', 'photo')
                ->where('idProjet', $idProjet)
                ->groupBy('idEmploye')
                ->get();

            $getIdAppr = DB::table('v_emargement_appr')
                ->select('idProjet', 'idEmploye', 'idSeance', 'name', 'firstName', 'photo')
                ->where('idProjet', $idProjet)
                ->get();

            $materiels = DB::table('prestation_modules')
                ->select('idPrestation', 'prestation_name', 'idModule')
                ->get();

            $prerequis = DB::table('prerequis_modules')
                ->select('idPrerequis', 'prerequis_name', 'idModule')
                ->get();

            $objectifs = DB::table('objectif_modules')->select('idObjectif', 'objectif', 'idModule')->get();

            $emargements = DB::table('emargements')
                ->select('idProjet', 'idEmploye', 'idSeance', 'isPresent')
                ->where('idProjet', $idProjet)
                ->get();

            $eval_content = DB::table('questions')
                ->select('idQuestion', 'question', 'idTypeQuestion')
                ->get();

            $eval_type = DB::table('questions')
                ->select('idQuestion', 'question', 'idTypeQuestion')
                ->groupBy('idTypeQuestion')
                ->get();

            $modalites = DB::table('modalites')->select('idModalite', 'modalite')->get();

            $checkEvaluation = DB::table('eval_chauds')->select('idProjet')->get();
            $checkEvaluationCount = count($checkEvaluation);

            if ($checkEvaluationCount > 0) {
                $notationProjet = DB::table('v_evaluation_alls')
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
                } else {
                    $noteGeneral = 0;
                }
            } else {
                $countNotationProjet = 0;
                $noteGeneral = 0;
            }

            $imagesMomentums = DB::table('images')
                ->select('url', 'idImages')
                ->where('idProjet', $idProjet)
                ->where('idTypeImage', 1)
                ->get();

            $nbPl = DB::table('inters')->select('nbPlace')->where('idProjet', $idProjet)->first();
            $place_available = $this->getPlaceAvailable($idProjet) ?? null;
            $place_reserved = $this->getNbPlaceReserved($idProjet) ?? null;
            $nbPlace = $nbPl->nbPlace ?? null;

            $restaurations = DB::table('project_restaurations AS pr')
                ->select('pr.idRestauration', 'rst.typeRestauration')
                ->join('restaurations AS rst', 'pr.idRestauration', 'rst.idRestauration')
                ->where('idProjet', $idProjet)
                ->get();

            $total_ht = ProjetController::getTotalHT($idProjet);
            if ($total_ht === null) {
                $total_ht = 0;
            }
            $fraisTotalHT = 0;
            $fraisTotalHT += (float) $total_ht;

            $fraisTotalTTC = $fraisTotalHT * 1.20;

            $idEtp = Customer::idCustomer();

            return response()->json([
                'status' => 200,
                'projet' => $projet->first(),
                'project_has' => [
                    'restaurations' => $restaurations,
                    'imagesMomentums' => $imagesMomentums,
                    'total_ht' => $total_ht,
                    'fraisTotalTTC' => $fraisTotalTTC,
                    'fraisTotalHT' => $fraisTotalHT,
                    'villes' => $villes,
                    'paiements' => $paiements,
                    'seances' => $seances,
                    'modules' => $modules,
                    'materiels' => $materiels,
                    'objectifs' => $objectifs,
                    'totalSession' => $totalSession,
                    'countDate' => $countDate,
                    'apprs' => $apprs,
                    'apprenantInter' => $apprenantInter,
                    'getSeance' => $getSeance,
                    'getAppr' => $getAppr,
                    'modalites' => $modalites,
                    'prerequis' => $prerequis,
                    'eval_content' => $eval_content,
                    'eval_type' => $eval_type,
                    'countNotationProjet' => $countNotationProjet,
                    'noteGeneral' => $noteGeneral,
                    'nbPlace' => $nbPlace,
                    'place_available' => $place_available,
                    'place_reserved' => $place_reserved,
                    'emargements' => $emargements,
                    'getIdAppr' => $getIdAppr,
                    'idEtp' => $idEtp,
                    'cfp' => $cfp
                ]
            ]);
        }else{
            return response()->json([
                'status' => 204,
                'message' => 'Projet introuvable !'
            ], 204);
        }
    }

    public function getMiniCV($idFormateur)
    {
        $form = DB::table('users')
                ->select('id', 'name', 'email', 'firstName', 'phone', 'photo')
                ->where('id', $idFormateur);

        if($form->exists()){
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

            return response()->json([
                'status' => 200,
                'form' => $form->first(),
                'form_has' => [
                    'experiences' => $exp,
                    'diplomes' => $dp,
                    'competences' => $cpc,
                    'langues' => $lg
                ]
            ]);
        }else{
            return response()->json([
                'status' => 204,
                'message' => 'Introuvable !'
            ], 204);
        }
    }

    public function formAssign($idProjet, $idFormateur){
        $check = DB::table('project_forms')
            ->select('idProjet', 'idFormateur')
            ->where('idProjet', $idProjet)
            ->where('idFormateur', $idFormateur)
            ->count();

        if ($check <= 0) {
            $insert = DB::table('project_forms')->insert([
                'idProjet' => $idProjet,
                'idFormateur' => $idFormateur
            ]);

            if ($insert) {
                return response()->json(['success' => 'Succès']);
            } else {
                return response()->json(['error' => 'Erreur inconnue !']);
            }
        } else {
            return response()->json(['error' => 'Formateur déjas inscrit au projet !']);
        }
    }

    public function getFormInterneAdded($idProjet)
    {
        $forms = DB::table('v_formateur_internes')
            ->select('idProjet', 'idEmploye', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'email AS form_email', 'initialNameForm AS form_initial_name')
            ->groupBy('idProjet', 'idEmploye', 'name', 'firstName', 'photoForm', 'email', 'initialNameForm')
            ->where('idProjet', $idProjet)
            ->get();

        if(count($forms) <= 0){
            return response()->json([
                'status' => 204,
                'message' => 'AUcun résultat !'
            ], 204);
        }

        return response()->json([
            'status' => 200,
            'forms' => $forms
        ]);
    }

    public function getFormAdded($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idProjet', 'idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'email AS form_email', 'initialNameForm AS form_initial_name', 'form_phone')
            ->groupBy('idProjet', 'idFormateur', 'name', 'firstName', 'photoForm', 'email', 'initialNameForm')
            ->where('idProjet', $idProjet)
            ->get();

        if(count($forms) <= 0){
            return response()->json([
                'forms' => [],
                'message' => 'AUcun résultat !'
            ], 204);
        }

        return response()->json([
            'status' => 200,
            'forms' => $forms
        ]);
    }

    public function formRemove($idProjet, $idFormateur)
    {
        $query = DB::table('project_forms')->where('idFormateur', $idFormateur)->where('idProjet', $idProjet);
        
        if($query->exists()){
            $query->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Succès'
            ]);
        }else{
            return response()->json([
                'status' => 204,
                'message' => 'AUcun résultat !'
            ], 204);
        }
    }

    public function getEtpAssign($idProjet)
    {
        $etp = DB::table('v_union_projets')->select('idProjet', 'idEtp', 'etp_initial_name', 'etp_name', 'etp_logo')->where('idProjet', $idProjet);

        if($etp->exists()){
            return response()->json([
                'status' => 200,
                'entreprise' => $etp->first()
            ]);
        }else{
            return response()->json([
                'status' => 204,
                'message' => 'AUcun résultat !'
            ], 204);
        }
    }

    public function etpAssign($idProjet, $idEtp)
    {
        $checkEval = DB::table('eval_chauds')
            ->join('detail_apprenants', 'eval_chauds.idEmploye', '=', 'detail_apprenants.idEmploye')
            ->select('eval_chauds.*')
            ->where('eval_chauds.idProjet', $idProjet)
            ->get();

        $checkPresence = DB::table('emargements')
            ->join('detail_apprenants', 'emargements.idEmploye', '=', 'detail_apprenants.idEmploye')
            ->select('emargements.*')
            ->where('emargements.idProjet', $idProjet)
            ->get();

        try {
            if (count($checkEval) > 0 && count($checkPresence) > 0) {
                DB::beginTransaction();

                //Remove Evaluation
                DB::table('eval_chauds')
                    ->join('detail_apprenants', 'eval_chauds.idEmploye', '=', 'detail_apprenants.idEmploye')
                    ->select('eval_chauds.*')
                    ->where('eval_chauds.idProjet', $idProjet)
                    ->delete();

                //Remove presence
                DB::table('emargements')
                    ->join('detail_apprenants', 'emargements.idEmploye', '=', 'detail_apprenants.idEmploye')
                    ->select('emargements.*')
                    ->where('emargements.idProjet', $idProjet)
                    ->delete();

                DB::table('detail_apprenants')->where('idProjet', $idProjet)->delete();

                DB::table('projets')
                    ->join('intras', 'intras.idProjet', 'projets.idProjet')
                    ->where('projets.idProjet', $idProjet)
                    ->update(['idEtp' => $idEtp]);

                DB::commit();
            } elseif (count($checkEval) > 0 && count($checkPresence) <= 0) {
                DB::beginTransaction();

                //Remove Evaluation
                DB::table('eval_chauds')
                    ->join('detail_apprenants', 'eval_chauds.idEmploye', '=', 'detail_apprenants.idEmploye')
                    ->select('eval_chauds.*')
                    ->where('eval_chauds.idProjet', $idProjet)
                    ->delete();

                DB::table('detail_apprenants')->where('idProjet', $idProjet)->delete();

                DB::table('projets')
                    ->join('intras', 'intras.idProjet', 'projets.idProjet')
                    ->where('projets.idProjet', $idProjet)
                    ->update(['idEtp' => $idEtp]);
                DB::commit();
            } elseif (count($checkEval) <= 0 && count($checkPresence) > 0) {
                DB::beginTransaction();

                //Remove presence
                DB::table('emargements')
                    ->join('detail_apprenants', 'emargements.idEmploye', '=', 'detail_apprenants.idEmploye')
                    ->select('emargements.*')
                    ->where('emargements.idProjet', $idProjet)
                    ->delete();

                DB::table('detail_apprenants')->where('idProjet', $idProjet)->delete();

                DB::table('projets')
                    ->join('intras', 'intras.idProjet', 'projets.idProjet')
                    ->where('projets.idProjet', $idProjet)
                    ->update(['idEtp' => $idEtp]);
                DB::commit();
            } else {
                DB::beginTransaction();
                DB::table('detail_apprenants')->where('idProjet', $idProjet)->delete();

                DB::table('projets')
                    ->join('intras', 'intras.idProjet', 'projets.idProjet')
                    ->where('projets.idProjet', $idProjet)
                    ->update(['idEtp' => $idEtp]);
                DB::commit();
            }
            return response()->json(['success' => 'Succès']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    public function moduleAssign($idProjet, $idModule)
    {
        $query = DB::table('projets')->where('idCustomer', Customer::idCustomer())->where('idProjet', $idProjet);
        
        if($query->exists()){
            $query->update(['idModule' => $idModule]);

            return response()->json([
                'status' => 200,
                'message' => 'Succès'
            ]);
        }else{
            return response()->json([
                'status' => 204,
                'message' => 'AUcun résultat !'
            ], 204);
        }
    }

    public function getModuleAssign($idProjet)
    {
        $mdl = DB::table('v_union_projets')->select('idProjet', 'idModule', 'module_name', 'module_description', 'module_image')->where('idProjet', $idProjet);

        if($mdl->exists()){
            return response()->json([
                'status' => 200,
                'module' => $mdl->first()
            ]);
        }else{
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }
    }

    public function dateAssign(Request $req, $idProjet)
    {
        $req->validate([
            'dateDebut' => 'required|date',
            'dateFin' => 'required|date|after_or_equal:dateDebut'
        ]);

        $query = DB::table('projets')->where('idCustomer', Customer::idCustomer())->where('idProjet', $idProjet);
        
        if($query->exists()){
            $query->update([
                'dateDebut' => $req->dateDebut,
                'dateFin' => $req->dateFin,
                'project_is_reserved' => $req->project_reservation,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Succès'
            ]);
        }else{
            return response()->json([
                'status' => 204,
                'message' => 'AUcun résultat !'
            ], 204);
        }
    }

    public function destroy($idProjet){
        $typeProjet = DB::table('projets')->select('idTypeProjet')->where('idProjet', $idProjet)->first();
        $interEtp = DB::table('inter_entreprises')->where('idProjet', $idProjet)->get();
        $apprs = DB::table('detail_apprenants')->where('idProjet', $idProjet)->get();

        $emgms = DB::table('emargements')
            ->join('seances', 'emargements.idSeance', 'seances.idSeance')
            ->where('seances.idProjet', $idProjet)
            ->get();
        $seances = DB::table('seances')->where('idProjet', $idProjet)->get();

        if (count($interEtp) > 0 || count($emgms) > 0) {
            return response()->json(['error' => 'Ce projet ne peut pas être supprimé !']);
        } else {
            if ($typeProjet->idTypeProjet == 3) {
                if (count($seances) > 0) {
                    DB::table('seances')->where('idProjet', $idProjet)->delete();
                } else {
                    try {
                        DB::beginTransaction();
                        DB::table('internes')->where('idProjet', $idProjet)->delete();
                        DB::table('projets')->where('idProjet', $idProjet)->delete();
                        DB::commit();

                        return response()->json([
                            'status' => 200,
                            'message' => 'Succès'
                        ]);
                    } catch (Exception $e) {
                        DB::rollBack();
                        return response()->json(['error' => 'Erreur inconnue !']);
                    }
                }
            }
        }
    }

    public function getProgramme($idModule)
    {
        $programmes = DB::table('programmes')->select('idProgramme', 'program_title', 'program_description', 'idModule')->where('idModule', $idModule)->get();

        if(count($programmes) <= 0){
            return response()->json([
                'status' => 204,
                'message' => 'AUcun résultat !'
            ], 204);
        }

        return response()->json([
            'status' => 200,
            'programmes' => $programmes
        ]);
    }

    public function getModuleRessourceProject($idModule)
    {
        $module_ressources = DB::table('module_ressources')
            ->select('idModuleRessource', 'module_ressource_name', 'module_ressource_extension', 'idModule')
            ->where('idModule', $idModule)
            ->get();

        if(count($module_ressources) <= 0){
            return response()->json([
                'status' => 204,
                'message' => 'AUcun résultat !'
            ], 204);
        }

        return response()->json([
            'status' => 200,
            'programmes' => $module_ressources
        ]);
    }

    public function duplicate($idProjet)
    {
        $project =  Projet::where('idProjet', $idProjet)->first();

        $newProject = Projet::create([
            'referenceEtp' => $project->referenceEtp,
            'project_reference' => $project->project_reference,
            'project_title' => $project->project_title,
            'projectName' => $project->projectName,
            'dateDebut' => $project->dateDebut,
            'dateFin' => $project->dateFin,
            'dateFin' => $project->dateFin,
            'lieu' => $project->lieu,
            'idVille' => $project->idVille,
            'idModule' => $project->idModule,
            //'restaurations' => $this->getRestauration($project->idProjet),
            'idCustomer' => $project->idCustomer,
            'idModalite' => $project->idModalite,
            'idTypeProjet' => $project->idTypeProjet,
            'idSalle' => $project->idSalle,
            'project_description' => $project->project_description,
            'project_num_fmfp' => $project->project_num_fmfp,
            'project_is_active' => 0,
            'project_is_reserved' => 0,
            'project_is_cancelled' => 0,
            'project_is_repported' => 0,
            'project_is_trashed' => 0,
            'project_price_pedagogique' => 0,
            'project_price_annexe' => 0,
            'total_ht' => 0,
            'total_ttc' => 0,
        ]);


        if (!$newProject) {
            return response()->json(['error' => 'Erreur inconnue !']);
        }

        $new_idProjet = Projet::latest()->first()->idProjet;

        $interne = DB::table('internes')
            ->where('idProjet', $idProjet)->first();

        $insert_interne = DB::table('internes')->insert([
            'idProjet' => $new_idProjet,
            'idEtp' => $interne->idEtp,
        ]);

        if (!$insert_interne) {
            DB::table('projets')->where('idProjet', $new_idProjet)->delete();
            return response()->json(['error' => 'Erreur inconnue !']);
        }

        return response()->json(['success' => 'Projet dupliquer avec succès']);
    }

    public function updateDate(Request $req, $idProjet)
    {
        $query = $this->project->getProject(Customer::idCustomer())->where('idProjet', $idProjet);
        
        if ($req->dateDebut) {
            $validate = Validator::make($req->all(), [
                'dateDebut' => 'required|date'
            ]);

            if ($validate->fails()) {
                return response()->json(['error' => $validate->messages()]);
            } else {
                if($query->exists()){
                    $query->update([
                        'dateDebut' => $req->dateDebut
                    ]);

                    return response()->json([
                        'status' => 200,
                        'message' => 'Succès'
                    ]);
                }else{
                    return response()->json([
                        'status' => 204,
                        'message' => 'Introuvable !'
                    ], 204);
                }
            }
        } elseif ($req->dateFin) {
            $validate = Validator::make($req->all(), [
                'dateFin' => 'required|date'
            ]);

            if ($validate->fails()) {
                return response()->json(['error' => $validate->messages()]);
            } else {
                if($query->exists()){
                    $query->update([
                        'dateFin' => $req->dateFin
                    ]);

                    return response()->json([
                        'status' => 200,
                        'message' => 'Succès'
                    ]);
                }else{
                    return response()->json([
                        'status' => 204,
                        'message' => 'Introuvable !'
                    ], 204);
                }
            }
        }
    }

    public function updateModule(Request $req, $idProjet)
    {
        $req->validate([
            'idModule' => 'required|exists:mdls,idModule'
        ]);
    
        $query = $this->project->getProject(Customer::idCustomer())->where('idProjet', $idProjet);

        if($query->exists()){
            $query->update([
                'idModule' => $req->idModule
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Succès'
            ]);
        }else{
            return response()->json([
                'status' => 204,
                'message' => 'Introuvable !'
            ], 204);
        }
    }

    public function salleAssign($idProjet, $idSalle)
    {
        $query = $this->project->getProject(Customer::idCustomer())->where('idProjet', $idProjet);

        if($query->exists()){
            $query->update([
                'idSalle' => $idSalle
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Succès'
            ]);
        }else{
            return response()->json([
                'status' => 204,
                'message' => 'Introuvable !'
            ], 204);
        }
    }

    public function getSalleAdded($idProjet)
    {
        $salle = DB::table('v_union_projets')
            ->select( 'salle_name', 'salle_rue', 'salle_quartier', 'ville', 'salle_code_postal')
            ->where(function ($query) use ($idProjet) {
                $query->where('idProjet', $idProjet)
                    ->where('salle_name', '!=', 'null');
            });

        if($salle->exists()){
            return response()->json([
                'status' => 200,
                'salle' => $salle->first()
            ]);
        }else{
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }
    }

    public function cancel($idProjet)
    {
        $query = $this->project->getProject(Customer::idCustomer())->where('idProjet', $idProjet);

        if($query->exists()){
            $query->update([
                'project_is_active' => 0,
                'project_is_reserved' => 0,
                'project_is_repported' => 0,
                'project_is_trashed' => 0,
                'project_is_cancelled' => 1
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Succès'
            ]);
        }else{
            return response()->json([
                'status' => 204,
                'message' => 'Introuvable !'
            ], 204);
        }
    }

    public function repport(Request $req, $idProjet)
    {
        $req->validate([
            'dateDebut' => 'required|date',
            'dateFin' => 'required|date|after_or_equal:dateDebut'
        ]);

        $query = $this->project->getProject(Customer::idCustomer())->where('idProjet', $idProjet);

        if($query->exists()){
            $query->update([
                'dateDebut' => $req->dateDebut,
                'dateFin' => $req->dateFin,
                'project_is_repported' => 1,
                'project_is_active' => 0,
                'project_is_reserved' => 0,
                'project_is_trashed' => 0,
                'project_is_cancelled' => 0
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Succès'
            ]);
        }else{
            return response()->json([
                'status' => 204,
                'message' => 'Introuvable !'
            ], 204);
        }
    }

    public function confirm($idProjet)
    {
        $query = $this->project->getProject(Customer::idCustomer())->where('idProjet', $idProjet);

        if($query->exists()){
            $query->update([
                'project_is_active' => 1,
                'project_is_reserved' => 0,
                'project_is_repported' => 0,
                'project_is_trashed' => 0,
                'project_is_cancelled' => 0
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Succès'
            ]);
        }else{
            return response()->json([
                'status' => 204,
                'message' => 'Introuvable !'
            ], 204);
        }
    }

    public function updateProjet(Request $req, $idProjet)
    {
        $req->validate([
            'project_reference' => 'required|min:2|max:150',
            'project_title' => 'required|min:2|max:150',
            'project_description' => 'required|min:2|max:150'
        ]);

        $query = $this->project->getProject(Customer::idCustomer())->where('idProjet', $idProjet);

        if($query->exists()){
            $query->update([
                'project_reference' => $req->project_reference,
                'project_title' => $req->project_title,
                'project_description' => $req->project_description
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Succès'
            ]);
        }else{
            return response()->json([
                'status' => 204,
                'message' => 'Projet Introuvable !'
            ], 204);
        }
    }

    public function getDropdownItem()
    {
        $status = [];

        $etp_id = Customer::idCustomer();

        $etp_is_grouped = DB::table('etp_groupeds')->where('idEntreprise', $etp_id)->exists();

        $id_projets = $this->list_id_project_etpgrouped($etp_id);

        if ($etp_is_grouped) {

            $projects = $this->getUnionProjects($id_projets)->get();

            $status = $projects->reduce(function ($carry, $item) {
                $carry[$item->project_status] = ($carry[$item->project_status] ?? 0) + 1;
                return $carry;
            }, []);

            $status = array_map(function ($key, $value) {
                return [
                    'project_status' => $key,
                    'projet_nb' => $value
                ];
            }, array_keys($status), array_values($status));

            $types = $this->getUnionProjectTypes($id_projets);
            $periodePrev3 = $this->getUnionProjectPeriodePrev3($id_projets);
            $periodePrev6 = $this->getUnionProjectPeriodePrev6($id_projets);
            $periodePrev12 = $this->getUnionProjectPeriodePrev12($id_projets);
            $periodeNext3 = $this->getUnionProjectPeriodeNext3($id_projets);
            $periodeNext6 = $this->getUnionPeriodeNext6($id_projets);
            $periodeNext12 = $this->getUnionProjectPeriodeNext12($id_projets);
            $modules = $this->getUnionProjectModules($id_projets);
            $villes = $this->getUnionProjectVilles($id_projets);

            $financements = DB::table('v_projet_cfps')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();


            //fonction pour selectionne tous les id des formateurs...        
            $queries  = $this->getIdFormateurInterne();

            $formateurs = [];
            foreach ($queries as $key => $queryInfo) {
                $query = DB::table('v_formateur_cfps')
                    ->select('idProjet', 'idFormateur', 'project_type', 'idEtp', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
                    ->where('idFormateur', $queryInfo->idFormateur)
                    ->groupBy('idProjet', 'idFormateur', 'project_type', 'idEtp');
                $forms = $query->get();
                $project_count = count($forms);
                $formateurs[$key] = [
                    'idFormateur' => $forms[0]->idFormateur,
                    'form_name' => $forms[0]->form_name,
                    'form_firstname' => $forms[0]->form_firstname,
                    'projet_nb' => $project_count
                ];
            }

            //dd($modules);
            //dd($types);
        } else {  //<== Liste des projets pour une groupe d'Entreprise(ex:AXIAN) 

            $projects = $this->getUnionProject()->get();

            $status = $projects->reduce(function ($carry, $item) {
                $carry[$item->project_status] = ($carry[$item->project_status] ?? 0) + 1;
                return $carry;
            }, []);

            $status = array_map(function ($key, $value) {
                return [
                    'project_status' => $key,
                    'projet_nb' => $value
                ];
            }, array_keys($status), array_values($status));

            $types = DB::table('v_union_projets')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $periodePrev3 = DB::table('v_union_projets')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "prev_3_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->first();

            $periodePrev12 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->first();

            $periodeNext3 = DB::table('v_union_projets')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "next_3_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_union_projets')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->first();

            $periodeNext12 = DB::table('v_union_projets')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->first();

            $modules = DB::table('v_union_projets')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('module_name', '!=', 'Default module')
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_union_projets')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            $financements = DB::table('v_projet_cfps')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();


            //fonction pour selectionne tous les id des formateurs...        
            $queries  = $this->getIdFormateurInterne();

            $formateurs = [];
            foreach ($queries as $key => $queryInfo) {
                $query = DB::table('v_formateur_cfps')
                    ->select('idProjet', 'idFormateur', 'project_type', 'idEtp', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
                    ->where('idFormateur', $queryInfo->idFormateur)
                    ->groupBy('idProjet', 'idFormateur', 'project_type', 'idEtp');
                $forms = $query->get();
                $project_count = count($forms);
                $formateurs[$key] = [
                    'idFormateur' => $forms[0]->idFormateur,
                    'form_name' => $forms[0]->form_name,
                    'form_firstname' => $forms[0]->form_firstname,
                    'projet_nb' => $project_count
                ];
            }
        }

        return response()->json([
            'status' => $status,
            //'etps' => [...$filteredEtps],
            'types' => $types,
            'periodePrev3' => $periodePrev3,
            'periodePrev6' => $periodePrev6,
            'periodePrev12' => $periodePrev12,
            'periodeNext3' => $periodeNext3,
            'periodeNext6' => $periodeNext6,
            'periodeNext12' => $periodeNext12,
            'modules' => $modules,
            'villes' => $villes,
            'financements' => $financements,
            'formateurs' => $formateurs,
        ]);
    }

    public function filterItems(Request $req)
    {
        $idStatus = explode(',', $req->idStatut);
        $idEtps = explode(',', $req->idEtp);
        $idTypes = explode(',', $req->idType);
        $idPeriodes = $req->idPeriode;
        $idModules = explode(',', $req->idModule);
        $idVilles = explode(',', $req->idVille);
        $idFinancements = explode(',', $req->idFinancement);
        $idFormateurs = explode(',', $req->idFormateur);
        $searchTerm = $req->search;

        $status = [];

        $etp_id = Customer::idCustomer();

        $etp_is_grouped = DB::table('etp_groupeds')->where('idEntreprise', $etp_id)->exists();

        $id_projets = $this->list_id_project_etpgrouped($etp_id);

        $query = $this->getUnionProjects($id_projets);

        $searchTerm = trim($searchTerm);
        if ($searchTerm !== '') {
            $like = '%' . mb_strtolower($searchTerm, 'UTF-8') . '%';

            $query->where(function($q) use ($like) {
                $q->whereRaw('LOWER(project_reference) LIKE ?', [$like])
                ->orWhereRaw('LOWER(module_name) LIKE ?', [$like]);
            });
        }

        /*******************************************ETP FILLE************************************************************************* */
        /**ETp grouped ex:TELMA */
        if ($etp_is_grouped && $idStatus[0] != null) {

            //dd($idStatus);

            $query = $this->getUnionProjects($id_projets);

            $query->whereIn('project_status', $idStatus);
            $types = $this->getUnionProjectTypes($id_projets);
            $periodePrev3 = $this->getUnionProjectPeriodePrev3($id_projets);
            $periodePrev6 = $this->getUnionProjectPeriodePrev6($id_projets);
            $periodePrev12 = $this->getUnionProjectPeriodePrev12($id_projets);
            $periodeNext3 = $this->getUnionProjectPeriodeNext3($id_projets);
            $periodeNext6 = $this->getUnionPeriodeNext6($id_projets);
            $periodeNext12 = $this->getUnionProjectPeriodeNext12($id_projets);
            $modules = $this->getUnionProjectModules($id_projets);
            $villes = $this->getUnionProjectVilles($id_projets);

            //dd($query->get());

            $financements = DB::table('v_projet_cfps')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();


            //fonction pour selectionne tous les id des formateurs...        
            $queries  = $this->getIdFormateurInterne();

            $formateurs = [];
            foreach ($queries as $key => $queryInfo) {
                $queryForm = DB::table('v_formateur_internes')
                    ->select('idProjet', 'idEmploye', 'project_type', 'idEntreprise', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
                    ->where('idEmploye', $queryInfo->idEmploye)
                    ->groupBy('idProjet', 'idEmploye', 'project_type', 'idEntreprise');
                $forms = $queryForm->get();
                $project_count = count($forms);
                $formateurs[$key] = [
                    'idFormateur' => $forms[0]->idEmploye,
                    'form_name' => $forms[0]->form_name,
                    'form_firstname' => $forms[0]->form_firstname,
                    'projet_nb' => $project_count
                ];
            }

            $projectDates = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate', 'headMonthDebut')
                ->orderBy('dateDebut', 'asc')
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->orWhereIn('idProjet', $id_projets)
                ->whereIn('project_status', $idStatus)
                ->get();
        }

        if ($etp_is_grouped && $idTypes[0] != null) {

            $query = $this->getUnionProjects($id_projets);

            $query->whereIn('project_type', $idTypes);
            $types = $this->getUnionProjectTypes($id_projets);
            $periodePrev3 = $this->getUnionProjectPeriodePrev3($id_projets);
            $periodePrev6 = $this->getUnionProjectPeriodePrev6($id_projets);
            $periodePrev12 = $this->getUnionProjectPeriodePrev12($id_projets);
            $periodeNext3 = $this->getUnionProjectPeriodeNext3($id_projets);
            $periodeNext6 = $this->getUnionPeriodeNext6($id_projets);
            $periodeNext12 = $this->getUnionProjectPeriodeNext12($id_projets);
            $modules = $this->getUnionProjectModules($id_projets);
            $villes = $this->getUnionProjectVilles($id_projets);


            $financements = DB::table('v_projet_cfps')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();


            //fonction pour selectionne tous les id des formateurs...        
            $queries  = $this->getIdFormateurInterne();

            $formateurs = [];
            foreach ($queries as $key => $queryInfo) {
                $queryForm = DB::table('v_formateur_internes')
                    ->select('idProjet', 'idEmploye', 'project_type', 'idEntreprise', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
                    ->where('idEmploye', $queryInfo->idEmploye)
                    ->groupBy('idProjet', 'idEmploye', 'project_type', 'idEntreprise');
                $forms = $queryForm->get();
                $project_count = count($forms);
                $formateurs[$key] = [
                    'idFormateur' => $forms[0]->idEmploye,
                    'form_name' => $forms[0]->form_name,
                    'form_firstname' => $forms[0]->form_firstname,
                    'projet_nb' => $project_count
                ];
            }
        }

        if ($idStatus[0] != null) {
            $query->whereIn('project_status', $idStatus);

            $etps = DB::table('v_union_projets')
                ->select('idEtp', 'etp_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('project_status', $idStatus)
                ->groupBy('idEtp', 'etp_name')
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_union_projets')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('project_status', $idStatus)
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $periodePrev3 = DB::table('v_union_projets')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "prev_3_month")
                ->whereIn('project_status', $idStatus)
                ->groupBy('idProjet', 'p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('project_status', $idStatus)
                ->first();

            $periodePrev12 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('project_status', $idStatus)
                ->first();

            $periodeNext3 = DB::table('v_union_projets')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "next_3_month")
                ->whereIn('project_status', $idStatus)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_union_projets')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->whereIn('project_status', $idStatus)
                ->first();

            $periodeNext12 = DB::table('v_union_projets')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->whereIn('project_status', $idStatus)
                ->first();

            $modules = DB::table('v_union_projets')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('project_status', $idStatus)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_union_projets')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('project_status', $idStatus)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            $financements = DB::table('v_union_projets')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEtp', Customer::idCustomer())
                ->whereIn('project_status', $idStatus)
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();

            $forms = DB::table('v_formateur_internes')
                ->select('idEmploye', 'name AS form_name', 'firstName AS form_firstname', DB::raw('COUNT(DISTINCT(idProjet)) AS projet_nb'))
                ->where('idEntreprise', Customer::idCustomer())
                ->whereIn('project_status', $idStatus)
                ->groupBy('idEmploye')
                ->get();
            //dd($forms);

            $projectDates = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate', 'headMonthDebut')
                ->orderBy('dateDebut', 'asc')
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('project_status', $idStatus)
                ->get();
        }
        /** AXIAN Entreprise... */
        if ($idEtps[0] != null) {
            $query->whereIn('idEtp', $idEtps);

            $status = DB::table('v_union_projets')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('idEtp', $idEtps)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();

            $types = DB::table('v_union_projets')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $periodePrev3 = DB::table('v_union_projets')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "prev_3_month")
                ->whereIn('idEtp', $idEtps)
                ->groupBy('idProjet', 'p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('idEtp', $idEtps)
                ->first();

            $periodePrev12 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('idEtp', $idEtps)
                ->first();

            $periodeNext3 = DB::table('v_union_projets')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "next_3_month")
                ->whereIn('idEtp', $idEtps)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_union_projets')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->whereIn('idEtp', $idEtps)
                ->first();

            $periodeNext12 = DB::table('v_union_projets')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->whereIn('idEtp', $idEtps)
                ->first();

            $modules = DB::table('v_union_projets')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('idEtp', $idEtps)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_union_projets')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('idEtp', $idEtps)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();


            $financements = DB::table('v_union_projets')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEtp', Customer::idCustomer())
                ->whereIn('project_status', $idStatus)
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();

            $projectDates = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate', 'headMonthDebut')
                ->orderBy('dateDebut', 'asc')
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('idEtp', $idEtps)
                ->get();
        }
        /** AXIAN Types ... */
        if ($idTypes[0] != null) {
            $query->whereIn('project_type', $idTypes);

            $status = DB::table('v_union_projets')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('project_type', $idTypes)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();

            $etps = DB::table('v_union_projets')
                ->select('idEtp', 'etp_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('project_type', $idTypes)
                ->groupBy('idEtp', 'etp_name')
                ->orderBy('etp_name', 'asc')
                ->get();

            $periodePrev3 = DB::table('v_union_projets')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "prev_3_month")
                ->whereIn('project_type', $idTypes)
                ->groupBy('idProjet', 'p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('project_type', $idTypes)
                ->first();

            $periodePrev12 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('project_type', $idTypes)
                ->first();

            $periodeNext3 = DB::table('v_union_projets')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "next_3_month")
                ->whereIn('project_type', $idTypes)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_union_projets')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->whereIn('project_type', $idTypes)
                ->first();

            $periodeNext12 = DB::table('v_union_projets')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->whereIn('project_type', $idTypes)
                ->first();

            $modules = DB::table('v_union_projets')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('project_type', $idTypes)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_union_projets')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('project_type', $idTypes)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();


            $financements = DB::table('v_union_projets')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEtp', Customer::idCustomer())
                ->whereIn('project_status', $idStatus)
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();

            $projectDates = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate', 'headMonthDebut')
                ->orderBy('dateDebut', 'asc')
                ->where('idEtp', Customer::idCustomer())
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('project_type', $idTypes)
                ->get();
        }
        /** AXIAN Periodes... */
        if ($idPeriodes != null) {
            switch ($idPeriodes) {
                case 'prev_3_month':
                    $query->where('p_id_periode', $idPeriodes);

                    $projectDates = DB::table('v_union_projets')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate', 'headMonthDebut')
                        ->orderBy('dateDebut', 'asc')
                        ->where(function ($query) {
                            $query->where('idEtp', Customer::idCustomer())
                                ->orWhereIn(
                                    'idCfp_inter',
                                    $this->getIdEtpCfps()
                                );
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('p_id_periode', $idPeriodes)
                        ->get();

                    break;
                case 'prev_6_month':
                    $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                    $projectDates = DB::table('v_union_projets')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate', 'headMonthDebut')
                        ->orderBy('dateDebut', 'asc')
                        ->where(function ($query) {
                            $query->where('idEtp', Customer::idCustomer())
                                ->orWhere(
                                    'idCfp_inter',
                                    Customer::idCustomer()
                                        ->orWhereIn('idCfp_inter', $this->getIdEtpCfps())
                                );
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                        ->get();

                    break;
                case 'prev_12_month':
                    $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                    $projectDates = DB::table('v_union_projets')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate', 'headMonthDebut')
                        ->orderBy('dateDebut', 'asc')
                        ->where(function ($query) {
                            $query->where('idEtp', Customer::idCustomer())
                                ->orWhere(
                                    'idCfp_inter',
                                    Customer::idCustomer()
                                        ->orWhereIn('idCfp_inter', $this->getIdEtpCfps())
                                );
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                        ->get();

                    break;
                case 'next_3_month':
                    $query->where('p_id_periode', $idPeriodes);

                    $projectDates = DB::table('v_union_projets')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate', 'headMonthDebut')
                        ->orderBy('dateDebut', 'asc')
                        ->where(function ($query) {
                            $query->where('idEtp', Customer::idCustomer())
                                ->orWhere(
                                    'idCfp_inter',
                                    Customer::idCustomer()
                                        ->orWhereIn('idCfp_inter', $this->getIdEtpCfps())
                                );
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('p_id_periode', $idPeriodes)
                        ->get();

                    break;
                case 'next_6_month':
                    $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);

                    $projectDates = DB::table('v_union_projets')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate', 'headMonthDebut')
                        ->orderBy('dateDebut', 'asc')
                        ->where(function ($query) {
                            $query->where('idEtp', Customer::idCustomer())
                                ->orWhere(
                                    'idCfp_inter',
                                    Customer::idCustomer()
                                        ->orWhereIn('idCfp_inter', $this->getIdEtpCfps())
                                );
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                        ->get();
                    break;
                case 'next_12_month':
                    $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                    $projectDates = DB::table('v_union_projets')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate', 'headMonthDebut')
                        ->orderBy('dateDebut', 'asc')
                        ->where(function ($query) {
                            $query->where('idEtp', Customer::idCustomer())
                                ->orWhere(
                                    'idCfp_inter',
                                    Customer::idCustomer()
                                        ->orWhereIn('idCfp_inter', $this->getIdEtpCfps())
                                );
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                        ->get();

                    break;

                default:
                    $query->where('p_id_periode', $idPeriodes);

                    $projectDates = DB::table('v_union_projets')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate', 'headMonthDebut')
                        ->orderBy('dateDebut', 'asc')
                        ->where(function ($query) {
                            $query->where('idEtp', Customer::idCustomer())
                                ->orWhere(
                                    'idCfp_inter',
                                    Customer::idCustomer()
                                        ->orWhereIn('idCfp_inter', $this->getIdEtpCfps())
                                );
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('p_id_periode', $idPeriodes)
                        ->get();

                    break;
            }

            $status = DB::table('v_union_projets')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->where('p_id_periode', $idPeriodes)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();

            $etps = DB::table('v_union_projets')
                ->select('idEtp', 'etp_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->where('p_id_periode', $idPeriodes)
                ->groupBy('idEtp', 'etp_name')
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_union_projets')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->where('p_id_periode', $idPeriodes)
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $modules = DB::table('v_union_projets')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->where('p_id_periode', $idPeriodes)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_union_projets')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->where('p_id_periode', $idPeriodes)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();


            $financements = DB::table('v_union_projets')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEtp', Customer::idCustomer())
                ->whereIn('project_status', $idStatus)
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();
        }
        /** AXIAN Modules... */
        if ($idModules[0] != null) {
            $query->whereIn('idModule', $idModules);

            $status = DB::table('v_union_projets')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('idModule', $idModules)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();

            $etps = DB::table('v_union_projets')
                ->select('idEtp', 'etp_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('idModule', $idModules)
                ->groupBy('idEtp', 'etp_name')
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_union_projets')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('idModule', $idModules)
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $periodePrev3 = DB::table('v_union_projets')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "prev_3_month")
                ->whereIn('idModule', $idModules)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('idModule', $idModules)
                ->first();

            $periodePrev12 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('idModule', $idModules)
                ->first();

            $periodeNext3 = DB::table('v_union_projets')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "next_3_month")
                ->whereIn('idModule', $idModules)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_union_projets')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->whereIn('idModule', $idModules)
                ->first();

            $periodeNext12 = DB::table('v_union_projets')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->whereIn('idModule', $idModules)
                ->first();

            $villes = DB::table('v_union_projets')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('idModule', $idModules)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            $financements = DB::table('v_union_projets')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEtp', Customer::idCustomer())
                ->whereIn('project_status', $idStatus)
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();

            $projectDates = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate', 'headMonthDebut')
                ->orderBy('dateDebut', 'asc')
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('idModule', $idModules)
                ->get();
        }
        /** AXIAN Villes... */
        if ($idVilles[0] != null) {
            $query->whereIn('idVille', $idVilles);

            $status = DB::table('v_union_projets')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('idVille', $idVilles)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();

            $etps = DB::table('v_union_projets')
                ->select('idEtp', 'etp_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('idVille', $idVilles)
                ->groupBy('idEtp', 'etp_name')
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_union_projets')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('idVille', $idVilles)
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $periodePrev3 = DB::table('v_union_projets')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "prev_3_month")
                ->whereIn('idVille', $idVilles)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('idVille', $idVilles)
                ->first();

            $periodePrev12 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('idVille', $idVilles)
                ->first();

            $periodeNext3 = DB::table('v_union_projets')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "next_3_month")
                ->whereIn('idVille', $idVilles)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_union_projets')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->whereIn('idVille', $idVilles)
                ->first();

            $periodeNext12 = DB::table('v_union_projets')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->whereIn('idVille', $idVilles)
                ->first();

            $modules = DB::table('v_union_projets')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('idVille', $idVilles)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $financements = DB::table('v_union_projets')
                ->select('idPaiement', 'paiement', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where('idEtp', Customer::idCustomer())
                ->whereIn('project_status', $idStatus)
                ->orderBy('paiement', 'asc')
                ->groupBy('idPaiement', 'paiement')
                ->get();

            $projectDates = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate', 'headMonthDebut')
                ->orderBy('dateDebut', 'asc')
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('idVille', $idVilles)
                ->get();
        }
        /** AXIAN Financements.. */
        if ($idFinancements[0] != null) {

            $status = DB::table('v_union_projets')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })

                ->whereIn('idPaiement', $idFinancements)
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();

            $etps = DB::table('v_union_projets')
                ->select('idEtp', 'etp_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('idPaiement', $idFinancements)
                ->groupBy('idEtp', 'etp_name')
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_union_projets')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('idPaiement', $idFinancements)
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $periodePrev3 = DB::table('v_union_projets')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "prev_3_month")
                ->whereIn('idPaiement', $idFinancements)
                ->groupBy('p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->whereIn('idPaiement', $idFinancements)
                ->first();

            $periodePrev12 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->whereIn('idPaiement', $idFinancements)
                ->first();

            $periodeNext3 = DB::table('v_union_projets')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "next_3_month")
                ->whereIn('idPaiement', $idFinancements)
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_union_projets')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->whereIn('idPaiement', $idFinancements)
                ->first();

            $periodeNext12 = DB::table('v_union_projets')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->whereIn('idPaiement', $idFinancements)
                ->first();

            $modules = DB::table('v_union_projets')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('idPaiement', $idFinancements)
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_union_projets')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('idPaiement', $idFinancements)
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            $projectDates = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate', 'headMonthDebut')
                ->orderBy('dateDebut', 'asc')
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('idPaiement', $idFinancements)
                ->get();
        }
        /*******************************************FIN ETP MERE ************************************************************************* */


        if ($idStatus[0] == null && $idEtps[0] == null && $idTypes[0] == null && $idPeriodes == null && $idModules[0] == null && $idVilles[0] == null && $idFinancements[0] == null) {
            $status = DB::table('v_union_projets')
                ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->groupBy('project_status')
                ->orderBy('project_status', 'asc')
                ->get();

            $etps = DB::table('v_union_projets')
                ->select('idEtp', 'etp_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->groupBy('idEtp', 'etp_name')
                ->orderBy('etp_name', 'asc')
                ->get();

            $types = DB::table('v_union_projets')
                ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->orderBy('project_type', 'asc')
                ->groupBy('project_type')
                ->get();

            $periodePrev3 = DB::table('v_union_projets')
                ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "prev_3_month")
                ->groupBy('idProjet', 'p_id_periode')
                ->first();

            $periodePrev6 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
                ->first();

            $periodePrev12 = DB::table('v_union_projets')
                ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
                ->first();

            $periodeNext3 = DB::table('v_union_projets')
                ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->where('p_id_periode', "next_3_month")
                ->groupBy('p_id_periode')
                ->first();

            $periodeNext6 = DB::table('v_union_projets')
                ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
                ->first();

            $periodeNext12 = DB::table('v_union_projets')
                ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
                ->first();

            $modules = DB::table('v_union_projets')
                ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->orderBy('module_name', 'asc')
                ->groupBy('idModule', 'module_name')
                ->get();

            $villes = DB::table('v_union_projets')
                ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->orderBy('ville', 'asc')
                ->groupBy('idVille', 'ville')
                ->get();

            $projectDates = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate', 'headMonthDebut')
                ->orderBy('dateDebut', 'asc')
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->get();
        }

        $projects = $query->get();

        //dd($projects);

        $projets = [];
        foreach ($projects as $project) {
            $projets[] = [
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'formateurs' => ($project->idTypeprojet == 3) ? $this->getFormInterneProject($project->idProjet) : $this->getFormIntraProject($project->idProjet),
                'apprCount' => $this->getApprenantProject($project->idProjet),
                'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'idProjet' => $project->idProjet,
                'idCfp' => $project->idCfp_intra,
                'etp_name' => ($project->idTypeprojet == 2) ? $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter) : $project->etp_name,
                'logoCfp' => ($project->idTypeprojet == 1) ? $this->getLogoCfpIntra($project->idCfp_intra) : null,
                'nameCfp' => ($project->idTypeprojet == 1) ? $this->getNameCfpIntra($project->idCfp_intra) : null,
                'initialnameCfp' => ($project->idTypeprojet == 1) ? $this->getInitialNameCfp($project->idCfp_intra) : null,
                'logoCfpInter' => ($project->idTypeprojet == 2) ? $this->getLogoCfpIntra($project->idCfp_inter) : null,
                'initialnameCfpInter' => ($project->idTypeprojet == 2) ?  $this->getInitialNameCfp($project->idCfp_inter) : null,

                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                // 'paiement' => ($project->idTypeprojet == 1 && $project->idTypeprojet == 2) ? $project->paiement : null,
                'paiement' => ($project->idTypeprojet == 1 || $project->idTypeprojet == 2 || $project->paiement === null) ? $project->paiement : '--',
                'modalite' => $project->modalite,
                'project_description' => $project->project_description,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'etp_initial_name' => $project->etp_initial_name,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'ville' => $project->ville,
                'etp_name_in_situ' => $project->etp_name,

                'total_ht' => $this->utilService->formatPrice(($project->total_ht_etp != 0) ? $project->total_ht_etp : $project->total_ht),
                'total_ttc' => $project->total_ttc,
                'idModule' => $project->idModule,
                'restaurations' => $this->getRestauration($project->idProjet),
                'apprs' => $this->getApprListProjet($project->idProjet),

                'headYear' => $project->headYear,
                'headMonthDebut' => $project->headMonthDebut,
                'headMonthFin' => $project->headMonthFin,
                'headDayDebut' => $project->headDayDebut,
                'headDayFin' => $project->headDayFin
            ];
        }

        //dd($projets);

        if ($etp_is_grouped && $idStatus[0] != null) {
            //dd($modules);
            return response()->json([
                'projets' => $projets,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'financements' => $financements,
                'projectDates' => $projectDates
            ]);
        } else if ($etp_is_grouped && $idTypes[0] != null) {
            return response()->json([
                'projets' => $projets,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'financements' => $financements,
                'projectDates' => $projectDates
            ]);
        }

        if ($idStatus[0] != null) {
            return response()->json([
                'projets' => $projets,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'financements' => $financements,
                'projectDates' => $projectDates
            ]);
        } elseif ($idEtps[0] != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'financements' => $financements,
                'projectDates' => $projectDates
            ]);
        } elseif ($idTypes[0] != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'financements' => $financements,
                'projectDates' => $projectDates
            ]);
        } elseif ($idPeriodes != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'types' => $types,
                'modules' => $modules,
                'villes' => $villes,
                'financements' => $financements,
                'projectDates' => $projectDates
            ]);
        } elseif ($idModules[0] != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'villes' => $villes,
                'financements' => $financements,
                'projectDates' => $projectDates
            ]);
        } elseif ($idVilles[0] != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'financements' => $financements,
                'projectDates' => $projectDates
            ]);
        } elseif ($idFinancements[0] != null) {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'projectDates' => $projectDates
            ]);
        } else {
            return response()->json([
                'projets' => $projets,
                'status' => $status,
                'etps' => $etps,
                'types' => $types,
                'periodePrev3' => $periodePrev3,
                'periodePrev6' => $periodePrev6,
                'periodePrev12' => $periodePrev12,
                'periodeNext3' => $periodeNext3,
                'periodeNext6' => $periodeNext6,
                'periodeNext12' => $periodeNext12,
                'modules' => $modules,
                'villes' => $villes,
                'projectDates' => $projectDates
            ]);
        }
    }
    
    public function filterItem(Request $req)
    {
        $idStatus = explode(',', $req->idStatut);
        $idEtps = explode(',', $req->idEtp);
        $idTypes = explode(',', $req->idType);
        $idPeriodes = $req->idPeriode;
        $idModules = explode(',', $req->idModule);
        $idVilles = explode(',', $req->idVille);
        $idFinancements = explode(',', $req->idFinancement);
        $searchTerm = $req->search;

        $etp_id = Customer::idCustomer();

        $etp_is_grouped = DB::table('etp_groupeds')->where('idEntreprise', $etp_id)->exists();

        $id_projets = $this->list_id_project_etpgrouped($etp_id);

        $queryDate = null;

        if ($etp_is_grouped) {

            $projects = $this->getUnionProjectStatus($id_projets, $idStatus)->get();

            //dd($projects);  // <== MArina

            $query = $this->getProjets($projects);

            //dd($query);

            $queryDates = DB::table('v_union_projets')
                ->select('headDate')
                ->groupBy('headDate')
                ->orderBy('dateDebut', 'asc')
                ->where('project_status', $idStatus)
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->whereIn('idProjet', $id_projets)

                ->get();

            $projetCount = count($query);
        } else {
            /**ex:AXAN**/
            $query = DB::table('v_union_projets')
                ->select('idProjet', 'idTypeprojet', 'idCfp_intra', 'dateDebut', 'dateFin', 'module_name', 'etp_name', 'ville', 'project_status', 'project_description', 'project_type', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'headMonthDebut', 'headMonthFin', 'headYear', 'headDayDebut', 'headDayFin', 'module_image', 'etp_logo', 'etp_initial_name', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'paiement', 'total_ht', 'total_ttc', 'idModule', 'total_ht_etp')
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->orderBy('dateDebut', 'asc');

                  // Initialiser $queryDate pour le cas non groupé
                    $queryDate = DB::table('v_union_projets')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate', 'headMonthDebut')
                        ->orderBy('dateDebut', 'asc')
                        ->where(function ($query) {
                            $query->where('idEtp', Customer::idCustomer())
                                ->orWhereIn(
                                    'idCfp_inter',
                                    $this->getIdEtpCfps()
                                );
                        });
        }

        /**CLIC BOUTONS STATUS...(ETP Grouped ex:TELMA) */

        $searchTerm = trim($searchTerm);
        if ($searchTerm !== '') {
            $like = '%' . mb_strtolower($searchTerm, 'UTF-8') . '%';

            $query->where(function($q) use ($like) {
                $q->whereRaw('LOWER(project_reference) LIKE ?', [$like])
                ->orWhereRaw('LOWER(module_name) LIKE ?', [$like]);
            });

              // Vérifier si $queryDate existe avant de l'utiliser
            if ($queryDate) {
                $queryDate->where(function($q) use ($like) {
                    $q->whereRaw('LOWER(project_reference) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(module_name) LIKE ?', [$like]);
                });
            }
        }

        if ($idStatus[0] != null) {

            if ($etp_is_grouped) {

                //dd('VIDE Etp Fille...');


                $projects = $this->getUnionProjectStatus($id_projets, $idStatus)->get();

                $projets = $this->getProjets($projects);

                $projectDates = $this->getUnionProjectDateStatus($idStatus, $id_projets);

                $projetCount = count($projects);
            } else {
                //dd('Ex:ETP AXIAN!!!');

                $query->whereIn('project_status', $idStatus);
                $queryDate = DB::table('v_union_projets')
                    ->select('headDate', 'headMonthDebut')
                    ->where(function ($query) {
                        $query->where('idEtp', Customer::idCustomer())
                            ->orWhereIn(
                                'idCfp_inter',
                                $this->getIdEtpCfps()
                            );
                    })

                    // ->where('headYear', Carbon::now()->format('Y'))
                    ->where('project_status', '=', $idStatus)
                    ->groupBy('headDate', 'headMonthDebut')
                    ->orderBy('dateDebut', 'asc');


                if ($idEtps[0] != null) {
                    $query->whereIn('idEtp', $idEtps);
                    $queryDate->whereIn('idEtp', $idEtps);
                }

                if ($idTypes[0] != null) {
                    $query->whereIn('project_type', $idTypes);
                    $queryDate->whereIn('project_type', $idTypes);
                }

                if ($idPeriodes != null) {
                    switch ($idPeriodes) {
                        case 'prev_3_month':
                            $query->where('p_id_periode', $idPeriodes);
                            $queryDate->where('p_id_periode', $idPeriodes);

                            break;
                        case 'prev_6_month':
                            $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                            $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                            break;
                        case 'prev_12_month':
                            $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                            $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                            break;
                        case 'next_3_month':
                            $query->where('p_id_periode', $idPeriodes);
                            $queryDate->where('p_id_periode', $idPeriodes);

                            break;
                        case 'next_6_month':
                            $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                            $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                            break;
                        case 'next_12_month':
                            $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                            $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                            break;

                        default:
                            $query->where('p_id_periode', $idPeriodes);

                            $queryDate = DB::table('v_union_projets')
                                ->select('headDate', 'headMonthDebut')
                                ->groupBy('headDate', 'headMonthDebut')
                                ->orderBy('dateDebut', 'asc')
                                ->where(function ($query) {
                                    $query->where('idEtp', Customer::idCustomer())
                                        ->orWhereIn(
                                            'idCfp_inter',
                                            $this->getIdEtpCfps()
                                        );
                                })
                                // ->where('headYear', Carbon::now()->format('Y'))
                                ->where('p_id_periode', $idPeriodes);
                            break;
                    }
                }

                if ($idModules[0] != null) {
                    $query->whereIn('idModule', $idModules);
                    $queryDate->whereIn('idModule', $idModules);
                }

                if ($idVilles[0] != null) {
                    $query->whereIn('idVille', $idVilles);
                    $queryDate->whereIn('idVille', $idVilles);
                }

                if ($idFinancements[0] != null) {
                    $query->whereIn('idPaiement', $idFinancements);
                    $queryDate->whereIn('idPaiement', $idFinancements);
                }
            }
        }

        /**FIN (ETP Grouped ex:TELMA) */

        if ($idEtps[0] != null) {
            $query->whereIn('idEtp', $idEtps);

            $queryDate = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate', 'headMonthDebut')
                ->orderBy('dateDebut', 'asc')
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhereIn(
                            'idCfp_inter',
                            $this->getIdEtpCfps()
                        );
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('idEtp', $idEtps);

            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
                $queryDate->whereIn('project_status', $idStatus);
            }

            if ($idTypes[0] != null) {
                $query->whereIn('project_type', $idTypes);
                $queryDate->whereIn('project_type', $idTypes);
            }

            if ($idPeriodes != null) {
                switch ($idPeriodes) {
                    case 'prev_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'prev_6_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                        break;
                    case 'prev_12_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                        break;
                    case 'next_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'next_6_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        break;
                    case 'next_12_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                        break;

                    default:
                        $query->where('p_id_periode', $idPeriodes);

                        $queryDate = DB::table('v_union_projets')
                            ->select('headDate', 'headMonthDebut')
                            ->groupBy('headDate', 'headMonthDebut')
                            ->orderBy('dateDebut', 'asc')
                            ->where(function ($query) {
                                $query->where('idEtp', Customer::idCustomer())
                                    ->orWhereIn(
                                        'idCfp_inter',
                                        $this->getIdEtpCfps()
                                    );
                            })
                            // ->where('headYear', Carbon::now()->format('Y'))
                            ->where('p_id_periode', $idPeriodes);
                        break;
                }
            }

            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
                $queryDate->whereIn('idModule', $idModules);
            }

            if ($idVilles[0] != null) {
                $query->whereIn('idVille', $idVilles);
                $queryDate->whereIn('idVille', $idVilles);
            }

            if ($idFinancements[0] != null) {
                $query->whereIn('idPaiement', $idFinancements);
                $queryDate->whereIn('idPaiement', $idFinancements);
            }
        }

        if ($idTypes[0] != null) {

            if ($etp_is_grouped) {
                //dd("ATO...");

                $query = $this->getUnionProjectsTypes($id_projets, $idTypes);

                $queryDate = DB::table('v_union_projets')
                    ->select('headDate', 'headMonthDebut')
                    ->groupBy('headDate', 'headMonthDebut')
                    ->orderBy('dateDebut', 'asc')

                    ->where(function ($query) {
                        $query->where('project_type', 'Interne')
                            ->orWhere(function ($query) {
                                $query->whereIn('project_type', ['Intra', 'Inter'])
                                    ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                            });
                    })
                    ->whereIn('idProjet', $id_projets)
                    // ->where('headYear', Carbon::now()->format('Y'))
                    ->where('project_is_trashed', 0)
                    ->whereIn('project_type', $idTypes);

                if ($idStatus[0] != null) {
                    $query->whereIn('project_status', $idStatus);
                    $queryDate->whereIn('project_status', $idStatus);
                }

                if ($idEtps[0] != null) {
                    $query->whereIn('idEtp', $idEtps);
                    $queryDate->whereIn('idEtp', $idEtps);
                }

                if ($idPeriodes != null) {
                    switch ($idPeriodes) {
                        case 'prev_3_month':
                            $query->where('p_id_periode', $idPeriodes);
                            $queryDate->where('p_id_periode', $idPeriodes);

                            break;
                        case 'prev_6_month':
                            $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                            $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                            break;
                        case 'prev_12_month':
                            $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                            $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                            break;
                        case 'next_3_month':
                            $query->where('p_id_periode', $idPeriodes);
                            $queryDate->where('p_id_periode', $idPeriodes);

                            break;
                        case 'next_6_month':
                            $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                            $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                            break;
                        case 'next_12_month':
                            $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                            $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                            break;

                        default:
                            $query->where('p_id_periode', $idPeriodes);

                            $queryDate = DB::table('v_union_projets')
                                ->select('headDate', 'headMonthDebut')
                                ->groupBy('headDate', 'headMonthDebut')
                                ->orderBy('dateDebut', 'asc')
                                ->where(function ($query) {
                                    $query->where('idEtp', Customer::idCustomer())
                                        ->orWhereIn(
                                            'idCfp_inter',
                                            $this->getIdEtpCfps()
                                        );
                                })
                                // ->where('headYear', Carbon::now()->format('Y'))
                                ->where('p_id_periode', $idPeriodes);
                            break;
                    }
                }

                if ($idModules[0] != null) {
                    $query->whereIn('idModule', $idModules);
                    $queryDate->whereIn('idModule', $idModules);
                }

                if ($idVilles[0] != null) {
                    $query->whereIn('idVille', $idVilles);
                    $queryDate->whereIn('idVille', $idVilles);
                }

                if ($idFinancements[0] != null) {
                    $query->whereIn('idPaiement', $idFinancements);
                    $queryDate->whereIn('idPaiement', $idFinancements);
                }
            } else {
                //dd('Vrais!!!');
                $query->whereIn('project_type', $idTypes);

                $queryDate = DB::table('v_union_projets')
                    ->select('headDate', 'headMonthDebut')
                    ->groupBy('headDate', 'headMonthDebut')
                    ->orderBy('dateDebut', 'asc')
                    ->where(function ($query) {
                        $query->where('idEtp', Customer::idCustomer())
                            ->orWhereIn(
                                'idCfp_inter',
                                $this->getIdEtpCfps()
                            );
                    })
                    // ->where('headYear', Carbon::now()->format('Y'))
                    ->whereIn('project_type', $idTypes);

                if ($idStatus[0] != null) {
                    $query->whereIn('project_status', $idStatus);
                    $queryDate->whereIn('project_status', $idStatus);
                }

                if ($idEtps[0] != null) {
                    $query->whereIn('idEtp', $idEtps);
                    $queryDate->whereIn('idEtp', $idEtps);
                }

                if ($idPeriodes != null) {
                    switch ($idPeriodes) {
                        case 'prev_3_month':
                            $query->where('p_id_periode', $idPeriodes);
                            $queryDate->where('p_id_periode', $idPeriodes);

                            break;
                        case 'prev_6_month':
                            $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                            $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                            break;
                        case 'prev_12_month':
                            $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                            $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                            break;
                        case 'next_3_month':
                            $query->where('p_id_periode', $idPeriodes);
                            $queryDate->where('p_id_periode', $idPeriodes);

                            break;
                        case 'next_6_month':
                            $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                            $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                            break;
                        case 'next_12_month':
                            $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                            $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                            break;

                        default:
                            $query->where('p_id_periode', $idPeriodes);

                            $queryDate = DB::table('v_union_projets')
                                ->select('headDate', 'headMonthDebut')
                                ->groupBy('headDate', 'headMonthDebut')
                                ->orderBy('dateDebut', 'asc')
                                ->where(function ($query) {
                                    $query->where('idEtp', Customer::idCustomer())
                                        ->orWhereIn(
                                            'idCfp_inter',
                                            $this->getIdEtpCfps()
                                        );
                                })
                                // ->where('headYear', Carbon::now()->format('Y'))
                                ->where('p_id_periode', $idPeriodes);
                            break;
                    }
                }

                if ($idModules[0] != null) {
                    $query->whereIn('idModule', $idModules);
                    $queryDate->whereIn('idModule', $idModules);
                }

                if ($idVilles[0] != null) {
                    $query->whereIn('idVille', $idVilles);
                    $queryDate->whereIn('idVille', $idVilles);
                }

                if ($idFinancements[0] != null) {
                    $query->whereIn('idPaiement', $idFinancements);
                    $queryDate->whereIn('idPaiement', $idFinancements);
                }
            }
        }

        if ($idPeriodes != null) {
            switch ($idPeriodes) {
                case 'prev_3_month':
                    $query->where('p_id_periode', $idPeriodes);

                    $queryDate = DB::table('v_union_projets')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate', 'headMonthDebut')
                        ->orderBy('dateDebut', 'asc')
                        ->where(function ($query) {
                            $query->where('idEtp', Customer::idCustomer())
                                ->orWhereIn(
                                    'idCfp_inter',
                                    $this->getIdEtpCfps()
                                );
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('p_id_periode', $idPeriodes);

                    break;
                case 'prev_6_month':
                    $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                    $queryDate = DB::table('v_union_projets')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate', 'headMonthDebut')
                        ->orderBy('dateDebut', 'asc')

                        ->where(function ($query) {
                            $query->where('idEtp', Customer::idCustomer())
                                ->orWhereIn(
                                    'idCfp_inter',
                                    $this->getIdEtpCfps()
                                );
                        })
                        ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                    break;
                case 'prev_12_month':
                    $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                    $queryDate = DB::table('v_union_projets')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate', 'headMonthDebut')
                        ->orderBy('dateDebut', 'asc')
                        ->where(function ($query) {
                            $query->where('idEtp', Customer::idCustomer())
                                ->orWhereIn(
                                    'idCfp_inter',
                                    $this->getIdEtpCfps()
                                );
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                    break;
                case 'next_3_month':
                    $query->where('p_id_periode', $idPeriodes);

                    $queryDate = DB::table('v_union_projets')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate', 'headMonthDebut')
                        ->orderBy('dateDebut', 'asc')
                        ->where(function ($query) {
                            $query->where('idEtp', Customer::idCustomer())
                                ->orWhereIn(
                                    'idCfp_inter',
                                    $this->getIdEtpCfps()
                                );
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('p_id_periode', $idPeriodes);

                    break;
                case 'next_6_month':
                    $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);

                    $queryDate = DB::table('v_union_projets')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate', 'headMonthDebut')
                        ->orderBy('dateDebut', 'asc')
                        ->where(function ($query) {
                            $query->where('idEtp', Customer::idCustomer())
                                ->orWhereIn(
                                    'idCfp_inter',
                                    $this->getIdEtpCfps()
                                );
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                    break;
                case 'next_12_month':
                    $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                    $queryDate = DB::table('v_union_projets')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate', 'headMonthDebut')
                        ->orderBy('dateDebut', 'asc')
                        ->where(function ($query) {
                            $query->where('idEtp', Customer::idCustomer())
                                ->orWhereIn(
                                    'idCfp_inter',
                                    $this->getIdEtpCfps()
                                );
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                    break;

                default:
                    $query->where('p_id_periode', $idPeriodes);

                    $queryDate = DB::table('v_union_projets')
                        ->select('headDate', 'headMonthDebut')
                        ->groupBy('headDate', 'headMonthDebut')
                        ->orderBy('dateDebut', 'asc')
                        ->where(function ($query) {
                            $query->where('idEtp', Customer::idCustomer())
                                ->orWhereIn(
                                    'idCfp_inter',
                                    $this->getIdEtpCfps()
                                );
                        })
                        // ->where('headYear', Carbon::now()->format('Y'))
                        ->where('p_id_periode', $idPeriodes);
                    break;
            }

            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
                $queryDate->whereIn('project_status', $idStatus);
            }

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
                $queryDate->whereIn('idEtp', $idEtps);
            }

            if ($idTypes[0] != null) {
                $query->whereIn('project_type', $idTypes);
                $queryDate->whereIn('project_type', $idTypes);
            }

            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
                $queryDate->whereIn('idModule', $idModules);
            }

            if ($idVilles[0] != null) {
                $query->whereIn('idVille', $idVilles);
                $queryDate->whereIn('idVille', $idVilles);
            }

            if ($idFinancements[0] != null) {
                $query->whereIn('idPaiement', $idFinancements);
                $queryDate->whereIn('idPaiement', $idFinancements);
            }
        }

        if ($idModules[0] != null) {

            if ($etp_is_grouped) {
                //dd($idModules);
                $id_projets = $this->list_id_project_etpgrouped($etp_id);

                $query = $this->getUnionProjectsModules($id_projets, $idModules);

                $queryDate = DB::table('v_union_projets')
                    ->select('headDate', 'headMonthDebut')
                    ->groupBy('headDate', 'headMonthDebut')
                    ->orderBy('dateDebut', 'asc')

                    ->where(function ($query) {
                        $query->where('project_type', 'Interne')
                            ->orWhere(function ($query) {
                                $query->whereIn('project_type', ['Intra', 'Inter'])
                                    ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                            });
                    })
                    ->whereIn('idProjet', $id_projets)
                    // ->where('headYear', Carbon::now()->format('Y'))
                    ->where('project_is_trashed', 0)
                    ->whereIn('idModule', $idModules);

                if ($idStatus[0] != null) {
                    $query->whereIn('project_status', $idStatus);
                    $queryDate->whereIn('project_status', $idStatus);
                }

                if ($idEtps[0] != null) {
                    $query->whereIn('idEtp', $idEtps);
                    $queryDate->whereIn('idEtp', $idEtps);
                }

                if ($idPeriodes != null) {
                    switch ($idPeriodes) {
                        case 'prev_3_month':
                            $query->where('p_id_periode', $idPeriodes);
                            $queryDate->where('p_id_periode', $idPeriodes);

                            break;
                        case 'prev_6_month':
                            $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                            $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                            break;
                        case 'prev_12_month':
                            $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                            $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                            break;
                        case 'next_3_month':
                            $query->where('p_id_periode', $idPeriodes);
                            $queryDate->where('p_id_periode', $idPeriodes);

                            break;
                        case 'next_6_month':
                            $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                            $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                            break;
                        case 'next_12_month':
                            $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                            $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                            break;

                        default:
                            $query->where('p_id_periode', $idPeriodes);

                            $queryDate = DB::table('v_union_projets')
                                ->select('headDate', 'headMonthDebut')
                                ->groupBy('headDate', 'headMonthDebut')
                                ->orderBy('dateDebut', 'asc')
                                ->where(function ($query) {
                                    $query->where('idEtp', Customer::idCustomer())
                                        ->orWhereIn(
                                            'idCfp_inter',
                                            $this->getIdEtpCfps()
                                        );
                                })
                                // ->where('headYear', Carbon::now()->format('Y'))
                                ->where('p_id_periode', $idPeriodes);
                            break;
                    }
                }

                if ($idModules[0] != null) {
                    $query->whereIn('idModule', $idModules);
                    $queryDate->whereIn('idModule', $idModules);
                }

                if ($idVilles[0] != null) {
                    $query->whereIn('idVille', $idVilles);
                    $queryDate->whereIn('idVille', $idVilles);
                }

                if ($idFinancements[0] != null) {
                    $query->whereIn('idPaiement', $idFinancements);
                    $queryDate->whereIn('idPaiement', $idFinancements);
                }
                //dd($queryDate->get()); //Marina
            } else {

                $query->whereIn('idModule', $idModules);

                $queryDate = DB::table('v_union_projets')
                    ->select('headDate', 'headMonthDebut')
                    ->groupBy('headDate', 'headMonthDebut')
                    ->orderBy('dateDebut', 'asc')
                    ->where(function ($query) {
                        $query->where('idEtp', Customer::idCustomer())
                            ->orWhereIn(
                                'idCfp_inter',
                                $this->getIdEtpCfps()
                            );
                    })
                    // ->where('headYear', Carbon::now()->format('Y'))
                    ->whereIn('idModule', $idModules);

                if ($idStatus[0] != null) {
                    $query->whereIn('project_status', $idStatus);
                    $queryDate->whereIn('project_status', $idStatus);
                }

                if ($idEtps[0] != null) {
                    $query->whereIn('idEtp', $idEtps);
                    $queryDate->whereIn('idEtp', $idEtps);
                }

                if ($idTypes[0] != null) {
                    $query->whereIn('project_type', $idTypes);
                    $queryDate->whereIn('project_type', $idTypes);
                }

                if ($idPeriodes != null) {
                    switch ($idPeriodes) {
                        case 'prev_3_month':
                            $query->where('p_id_periode', $idPeriodes);
                            $queryDate->where('p_id_periode', $idPeriodes);

                            break;
                        case 'prev_6_month':
                            $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                            $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                            break;
                        case 'prev_12_month':
                            $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                            $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                            break;
                        case 'next_3_month':
                            $query->where('p_id_periode', $idPeriodes);
                            $queryDate->where('p_id_periode', $idPeriodes);

                            break;
                        case 'next_6_month':
                            $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                            $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                            break;
                        case 'next_12_month':
                            $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                            $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                            break;

                        default:
                            $query->where('p_id_periode', $idPeriodes);

                            $queryDate = DB::table('v_union_projets')
                                ->select('headDate', 'headMonthDebut')
                                ->groupBy('headDate', 'headMonthDebut')
                                ->orderBy('dateDebut', 'asc')
                                ->where(function ($query) {
                                    $query->where('idEtp', Customer::idCustomer())
                                        ->orWhereIn(
                                            'idCfp_inter',
                                            $this->getIdEtpCfps()
                                        );
                                })
                                // ->where('headYear', Carbon::now()->format('Y'))
                                ->where('p_id_periode', $idPeriodes);
                            break;
                    }
                }

                if ($idVilles[0] != null) {
                    $query->whereIn('idVille', $idVilles);
                    $queryDate->whereIn('idVille', $idVilles);
                }

                if ($idFinancements[0] != null) {
                    $query->whereIn('idPaiement', $idFinancements);
                    $queryDate->whereIn('idPaiement', $idFinancements);
                }
            }
        }

        if ($idVilles[0] != null) {
            $query->whereIn('idVille', $idVilles);

            $queryDate = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate', 'headMonthDebut')
                ->orderBy('dateDebut', 'asc')
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhereIn(
                            'idCfp_inter',
                            $this->getIdEtpCfps()
                        );
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('idVille', $idVilles);

            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
                $queryDate->whereIn('project_status', $idStatus);
            }

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
                $queryDate->whereIn('idEtp', $idEtps);
            }

            if ($idTypes[0] != null) {
                $query->whereIn('project_type', $idTypes);
                $queryDate->whereIn('project_type', $idTypes);
            }

            if ($idPeriodes != null) {
                switch ($idPeriodes) {
                    case 'prev_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'prev_6_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                        break;
                    case 'prev_12_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                        break;
                    case 'next_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'next_6_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        break;
                    case 'next_12_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                        break;

                    default:
                        $query->where('p_id_periode', $idPeriodes);

                        $queryDate = DB::table('v_union_projets')
                            ->select('headDate', 'headMonthDebut')
                            ->groupBy('headDate', 'headMonthDebut')
                            ->orderBy('dateDebut', 'asc')
                            ->where(function ($query) {
                                $query->where('idEtp', Customer::idCustomer())
                                    ->orWhereIn(
                                        'idCfp_inter',
                                        $this->getIdEtpCfps()
                                    );
                            })
                            // ->where('headYear', Carbon::now()->format('Y'))
                            ->where('p_id_periode', $idPeriodes);
                        break;
                }
            }

            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
                $queryDate->whereIn('idModule', $idModules);
            }

            if ($idFinancements[0] != null) {
                $query->whereIn('idPaiement', $idFinancements);
                $queryDate->whereIn('idPaiement', $idFinancements);
            }
        }

        if ($idFinancements[0] != null) {
            $query->whereIn('idPaiement', $idFinancements);

            $queryDate = DB::table('v_union_projets')
                ->select('headDate', 'headMonthDebut')
                ->groupBy('headDate', 'headMonthDebut')
                ->orderBy('dateDebut', 'asc')
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhereIn(
                            'idCfp_inter',
                            $this->getIdEtpCfps()
                        );
                })
                // ->where('headYear', Carbon::now()->format('Y'))
                ->whereIn('idPaiement', $idFinancements);

            if ($idStatus[0] != null) {
                $query->whereIn('project_status', $idStatus);
                $queryDate->whereIn('project_status', $idStatus);
            }

            if ($idEtps[0] != null) {
                $query->whereIn('idEtp', $idEtps);
                $queryDate->whereIn('idEtp', $idEtps);
            }

            if ($idTypes[0] != null) {
                $query->whereIn('project_type', $idTypes);
                $queryDate->whereIn('project_type', $idTypes);
            }

            if ($idPeriodes != null) {
                switch ($idPeriodes) {
                    case 'prev_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'prev_6_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                        break;
                    case 'prev_12_month':
                        $query->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                        break;
                    case 'next_3_month':
                        $query->where('p_id_periode', $idPeriodes);
                        $queryDate->where('p_id_periode', $idPeriodes);

                        break;
                    case 'next_6_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                        break;
                    case 'next_12_month':
                        $query->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                        $queryDate->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                        break;

                    default:
                        $query->where('p_id_periode', $idPeriodes);

                        $queryDate = DB::table('v_projet_intra_internes')
                            ->select('headDate', 'headMonthDebut')
                            ->groupBy('headDate', 'headMonthDebut')
                            ->orderBy('dateDebut', 'asc')
                            ->where(function ($query) {
                                $query->where('idEtp', Customer::idCustomer())
                                    ->orWhereIn(
                                        'idCfp_inter',
                                        $this->getIdEtpCfps()
                                    );
                            })
                            // ->where('headYear', Carbon::now()->format('Y'))
                            ->where('p_id_periode', $idPeriodes);
                        break;
                }
            }

            if ($idModules[0] != null) {
                $query->whereIn('idModule', $idModules);
                $queryDate->whereIn('idModule', $idModules);
            }

            if ($idVilles[0] != null) {
                $query->whereIn('idVille', $idVilles);
                $queryDate->whereIn('idVille', $idVilles);
            }
        }
        /** FILTRE CLIC COURS(Ex:AXIAN) **/




        if ($etp_is_grouped) {

            if ($idStatus[0] == null) {
                /***** Seulement lorsqu'on clique sur les boutons Status... *****/
                // dd("Clic Bouton!!!");
                $projects = $query->get();
                $projectDates = $queryDate->get();

                $projets = [];
                $projets = $this->getProjets($projects);

                $projetCount = count($projets);
                /***** FIN clique sur les boutons Status... *****/
            } else {

                $projets = $query;
                $projectDates = $queryDates;
                $projetCount = count($projets);
            }
        } else {
            // dd($projects); /** ex:AXIAN **/
            $projects = $query->get();
            $projectDates = $queryDate->get();

            $projets = [];
            foreach ($projects as $project) {
                $projets[] = [
                    'seanceCount' => $this->getSessionProject($project->idProjet),
                    'formateurs' => ($project->idTypeprojet == 3) ? $this->getFormInterneProject($project->idProjet) : $this->getFormIntraProject($project->idProjet),
                    'apprCount' => $this->getApprenantProject($project->idProjet),
                    'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                    'totalSessionHour' => $this->getSessionHour($project->idProjet),
                    'idProjet' => $project->idProjet,
                    'idCfp' => $project->idCfp_intra,
                    'etp_name' => ($project->idTypeprojet == 2) ? $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter) : $project->etp_name,
                    'logoCfp' => ($project->idTypeprojet == 1) ? $this->getLogoCfpIntra($project->idCfp_intra) : null,
                    'nameCfp' => ($project->idTypeprojet == 1) ? $this->getNameCfpIntra($project->idCfp_intra) : null,
                    'initialnameCfp' => ($project->idTypeprojet == 1) ? $this->getInitialNameCfp($project->idCfp_intra) : null,
                    'logoCfpInter' => ($project->idTypeprojet == 2) ? $this->getLogoCfpIntra($project->idCfp_inter) : null,
                    'initialnameCfpInter' => ($project->idTypeprojet == 2) ?  $this->getInitialNameCfp($project->idCfp_inter) : null,
                    'dateDebut' => $project->dateDebut,
                    'dateFin' => $project->dateFin,
                    'module_name' => $project->module_name,
                    'ville' => $project->ville,
                    'project_status' => $project->project_status,
                    'project_type' => $project->project_type,
                    // 'paiement' => ($project->idTypeprojet == 1 && $project->idTypeprojet == 2) ? $project->paiement : null,
                    'paiement' => ($project->idTypeprojet == 1 || $project->idTypeprojet == 2 || $project->paiement === null) ? $project->paiement : '--',
                    'modalite' => $project->modalite,
                    'project_description' => $project->project_description,
                    'headDate' => $project->headDate,
                    'module_image' => $project->module_image,
                    'etp_logo' => $project->etp_logo,
                    'etp_initial_name' => $project->etp_initial_name,
                    'salle_name' => $project->salle_name,
                    'salle_quartier' => $project->salle_quartier,
                    'salle_code_postal' => $project->salle_code_postal,
                    'ville' => $project->ville,

                    'etp_name_in_situ' => $project->etp_name,
                    'total_ht' => $this->utilService->formatPrice(($project->total_ht_etp != 0) ? $project->total_ht_etp : $project->total_ht),
                    'total_ttc' => $project->total_ttc,
                    'idModule' => $project->idModule,
                    'restaurations' => $this->getRestauration($project->idProjet),
                    'apprs' => $this->getApprListProjet($project->idProjet),

                    'headYear' => $project->headYear,
                    'headMonthDebut' => $project->headMonthDebut,
                    'headMonthFin' => $project->headMonthFin,
                    'headDayDebut' => $project->headDayDebut,
                    'headDayFin' => $project->headDayFin
                ];
            }

            $projetCount = DB::table('v_union_projets')
                ->where(function ($query) {
                    $query->where('idEtp', Customer::idCustomer())
                        ->orWhere('idEtp_inter', Customer::idCustomer());
                })
                ->where(function ($query) {
                    $query->where('project_type', 'Interne')
                        ->orWhere(function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        });
                })
                ->count();
        }

        return response()->json([
            'projets' => $projets,
            'projectDates' => $projectDates,
            'projetCount' => $projetCount
        ]);
    }

    public function getStatutProjet($idProjet)
    {
        $projects = DB::table('v_union_projets')
            ->select('idTypeprojet', 'project_type')
            ->where('idProjet', $idProjet)
            ->groupBy('idTypeprojet', 'project_type')
            ->get();

        return response()->json([
            'status' => 200,
            'projects' => $projects
        ]);
    }

    public function showmomentum($idProjet, Request $request)
    {
        $images = DB::table('images')
            ->select('idProjet', 'idImages', 'url', 'nomImage')
            ->where('idProjet', $idProjet)
            ->where('idTypeImage', 1);

        if(count($images->get()) <= 0){
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }else{    
            return response()->json([
                'status' => 200,
                'images' => $images->paginate(8),
                'idProjet' => $request->idProjet
            ]);
        }
        
    }







    private function getFormProject($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
            ->groupBy('idFormateur', 'name', 'firstName', 'photoForm', 'initialNameForm')
            ->where('idProjet', $idProjet)->get();

        return $forms->toArray();
    }

     public function getNote($idProjet)
    {
        $checkEvaluation = DB::table('eval_chauds')->select('idProjet')->get();
        $checkEvaluationCount = count($checkEvaluation);

        if ($checkEvaluationCount > 0) {
            $notationProjet = DB::table('v_evaluation_alls')
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

    private function getUnionProject()
    {
        $projects = DB::table('v_union_projets')
            ->select(
                'v_union_projets.idProjet',
                'idTypeprojet',
                'idEtp_inter',
                'idCfp_intra',
                'idCfp_inter',
                'paiement',
                'dateDebut',
                'dateFin',
                'module_name',
                'etp_name',
                'ville',
                'project_status',
                'project_description',
                'project_type',
                DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'),
                'module_image',
                'etp_logo',
                'etp_initial_name',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'modalite',
                'total_ht',
                'total_ttc',
                'total_ht_etp',
                'idModule'
            )

            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('project_is_trashed', 0)

            ->groupBy('idProjet')

            ->orderBy('dateDebut', 'asc');

        return $projects;
    }
    private function getUnionProjects($id_projets)
    {
        $projects = DB::table('v_union_projets')
            ->select(
                'idProjet',
                'idTypeprojet',
                'idEtp_inter',
                'idCfp_intra',
                'idCfp_inter',
                'paiement',
                'dateDebut',
                'dateFin',
                'module_name',
                'etp_name',
                'ville',
                'project_status',
                'project_description',
                'project_type',
                DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'),
                'headMonthDebut',
                'headMonthFin',
                'headYear',
                'headDayDebut',
                'headDayFin',
                'module_image',
                'etp_logo',
                'etp_initial_name',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'modalite',
                'total_ht',
                'total_ttc',
                'total_ht_etp',
                'idModule'
            )


            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })

            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->orWhereIn('idProjet', $id_projets)
            // ->where('headYear', Carbon::now()->format('Y'))

            ->where('project_is_trashed', 0)
            ->groupBy('idProjet')
            ->orderBy('dateDebut', 'asc');

        return $projects;
    }
    private function getUnionProjectsFilter($id_projets)
    {
        $projects = DB::table('v_union_projets')
            ->select(
                'idProjet',
                'idTypeprojet',
                'idEtp_inter',
                'idCfp_intra',
                'idCfp_inter',
                'paiement',
                'dateDebut',
                'dateFin',
                'module_name',
                'etp_name',
                'ville',
                'project_status',
                'project_description',
                'project_type',
                DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'),
                'headMonthDebut',
                'headMonthFin',
                'headYear',
                'headDayDebut',
                'headDayFin',
                'module_image',
                'etp_logo',
                'etp_initial_name',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'modalite',
                'total_ht',
                'total_ttc',
                'total_ht_etp',
                'idModule'
            )


            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })

            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->whereIn('idProjet', $id_projets)
            // ->where('headYear', Carbon::now()->format('Y'))

            ->where('project_is_trashed', 0)
            ->groupBy('idProjet')
            ->orderBy('dateDebut', 'asc');

        return $projects;
    }
    private function getUnionProjectsTypes($id_projets, $idTypes)
    {
        $projects = DB::table('v_union_projets')
            ->select(
                'idProjet',
                'idTypeprojet',
                'idEtp_inter',
                'idCfp_intra',
                'idCfp_inter',
                'paiement',
                'dateDebut',
                'dateFin',
                'module_name',
                'etp_name',
                'ville',
                'project_status',
                'project_description',
                'project_type',
                DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'),
                'headMonthDebut',
                'headMonthFin',
                'headYear',
                'headDayDebut',
                'headDayFin',
                'module_image',
                'etp_logo',
                'etp_initial_name',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'modalite',
                'total_ht',
                'total_ttc',
                'total_ht_etp',
                'idModule'
            )

            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->whereIn('idProjet', $id_projets)
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('project_is_trashed', 0)
            ->whereIn('project_type', $idTypes)
            ->groupBy('idProjet')
            ->orderBy('dateDebut', 'asc');

        return $projects;
    }

    private function getUnionProjectsModules($id_projets, $idModules)
    {
        $projects = DB::table('v_union_projets')
            ->select(
                'idProjet',
                'idTypeprojet',
                'idEtp_inter',
                'idCfp_intra',
                'idCfp_inter',
                'paiement',
                'dateDebut',
                'dateFin',
                'module_name',
                'etp_name',
                'ville',
                'project_status',
                'project_description',
                'project_type',
                DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'),
                'headMonthDebut',
                'headMonthFin',
                'headYear',
                'headDayDebut',
                'headDayFin',
                'module_image',
                'etp_logo',
                'etp_initial_name',
                'salle_name',
                'salle_quartier',
                'salle_code_postal',
                'modalite',
                'total_ht',
                'total_ttc',
                'total_ht_etp',
                'idModule'
            )

            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->whereIn('idProjet', $id_projets)
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('project_is_trashed', 0)
            ->whereIn('idModule', $idModules)
            ->groupBy('idProjet')
            ->orderBy('dateDebut', 'asc');

        return $projects;
    }


    private function getUnionProjectTypes($id_projets)
    {
        $types = DB::table('v_union_projets')
            ->select('project_type', DB::raw('COUNT(DISTINCT idProjet)  AS projet_nb'))
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->orWhereIn('idProjet', $id_projets)
            // ->where('headYear', Carbon::now()->format('Y'))
            ->orderBy('project_type', 'asc')
            ->groupBy('project_type')
            ->get();

        return $types;
    }

    private function getUnionProjectPeriodePrev3($id_projets)
    {
        $periodePrev3 = DB::table('v_union_projets')
            ->select('idProjet', 'p_id_periode', DB::raw('COUNT(DISTINCT idProjet)  AS projet_nb'))
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer());
            })
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->orWhereIn('idProjet', $id_projets)
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('p_id_periode', "prev_3_month")
            ->groupBy('p_id_periode')
            ->first();
        return $periodePrev3;
    }

    private function getUnionProjectPeriodePrev6($id_projets)
    {
        $periodePrev6 = DB::table('v_union_projets')
            ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(DISTINCT idProjet)  AS projet_nb'))
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->orWhereIn('idProjet', $id_projets)
            // ->where('headYear', Carbon::now()->format('Y'))
            ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
            ->first();
        return $periodePrev6;
    }

    private function getUnionProjectPeriodePrev12($id_projets)
    {
        $periodePrev12 = DB::table('v_union_projets')
            ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(DISTINCT idProjet)  AS projet_nb'))
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->orWhereIn('idProjet', $id_projets)
            // ->where('headYear', Carbon::now()->format('Y'))
            ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
            ->first();
        return $periodePrev12;
    }

    private function getUnionProjectPeriodeNext3($id_projets)
    {
        $periodeNext3 = DB::table('v_union_projets')
            ->select('p_id_periode', DB::raw('COUNT(DISTINCT idProjet)  AS projet_nb'))
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->orWhereIn('idProjet', $id_projets)
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('p_id_periode', "next_3_month")
            ->groupBy('p_id_periode')
            ->first();
        return $periodeNext3;
    }

    private function getUnionPeriodeNext6($id_projets)
    {
        $periodeNext6 = DB::table('v_union_projets')
            ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(DISTINCT idProjet)  AS projet_nb'))
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->orWhereIn('idProjet', $id_projets)
            // ->where('headYear', Carbon::now()->format('Y'))
            ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
            ->first();
        return $periodeNext6;
    }

    private function getUnionProjectPeriodeNext12($id_projets)
    {
        $periodeNext12 = DB::table('v_union_projets')
            ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(DISTINCT idProjet)  AS projet_nb'))
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->orWhereIn('idProjet', $id_projets)
            // ->where('headYear', Carbon::now()->format('Y'))
            ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
            ->first();
        return $periodeNext12;
    }

    private function getUnionProjectModules($id_projets)
    {
        $modules = DB::table('v_union_projets')
            ->select('idModule', 'module_name', DB::raw('COUNT(DISTINCT idProjet)  AS projet_nb'))
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->orWhereIn('idProjet', $id_projets)
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('module_name', '!=', 'Default module')
            ->orderBy('module_name', 'asc')
            ->groupBy('idModule', 'module_name')
            ->get();
        return $modules;
    }

    private function getUnionProjectVilles($id_projets)
    {
        $villes = DB::table('v_union_projets')
            ->select('idVille', 'ville', DB::raw('COUNT(DISTINCT idProjet)  AS projet_nb'))
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->orWhereIn('idProjet', $id_projets)
            // ->where('headYear', Carbon::now()->format('Y'))
            ->orderBy('ville', 'asc')
            ->groupBy('idVille', 'ville')
            ->get();
        return $villes;
    }

    private function getUnionProjectDateStatus($idStatus, $id_projets)
    {
        $dates = DB::table('v_union_projets')
            ->select('headDate')
            ->groupBy('headDate')
            ->orderBy('dateDebut', 'asc')
            ->where('project_status', $idStatus)
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->whereIn('idProjet', $id_projets)

            ->get();
        return $dates;
    }

    /**liste des projets en fonction du statut et projets existants de l'ETP Mère **/
    private function getUnionProjectStatus($id_projets, $idStatus)
    {
        $projects = DB::table('v_union_projets')
            ->select('idProjet', 'idTypeprojet', 'idEtp_inter', 'idCfp_intra', 'idCfp_inter', 'paiement', 'dateDebut', 'dateFin', 'module_name', 'etp_name', 'ville', 'project_status', 'project_description', 'project_type', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'salle_name', 'salle_quartier', 'salle_code_postal', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'total_ht_etp')

            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })
            ->whereIn('idProjet', $id_projets)
            // ->where('headYear', Carbon::now()->format('Y'))
            ->where('project_is_trashed', 0)
            ->where('project_status', $idStatus)
            ->groupBy('idProjet')
            ->orderBy('dateDebut', 'asc');

        return $projects;
    }

    // private function get
    
    public function getInvoiceProject($idProjet)
    {
       $invoice = DB::table('invoice_details')
            ->join('invoices', 'invoices.idInvoice', '=', 'invoice_details.idInvoice')
            ->join('invoice_status', 'invoice_status.idInvoiceStatus', '=', 'invoices.invoice_status')
            ->select('invoice_status.invoice_status_name') 
            ->where('invoice_details.idProjet', $idProjet)
            ->get();


       return $invoice;
    }

    public function getNomDossier($idProjet)
    {
        $dossier = DB::table('dossiers')
            ->select('dossiers.idDossier', 'nomDossier')
            ->join('projets', 'dossiers.idDossier', 'projets.idDossier')
            ->where('idProjet', $idProjet)
            ->get();

        return $dossier;
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

    private function getProjets($projects)
    {
        $projets = [];
        foreach ($projects as $project) {
            $projets[] = [
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'dossier' => $this->getNomDossier($project->idProjet),
                'invoice' => $this->getInvoiceProject($project->idProjet),
                'formateurs' => ($project->idTypeprojet == 3) ? $this->getFormInterneProject($project->idProjet) : $this->getFormIntraProject($project->idProjet),
                'apprCount' => $this->getApprenantProject($project->idProjet),
                'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'evaluation' => $this->getNote($project->idProjet),
                'idProjet' => $project->idProjet,
                'idCfp' => ($project->idTypeprojet == 1) ?  $project->idCfp_intra : $project->idCfp_inter,
                'etp_name' => ($project->idTypeprojet == 2) ? $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter) : $project->etp_name,
                'logoCfp' => ($project->idTypeprojet == 1) ? $this->getLogoCfpIntra($project->idCfp_intra) : null,
                'logoCfpInter' => ($project->idTypeprojet == 2) ? $this->getLogoCfpIntra($project->idCfp_inter) : null,
                'nameCfp' => ($project->idTypeprojet == 1) ? $this->getNameCfpIntra($project->idCfp_intra) : null,
                'initialnameCfp' => ($project->idTypeprojet == 1) ? $this->getInitialNameCfp($project->idCfp_intra) : null,
                'initialnameCfpInter' => ($project->idTypeprojet == 2) ?  $this->getInitialNameCfp($project->idCfp_inter) : null,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'ville' => $project->ville,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'paiement' => ($project->idTypeprojet == 1 || $project->idTypeprojet == 2 || $project->paiement === null) ? $project->paiement : '--',
                'modalite' => $project->modalite,
                'project_description' => $project->project_description,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'etp_initial_name' => $project->etp_initial_name,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'ville' => $project->ville,
                'etp_name_in_situ' => $project->etp_name,
                'total_ht' => $this->utilService->formatPrice(($project->total_ht_etp != 0) ? $project->total_ht_etp : $project->total_ht),
                'total_ttc' => $project->total_ttc,
                'idModule' => $project->idModule,
                'restaurations' => $this->getRestauration($project->idProjet),
                // 'project_inter_privacy' => $project->project_inter_privacy,
                // 'checkEmg' => $this->checkEmg($project->idProjet),
                // 'checkEval' => $this->checkEval($project->idProjet),
                // 'avg_before' => $this->averageEvalApprenant($project->idProjet)->avg_avant,
                // 'avg_after' => $this->averageEvalApprenant($project->idProjet)->avg_apres,
                'apprs' => $this->getApprListProjet($project->idProjet)

            ];
        }

        return $projets;
    }

    private function list_id_project_etpgrouped($etp_id)
    {

        $id_projets = DB::table('detail_apprenants AS da')
            ->join('employes AS emp', 'da.idEmploye', 'emp.idEmploye')
            ->join('customers AS cst', 'emp.idCustomer', 'cst.idCustomer')
            ->join('projets as p', 'p.idProjet', '=', 'da.idProjet')
            ->where('emp.idCustomer', $etp_id)
            /*->whereNot('p.project_is_closed', 1)
                ->whereNot('p.project_is_cancelled', 1)
                ->whereNot('p.project_is_repported', 1)*/
            ->groupBy('da.idProjet')
            ->pluck('da.idProjet');

        return $id_projets;
    }




    public function getApprenantAddedInter($idProjet)
    {
        $apprs = DB::table('v_list_apprenant_inter_added')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_fonction', 'emp_email', 'emp_photo', 'emp_matricule', 'etp_name', 'idEtp', 'idProjet')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();

        return response()->json(['apprs' => $apprs]);
    }

    public function getSessionProject($idProjet)
    {
        $countSession = DB::table('v_seances') //<---- A modifier...
            ->select('idSeance', 'dateSeance', 'heureDebut', 'id_google_seance', 'heureFin', 'idSalle', 'idProjet', 'salle_name', 'salle_quartier', 'project_title', 'project_description', 'idModule', 'module_name', 'ville')
            ->where('idProjet', $idProjet)
            ->get();

        return count($countSession);
    }

    public function getFormInterneProject($idProjet)
    {
        $forms = DB::table('v_formateur_internes')
            ->select('idEmploye as idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
            ->groupBy('idEmploye', 'name', 'firstName', 'photoForm', 'initialNameForm')
            ->where('idEntreprise', '=', Customer::idCustomer())
            ->where('idProjet', $idProjet)->get();
        return $forms->toArray();
    }

    public function getFormIntraProject($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
            ->groupBy('idFormateur', 'name', 'firstName', 'photoForm', 'initialNameForm')
            ->where('idProjet', $idProjet)->get();

        return $forms->toArray();
    }
    public function getLogoCfpIntra($idCfp)
    {
        $logoCfp = DB::table('v_collaboration_etp_cfps')
            ->select('etp_logo')
            ->where('idCfp', $idCfp)
            ->first();
        return $logoCfp->etp_logo;
    }
    public function getEtpProjectInter($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null) {
            $etp = DB::table('v_projet_cfps')
                ->select('etp_name', 'etp_logo')
                ->where('idProjet', $idProjet)
                ->orderBy('etp_name', 'asc')
                ->get();
        } else {
            $etp = DB::table('v_list_entreprise_inter')
                ->select('etp_name', 'etp_logo')
                ->where('idProjet', $idProjet)
                ->orderBy('etp_name', 'asc')
                ->get();
        }

        return $etp->toArray();
    }
    public function getInitialNameCfp($idCfp)
    {
        $initialnameCfp = DB::table('v_collaboration_etp_cfps')
            ->select('etp_initial_name')
            ->where('idCfp', $idCfp)
            ->first();
        return $initialnameCfp->etp_initial_name;
    }

    public function getNameCfpIntra($idCfp)
    {
        $nameCfp = DB::table('v_collaboration_etp_cfps')
            ->select('etp_name')
            ->where('idCfp', $idCfp)
            ->first();
        return $nameCfp->etp_name;
    }

    public function getProjectTotalPrice($idProjet)
    {
        $projectPrice = DB::table('v_union_projets')
            ->select(DB::raw('SUM(project_price_pedagogique + project_price_annexe) AS project_total_price'))
            ->where('idProjet', $idProjet)
            ->first();

        return $projectPrice->project_total_price;
    }

    public function getSessionHour($idProjet)
    {
        $countSessionHour = DB::table('v_seances')
            ->selectRaw('SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))) as sumHourSession')
            ->where('idProjet', $idProjet)
            ->first();

        return $countSessionHour->sumHourSession;
    }


    public function getApprenantProject($idProjet)
    {
        $apprs = DB::table('v_list_apprenants')
            ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_photo', 'emp_matricule', 'emp_phone', 'etp_name')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();

        return count($apprs);
    }

    // public function getApprenantInterProject($idProjet, $idCfp_inter)
    // {
    //     if ($idCfp_inter == null) {
    //         $apprs = DB::table('v_list_apprenants')
    //             ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_photo', 'emp_matricule', 'emp_phone', 'etp_name')
    //             ->where('idProjet', $idProjet)
    //             ->orderBy('emp_name', 'asc')
    //             ->get();
    //     } elseif ($idCfp_inter == 2) {
    //         $apprs = DB::table('v_list_apprenant_inter_added')
    //             ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_fonction', 'emp_email', 'emp_photo', 'emp_matricule', 'etp_name', 'idEtp')
    //             ->where('idProjet', $idProjet)
    //             ->orderBy('emp_name', 'asc')
    //             ->get();
    //     }
    //     return count($apprs);
    // }

    public function create()
    {
        return view('ETP.projetInternes.create');
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

    public function reservationvalidation($id, $type)
    {
        $project_reserved = DB::table('inter_entreprises')->select('idProjet', 'nbPlaceReserved')->where('id', $id)->first();
        $nb_place_reserved = $project_reserved->nbPlaceReserved;
        $place_available = $this->getPlaceAvailable($project_reserved->idProjet);
        if ($type == 'validate') {
            if ($place_available >= $nb_place_reserved) {
                DB::table('inter_entreprises')->where('id', $id)->update(['isActiveInter' => 1]);
                return response()->json(['success' => 'Reservation validé avec succes.']);
            } else {
                return response()->json(['error' => 'Impossible de valider la réservation car le nombre de places demandées dépasse le nombre de places disponibles.']);
            }
        } elseif ($type == 'stack') {
            DB::table('inter_entreprises')->where('id', $id)->update(['isActiveInter' => 2]);
            return response()->json(['success' => 'Réservation sur la liste d\'attente effectuée avec succès.']);
        } else {
            DB::table('inter_entreprises')->where('id', $id)->update(['isActiveInter' => 3]);
            return response()->json(['success' => 'Reservation refusé svec succes.']);
        }
    }





    private function getEtpNameProjectInter($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null) {
            $etp = DB::table('v_projet_cfps')
                ->select('etp_name', 'etp_logo', 'etp_initial_name')
                ->where('idProjet', $idProjet)
                ->orderBy('etp_name', 'asc')
                ->get();
        } elseif ($idCfp_inter != null) {
            $etp = DB::table('v_list_entreprise_inter')
                ->select('etp_name', 'etp_logo', 'etp_initial_name')
                ->where('idProjet', $idProjet)
                ->where('etp_name', '!=', 'null')
                ->orderBy('etp_name', 'asc')
                ->get();
        }
        return $etp;
    }

    public function detailsJson($idProjet)
    {
        $projet = DB::table('v_projet_etps')
            ->select(
                'idProjet',
                'dateDebut',
                'dateFin',
                'project_title',
                'etp_name',
                'ville',
                'project_status',
                'project_type',
                'module_image',
                //'paiement',
                'project_reference',
                'idModule',
                'modalite',
                'idEtp',
                'salle_quartier',
                'salle_code_postal',
            )
            ->where('idProjet', $idProjet)
            ->first();

        //dd($projet);

        $villes = DB::table('villes')->select('idVille', 'ville')->get();
        $paiements = DB::table('paiements')->select('idPaiement', 'paiement')->get();

        $forms = DB::table('v_formateur_internes')
            ->select('idProjet', 'idEmploye', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
            ->groupBy('idProjet', 'idEmploye', 'name', 'firstName', 'photoForm', 'initialNameForm')
            ->where('idProjet', $idProjet)
            ->get();

        $apprs = DB::table('v_list_apprenants')
            ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_photo', 'emp_matricule', 'emp_phone', 'etp_name')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();

        $seances = DB::table('v_seances_etp')
            ->select('idSeance', 'dateSeance', 'heureDebut', 'id_google_seance', 'heureFin', 'idSalle', 'idProjet', 'salle_name', 'salle_quartier', 'project_title', 'project_description', 'idModule', 'module_name', 'ville')
            ->where('idProjet', $idProjet)
            ->get();

        $apprenantInterCount = DB::table('v_list_apprenant_inter_added')
            ->select('idProjet')
            ->where('idProjet', $idProjet)
            ->get();

        // Matériel - Prérequis - Objectif pour le projet
        $materiels = DB::table('prestation_modules')
            ->select('idPrestation', 'prestation_name', 'idModule')
            ->where('idModule', $projet->idModule)->get();

        $nameEtps = $this->getEtpNameProjectInter($idProjet, Customer::idCustomer());

        return response()->json([
            'project' => $projet,
            //'etps' => $etps,
            //'modules' => $modules,
            'paiements' => $paiements,
            'modalite' => $projet->modalite,
            'villes' => $villes,
            'forms' => $forms,
            'apprenants' => $apprs,
            'apprenantInterCount' => count($apprenantInterCount),
            'materiels' => $materiels,
            'nameEtps' => $nameEtps,
            'reference' => $projet->project_reference,
            'quartier' => $projet->salle_quartier,
            'codePostal' => $projet->salle_code_postal,
            'apprsCount' => count($apprs),
            'seanceCount' => count($seances),
        ]);
    }

    public function removeEtpInter($idProjet, $idEtp)
    {
        try {
            $delete = DB::table('inter_entreprises')->where('idProjet', $idProjet)->where('idEtp', $idEtp)->delete();

            if ($delete) {
                return response()->json(['success' => 'Succès']);
            } else {
                return response()->json(['error' => 'Erreur inconnue !']);
            }
        } catch (Exception $th) {
            return response()->json(['error' => $th->getMessage()]);
        }
    }

    public function addApprenantInter($idProjet, $idApprenant, $idEtp)
    {
        $checkAppr = DB::table('apprenants')->where('idEmploye', $idApprenant)->get();
        $check = DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->where('idEtp', $idEtp)->where('idEmploye', $idApprenant)->get();

        if (count($checkAppr) < 1 && count($check) < 1) {
            try {
                DB::beginTransaction();
                DB::table('apprenants')->insert([
                    'idEmploye' => $idApprenant
                ]);

                DB::table('detail_apprenant_inters')->insert([
                    'idProjet' => $idProjet,
                    'idEmploye' => $idApprenant,
                    'idEtp' => $idEtp
                ]);
                DB::commit();
                return response()->json(['success' => 'Apprenant ajouté avec succès']);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Erreur inconnue !']);
            }
        } elseif (count($checkAppr) >= 1 && count($check) < 1) {
            DB::table('detail_apprenant_inters')->insert([
                'idProjet' => $idProjet,
                'idEmploye' => $idApprenant,
                'idEtp' => $idEtp
            ]);

            return response()->json(['success' => 'Apprenant ajouté avec succès']);
        } elseif (count($checkAppr) >= 1 && count($check) >= 1) {
            return response()->json(['error' => 'Employée déjà inscrit à la session']);
        }
    }

    public function removeApprsEtp($idProjet, $idApprenant, $idEtp)
    {
        $delete = DB::table('detail_apprenant_inters')->where('idProjet', $idProjet)->where('idEtp', $idEtp)->where('idEmploye', $idApprenant)->delete();

        if ($delete) {
            return response()->json(['success' => 'Succès']);
        } else {
            return response()->json(['error' => 'Erreur inconnue !']);
        }
    }

    

    public function update(Request $req, $projectId, $projectName)
    {
        $req->validate([
            'dateDebut' => 'required | date | after_or_equal:today',
            'dateFin' => 'required | date | after:dateDebut',
            'module_interne_id' => 'required | integer',
        ]);

        try {
            DB::table('groupe_internes')
                ->join('projet_internes', 'groupe_internes.projet_interne_id', 'projet_internes.id')
                ->where('projet_internes.etp_id', '=', Auth::user()->id)
                ->where('projet_internes.id', '=', $projectId)
                ->where('projet_internes.projectName', '=', $projectName)
                ->update([
                    'groupe_internes.dateDebut' => $req->dateDebut,
                    'groupe_internes.dateFin' => $req->dateFin,
                    'groupe_internes.module_interne_id' => $req->module_interne_id
                ]);

            return redirect('projetEtps')->with('successMod', 'Modifiée avec succès');
        } catch (Exception $e) {
            return $e->getMessage();
            return "Erreur";
        }
    }

    public function mainGetIdModule($idProjet)
    {
        $projet = DB::table('v_projet_etps')->select('idProjet', 'idModule')->where('idProjet', $idProjet)->first();

        return response()->json(['projet' => $projet]);
    }







    public function edit($idProgramme)
    {
        $programme = DB::table('programmes')->select('idProgramme', 'program_title', 'program_description')->where('idProgramme', $idProgramme)->first();
        return response()->json(['programme' => $programme]);
    }




















    //fonction pour selectionner tous les id des formateurs...
    public function getIdFormateurInterne()
    {
        $allId = [];
        // $allId = DB::select("SELECT idFormateur FROM `v_formateur_cfps` WHERE idCfp = ? GROUP BY idFormateur ", [Customer::idCustomer()] );       
        $allId = DB::table('v_formateur_cfps')
            ->select('idFormateur')
            ->where('idCfp', 2)
            ->groupBy('idFormateur')
            ->get();

        return $allId;
    }

    //Filtre


    public function projectInter()
    {
        $projet_id = DB::table('inter_entreprises')->where('idEtp', Customer::idCustomer())->pluck('idProjet');
        $projects = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'idEtp', 'dateFin', 'module_name', 'etp_name', 'ville', 'project_status', 'project_description', 'project_type', 'paiement', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule')
            ->whereIn('idProjet', $projet_id)
            ->groupBy('idProjet')
            ->orderBy('dateDebut', 'asc')
            ->get();

        $projets = [];
        foreach ($projects as $project) {
            $projets[] = [
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'formateurs' => $this->getFormProject($project->idProjet),
                'apprCount' => $this->getApprenantProject($project->idProjet, $project->idCfp_inter),
                'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'general_note' => $this->getNote($project->idProjet),
                'idProjet' => $project->idProjet,
                'idCfp_inter' => $project->idCfp_inter,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'logoCfp' => $this->getCustomer($project->idCfp_inter)['logo'],
                'nameCfp' => $this->getCustomer($project->idCfp_inter)['customerName'],
                'nbPlace' => $this->getNbPlace($project->idProjet),
                'is_active_inter' => $this->getIsActiveInter($project->idProjet),
                // 'partCount' => $this->getParticulierProject($project->idProjet, $project->idCfp_inter),
                'etp_name' => $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter),
                'idEtp' => $project->idEtp,
                'ville' => $project->ville,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'paiement' => $project->paiement,
                'modalite' => $project->modalite,
                'project_description' => $project->project_description,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'etp_initial_name' => $project->etp_initial_name,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'ville' => $project->ville,
                'etp_name_in_situ' => $project->etp_name,
                'total_ht' => $this->getTotalPrice($project->idProjet),
                'total_ttc' => $project->total_ttc,
                'idModule' => $project->idModule,
                'restaurations' => $this->getRestauration($project->idProjet),
                'deb' => Carbon::parse($project->dateDebut)->locale('fr')->translatedFormat('j M Y'),
                'fin' => Carbon::parse($project->dateFin)->locale('fr')->translatedFormat('j M Y'),
            ];
        }

        $projectDates = DB::table('v_projet_cfps')
            ->select(DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'))
            ->groupBy('headDate')
            ->orderBy('dateDebut', 'asc')
            ->whereIn('idProjet', $projet_id)
            ->get();

        $projetCount = DB::table('v_projet_cfps')
            ->whereIn('idProjet', $projet_id)
            ->count(DB::raw('DISTINCT(idProjet)'));

        return view('ETP.projets.reservations.index', compact(['projets', 'projetCount', 'projectDates']));
    }

    private function getPaiementProjet($idProjet)
    {
        $paiement = DB::table('v_projet_cfps')->select('paiement')->where('idProjet', $idProjet)->first();
        return $paiement->paiement;
    }

    private function getCustomer($idCustomer)
    {
        $customer = DB::table('customers')->select('logo', 'customerName')->where('idCustomer', $idCustomer)->first();

        return [
            'logo' => $customer->logo,
            'customerName' => $customer->customerName
        ];
    }

    private function getNbPlace($idProjet)
    {
        $nb_place = DB::table('inter_entreprises')->select('nbPlaceReserved')->where('idProjet', $idProjet)->where('idEtp', auth()->user()->id)->first();
        return $nb_place->nbPlaceReserved;
    }

    private function getIsActiveInter($idProjet)
    {
        $is_active_inter = DB::table('inter_entreprises')->select('isActiveInter')->where('idProjet', $idProjet)->first();
        return $is_active_inter->isActiveInter;
    }

    private function getForms($id_project)
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idProjet', 'idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'email AS form_email', 'initialNameForm AS form_initial_name')
            ->groupBy('idProjet', 'idFormateur', 'name', 'firstName', 'photoForm', 'email', 'initialNameForm')
            ->where('idProjet', $id_project)
            ->get();

        return $forms;
    }

    private function getTotalPrice($idProjet)
    {
        $price = DB::table('inter_entreprises as I')
            ->join('projets as P', 'P.idProjet', 'I.idProjet')
            ->join('mdls as M', 'M.idModule', 'P.idModule')
            ->join('modules as MD', 'M.idModule', 'MD.idModule')
            ->where('I.idEtp', Customer::idCustomer())
            ->where('I.idProjet', $idProjet)
            ->value(DB::raw('I.nbPlaceReserved * MD.prix'));

        return $this->utilService->formatPrice($price);
    }


    private function getProjetsInter($projects)
    {
        $projets = [];
        foreach ($projects as $project) {
            $projets[] = [
                'seanceCount' => $this->getSessionProject($project->idProjet),
                'formateurs' => ($project->idTypeprojet == 3) ? $this->getFormInterneProject($project->idProjet) : $this->getFormIntraProject($project->idProjet),
                'apprCount' => $this->getApprenantProject($project->idProjet),
                'projectTotalPrice' => $this->getProjectTotalPrice($project->idProjet),
                'totalSessionHour' => $this->getSessionHour($project->idProjet),
                'idProjet' => $project->idProjet,
                'idCfp' => $project->idCfp_intra,
                'etp_name' => ($project->idTypeprojet == 2) ? $this->getEtpProjectInter($project->idProjet, $project->idCfp_inter) : $project->etp_name,
                'logoCfp' => ($project->idTypeprojet == 1) ? $this->getLogoCfpIntra($project->idCfp_intra) : null,
                'nameCfp' => ($project->idTypeprojet == 1) ? $this->getNameCfpIntra($project->idCfp_intra) : null,
                'initialnameCfp' => ($project->idTypeprojet == 1) ? $this->getInitialNameCfp($project->idCfp_intra) : null,
                'dateDebut' => $project->dateDebut,
                'dateFin' => $project->dateFin,
                'module_name' => $project->module_name,
                'ville' => $project->ville,
                'project_status' => $project->project_status,
                'project_type' => $project->project_type,
                'paiement' => ($project->idTypeprojet == 1 && $project->idTypeprojet == 2) ? $project->paiement : null,
                'modalite' => $project->modalite,
                'project_description' => $project->project_description,
                'headDate' => $project->headDate,
                'module_image' => $project->module_image,
                'etp_logo' => $project->etp_logo,
                'etp_initial_name' => $project->etp_initial_name,
                'salle_name' => $project->salle_name,
                'salle_quartier' => $project->salle_quartier,
                'salle_code_postal' => $project->salle_code_postal,
                'ville' => $project->ville
            ];
        }
        return $projets;
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


}
