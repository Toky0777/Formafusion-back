<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditFormRequest;
use App\Http\Requests\FormateurUpdateRequest;
use App\Mail\CfpInviteFormCreated;
use App\Mail\InvitationFormateur;
use App\Models\Customer;
use App\Models\RoleUser;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Laravelcm\Subscriptions\Models\Feature;
use Laravelcm\Subscriptions\Models\Subscription;
use Termwind\Components\Raw;
use App\Services\FormateurService;
use App\Services\BrevoService;
use App\Traits\HasFormateur;

class FormateurController extends Controller
{
    use HasFormateur;

    public $formateur;

    public function __construct(FormateurService $form)
    {
        $this->formateur = $form;
    }

    public function idCfp()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idCustomer;
    }

    public function idCustomer()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);

        return !empty($customer) ? $customer[0]->idCustomer : null;
    }


    public function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = [];
        $alphaLength = strlen($alphabet) - 1;

        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }

        return implode($pass);
    }

    public function getIdCustomer()
    {
        $userId = Auth::user()->id;
        return response()->json(['idCustomer' => $userId]);
    }

    public function invite(Request $req)
    {
        $validate = Validator::make($req->all(), [
            'form_name' => 'required|min:2|max:200',
            'form_email' => 'required|email'
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->messages()]);
        } else {
            $checkUser = DB::table('users')->select('id', 'email')->where('email', $req->form_email)->get();
            $password = $this->randomPassword();

            if (count($checkUser) >= 1) {
                $checkForm = DB::table('forms')->select('idFormateur')->where('idFormateur', '=', $checkUser[0]->id)->get();
                $checkFormateur = DB::table('formateurs')->select('idFormateur')->where('idFormateur', '=', $checkUser[0]->id)->get();

                $checkCfpFormActive = DB::table('cfp_formateurs')
                    ->select('idFormateur')
                    ->where('idFormateur', $checkUser[0]->id)
                    ->where('idCfp', $this->idCfp())
                    ->where('isActiveFormateur', 1)
                    ->where('isActiveCfp', 1)
                    ->get();

                $checkCfpFormInactive = DB::table('cfp_formateurs')
                    ->select('idFormateur')
                    ->where('idFormateur', '=', $checkUser[0]->id)
                    ->where('idCfp', $this->idCfp())
                    ->where('isActiveFormateur', '=', 0)
                    ->where('isActiveCfp', '=', 1)
                    ->get();

                if (count($checkForm) <= 0 && count($checkFormateur) <= 0 && count($checkCfpFormActive) <= 0 && count($checkCfpFormInactive) <= 0) {
                    DB::beginTransaction();
                    DB::table('forms')->insert([
                        'idFormateur' => $checkUser[0]->id,
                        'idTypeFormateur' => 1,
                        'idSexe' => 1
                    ]);

                    DB::table('formateurs')->insert([
                        'idFormateur' => $checkUser[0]->id,
                        'idSp' => 1
                    ]);

                    DB::table('cfp_formateurs')->insert([
                        'idCfp' => $this->idCfp(),
                        'idFormateur' => $checkUser[0]->id,
                        'dateCollaboration' => Carbon::now(),
                        'isActiveFormateur' => 0,
                        'isActiveCfp' => 1
                    ]);

                    try {
                        $cfp = DB::table('customers')->select('idCustomer', 'customerName', 'customer_addr_lot AS customerAdress')->where('idCustomer', $this->idCfp())->first();
                        $form = $req->form_email;

                        $htmlContent = (new InvitationFormateur($cfp, $form, $password))->render();

                        app(BrevoService::class)->sendEmail(
                            $req->form_email,
                            "Invitation de FormaFusion",
                            $htmlContent
                        );

                        // Mail::to($req->form_email)->send(new InvitationFormateur($cfp, $form, $password));
                    } catch (Exception $e) {
                        return response()->json(['error' => 'Erreur inconnue !' . $e->getMessage()]);
                    }

                    DB::commit();
                    return response()->json(['success' => 'Invitation envoyée avec succès']);
                } elseif (count($checkForm) >= 1 && count($checkFormateur) >= 1 && count($checkCfpFormActive) <= 0 && count($checkCfpFormInactive) <= 0) {
                    try {
                        DB::beginTransaction();

                        DB::table('cfp_formateurs')->insert([
                            'idCfp' => $this->idCfp(),
                            'idFormateur' => $checkUser[0]->id,
                            'dateCollaboration' => Carbon::now(),
                            'isActiveFormateur' => 0,
                            'isActiveCfp' => 1
                        ]);

                        $cfp = DB::table('customers')->select('idCustomer', 'customerName', 'customer_addr_lot AS customerAdress')->where('idCustomer', $this->idCfp())->first();
                        $form = $req->form_email;

                        $htmlContent = (new CfpInviteFormCreated($cfp, $form))->render();

                        app(BrevoService::class)->sendEmail(
                            $req->form_email,
                            "Invitation de FormaFusion",
                            $htmlContent
                        );
                        // Mail::to($req->form_email)->send(new CfpInviteFormCreated($cfp, $form));

                        DB::commit();
                        return response()->json(['success' => 'Invitation envoyée avec succès']);
                    } catch (Exception $e) {
                        DB::rollBack();
                        return response()->json(['error' => 'Erreur inconnue !']);
                    }
                } elseif (count($checkCfpFormInactive) >= 1) {
                    return response()->json(['error' => 'Une invitation est déjas envoye']);
                } else {
                    return response()->json(['error' => 'Vous êtes déjas en collaboration avec ce formateur']);
                }
            } elseif (count($checkUser) <= 0) {
                DB::beginTransaction();

                DB::table('users')->insert([
                    'name' => $req->form_name,
                    'firstName' => $req->form_first_name,
                    'email' => $req->form_email,
                    'password' => Hash::make($password),
                    'phone' => $req->form_phone
                ]);

                $user = DB::table('users')->select('id')->orderBy('id', 'desc')->first();

                DB::table('forms')->insert([
                    'idFormateur' => $user->id,
                    'idTypeFormateur' => 1,
                    'idSexe' => 1
                ]);

                DB::table('formateurs')->insert([
                    'idFormateur' => $user->id,
                    'idSp' => 1
                ]);

                DB::table('cfp_formateurs')->insert([
                    'idCfp' => $this->idCfp(),
                    'idFormateur' => $user->id,
                    'dateCollaboration' => Carbon::now(),
                    'isActiveFormateur' => 0,
                    'isActiveCfp' => 1
                ]);

                RoleUser::create([
                    'user_id' => $user->id,
                    'role_id' => 5,
                    'isActive' => 1,
                    'hasRole' => 1
                ]);

                try {
                    $cfp = DB::table('customers')->select('idCustomer', 'customerName', 'customer_addr_lot AS customerAdress')->where('idCustomer', $this->idCfp())->first();
                    $form = $req->form_email;
                    $htmlContent = (new InvitationFormateur($cfp, $form, $password))->render();

                    app(BrevoService::class)->sendEmail(
                        $req->form_email,
                        "Invitation de FormaFusion",
                        $htmlContent
                    );
                    // Mail::to($req->form_email)->send(new InvitationFormateur($cfp, $form, $password));

                    $htmlContent = (new InvitationFormateur($cfp, $form, $password))->render();
                } catch (Exception $e) {
                    return response()->json(['error' => 'Erreur inconnue !']);
                }

                DB::commit();
                return response()->json(['success' => 'Invitation envoyée avec succès']);
            } else {
                return response()->json(['error' => 'Le formateur est déjas en collaboration avec vous']);
            }
        }
    }
    // CFP
    // public function index()
    // {
    //     $formateurs = $this->formateur->index(Customer::idCustomer())->get();

    //     if (count($formateurs) <= 0) {
    //         return response()->json([
    //             'status' => 204,
    //             'message' => 'Aucun résultat !'
    //         ], 204);
    //     }

    //     return response()->json([
    //         'status' => 200,
    //         'formateurs' => $formateurs,
    //         'count' => count($formateurs)
    //     ]);
    // }

    public function index()
    {
        // Récupère tous les formateurs du CFP connecté
        $formateurs = $this->formateur->index(Customer::idCustomer())->get();

        if (count($formateurs) <= 0) {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }

        // Compter les formateurs actuels et anciens
        $countActuel = collect($formateurs)->where('form_is_active', 0)->count();
        $countAncien = collect($formateurs)->where('form_is_active', 1)->count();

        return response()->json([
            'status' => 200,
            'formateurs' => $formateurs,
            'count' => count($formateurs),
            'countActuel' => $countActuel,
            'countAncien' => $countAncien
        ]);
    }



    // Formateur
    public function getParticulierProject($idProjet, $idCfp_inter)
    {

        $parts = [];
        if ($idCfp_inter == !null) {
            $parts = DB::table('v_particuliers_projet')
                ->select('*')
                ->where('idProjet', $idProjet)
                ->orderBy('part_name', 'asc')
                ->get();
        }
        return count($parts);
    }
    public function dashboardFormateur()
    {

        $year = now()->year;

        $formateurs = DB::table('users AS u')
            ->select('f.idFormateur', 'u.name', 'u.firstName', 'u.photo', 'f.idCfp')
            ->join('cfp_formateurs AS f', 'u.id', 'f.idFormateur')
            ->where('f.idCfp', $this->idCfp())
            ->orderBy('u.name', 'asc')
            ->get();

        $projects = DB::table('v_projet_form')
            ->select('v_projet_form.*', 'customers.customerName')
            ->join('customers', 'v_projet_form.idCfp', '=', 'customers.idCustomer')
            ->where('v_projet_form.idCfp', $this->idCfp())
            ->get()
            ->groupBy('idFormateur');

        $all_project_ids = $projects->flatten()->pluck('idProjet');

        $trainer_hours = DB::table('v_seances_form')
            ->join('v_union_projets', 'v_seances_form.idProjet', '=', 'v_union_projets.idProjet')
            ->select(
                'v_seances_form.idFormateur',
                DB::raw("FORMAT(SUM(TIMESTAMPDIFF(SECOND, heureDebut, heureFin)) / 3600, 2) AS heures")
            )
            ->whereIn('v_seances_form.idProjet', $all_project_ids)
            ->whereIn(DB::raw("v_union_projets.project_status COLLATE utf8mb4_unicode_ci"), ['Terminé', 'Cloturé'])
            ->groupBy('v_seances_form.idFormateur')
            ->pluck('heures', 'idFormateur');

        $monthly_hours = DB::table('v_seances_form')
            ->join('v_union_projets', 'v_seances_form.idProjet', '=', 'v_union_projets.idProjet')
            ->select(
                'v_seances_form.idFormateur',
                DB::raw("MONTH(v_seances_form.dateSeance) AS mois"),
                DB::raw("FORMAT(SUM(TIMESTAMPDIFF(SECOND, heureDebut, heureFin)) / 3600, 2) AS heures")
            )
            ->whereIn('v_seances_form.idProjet', $all_project_ids)
            // ->whereYear('v_seances_form.dateSeance', $year)
            ->whereIn(DB::raw("v_union_projets.project_status COLLATE utf8mb4_unicode_ci"), ['Terminé', 'Cloturé'])
            ->groupBy('v_seances_form.idFormateur', DB::raw("MONTH(v_seances_form.dateSeance)"))
            ->get()
            ->groupBy('idFormateur');

        $evaluations = DB::table('eval_chauds')
            ->select('eval_chauds.*', 'type_questions.idTypeQuestion', 'v_projet_form.idFormateur', DB::raw('MONTH(v_projet_form.dateDebut) as mois'))
            ->join('questions', 'eval_chauds.idQuestion', '=', 'questions.idQuestion')
            ->join('type_questions', 'questions.idTypeQuestion', '=', 'type_questions.idTypeQuestion')
            ->join('v_projet_form', 'eval_chauds.idProjet', '=', 'v_projet_form.idProjet')
            ->where('v_projet_form.idCfp', $this->idCfp())
            ->whereIn('v_projet_form.project_status', ['Terminé', 'Cloturé'])
            ->where('type_questions.idTypeQuestion', 2)
            // ->whereYear('v_projet_form.dateDebut', $year)
            ->get()
            ->groupBy('idFormateur');

        foreach ($formateurs as $formateur) {
            $formateurEvaluations = $evaluations->get($formateur->idFormateur, collect());

            // Calcul de la moyenne générale corrigée (par employé)
            $evaluationsByEmployee = $formateurEvaluations->groupBy('idEmploye');
            $count_review = $evaluationsByEmployee->count();
            $total_note = $evaluationsByEmployee->map(function ($evals) {
                return $evals->avg('note');
            })->sum();

            $average = $count_review === 0 ? 0 : number_format(($total_note / $count_review), 1);

            $formateur->average_note = $average;
            $formateur->sumHourSession = $trainer_hours[$formateur->idFormateur] ?? '0.00';

            $formateur->monthly_hours = $monthly_hours->get($formateur->idFormateur, collect())
                ->pluck('heures', 'mois');

            $formateur->monthly_notes = $formateurEvaluations->groupBy('mois')->map(function ($evals) {
                $byEmployee = $evals->groupBy('idEmploye');
                $count = $byEmployee->count();
                $total = $byEmployee->map(fn($e) => $e->avg('note'))->sum();
                return $count === 0 ? 0 : number_format($total / $count, 1);
            });
        }

        return response()->json([
            'formateurs' => $formateurs
        ]);
    }
    public function getDetailsByForm(Request $request, $idFormateur)
    {
        $year = $request->input('year', now()->year);

        $projects = DB::table('v_projet_form')
            ->select('v_projet_form.*', 'customers.customerName')
            ->join('customers', 'v_projet_form.idCfp', '=', 'customers.idCustomer')
            ->where('v_projet_form.idCfp', $this->idCfp())
            ->where('idFormateur', $idFormateur)
            ->get();

        $all_project_ids = $projects->pluck('idProjet');

        // Heures mensuelles par projets terminés ou clôturés
        $monthly_hours = DB::table('v_seances_form')
            ->join('v_union_projets', 'v_seances_form.idProjet', '=', 'v_union_projets.idProjet')
            ->select(
                'v_seances_form.idFormateur',
                DB::raw("MONTH(v_seances_form.dateSeance) AS mois"),
                DB::raw("FORMAT(SUM(TIMESTAMPDIFF(SECOND, heureDebut, heureFin)) / 3600, 2) AS heures")
            )
            ->whereIn('v_seances_form.idProjet', $all_project_ids)
            ->whereYear('v_seances_form.dateSeance', $year)
            ->whereIn(DB::raw("v_union_projets.project_status COLLATE utf8mb4_unicode_ci"), ['Terminé', 'Cloturé'])
            ->where('v_seances_form.idFormateur', $idFormateur)
            ->groupBy('v_seances_form.idFormateur', DB::raw("MONTH(v_seances_form.dateSeance)"))
            ->get();

        // Taux d'occupation

        $total = $monthly_hours->sum(function ($item) {
            return (($item->heures / 1000) * 100);
        });

        $occupancy_rate = round($total, 1);

        // Évaluations groupées par mois
        $evaluations_grouped = DB::table('eval_chauds')
            ->select(
                'eval_chauds.*',
                'type_questions.idTypeQuestion',
                'v_projet_form.idFormateur',
                DB::raw('MONTH(v_projet_form.dateDebut) as mois')
            )
            ->join('questions', 'eval_chauds.idQuestion', '=', 'questions.idQuestion')
            ->join('type_questions', 'questions.idTypeQuestion', '=', 'type_questions.idTypeQuestion')
            ->join('v_projet_form', 'eval_chauds.idProjet', '=', 'v_projet_form.idProjet')
            ->where('v_projet_form.idCfp', $this->idCfp())
            ->whereIn('v_projet_form.project_status', ['Terminé', 'Cloturé'])
            ->where('type_questions.idTypeQuestion', 2)
            ->where('v_projet_form.idFormateur', $idFormateur)
            ->whereYear('v_projet_form.dateDebut', $year)
            ->get()
            ->groupBy('mois');

        foreach ($monthly_hours as $item) {
            $mois = $item->mois;
            $evaluations = $evaluations_grouped->get($mois, collect());

            $count = $evaluations
                ->map(fn($e) => "{$e->idProjet}-{$e->idEmploye}")
                ->unique()
                ->count();
            $total = $evaluations->sum('note');

            $item->note = $count === 0 ? 0 : number_format($total / ($count * 5), 1);
        }




        // 🔹 Modules + heures par module
        $modules = DB::table('v_seances_form')
            ->join('mdls', 'v_seances_form.idModule', '=', 'mdls.idModule')
            ->join('v_union_projets', 'v_seances_form.idProjet', '=', 'v_union_projets.idProjet')
            ->select(
                'mdls.moduleName',
                'mdls.idModule',
                DB::raw("FORMAT(SUM(TIMESTAMPDIFF(SECOND, heureDebut, heureFin)) / 3600, 2) AS total_heures")
            )
            ->whereIn('v_seances_form.idProjet', $all_project_ids)
            ->whereYear('v_seances_form.dateSeance', $year)
            ->where('v_seances_form.idFormateur', $idFormateur)
            ->whereIn(DB::raw("v_union_projets.project_status COLLATE utf8mb4_unicode_ci"), ['Terminé', 'Cloturé'])
            ->groupBy('mdls.idModule', 'mdls.moduleName')
            ->get();

        // 🔹 Ajouter les notes moyennes par module
        $eval_modules = DB::table('eval_chauds')
            ->join('questions', 'eval_chauds.idQuestion', '=', 'questions.idQuestion')
            ->join('type_questions', 'questions.idTypeQuestion', '=', 'type_questions.idTypeQuestion')
            ->join('v_projet_form', 'eval_chauds.idProjet', '=', 'v_projet_form.idProjet')
            ->where('v_projet_form.idCfp', $this->idCfp())
            ->where('v_projet_form.idFormateur', $idFormateur)
            ->whereIn('v_projet_form.project_status', ['Terminé', 'Cloturé'])
            ->whereYear('v_projet_form.dateDebut', $year)
            ->where('type_questions.idTypeQuestion', 2)
            ->get()
            ->groupBy('idModule');

        foreach ($modules as $module) {
            $evals = $eval_modules->get($module->idModule, collect());
            $count = $evals
                ->map(fn($e) => "{$e->idProjet}-{$e->idEmploye}")
                ->unique()
                ->count();
            $total = $evals->sum('note');
            $module->note = $count === 0 ? 0 : number_format($total / ($count * 5), 1);
        }

        return response()->json([
            'occupancy_rate' => $occupancy_rate,
            'monthly' => $monthly_hours,
            'modules' => $modules,
        ]);
    }

    public function getProjetForm($statut)
    {
        $projets = DB::table('v_union_seanceForms')
            ->select('idProjet', 'idFormateur', 'dateDebut', 'dateFin', 'isActiveProjet', 'formation', 'moduleName', 'etpName AS customerName', 'statut')
            ->groupBy('idProjet', 'idFormateur', 'dateDebut', 'dateFin', 'isActiveProjet', 'formation', 'moduleName', 'etpName', 'statut')
            ->where('idFormateur', Auth::user()->id)
            ->where('isActiveProjet', 1)
            ->where('statut', $statut)
            ->get();

        $pe = DB::table('v_union_seanceForms')
            ->select('idProjet', 'projectName', 'statut')
            ->groupBy('idProjet', 'projectName', 'statut')
            ->where('idFormateur', Auth::user()->id)
            ->where('isActiveProjet', 1)
            ->where('statut', "En cours")
            ->get();

        $pp = DB::table('v_union_seanceForms')
            ->select('idProjet', 'projectName', 'statut')
            ->groupBy('idProjet', 'projectName', 'statut')
            ->where('idFormateur', Auth::user()->id)
            ->where('isActiveProjet', 1)
            ->where('statut', "Prévisionnel")
            ->get();

        $pb = DB::table('v_union_seanceForms')
            ->select('idProjet', 'projectName', 'statut')
            ->groupBy('idProjet', 'projectName', 'statut')
            ->where('idFormateur', Auth::user()->id)
            ->where('isActiveProjet', 1)
            ->where('statut', "Brouillant")
            ->get();

        $pt = DB::table('v_union_seanceForms')
            ->select('idProjet', 'projectName', 'statut')
            ->groupBy('idProjet', 'projectName', 'statut')
            ->where('idFormateur', Auth::user()->id)
            ->where('isActiveProjet', 1)
            ->where('statut', "Terminée")
            ->get();
        $countProjetE = count($pe);
        $countProjetP = count($pp);
        $countProjetB = count($pb);
        $countProjetT = count($pt);

        return response()->json([
            'projets' => $projets,
            'countProjetE' => $countProjetE,
            'countProjetP' => $countProjetP,
            'countProjetB' => $countProjetB,
            'countProjetT' => $countProjetT,
        ]);
    }

    public function indexForm()
    {
        $isIndex = true;
        return view('formateurs.projets.index', compact(['isIndex']));
    }

    //get list learner by etp
    public function checkNameLearnerByEtp($idEtp, Request $req)
    {
        $key = $req->input('query', null);
        $learners = DB::table('employes as E')
            ->join('users as U', 'E.idEmploye', 'U.id')
            ->join('role_users as R', 'R.user_id', 'U.id')
            ->select('U.name')
            ->where('role_id', 4)
            ->where('E.idCustomer', $idEtp)
            ->where('U.name', 'LIKE', "%$key%")
            ->get();

        return response()->json($learners, 200);
    }

    // Vérification Nom + Prénom
    public function checkFullnameLearnerByEtp($idEtp, Request $req)
    {
        $name = $req->input('name', null);
        $firstName = $req->input('firstName', null);

        $exists = DB::table('employes as E')
            ->join('users as U', 'E.idEmploye', 'U.id')
            ->join('role_users as R', 'R.user_id', 'U.id')
            ->where('role_id', 4)
            ->where('E.idCustomer', $idEtp)
            ->where(DB::raw("CONCAT(U.name, U.firstName)"), $name . $firstName)
            ->exists();

        return response()->json(['isExist' => $exists], 200);
    }


    public function getListProjectTrainer()
    {
        $projects = DB::table('project_forms as PF')
            ->join('projets as P', 'P.idProjet', 'PF.idProjet')
            ->join('mdls as M', 'P.idModule', 'M.idModule')
            ->select('M.moduleName', 'P.dateDebut', 'P.dateFin', 'P.idProjet', 'P.idTypeProjet')
            ->where(function ($query) {
                $query->whereBetween('dateFin', [Carbon::now()->subDays(7), Carbon::now()])
                    ->orWhere(function ($q) {
                        $q->where('dateDebut', '<=', Carbon::now())
                            ->where('dateFin', '>=', Carbon::now());
                    })
                    ->orWhere('P.dateDebut', '>=', Carbon::now());
            })
            ->where('PF.idFormateur', Auth::user()->id)
            ->where('P.project_is_trashed', 0)
            ->where('P.project_is_active', 1)
            ->get();

        return response()->json($projects, 200);
    }

    public function getLisEtpByProject($projectId, $projectType)
    {
        $tables = [
            1 => ['intras as I', 'I.idEtp', 'I.idProjet'],
            2 => ['inter_entreprises as IE', 'IE.idEtp', 'IE.idProjet'],
        ];

        if (!isset($tables[$projectType])) {
            return response()->json(['error' => 'Type de projet invalide'], 400);
        }

        [$table, $etpCol, $projetCol] = $tables[$projectType];

        $entreprises = DB::table($table)
            ->join('customers as C', 'C.idCustomer', '=', $etpCol)
            ->select('C.idCustomer', 'C.customerName')
            ->where($projetCol, $projectId)
            ->where('idTypeCustomer', 2)
            ->get();

        if ($entreprises->count() > 1) {
            return response()->json($entreprises, 200);
        }

        $customerIdTrainer = DB::table('cfp_formateurs')
            ->where('idFormateur', Auth::id())
            ->value('idCfp');

        if ($customerIdTrainer) {
            $entreprises = DB::table('cfp_etps as CE')
                ->join('customers as C', 'CE.idEtp', '=', 'C.idCustomer')
                ->select('C.idCustomer', 'C.customerName')
                ->distinct()
                ->get();

            return response()->json($entreprises, 200);
        }

        return response()->json(['message' => 'Aucune entreprise trouvée'], 204);
    }

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

    public function getProjectListForm(Request $request)
    {
        $status = $request->query('status');
        $userId = Auth::user()->id;
        $projet = DB::table('v_projet_form')
            ->select('idProjet', 'dateDebut', 'idEtp', 'idFormateur', 'idParticulier', 'dateFin', 'project_reference', 'module_name', 'etp_name', 'ville', 'project_status', 'project_description', 'project_type', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name')
            ->where('idFormateur', $userId)
            ->where('project_status', $status)
            ->where('project_is_trashed', 0)
            ->orderBy('dateDebut', 'asc')
            ->get();


        $projets = [];
        foreach ($projet as $projet) {
            $projets[] = [
                'nbDocument' => $this->getNombreDocument($projet->idProjet),
                'invoice' => $this->getInvoiceProject($projet->idProjet),
                'dossier' => $this->getNomDossier($projet->idProjet),
                'seanceCount' => $this->getSessionProject($projet->idProjet),
                'formateurs' => $this->getFormProject($projet->idProjet),
                'apprCount' => $this->getApprenantProject($projet->idProjet),
                'totalSessionHour' => $this->getSessionHour($projet->idProjet),
                'general_note' => $this->getNote($projet->idProjet),
                'partCount' => $this->getParticulierProject($projet->idProjet, $projet->idCfp_inter),
                'idFormateur' => $projet->idFormateur,
                'idProjet' => $projet->idProjet,
                'idCfp_inter' => $projet->idCfp_inter,
                'dateDebut' => $projet->dateDebut,
                'dateFin' => $projet->dateFin,
                'module_name' => $projet->module_name,
                'modalite' => $projet->modalite,
                'etp_name' => $this->getEtpProjectInter($projet->idProjet, $projet->idCfp_inter),
                'idEtp' => $projet->idEtp,
                'ville' => $projet->ville,
                'project_status' => $projet->project_status,
                'project_description' => $projet->project_description,
                'project_reference' => $projet->project_reference,
                'project_type' => $projet->project_type,
                'headDate' => $projet->headDate,
                'module_image' => $projet->module_image,
                'etp_logo' => $projet->etp_logo,
                'etp_initial_name' => $projet->etp_initial_name,
                'salle_name' => $projet->salle_name,
                'salle_quartier' => $projet->salle_quartier,
                'salle_code_postal' => $projet->salle_code_postal,
                'ville' => $projet->ville,
                'etp_name_in_situ' => $projet->etp_name,
                'project_description' => $projet->project_description,
                'idModule' => $projet->idModule,
                'project_inter_privacy' => $projet->project_inter_privacy,
                'restaurations' => $this->getRestauration($projet->idProjet),
                'checkEmg' => $this->checkEmg($projet->idProjet),
                'checkEval' => $this->checkEval($projet->idProjet),
                'avg_before' => $this->averageEvalApprenant($projet->idProjet)->avg_avant,
                'avg_after' => $this->averageEvalApprenant($projet->idProjet)->avg_apres,
                'apprs' => $this->getApprListProjet($projet->idProjet),
                'sub_name' => $projet->sub_name,
                'idSubContractor' => $projet->idSubContractor,
                'idCfp' => $projet->idCfp,
                'cfp_name' => $projet->cfp_name,
            ];
        }
        $projectDates = DB::table('v_projet_form')
            ->select(DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'))
            ->groupBy('headDate')
            ->orderBy('dateDebut', 'asc')
            ->where('idFormateur', $userId)
            ->get();

        $projetFormCount = DB::table('v_projet_form')
            ->where('idFormateur', $userId)
            ->where('headYear', Carbon::now()->format('Y'))
            ->count();

        return response()->json([
            'projets' => $projets,
            'projetFormCount' => $projetFormCount,
            'projectDates' => $projectDates
        ]);
    }

    public function getNombreDocument($idProjet)
    {
        $nbDocument = DB::table('projets')
            ->join('dossiers', 'projets.idDossier', '=', 'dossiers.idDossier')
            ->leftJoin('documents', 'dossiers.idDossier', '=', 'documents.idDossier')
            ->select(DB::raw('COUNT(documents.idDocument) as document_count'))
            ->where('idProjet', $idProjet)
            ->first();

        return $nbDocument->document_count;
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

    public function checkEmg($idProjet)
    {
        $query = DB::table('emargements')->where('idProjet', $idProjet);

        if ($query) {
            return $query->count();
        } else {
            return null;
        }
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
    public function getRestauration($idProjet)
    {
        $restaurations = DB::table('project_restaurations')
            ->select('idRestauration')
            ->where('idProjet', $idProjet)
            ->get()
            ->pluck('idRestauration')
            ->toArray();
        return $restaurations;
    }

    public function detailForm($idProjet)
    {

        $userId = Auth::user()->id;

        $projet = DB::table('v_projet_form')
            ->select('idProjet', 'dateDebut', 'dateFin', 'project_title', 'etp_name', 'ville', 'project_status', 'project_description',  'project_type', 'paiement', 'modalite', 'idEtp', 'etp_initial_name', 'etp_logo', 'idModule', 'module_name', 'module_image', 'project_price_pedagogique', 'project_price_annexe', 'module_description', 'salle_name', 'salle_rue', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'idCfp', 'modalite', 'idModule', 'idSubContractor', 'project_reference')
            ->where('idProjet', $idProjet)
            ->first();
        // dd($projet);


        // return count($apprenantInter);

        $villes = DB::table('villes')->select('idVille', 'ville')->get();

        $seances = DB::table('v_seances')
            ->select('idSeance', 'dateSeance', 'heureDebut', 'session_catch_up', 'heureFin', 'idProjet', 'idModule', 'intervalle_raw')
            ->where('idProjet', $idProjet)
            ->orderBy('dateSeance', 'asc')
            ->get();

        $countDate = DB::table('v_seances')
            ->select('idProjet', 'dateSeance', DB::raw('COUNT(*) as count'))
            ->where('idProjet', $idProjet)
            ->groupBy('dateSeance')
            ->get();

        $totalSession = DB::table('v_seances')
            ->selectRaw("IFNULL(TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))), '%H:%i'), '00:00') as sumHourSession")
            ->where('idProjet', $idProjet)
            ->groupBy('idProjet')
            ->first();

        $modules = DB::table('mdls')
            ->select('idModule', 'moduleName AS module_name')
            ->where('moduleName', '!=', 'Default module')
            ->orderBy('moduleName', 'asc')
            ->get();

        // $apprs = DB::table('v_list_apprenants')
        //     ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_fonction', 'emp_email', 'emp_photo', 'emp_matricule', 'etp_name')
        //     ->where('idProjet', $idProjet)
        //     ->orderBy('emp_name', 'asc')
        //     ->get();

        $apprenantInter = DB::table('v_list_apprenant_inter_added')
            ->select('*')
            ->where('idProjet', $idProjet)
            ->get();

        $aa = DB::table('detail_apprenants')
            ->select(
                'detail_apprenants.idProjet',
                'detail_apprenants.idEmploye',
                DB::raw("SUBSTRING(users.name, 1, 1) as emp_initial_name"),
                'users.name as emp_name',
                'users.firstName as emp_firstname',
                'fonctions.fonction as emp_fonction', // Garder 'fonctions.fonction'
                'users.email as emp_email',
                'users.photo as emp_photo',
                'users.matricule as emp_matricule',
                'customers.customerName as etp_name'
            )
            ->join('users', 'detail_apprenants.idEmploye', '=', 'users.id')
            ->join('employes', 'detail_apprenants.idEmploye', '=', 'employes.idEmploye')
            ->join('customers', 'employes.idCustomer', '=', 'customers.idCustomer')
            ->join('fonctions', 'employes.idFonction', '=', 'fonctions.idFonction') // Sans alias
            ->where('detail_apprenants.idProjet', $idProjet);

        $apprs = DB::table('detail_apprenant_inters')
            ->select(
                'detail_apprenant_inters.idProjet',
                'detail_apprenant_inters.idEmploye',
                DB::raw("SUBSTRING(users.name, 1, 1) as emp_initial_name"),
                'users.name as emp_name',
                'users.firstName as emp_firstname',
                'fonctions.fonction as emp_fonction', // Garder 'fonctions.fonction'
                'users.email as emp_email',
                'users.photo as emp_photo',
                'users.matricule as emp_matricule',
                'customers.customerName as etp_name'
            )
            ->join('users', 'detail_apprenant_inters.idEmploye', '=', 'users.id')
            ->join('employes', 'detail_apprenant_inters.idEmploye', '=', 'employes.idEmploye')
            ->join('customers', 'employes.idCustomer', '=', 'customers.idCustomer')
            ->join('fonctions', 'employes.idFonction', '=', 'fonctions.idFonction') // Sans alias
            ->where('detail_apprenant_inters.idProjet', $idProjet)
            ->union($aa)
            ->get();

        $parts = DB::table('v_list_particuliers')
            ->select('idParticulier', 'part_name', 'part_firstname', 'part_cin', 'part_email', 'part_photo', 'part_matricule', 'user_is_in_service')
            ->where('user_is_in_service', 1)
            ->orderBy('part_name', 'asc')
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
            ->select('url')
            ->where('idProjet', $idProjet)
            ->where('idTypeImage', 1)
            ->get();

        $nbPl = DB::table('inters')->select('nbPlace')->where('idProjet', $idProjet)->first();
        $place_available = $this->getPlaceAvailable($idProjet) ?? null;
        $place_reserved = $this->getNbPlaceReserved($idProjet) ?? null;
        $nbPlace = $nbPl->nbPlace ?? null;

        $deb =  Carbon::parse($projet->dateDebut)->locale('fr')->translatedFormat('l j F Y');
        $fin =  Carbon::parse($projet->dateFin)->locale('fr')->translatedFormat('l j F Y');


        $restaurations = DB::table('project_restaurations')
            ->select('idRestauration')
            ->where('idProjet', $idProjet)
            ->get();

        return response()->json([
            'apprenantInter' => $apprenantInter,
            'restaurations' => $restaurations,
            'imagesMomentums' => $imagesMomentums,
            'prerequis' => $prerequis,
            'projet' => $projet,
            'apprs' => $apprs,
            'villes' => $villes,
            'seances' => $seances,
            'modules' => $modules,
            'materiels' => $materiels,
            'objectifs' => $objectifs,
            'totalSession' => $totalSession,
            'countDate' => $countDate,
            'emargements' => $emargements,
            'modalites' => $modalites,
            'eval_content' => $eval_content,
            'eval_type' => $eval_type,
            'countNotationProjet' => $countNotationProjet,
            'noteGeneral' => $noteGeneral,
            'deb' => $deb,
            'fin' => $fin
        ]);
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

    public function getApprenants()
    {

        $apprs = DB::table('v_apprenant_etp_alls')
            ->select('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->where('role_id', 4)
            ->groupBy('idEmploye', 'emp_initial_name', 'emp_photo', 'emp_name', 'emp_firstname', 'emp_matricule', 'emp_email', 'etp_name', 'emp_phone', 'emp_is_active')
            ->orderBy('emp_name', 'asc')
            ->get();

        return response()->json(['apprs' => $apprs]);
    }

    public function getEtpAdded($idProjet)
    {
        $etps = DB::table('v_list_entreprise_inter')->select('idEtp', 'etp_logo', 'etp_name', 'mail', 'idProjet')->where('idProjet', $idProjet)->get();

        return response()->json(['etps' => $etps]);
    }

    public function getEtpAssign($idProjet)
    {
        $etp = DB::table('v_projet_form')->select('idProjet', 'idEtp', 'etp_initial_name', 'etp_name', 'etp_logo', 'etp_email')->where('idProjet', $idProjet)->first();

        return response()->json(['etp' => $etp]);
    }

    public function getApprenantProjets($idEtp)
    {
        $checkEtp = DB::table('entreprises')->select('idCustomer', 'idTypeEtp')->where('idCustomer', $idEtp)->first();

        if ($checkEtp) {
            if ($checkEtp->idTypeEtp == 1 || $checkEtp->idTypeEtp == 3) {
                $apprs = DB::table('v_apprenant_etp')
                    ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_fonction', 'emp_email', 'emp_photo', 'emp_matricule')
                    ->where('role_id', 4)
                    ->where('idEtp', $idEtp)
                    ->where('user_is_in_service', 1)
                    ->orderBy('emp_name', 'asc')
                    ->get();

                return response()->json(['apprs' => $apprs]);
            } elseif ($checkEtp->idTypeEtp == 2) {
                $apprs = DB::table('v_list_emp_grps')
                    ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_photo', 'emp_matricule', 'idEntrepriseParent', 'etp_name', 'etp_name_parent')
                    ->where('role_id', 4)
                    ->where('idEntrepriseParent', $idEtp)
                    ->where('user_is_in_service', 1)
                    ->orderBy('emp_name', 'asc')
                    ->get();

                return response()->json(['apprs' => $apprs]);
            } else {
                return response(['error' => 'Erreur inconnue !']);
            }
        } else {
            return response(['error' => 'Entreprise introuvable !']);
        }
    }

    public function getApprenantAddedInter($idProjet)
    {
        $apprs = DB::table('v_list_apprenant_inter_added')
            ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_fonction', 'emp_email', 'emp_photo', 'emp_matricule', 'etp_name', 'idEtp', 'idProjet')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();

        $getEtps = DB::table('v_list_apprenant_inter_added')
            ->select('etp_name', 'idEtp', 'idProjet')
            ->where('idProjet', $idProjet)
            ->groupBy('idEtp', 'etp_name')
            ->orderBy('etp_name', 'asc')
            ->get();


        $getSeance = DB::table('v_emargement_appr')
            ->select('idSeance', 'idProjet', 'heureDebut', 'heureFin', 'dateSeance', 'isPresent', 'idEmploye')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', Carbon::now()->toDateString()) // Ajoute la condition pour la date
            ->groupBy('idSeance')
            ->get();

        $getPresence = DB::table('v_emargement_appr')
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


        $getAppr = DB::table('v_emargement_appr')
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

    public function getApprenantAdded($idProjet)
    {

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
                'E.avant as avant',
                'E.apres as apres'
            )
            ->where('L.idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();

        $getEtps = DB::table('detail_apprenants AS da')
            ->select('da.idProjet', 'da.idEmploye', 'cst.idCustomer AS idEtp', 'cst.customerName AS etp_name', 'cst.logo AS etp_logo')
            ->join('employes AS emp', 'da.idEmploye', 'emp.idEmploye')
            ->join('customers AS cst', 'emp.idCustomer', 'cst.idCustomer')
            ->where('da.idProjet', $idProjet)
            ->groupBy('cst.idCustomer', 'cst.customerName')
            ->orderBy('etp_name', 'asc')
            ->get();

        $getSeance = DB::table('v_emargement_appr')
            ->select('idSeance', 'idProjet', 'heureDebut', 'heureFin', 'dateSeance', 'isPresent', 'idEmploye')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', Carbon::now()->toDateString()) // Ajoute la condition pour la date
            ->groupBy('idSeance')
            ->get();

        $getPresence = DB::table('v_emargement_appr')
            ->select('idSeance', 'dateSeance', 'idProjet', 'isPresent', 'idEmploye')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', Carbon::now()->toDateString()) // Ajoute la condition pour la date
            ->get();

        $countDate = DB::table('v_seances')
            ->select('idProjet', 'idSeance', DB::raw('COUNT(*) as count'), 'dateSeance')
            ->where('idProjet', $idProjet)
            ->whereDate('dateSeance', '<=', Carbon::now()->toDateString()) // Ajoute la condition pour la date
            ->groupBy('dateSeance')
            ->get();


        $getAppr = DB::table('v_emargement_appr')
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

        $getIdAppr = DB::table('v_emargement_appr')
            ->select('idProjet', 'idEmploye', 'idSeance', 'name', 'firstName', 'photo')
            ->where('idProjet', $idProjet)
            ->get();

        return response()->json([
            'apprs' => $apprs,
            'getEtps' => $getEtps,
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



    public function checkFinishF($idProjet, $idEmploye)
    {
        $check = DB::select("SELECT idProjet, idEmploye FROM eval_chauds WHERE idProjet = ? AND idEmploye = ? GROUP BY idProjet, idEmploye", [$idProjet, $idEmploye]);
        $checkFinished = count($check);

        if ($checkFinished >= 1) {
            $typeQuestions = DB::select("SELECT idTypeQuestion, typeQuestion FROM type_questions WHERE idTypeQuestion <> 4");
            $questions = DB::select("SELECT idQuestion, question, idTypeQuestion FROM questions");
            $notes = DB::select('select idQuestion, note from v_evaluation_alls where idProjet = ? AND idEmploye = ?', [$idProjet, $idEmploye]);
            $one = DB::select("SELECT idEmploye, valComment, com1, com2, generalApreciate FROM v_evaluation_alls WHERE idProjet = ? AND idEmploye = ? GROUP BY valComment, com1, com2, generalApreciate", [$idProjet, $idEmploye]);

            $examiner = DB::select("SELECT idEmploye, name_examiner, firstname_examiner FROM v_evaluation_alls WHERE idProjet = ? AND idEmploye = ? GROUP BY name_examiner, firstname_examiner", [$idProjet, $idEmploye]);

            $projet = DB::table('v_projet_cfps')->select('idProjet')->where('idProjet', $idProjet)->first();

            $valComments = DB::table('val_comments')->select('idValComment', 'valComment')->get();
            return response()->json([
                'checkEval' => $checkFinished,
                'typeQuestions' => $typeQuestions,
                'questions' => $questions,
                'notes' => $notes,
                'one' => $one,
                'examiner' => $examiner,
                'projet' => $projet
            ]);
        } else {
            $typeQuestions = DB::select("SELECT idTypeQuestion, typeQuestion FROM type_questions WHERE idTypeQuestion <> 4");
            $questions = DB::select("SELECT idQuestion, question, idTypeQuestion FROM questions");
            return response()->json([
                'checkEval' => $checkFinished,
                'typeQuestions' => $typeQuestions,
                'questions' => $questions
            ]);
        }
    }
    public function getPresenceUnique($idProjet, $idEmploye)
    {
        $checkEmp = DB::table('emargements')->select('idProjet', 'idEmploye', 'isPresent')->where('idProjet', $idProjet)->where('idEmploye', $idEmploye)->get();

        $sum = DB::table('emargements')->select(DB::raw('SUM(isPresent) AS somme'))->where('idProjet', $idProjet)->where('idEmploye', $idEmploye)->first();

        $intSum = (int)$sum->somme;

        $countCheckEmp = count($checkEmp);

        if ($countCheckEmp > 0) {
            $present = $countCheckEmp * 3;
            $absent = $countCheckEmp;

            if ($intSum === $present) {
                return response()->json(['checking' => 3]);
            } elseif ($intSum < $present && $intSum !== $absent) {
                return response()->json(['checking' => 2]);
            } elseif ($intSum === $absent) {
                return response()->json(['checking' => 1]);
            }
        } else {
            return response()->json(['checking' => 0]);
        }
    }

    public function countGlobalEmg($idSeance)
    {
        $countPresence = DB::select("SELECT isPresent, COUNT(idEmploye) AS countPresent FROM emargements WHERE idSeance = ? GROUP BY isPresent", [$idSeance]);

        return $countPresence;
    }

    // Seance Formateur
    public function getAllSeanceForm($idProjet)
    {
        $seances = [];

        $se = DB::table('v_union_seanceForms')
            ->select('idProjet', 'idSeance', 'dateSeance', 'heureDebut', 'heureFin', 'initialNameForm', 'nameForm', 'firstNameForm', 'photoForm', 'salle', 'quartier', 'ville', 'moduleName')
            ->where('idFormateur', Auth::user()->id)
            ->where('idProjet', $idProjet)
            ->get();

        foreach ($se as $s) {
            $seances[] = [
                'idProjet' => $s->idProjet,
                'idSeance' => $s->idSeance,
                'dateSeance' => $s->dateSeance,
                'heureDebut' => $s->heureDebut,
                'heureFin' => $s->heureFin,
                'initialNameForm' => $s->initialNameForm,
                'nameForm' => $s->nameForm,
                'firstNameForm' => $s->firstNameForm,
                'photoForm' => $s->photoForm,
                'salle' => $s->salle,
                'quartier' => $s->quartier,
                'ville' => $s->ville,
                'moduleName' => $s->moduleName,
                'countGlobalEmg' => $this->countGlobalEmg($s->idSeance)
            ];
        }

        $countSeances = DB::table('v_union_seanceForms')
            ->select('idSeance')
            ->where('idFormateur', Auth::user()->id)
            ->where('idProjet', $idProjet)
            ->count();

        $countApprs = DB::table('v_list_apprenants')
            ->select('idEmploye')
            ->where('idProjet', $idProjet)
            ->count();

        return response()->json([
            'seances' => $seances,
            'countSeances' => $countSeances,
            'countApprs' => $countApprs
        ]);
    }

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

    public function getIdEtpAdded($idProjet)
    {
        $idEtpAdded = DB::table('v_list_entreprise_inter')->where('idProjet', $idProjet)->pluck('idEtp')->toArray();

        return $idEtpAdded;
    }

    public function getEtpProjectInter($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null) {
            $etp = DB::table('v_projet_cfps')
                ->select('idEtp', 'etp_name', 'etp_logo', 'etp_initial_name')
                ->where('idProjet', $idProjet)
                ->whereNot('idEtp', $this->idCustomer())
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

    public function getSessionProject($idProjet)
    {
        $countSession = DB::table('v_seances')
            ->select('idSeance', 'dateSeance', 'heureDebut', 'id_google_seance', 'heureFin', 'idSalle', 'idProjet', 'salle_name', 'salle_quartier', 'project_title', 'project_description', 'idModule', 'module_name', 'ville')
            ->where('idProjet', $idProjet)
            ->get();

        return count($countSession);
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

    public function getFormProject($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
            ->groupBy('idFormateur', 'name', 'firstName', 'photoForm', 'initialNameForm')
            ->where('idProjet', $idProjet)->get();

        return $forms->toArray();
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


    public function getSessionHour($idProjet)
    {
        $countSessionHour = DB::table('v_seances')
            ->selectRaw('SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))) as sumHourSession')
            ->where('idProjet', $idProjet)
            ->first();

        return $countSessionHour->sumHourSession;
    }

    // Debut profil formateur
    public function getForm()
    {
        $form = DB::select('SELECT email, forms.idFormateur, forms.name, forms.firstName, forms.photo FROM users INNER JOIN forms ON forms.idFormateur = users.id where forms.idFormateur = ? and forms.idTypeFormateur = ?', [Auth::user()->id, 1]);

        return response()->json($form);
    }

    public function createPhoto()
    {
        $idFormateur = Auth::user()->id;
        $form = DB::select('SELECT email, users.id as idFormateur, users.name, users.firstName, users.photo FROM users WHERE users.id = ?', [$idFormateur]);

        return view('formateurs.profiles.createPhoto', compact('idFormateur', 'form'));
    }



    //Fin profil Formateur

    public function indexCv()
    {
        try {
            if (!Auth::check()) {
                throw new Exception('User is not authenticated.');
            }
            $userId = Auth::user()->id;

            //expériences
            $exp = DB::table('experiences')
                ->select('id', 'idFormateur', 'Lieu_de_stage', 'Fonction', 'Date_debut', 'Date_fin', 'Lieu')
                ->where('idFormateur', $userId)
                ->get();

            //diplômes
            $dp = DB::table('diplomes')
                ->select('id', 'idFormateur', 'Ecole', 'Diplome', 'Domaine', 'Date_debut', 'Date_fin')
                ->where('idFormateur', $userId)
                ->get();

            //compétences
            $cpc = DB::table('competences')
                ->select('id', 'idFormateur', 'Competence', 'note')
                ->where('idFormateur', $userId)
                ->get();

            //langues
            $lg = DB::table('langues')
                ->select('id', 'idFormateur', 'Langue', 'note')
                ->where('idFormateur', $userId)
                ->get();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => ['message' => $e->getMessage()]], 500);
        }

        $form = DB::table('formateurs')
            ->join('users', 'formateurs.idFormateur', 'users.id')
            ->join('role_users', 'role_users.user_id', 'users.id')
            ->select('idFormateur', 'name', 'firstName', 'cin', 'photo', 'phone', 'form_titre', 'form_speciality', 'email')
            ->where('idFormateur', Auth::user()->id)
            ->first();

        return response()->json([
            'exp'  => $exp,
            'dp'   => $dp,
            'cpc'  => $cpc,
            'lg'   => $lg,
            'form' => $form,
        ]);
    }

    //ETP Form
    public function getAllEtps()
    {
        $etps = DB::table('v_collaboration_cfp_etps')
            ->select('idEtp', 'etp_name', 'etp_email', 'etp_initial_name', 'etp_logo')
            ->orderBy('etp_name', 'asc')
            ->get();

        return response()->json(['etps' => $etps]);
    }
    public function updateNote(Request $request, $id)
    {
        $type = $request->input('type'); // Ajoutez ceci pour récupérer le type

        if ($type === 'langue') {
            DB::table('langues')
                ->where('id', $id)
                ->update(['note' => $request->note]);
        } elseif ($type === 'competence') {
            DB::table('competences')
                ->where('id', $id)
                ->update(['note' => $request->note]);
        } else {
            return response()->json(['error' => 'Type invalide'], 400);
        }

        return response()->json(['success' => true]);
    }

    public function createCv()
    {
        return view('formateurs.profiles.components.button.addExp');
    }

    public function createDp()
    {
        return view('formateurs.profiles.components.button.addDipl');
    }

    public function createCp()
    {
        return view('formateurs.profiles.components.button.addCompetence');
    }

    public function createLg()
    {
        return view('formateurs.profiles.components.button.addLangue');
    }

    public function storeCv(Request $req)
    {
        $type = $req->input('type');

        if ($type == 'exp') {
            $req->validate([
                'Lieu_de_stage' => 'required',
                'Fonction' => 'required',
                'Date_debut' => 'required',
                'Date_fin' => 'required',
                'Lieu' => 'required'
            ], [
                'Lieu_de_stage.required' => "Le nom est obligatoire",
                'Fonction.required' => "L'intitulé est obligatoire",
                'Date_debut.required' => "La date de début est obligatoire",
                'Date_fin.required' => "La date de fin est obligatoire",
                'Lieu.required' => "Le lieu est obligatoire"
            ]);
            DB::table('experiences')->insert([
                'idFormateur' => auth()->user()->id,
                'Lieu_de_stage' => $req->Lieu_de_stage,
                'Fonction' => $req->Fonction,
                'Date_debut' => $req->Date_debut,
                'Date_fin' => $req->Date_fin,
                'Lieu' => $req->Lieu
            ]);

            return response()->json(['success' => 'Expérience ajoutée avec succès']);
        } elseif ($type == 'dp') {
            $req->validate([
                'Ecole' => 'required',
                'Diplome' => 'required',
                'Domaine' => 'required',
                'Date_debut' => 'required',
                'Date_fin' => 'required'
            ], [
                'Ecole.required' => "Le nom de l'école est obligatoire",
                'Diplome.required' => "Le nom du diplôme est obligatoire",
                'Domaine.required' => "Le domaine est obligatoire",
                'Date_debut' => "La date debut est obligatoire",
                'Date_fin' => "La date fin est obligatoire"
            ]);

            DB::table('diplomes')->insert([
                'idFormateur' => auth()->user()->id,
                'Ecole' => $req->Ecole,
                'Diplome' => $req->Diplome,
                'Domaine' => $req->Domaine,
                'Date_debut' => $req->Date_debut,
                'Date_fin' => $req->Date_fin
            ]);

            return response()->json(['success' => 'Diplôme ajouté avec succès']);
        } elseif ($type == 'cpc') {
            $req->validate([
                'Competence' => 'required'
            ], [
                'Competence.required' => "Le competence est obligatoire"
            ]);

            DB::table('competences')->insert([
                'idFormateur' => auth()->user()->id,
                'Competence' => $req->Competence,
                'note' => $req->note
            ]);
            return response()->json(['success' => 'Compétence ajouté avec succès']);
        } elseif ($type == 'lg') {
            $req->validate([
                'Langue' => 'required'
            ], [
                'Langue.required' => "Veuillez ajoutez une langue que maitrisez"
            ]);

            DB::table('langues')->insert([
                'idFormateur' => auth()->user()->id,
                'Langue' => $req->Langue,
                'note' => $req->note
            ]);
            return response()->json(['success' => 'Langue ajouté avec succès']);
        } else {
            return response()->json(['error' => 'Type de données non reconnu']);
        }
    }

    public function toggleActive(Request $request, $idFormateur)
    {
        $newStatus = $request->input('isActiveFormateur', 0);

        DB::table('v_cfp_formateurs')
            ->where('idFormateur', $idFormateur)
            ->update(['isActiveFormateur' => $newStatus]);

        return response()->json(['success' => true, 'newStatus' => $newStatus]);
    }




    public function destroyCv(Request $req, $id)
    {
        $type = $req->input('type');  // Obtient le type de la requête

        try {
            if ($type == 'exp') {
                $deleted = DB::table('experiences')->where('id', $id)->delete();
            } elseif ($type == 'dp') {
                $deleted = DB::table('diplomes')->where('id', $id)->delete();
            } elseif ($type == 'cpc') {
                $deleted = DB::table('competences')->where('id', $id)->delete();
            } elseif ($type == 'lg') {
                $deleted = DB::table('langues')->where('id', $id)->delete();
            } else {
                return response()->json(['error' => 'Type de données non reconnu']);
            }

            if ($deleted) {
                return response()->json(['success' => 'Supprimé']);
            } else {
                return response()->json(['error' => 'L\'enregistrement n\'existe pas']);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Une erreur s\'est produite: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $query = $this->formateur->edit(Customer::idCustomer(), $id);

        if ($query->exists()) {
            try {
                $hasProject = DB::table('project_forms')->where('idFormateur', $id)->exists();
                if ($hasProject) {
                    return response()->json([
                        'status' => 409,
                        'message' => 'Suppréssion impossible, ce formateur est déjas associé à un projet !'
                    ], 409);
                }
                $this->formateur->destroy(Customer::idCustomer(), $id);

                return response()->json([
                    'status' => 200,
                    'message' => 'Formateur supprimé avec succès'
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Suppréssion impossible, ce formateur est déjas associé à un projet !'
                ], 422);
            }
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Formateur introuvable !'
            ], 204);
        }
    }

    //Edit Profil

    public function editProfile()
    {
        $user = Auth::user();
        return view('formateurs.miniCv.index', compact('user'));
    }

    public function updateProfile(EditFormRequest $request, $id)
    {
        $query = DB::table('users')->where('id', $id);

        if ($query->first()) {
            try {
                DB::transaction(function () use ($request, $query, $id) {
                    $query->update([
                        'name' => $request->input('name'),
                        'firstName' => $request->input('firstName'),
                        'email' => $request->input('email'),
                        'phone' => $request->input('phone')
                    ]);

                    DB::table('formateurs')->where('idFormateur', $id)->update([
                        'form_titre' => $request->fonction,
                        'form_speciality' => $request->specialite
                    ]);
                });
                return response()->json(['success' => 'Profil mis à jour avec succès']);
            } catch (Exception $e) {
                return response()->json(['error' => 'Erreur inconnue !']);
            }
        } else {
            return response()->json(['error' => 'Formateur introuvable !']);
        }
    }

    public function updatePhoto(Request $req, $id)
    {
        // Validation
        $req->validate([
            'photo' => 'required|string', // On accepte les chaînes Base64
        ]);

        $imageData = $req->input('photo');

        // Décoder l'image Base64
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $type = strtolower($type[1]); // png, jpg, etc.

            // Vérification du type de fichier
            if (!in_array($type, ['jpeg', 'jpg', 'png', 'gif'])) {
                return response()->json(['error' => 'Type d\'image non valide'], 422);
            }

            $imageData = base64_decode($imageData);

            // Vérification du décodage
            if ($imageData === false) {
                return response()->json(['error' => 'Décodage Base64 échoué'], 422);
            }

            // Nom et chemin du fichier
            $fileName = time() . '.' . $type;
            $filePath = 'img/formateurs/' . $fileName;

            // Sauvegarder dans DigitalOcean Spaces ou S3
            Storage::disk('do')->put($filePath, $imageData);

            // Mettre à jour la base de données
            DB::table('users')
                ->where('id', $id)
                ->update(['photo' => $fileName]); // Sauvegarder le chemin complet

            // Retourner une réponse JSON
            return response()->json(['success' => true, 'imageName' => $fileName]);
        } else {
            return response()->json(['error' => 'Format Base64 non valide'], 422);
        }
    }


    // cv get
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
            'langues' => $lg
        ]);
    }

    public function sendInvitation(Request $req)
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
        $featureSlug = Feature::where('plan_id', $idplan)->where('name', '{"fr":"Formateurs"}')->first()->slug;

        $subscription = $user->planSubscription($subscriptionSlug);
        $usage = $subscription->usage()->byFeatureSlug($featureSlug)->first();

        //Initialisation du premier usage 0
        if (!$usage) {
            $subscription->recordFeatureUsage($featureSlug, 0, false);
        }

        if (!$subscription->canUseFeature($featureSlug)) {
            return response()->json(['error' => 'Vous avez atteint le nombre maximum de formateurs autorisés.']);
        }
        // FIN LIMITEUR PAR RAPPORT AU ABONNEMENT 

        $validate = Validator::make($req->all(), [
            'form_name' => 'required|min:2|max:200',
            'form_email' => 'required|email'
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->messages()]);
        } else {
            $checkUser = DB::table('users')->select('id', 'email')->where('email', $req->form_email)->get();
            $password = $this->randomPassword();

            if (count($checkUser) >= 1) {
                $checkForm = DB::table('forms')->select('idFormateur')->where('idFormateur', '=', $checkUser[0]->id)->get();
                $checkFormateur = DB::table('formateurs')->select('idFormateur')->where('idFormateur', '=', $checkUser[0]->id)->get();

                $checkCfpFormActive = DB::table('cfp_formateurs')
                    ->select('idFormateur')
                    ->where('idFormateur', $checkUser[0]->id)
                    ->where('idCfp', $this->idCfp())
                    ->where('isActiveFormateur', 1)
                    ->where('isActiveCfp', 1)
                    ->get();

                $checkCfpFormInactive = DB::table('cfp_formateurs')
                    ->select('idFormateur')
                    ->where('idFormateur', '=', $checkUser[0]->id)
                    ->where('idCfp', $this->idCfp())
                    ->where('isActiveFormateur', '=', 0)
                    ->where('isActiveCfp', '=', 1)
                    ->get();

                if (count($checkForm) <= 0 && count($checkFormateur) <= 0 && count($checkCfpFormActive) <= 0 && count($checkCfpFormInactive) <= 0) {
                    DB::beginTransaction();
                    DB::table('forms')->insert([
                        'idFormateur' => $checkUser[0]->id,
                        'idTypeFormateur' => 1,
                        'idSexe' => 1
                    ]);

                    DB::table('formateurs')->insert([
                        'idFormateur' => $checkUser[0]->id,
                        'idSp' => 1
                    ]);

                    DB::table('cfp_formateurs')->insert([
                        'idCfp' => $this->idCfp(),
                        'idFormateur' => $checkUser[0]->id,
                        'dateCollaboration' => Carbon::now(),
                        'isActiveFormateur' => 0,
                        'isActiveCfp' => 1
                    ]);

                    try {
                        $cfp = DB::table('customers')->select('idCustomer', 'customerName', 'customer_addr_lot AS customerAdress')->where('idCustomer', $this->idCfp())->first();
                        $form = $req->form_email;

                        $htmlContent = (new InvitationFormateur($cfp, $form, $password))->render();

                        app(BrevoService::class)->sendEmail(
                            $req->form_email,
                            "Invitation de FormaFusion",
                            $htmlContent
                        );

                        // Mail::to($req->form_email)->send(new InvitationFormateur($cfp, $form, $password));
                    } catch (Exception $e) {
                        return response()->json(['error' => 'Erreur inconnue !' . $e->getMessage()]);
                    }

                    DB::commit();
                    $subscription->recordFeatureUsage($featureSlug);
                    return response()->json(['success' => 'Invitation envoyée avec succès']);
                } elseif (count($checkForm) >= 1 && count($checkFormateur) >= 1 && count($checkCfpFormActive) <= 0 && count($checkCfpFormInactive) <= 0) {
                    try {
                        DB::beginTransaction();

                        DB::table('cfp_formateurs')->insert([
                            'idCfp' => $this->idCfp(),
                            'idFormateur' => $checkUser[0]->id,
                            'dateCollaboration' => Carbon::now(),
                            'isActiveFormateur' => 0,
                            'isActiveCfp' => 1
                        ]);

                        $cfp = DB::table('customers')->select('idCustomer', 'customerName', 'customer_addr_lot AS customerAdress')->where('idCustomer', $this->idCfp())->first();
                        $form = $req->form_email;

                        $htmlContent = (new CfpInviteFormCreated($cfp, $form))->render();

                        app(BrevoService::class)->sendEmail(
                            $req->form_email,
                            "Invitation de FormaFusion",
                            $htmlContent
                        );
                        // Mail::to($req->form_email)->send(new CfpInviteFormCreated($cfp, $form));

                        DB::commit();
                        $subscription->recordFeatureUsage($featureSlug);
                        return response()->json(['success' => 'Invitation envoyée avec succès']);
                    } catch (Exception $e) {
                        DB::rollBack();
                        return response()->json(['error' => 'Erreur inconnue !']);
                    }
                } elseif (count($checkCfpFormInactive) >= 1) {
                    return response()->json(['error' => 'Une invitation est déjas envoye']);
                } else {
                    return response()->json(['error' => 'Vous êtes déjas en collaboration avec ce formateur']);
                }
            } elseif (count($checkUser) <= 0) {
                DB::beginTransaction();

                DB::table('users')->insert([
                    'name' => $req->form_name,
                    'firstName' => $req->form_first_name,
                    'email' => $req->form_email,
                    'password' => Hash::make($password),
                    'phone' => $req->form_phone
                ]);

                $user = DB::table('users')->select('id')->orderBy('id', 'desc')->first();

                DB::table('forms')->insert([
                    'idFormateur' => $user->id,
                    'idTypeFormateur' => 1,
                    'idSexe' => 1
                ]);

                DB::table('formateurs')->insert([
                    'idFormateur' => $user->id,
                    'idSp' => 1
                ]);

                DB::table('cfp_formateurs')->insert([
                    'idCfp' => $this->idCfp(),
                    'idFormateur' => $user->id,
                    'dateCollaboration' => Carbon::now(),
                    'isActiveFormateur' => 0,
                    'isActiveCfp' => 1
                ]);

                RoleUser::create([
                    'user_id' => $user->id,
                    'role_id' => 5,
                    'isActive' => 1,
                    'hasRole' => 1
                ]);

                try {
                    $cfp = DB::table('customers')->select('idCustomer', 'customerName', 'customer_addr_lot AS customerAdress')->where('idCustomer', $this->idCfp())->first();
                    $form = $req->form_email;

                    $htmlContent = (new InvitationFormateur($cfp, $form, $password))->render();

                    app(BrevoService::class)->sendEmail(
                        $req->form_email,
                        "Invitation de FormaFusion",
                        $htmlContent
                    );
                    // Mail::to($req->form_email)->send(new InvitationFormateur($cfp, $form, $password));
                } catch (Exception $e) {
                    return response()->json(['error' => 'Erreur inconnue !']);
                }

                DB::commit();
                $subscription->recordFeatureUsage($featureSlug);
                return response()->json(['success' => 'Invitation envoyée avec succès']);
            } else {
                return response()->json(['error' => 'Le formateur est déjas en collaboration avec vous']);
            }
        }
    }

    public function getAllForms()
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'isActiveFormateur AS form_is_active', 'photoForm AS form_photo', 'name AS form_name', 'firstName AS form_first_name', 'email AS form_email', 'initialNameForm AS form_initial_name')
            ->where('isActiveFormateur', 0)
            ->where('idCfp', $this->idCfp())
            ->groupBy('idFormateur', 'photoForm', 'name', 'firstName', 'email', 'initialNameForm')
            ->orderBy('name', 'asc')
            ->get();
        return response()->json(['forms'  => $forms]);
    }

    public function listForm()
    {
        $data = [];
        $data = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'photoForm AS form_photo', 'name AS form_name', 'firstName AS form_first_name', 'email AS form_email', 'initialNameForm AS form_initial_name')
            ->where('idCfp', $this->idCfp())
            ->groupBy('idFormateur', 'photoForm', 'name', 'firstName', 'email', 'initialNameForm')
            ->orderBy('name', 'asc')
            ->get();
        return response()->json(
            $data
        );
    }

    public function edit($idFormateur)
    {
        $query = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'isActiveFormateur AS form_is_active', 'isActiveCfp AS cfp_is_active', 'initialNameForm AS form_initial_name', 'photoForm AS form_photo', 'name AS form_name', 'firstName AS form_firstname', 'email AS form_email', 'isActive AS user_is_active', 'form_phone', 'form_addr_lot', 'form_addr_qrt', 'form_addr_cp')
            ->where('idFormateur', $idFormateur)
            ->groupBy('idFormateur', 'idCfp', 'isActiveFormateur', 'isActiveCfp', 'initialNameForm', 'photoForm', 'name', 'firstName', 'email', 'isActive', 'form_phone', 'form_addr_lot', 'form_addr_qrt', 'form_addr_cp');

        if ($query->first()) {
            $form = $query->first();

            return response()->json(['form' => $form]);
        } else {
            return response()->json(['error' => 'Formateur introuvable !']);
        }
    }

    public function update(FormateurUpdateRequest $req, $id)
    {
        $query = $this->formateur->edit(Customer::idCustomer(), $id);

        if ($query->exists()) {
            $this->formateur->update(Customer::idCustomer(), $id, $req->validated()['name'], $req->validated()['firstname'], $req->validated()['email'], null);

            return response()->json([
                'status' => 200,
                'message' => 'Modifié avec succès'
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Formateur introuvable !'
            ], 204);
        }
    }

    public function updateImageForm(Request $req, $idFormateur)
    {
        // $validate = Validator::make($req->all(), [
        //     'photo' => 'required|image|mimes:png,jpg,webp,gif|max:6144'
        // ]);
        // if ($validate->fails()) {
        //     return back()->with('error', $validate->messages());
        // } else {

        $form = DB::table('users')->select('photo')->where('id', $idFormateur)->first();

        $driver = new Driver();

        $manager = new ImageManager($driver);

        if ($form != null) {
            if (!empty($module->module_image)) {
                Storage::disk('do')->delete('img/formateurs/' . $form->photo);
            }

            $folderPath = public_path('img/formateurs/');

            $image_parts = explode(";base64,", $req->image);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $image = $manager->read($image_base64)->toWebp(25);

            $imageName = uniqid() . '.webp';
            $filePath = 'img/formateurs/' . $imageName;

            // Upload the image to DigitalOcean Space
            Storage::disk('do')->put($filePath, $image, 'public');

            // Update the database with the new image name
            DB::table('users')->where('id', $idFormateur)->update([
                'photo' => $imageName,
            ]);

            return response()->json([
                'success' => 'Image Uploaded Successfully',
                'imageName' =>  $imageName
            ]);
        }
    }

    public function activateForm(Request $req, $idCfp, $idFormateur)
    {
        $abn = DB::table('v_abonnement_cfps')
            ->select('idAbn', 'idCustomer', 'nbForm', 'isInfinity', 'isActive')
            ->where('idCustomer', Auth::user()->id)
            ->where('isActive', 1)
            ->first();

        $checkForm = DB::table('formateurs')
            ->join('forms', 'forms.idFormateur', 'formateurs.idFormateur')
            ->join('users', 'users.id', 'forms.idFormateur')
            ->join('role_users', 'role_users.user_id', 'users.id')
            ->where('role_users.isActive', 1)
            ->count('formateurs.idFormateur');

        if ($abn->isInfinity == 1) {
            DB::table('cfp_formateurs')
                ->join('formateurs', 'cfp_formateurs.idFormateur', 'formateurs.idFormateur')
                ->join('forms', 'forms.idFormateur', 'formateurs.idFormateur')
                ->join('users', 'users.id', 'forms.idFormateur')
                ->join('role_users', 'role_users.user_id', 'users.id')
                ->where('cfp_formateurs.idCfp', $idCfp)
                ->where('cfp_formateurs.idFormateur', $idFormateur)
                ->update([
                    'role_users.isActive' => 1
                ]);

            return back();
        } elseif ($abn->idAbn == $req->idAbn && $abn->nbForm == $req->nbForm) {
            if ($checkForm < $req->nbForm) {
                DB::table('cfp_formateurs')
                    ->join('formateurs', 'cfp_formateurs.idFormateur', 'formateurs.idFormateur')
                    ->join('forms', 'forms.idFormateur', 'formateurs.idFormateur')
                    ->join('users', 'users.id', 'forms.idFormateur')
                    ->join('role_users', 'role_users.user_id', 'users.id')
                    ->where('cfp_formateurs.idCfp', $idCfp)
                    ->where('cfp_formateurs.idFormateur', $idFormateur)
                    ->update([
                        'role_users.isActive' => 1
                    ]);

                return back();
            } else {
                return back()->with('errorAbn', 'Vous avez atteint le nombre maximale de formateur, Veuillez mettre à niveau votre abonnement !');
            }
        }
    }

    public function disableForm($idCfp, $idFormateur)
    {
        DB::table('cfp_formateurs')
            ->join('formateurs', 'cfp_formateurs.idFormateur', 'formateurs.idFormateur')
            ->join('forms', 'forms.idFormateur', 'formateurs.idFormateur')
            ->join('users', 'users.id', 'forms.idFormateur')
            ->join('role_users', 'role_users.user_id', 'users.id')
            ->where('cfp_formateurs.idCfp', $idCfp)
            ->where('cfp_formateurs.idFormateur', $idFormateur)
            ->update([
                'role_users.isActive' => 0
            ]);

        return back();
    }

    // Formateur
    public function listInvitation()
    {
        $invitations = DB::table('v_cfp_forms')
            ->select('idFormateur', 'initialNameCustomer', 'customerName as name', 'description', 'logo', 'idTypeCustomer', 'isActiveFormateur', 'isActiveCfp')
            ->where('idFormateur', Auth::user()->id)
            ->paginate(6);

        return view('formateurs.listInvitation', compact('invitations'));
    }

    public function acceptInv(Request $req, $idFormateur)
    {
        $req->validate([
            'idFormateur' => 'required|integer|exists:cfp_formateurs'
        ]);

        DB::table('cfp_formateurs')->where('idFormateur', '=', $idFormateur)->update([
            'isActiveFormateur' => 1
        ]);

        return back()->with('successPost', 'Succès');
    }

    public function indexQCM()
    {
        return view('formateurs.QCM.index');
    }
    public function createQCM()
    {
        return view('formateurs.QCM.components.create');
    }
    public function getQuiz()
    {
        $reponses = DB::table('reponses')
            ->join('sections', 'sections.idSection', '=', 'reponses.idSection')
            ->join('quizz_questions', 'quizz_questions.idQuestion', '=', 'reponses.idQuestion')
            ->select('sections.idSection', 'sections.section', 'quizz_questions.idQuestion', 'quizz_questions.question', 'idReponse', 'reponse')
            ->get();

        $quiz = response()->json(['reponse' => $reponses]);

        return $quiz;
    }
    public function getSectionQuiz()
    {
        $section = DB::table('sections')
            ->select('idSection', 'section')
            ->get();

        $quizSection = response()->json(['section' => $section]);

        return $quizSection;
    }
    public function getQuestionQuiz()
    {
        $question = DB::table('quizz_questions')
            ->select('idQuestion', 'question', 'idSection')
            ->get();

        $quizQuestion = response()->json(['question' => $question]);

        return $quizQuestion;
    }
    public function paramQuiz()
    {
        return view('formateurs.QCM.pages.paramQuiz');
    }
    public function resultatQuiz()
    {
        return view('formateurs.QCM.pages.resultatQuiz');
    }
    public function resultatDetailQuiz()
    {
        return view('formateurs.QCM.pages.resultatDetail');
    }
    public function resultatDetailPersonneQuiz()
    {
        return view('formateurs.QCM.pages.resultatDetailPersonne');
    }
    public function listeQuiz()
    {
        return view('formateurs.QCM.pages.quizList');
    }

    public function startQCM()
    {
        return view('employes.QCM.pages.startQuiz');
    }
    // Resultat Quiz Employé
    public function resultatQuizEmp()
    {
        return view('employes.QCM.pages.resultatQuiz');
    }
    public function resultatDetailQuizEmp()
    {
        return view('employes.QCM.pages.resultatDetail');
    }
    public function resultatDetailPersonneQuizEmp()
    {
        return view('employes.QCM.pages.resultatDetailPersonne');
    }

    // NEW DESIGN
    public function createQuiz()
    {
        return view('formateurs.quiz.index');
    }
    public function apercuQuiz()
    {
        return view('formateurs.quiz.apercu');
    }
    public function listQuiz()
    {
        return view('formateurs.quiz.list');
    }


    public function indexCf()
    {
        try {
            if (!Auth::check()) {
                throw new Exception('User is not authenticated.');
            }
            $userId = Auth::user()->id;

            $list = DB::table('v_cfp_forms')
                ->select('idFormateur', 'idCustomer', 'initialNameCustomer', 'customerName as name', 'customerEmail', 'description', 'logo', 'idTypeCustomer', 'isActiveFormateur', 'isActiveCfp')
                ->where('idFormateur', $userId)
                ->get();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error', ['message' => $e->getMessage()], 500]);
        }
        return view('formateurs.centreDeFormations.index', compact('list'));
    }

    public function acceptInvCf(Request $req, $idFormateur)
    {
        $req->validate([
            'idFormateur' => 'required|integer|exists:cfp_formateurs'
        ]);

        DB::table('cfp_formateurs')->where('idFormateur', '=', $idFormateur)->update([
            'isActiveFormateur' => 1
        ]);

        return back()->with('successPost', 'Succès');
    }

    //Drawer Formateur et Etp
    public function showEtpDrawer(Request $request)
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

    public function showFormDrawer(Request $request)
    {
        $formateur = DB::table('users')
            ->select('users.*', 'formateurs.idCustomer')
            ->join('formateurs', 'users.id', '=', 'formateurs.idFormateur')
            ->join('role_users', 'users.id', '=', 'role_users.user_id')
            ->where('formateurs.idCustomer', $request->idFormateur)
            ->first();
        return response()->json(['formateur' => $formateur]);
    }

    public function getIdFormateur()
    {
        $allId = [];
        // $allId = DB::select("SELECT idFormateur FROM `v_formateur_cfps` WHERE idCfp = ? GROUP BY idFormateur ", [$this->idCfp()] );       
        $allId = DB::table('v_formateur_cfps')
            ->select('idFormateur')
            ->where('idCfp', 'idCfp')
            ->groupBy('idFormateur')
            ->get();

        return $allId;
    }
    // Filtre Formateur
    public function getDropdownItem()
    {
        $status = DB::table('v_projet_form')
            ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->groupBy('project_status')
            ->orderBy('project_status', 'asc')
            ->get();


        // $etps = DB::table('v_union_projets')
        //     ->select(DB::raw('COUNT(DISTINCT(v_union_projets.idProjet)) AS projet_nb'), DB::raw('COALESCE(idEtp, idEtp_inter) AS idEtp'), DB::raw('customers.customerName AS etp_name'))
        //     ->leftJoin('entreprises', function ($join) {
        //         $join->on(DB::raw('COALESCE(v_union_projets.idEtp, v_union_projets.idEtp_inter)'), '=', 'entreprises.idCustomer');
        //     })
        //     ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
        //     ->leftJoin('project_forms', 'v_union_projets.idProjet', '=', 'project_forms.idProjet')
        //     ->leftJoin('formateurs', 'formateurs.idFormateur', '=', 'project_forms.idFormateur')
        //     ->groupBy(DB::raw('COALESCE(idEtp, idEtp_inter)'))
        //     ->orderBy('etp_name', 'asc')
        //     ->get();


        $etps = DB::table('v_projet_form')
            ->select(DB::raw('COUNT(DISTINCT(v_projet_form.idProjet)) AS projet_nb'), 'idEtp', DB::raw('customers.customerName AS etp_name'))
            ->leftJoin('entreprises', function ($join) {
                $join->on('v_projet_form.idEtp', '=', 'entreprises.idCustomer');
            })
            ->leftJoin('customers', 'entreprises.idCustomer', '=', 'customers.idCustomer')
            ->leftJoin('project_forms', 'v_projet_form.idProjet', '=', 'project_forms.idProjet')
            ->leftJoin('formateurs', 'formateurs.idFormateur', '=', 'project_forms.idFormateur')
            ->where('v_projet_form.idFormateur', auth()->user()->id)
            ->groupBy('idEtp')
            ->orderBy('etp_name', 'asc')
            ->get();

        $filteredEtps = $etps->filter(function ($etp) {
            return $etp->etp_name !== null && $etp->etp_name !== '';
        });


        //fonction pour selectionne tous les id des formateurs...        
        $queries  = $this->getIdFormateur();
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

        //ajout mois...
        $months = DB::table('v_projet_form')
            ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
            //->select('headDate', 'headMonthDebut')
            ->groupBy('headDate')
            ->orderBy('dateDebut', 'asc')
            ->where('idFormateur', auth()->user()->id)
            ->get();

        $types = DB::table('v_projet_form')
            ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->orderBy('project_type', 'asc')
            ->groupBy('project_type')
            ->get();

        $periodePrev3 = DB::table('v_projet_form')
            ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where(function ($query) {
                $query->where('idCfp', 'idCfp')
                    ->orWhere('idCfp_inter', 'idCfp_inter');
            })
            ->where('idFormateur', auth()->user()->id)
            // ->where('p_id_periode', "prev_3_month")
            ->whereRaw("p_id_periode COLLATE utf8mb4_unicode_ci = 'prev_3_month'")
            ->groupBy('p_id_periode')
            ->first();

        $periodePrev6 = DB::table('v_projet_form')
            ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"])
            ->first();

        $periodePrev12 = DB::table('v_projet_form')
            ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"])
            ->first();

        $periodeNext3 = DB::table('v_projet_form')
            ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->where('p_id_periode', "next_3_month")
            ->groupBy('p_id_periode')
            ->first();

        $periodeNext6 = DB::table('v_projet_form')
            ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->whereIn('p_id_periode', ["next_3_month", "next_6_month"])
            ->first();

        $periodeNext12 = DB::table('v_projet_form')
            ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"])
            ->first();

        $modules = DB::table('v_projet_form')
            ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->where('module_name', '!=', 'Default module')
            ->orderBy('module_name', 'asc')
            ->groupBy('idModule', 'module_name')
            ->get();

        $villes = DB::table('v_projet_form')
            ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->orderBy('ville', 'asc')
            ->groupBy('idVille', 'ville')
            ->get();

        $projetFormCount = DB::table('v_projet_form')
            ->select('idProjet', 'dateDebut', 'idEtp', 'idFormateur', 'idParticulier', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin', 'dateFin', 'module_name', 'etp_name', 'ville', 'project_status', 'project_description', 'project_type', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name')
            ->where('idFormateur', auth()->user()->id)->count();

        return response()->json([
            'status' => $status,
            'etps' => [...$filteredEtps],
            'types' => $types,
            'periodePrev3' => $periodePrev3,
            'periodePrev6' => $periodePrev6,
            'periodePrev12' => $periodePrev12,
            'periodeNext3' => $periodeNext3,
            'periodeNext6' => $periodeNext6,
            'periodeNext12' => $periodeNext12,
            'modules' => $modules,
            'villes' => $villes,
            'months' => $months,
            'projetFormCount' => $projetFormCount
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
        $idMois = explode(',', $req->idMois);

        $projectQuery = DB::table('v_projet_form')
            ->select('idProjet', 'dateDebut', 'idEtp', 'idFormateur', 'idParticulier', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin', 'dateFin', 'module_name', 'etp_name', 'ville', 'project_status', 'project_description', 'project_type', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name')
            ->where('idFormateur', auth()->user()->id);

        $status = DB::table('v_projet_form')
            ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->groupBy('project_status')
            ->orderBy('project_status', 'asc');

        $etps = DB::table('v_projet_form')
            ->select(DB::raw('COUNT(DISTINCT(idProjet)) AS projet_nb'), 'idEtp', 'etp_name')
            ->where('idFormateur', auth()->user()->id)
            ->groupBy('idEtp')
            ->orderBy('etp_name', 'asc');

        $months = DB::table('v_projet_form')
            ->select(DB::raw('MONTH(dateDebut) as idMois'), 'headDate', 'dateDebut', 'dateFin', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->groupBy('headDate')
            ->orderBy('dateDebut', 'asc')
            ->where('idFormateur', auth()->user()->id);

        $types = DB::table('v_projet_form')
            ->select('project_type', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->orderBy('project_type', 'asc')
            ->groupBy('project_type');

        $periodePrev3 = DB::table('v_projet_form')
            ->select('idProjet', 'p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where(function ($q) {
                $q->where('idCfp', 'idCfp')
                    ->orWhere('idCfp_inter', 'idCfp_inter');
            })
            ->where('idFormateur', auth()->user()->id)
            ->where('p_id_periode', "prev_3_month")
            ->whereRaw("p_id_periode COLLATE utf8mb4_unicode_ci = 'prev_3_month'")
            ->groupBy('p_id_periode');

        $periodePrev6 = DB::table('v_projet_form')
            ->select(DB::raw('"prev_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

        $periodePrev12 = DB::table('v_projet_form')
            ->select(DB::raw('"prev_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

        $periodeNext3 = DB::table('v_projet_form')
            ->select('p_id_periode', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->where('p_id_periode', "next_3_month")
            ->groupBy('p_id_periode');

        $periodeNext6 = DB::table('v_projet_form')
            ->select(DB::raw('"next_6_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);

        $periodeNext12 = DB::table('v_projet_form')
            ->select(DB::raw('"next_12_month" AS p_id_periode'), DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

        $modules = DB::table('v_projet_form')
            ->select('idModule', 'module_name', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->where('module_name', '!=', 'Default module')
            ->orderBy('module_name', 'asc')
            ->groupBy('idModule', 'module_name');

        $villes = DB::table('v_projet_form')
            ->select('idVille', 'ville', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('idFormateur', auth()->user()->id)
            ->orderBy('ville', 'asc')
            ->groupBy('idVille', 'ville');

        $projectDates = DB::table('v_projet_form')
            ->select('headDate', 'headMonthDebut')
            ->groupBy('headDate')
            ->orderBy('dateDebut', 'asc')
            ->where('idFormateur', auth()->user()->id);

        if ($idEtps[0] != null) {
            $projectQuery->whereIn('idEtp', $idEtps);
            $months = $months->whereIn('idEtp', $idEtps);
            $types = $types->whereIn('idEtp', $idEtps);
            $modules = $modules->whereIn('idEtp', $idEtps);
            $villes = $villes->whereIn('idEtp', $idEtps);
            $status = $status->whereIn('idEtp', $idEtps);
            $periodePrev3 = $periodePrev3->whereIn('idEtp', $idEtps);
            $periodePrev6 = $periodePrev6->whereIn('idEtp', $idEtps);
            $periodePrev12 = $periodePrev12->whereIn('idEtp', $idEtps);
            $periodeNext3 = $periodeNext3->whereIn('idEtp', $idEtps);
            $periodeNext6 = $periodeNext6->whereIn('idEtp', $idEtps);
            $periodeNext12 = $periodeNext12->whereIn('idEtp', $idEtps);
            $projectDates->whereIn('idEtp', $idEtps);
        }

        if ($idStatus[0] != null) {
            $projectQuery->whereIn('project_status', $idStatus);
            $etps = $etps->whereIn('project_status', $idStatus);
            $months = $months->whereIn('project_status', $idStatus);
            $types = $types->whereIn('project_status', $idStatus);
            $modules = $modules->whereIn('project_status', $idStatus);
            $villes = $villes->whereIn('project_status', $idStatus);
            $periodePrev3 = $periodePrev3->whereIn('project_status', $idStatus);
            $periodePrev6 = $periodePrev6->whereIn('project_status', $idStatus);
            $periodePrev12 = $periodePrev12->whereIn('project_status', $idStatus);
            $periodeNext3 = $periodeNext3->whereIn('project_status', $idStatus);
            $periodeNext6 = $periodeNext6->whereIn('project_status', $idStatus);
            $periodeNext12 = $periodeNext12->whereIn('project_status', $idStatus);
            $projectDates->whereIn('project_status', $idStatus);
        }

        if ($idTypes[0] != null) {
            $projectQuery->whereIn('project_type', $idTypes);
            $etps = $etps->whereIn('project_type', $idTypes);
            $months = $months->whereIn('project_type', $idTypes);
            $modules = $modules->whereIn('project_type', $idTypes);
            $villes = $villes->whereIn('project_type', $idTypes);
            $status = $status->whereIn('project_type', $idTypes);
            $periodePrev3 = $periodePrev3->whereIn('project_type', $idTypes);
            $periodePrev6 = $periodePrev6->whereIn('project_type', $idTypes);
            $periodePrev12 = $periodePrev12->whereIn('project_type', $idTypes);
            $periodeNext3 = $periodeNext3->whereIn('project_type', $idTypes);
            $periodeNext6 = $periodeNext6->whereIn('project_type', $idTypes);
            $periodeNext12 = $periodeNext12->whereIn('project_type', $idTypes);
            $projectDates->whereIn('project_type', $idTypes);
        }

        if ($idModules[0] != null) {
            $projectQuery->whereIn('idModule', $idModules);
            $etps = $etps->whereIn('idModule', $idModules);
            $months = $months->whereIn('idModule', $idModules);
            $types = $types->whereIn('idModule', $idModules);
            $villes = $villes->whereIn('idModule', $idModules);
            $status = $status->whereIn('idModule', $idModules);
            $periodePrev3 = $periodePrev3->whereIn('idModule', $idModules);
            $periodePrev6 = $periodePrev6->whereIn('idModule', $idModules);
            $periodePrev12 = $periodePrev12->whereIn('idModule', $idModules);
            $periodeNext3 = $periodeNext3->whereIn('idModule', $idModules);
            $periodeNext6 = $periodeNext6->whereIn('idModule', $idModules);
            $periodeNext12 = $periodeNext12->whereIn('idModule', $idModules);
            $projectDates->whereIn('idModule', $idModules);
        }

        if ($idPeriodes != null) {
            switch ($idPeriodes) {
                case 'prev_3_month':
                    $projectQuery->where('p_id_periode', $idPeriodes);

                    $projectDates->where('p_id_periode', $idPeriodes);

                    break;
                case 'prev_6_month':
                    $projectQuery->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                    $projectDates->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                    break;
                case 'prev_12_month':
                    $projectQuery->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                    $projectDates->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                    break;
                case 'next_3_month':
                    $projectQuery->where('p_id_periode', $idPeriodes);

                    $projectDates->whereIn('p_id_periode', $idPeriodes);

                    break;
                case 'next_6_month':
                    $projectQuery->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);

                    $projectDates->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);
                    break;
                case 'next_12_month':
                    $projectQuery->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                    $projectDates->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);
                    break;

                default:
                    $projectQuery->where('p_id_periode', $idPeriodes);

                    $projectDates->where('p_id_periode', $idPeriodes);

                    break;
            }

            $etps = $etps->where('p_id_periode', $idPeriodes);
            $months = $months->where('p_id_periode', $idPeriodes);
            $types = $types->where('p_id_periode', $idPeriodes);
            $villes = $villes->where('p_id_periode', $idPeriodes);
            $status = $status->where('p_id_periode', $idPeriodes);
            $modules = $modules->where('p_id_periode', $idPeriodes);
        }

        if ($idVilles[0] != null) {
            $projectQuery->whereIn('idVille', $idVilles);
            $etps = $etps->whereIn('idVille', $idVilles);
            $modules = $modules->whereIn('idVille', $idVilles);
            $months = $months->whereIn('idVille', $idVilles);
            $types = $types->whereIn('idVille', $idVilles);
            $status = $status->whereIn('idVille', $idVilles);
            $periodePrev3 = $periodePrev3->whereIn('idVille', $idVilles);
            $periodePrev6 = $periodePrev6->whereIn('idVille', $idVilles);
            $periodePrev12 = $periodePrev12->whereIn('idVille', $idVilles);
            $periodeNext3 = $periodeNext3->whereIn('idVille', $idVilles);
            $periodeNext6 = $periodeNext6->whereIn('idVille', $idVilles);
            $periodeNext12 = $periodeNext12->whereIn('idVille', $idVilles);
            $projectDates->whereIn('idVille', $idVilles);
        }

        if ($idMois[0] != null) {
            $projectQuery->whereIn(DB::raw('MONTH(dateDebut)'), $idMois);
            $etps = $etps->whereIn(DB::raw('MONTH(dateDebut)'), $idMois);
            $villes = $villes->whereIn(DB::raw('MONTH(dateDebut)'), $idMois);
            $modules = $modules->whereIn(DB::raw('MONTH(dateDebut)'), $idMois);
            $types = $types->whereIn(DB::raw('MONTH(dateDebut)'), $idMois);
            $status = $status->whereIn(DB::raw('MONTH(dateDebut)'), $idMois);
            $periodePrev3 = $periodePrev3->whereIn(DB::raw('MONTH(dateDebut)'), $idMois);
            $periodePrev6 = $periodePrev6->whereIn(DB::raw('MONTH(dateDebut)'), $idMois);
            $periodePrev12 = $periodePrev12->whereIn(DB::raw('MONTH(dateDebut)'), $idMois);
            $periodeNext3 = $periodeNext3->whereIn(DB::raw('MONTH(dateDebut)'), $idMois);
            $periodeNext6 = $periodeNext6->whereIn(DB::raw('MONTH(dateDebut)'), $idMois);
            $periodeNext12 = $periodeNext12->whereIn(DB::raw('MONTH(dateDebut)'), $idMois);
            $projectDates->whereIn(DB::raw('MONTH(dateDebut)'), $idMois);
        }

        $etps = $etps->get();
        $months = $months->get();
        $villes = $villes->get();
        $modules = $modules->get();
        $types = $types->get();
        $status = $status->get();
        $periodePrev3 = $periodePrev3->first();
        $periodePrev6 = $periodePrev6->first();
        $periodePrev12 = $periodePrev12->first();
        $periodeNext3 = $periodeNext3->first();
        $periodeNext6 = $periodeNext6->first();
        $periodeNext12 = $periodeNext12->first();
        $dateProjects = $projectDates->get();

        $projects = $projectQuery->get();
        $projets = [];
        foreach ($projects as $projet) {
            $projets[] = [
                'seanceCount' => $this->getSessionProject($projet->idProjet),
                'formateurs' => $this->getFormProject($projet->idProjet),
                'apprCount' => $this->getApprenantProject($projet->idProjet),
                'totalSessionHour' => $this->getSessionHour($projet->idProjet),
                'general_note' => $this->getNote($projet->idProjet),
                'partCount' => $this->getParticulierProject($projet->idProjet, $projet->idCfp_inter),
                'idFormateur' => $projet->idFormateur,
                'idProjet' => $projet->idProjet,
                'dateDebut' => $projet->dateDebut,
                'dateFin' => $projet->dateFin,
                'module_name' => $projet->module_name,
                'modalite' => $projet->modalite,
                'etp_name' => $this->getEtpProjectInter($projet->idProjet, $projet->idCfp_inter),
                'ville' => $projet->ville,
                'project_status' => $projet->project_status,
                'project_description' => $projet->project_description,
                'project_type' => $projet->project_type,
                'headDate' => $projet->headDate,
                'module_image' => $projet->module_image,
                'etp_logo' => $projet->etp_logo,
                'etp_initial_name' => $projet->etp_initial_name,
                'salle_name' => $projet->salle_name,
                'salle_quartier' => $projet->salle_quartier,
                'salle_code_postal' => $projet->salle_code_postal,
                'ville' => $projet->ville,
                'headYear' => $projet->headYear,
                'headMonthDebut' => $projet->headMonthDebut,
                'headMonthFin' => $projet->headMonthFin,
                'headDayDebut' => $projet->headDayDebut,
                'headDayFin' => $projet->headDayFin,
                'project_description' => $projet->project_description,
                'totalSessionHour' => $this->getSessionHour($projet->idProjet),
                'general_note' => $this->getNote($projet->idProjet),
                'idModule' => $projet->idModule,
                'restaurations' => $this->getRestauration($projet->idProjet),
                'apprs' => $this->getApprListProjet($projet->idProjet)
            ];
        }

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
            'months' => $months,
            'projectDates' => $dateProjects,
            'projetFormCount' => $projects->count()
        ]);
    }

    public function filterItem(Request $req)
    {
        $idStatus = explode(',', $req->idStatut);
        $idEtps = explode(',', $req->idEtp);
        $idTypes = explode(',', $req->idType);
        $idPeriodes = $req->idPeriode;
        $idModules = explode(',', $req->idModule);
        $idVilles = explode(',', $req->idVille);
        $idMois = explode(',', $req->idMois);

        $projectQuery = DB::table('v_projet_form')
            ->select('idProjet', 'dateDebut', 'idEtp', 'idFormateur', 'idParticulier', 'headYear', 'headMonthDebut', 'headMonthFin', 'headDayDebut', 'headDayFin', 'dateFin', 'module_name', 'etp_name', 'ville', 'project_status', 'project_description', 'project_type', DB::raw('DATE_FORMAT(dateDebut, "%M, %Y") AS headDate'), 'module_image', 'etp_logo', 'etp_initial_name', 'salle_name', 'salle_quartier', 'salle_code_postal', 'ville', 'idCfp_inter', 'modalite', 'total_ht', 'total_ttc', 'idModule', 'project_inter_privacy', 'sub_name', 'idSubContractor', 'idCfp', 'cfp_name')
            ->where('idFormateur', auth()->user()->id);

        $projectDates = DB::table('v_projet_form')
            ->select('headDate', 'headMonthDebut')
            ->groupBy('headDate')
            ->orderBy('dateDebut', 'asc')
            ->where('idFormateur', auth()->user()->id);

        if ($idEtps[0] != null) {
            $projectQuery->whereIn('idEtp', $idEtps);
            $projectDates->whereIn('idEtp', $idEtps);
        }

        if ($idStatus[0] != null) {
            $projectQuery->whereIn('project_status', $idStatus);
            $projectDates->whereIn('project_status', $idStatus);
        }

        if ($idTypes[0] != null) {
            $projectQuery->whereIn('project_type', $idTypes);
            $projectDates->whereIn('project_type', $idTypes);
        }

        if ($idModules[0] != null) {
            $projectQuery->whereIn('idModule', $idModules);
            $projectDates->whereIn('idModule', $idModules);
        }

        if ($idPeriodes != null) {
            switch ($idPeriodes) {
                case 'prev_3_month':
                    $projectQuery->where('p_id_periode', $idPeriodes);

                    $projectDates->where('p_id_periode', $idPeriodes);

                    break;
                case 'prev_6_month':
                    $projectQuery->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                    $projectDates->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                    break;
                case 'prev_12_month':
                    $projectQuery->whereIn('p_id_periode', ["prev_3_month", "prev_6_month", "prev_12_month"]);

                    $projectDates->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                    break;
                case 'next_3_month':
                    $projectQuery->where('p_id_periode', $idPeriodes);

                    $projectDates->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                    break;
                case 'next_6_month':
                    $projectQuery->whereIn('p_id_periode', ["next_3_month", "next_6_month"]);

                    $projectDates->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                    break;
                case 'next_12_month':
                    $projectQuery->whereIn('p_id_periode', ["next_3_month", "next_6_month", "next_12_month"]);

                    $projectDates->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);
                    break;

                default:
                    $projectQuery->where('p_id_periode', $idPeriodes);

                    $projectDates->whereIn('p_id_periode', ["prev_3_month", "prev_6_month"]);

                    break;
            }
        }

        if ($idVilles[0] != null) {
            $projectQuery->whereIn('idVille', $idVilles);
            $projectDates->whereIn('idVille', $idVilles);
        }

        if ($idMois[0] != null) {
            $projectQuery->whereIn(DB::raw('MONTH(dateDebut)'), $idMois);
            $projectDates->whereIn(DB::raw('MONTH(dateDebut)'), $idMois);
        }

        $dateProjects = $projectDates->get();

        $projects = $projectQuery->get();
        $projets = [];
        foreach ($projects as $projet) {
            $projets[] = [
                'seanceCount' => $this->getSessionProject($projet->idProjet),
                'formateurs' => $this->getFormProject($projet->idProjet),
                'apprCount' => $this->getApprenantProject($projet->idProjet),
                'totalSessionHour' => $this->getSessionHour($projet->idProjet),
                'general_note' => $this->getNote($projet->idProjet),
                'partCount' => $this->getParticulierProject($projet->idProjet, $projet->idCfp_inter),
                'idFormateur' => $projet->idFormateur,
                'idProjet' => $projet->idProjet,
                'dateDebut' => $projet->dateDebut,
                'dateFin' => $projet->dateFin,
                'module_name' => $projet->module_name,
                'modalite' => $projet->modalite,
                'etp_name' => $this->getEtpProjectInter($projet->idProjet, $projet->idCfp_inter),
                'ville' => $projet->ville,
                'project_status' => $projet->project_status,
                'project_description' => $projet->project_description,
                'project_type' => $projet->project_type,
                'headDate' => $projet->headDate,
                'module_image' => $projet->module_image,
                'etp_logo' => $projet->etp_logo,
                'etp_initial_name' => $projet->etp_initial_name,
                'salle_name' => $projet->salle_name,
                'salle_quartier' => $projet->salle_quartier,
                'salle_code_postal' => $projet->salle_code_postal,
                'ville' => $projet->ville,
                'headYear' => $projet->headYear,
                'headMonthDebut' => $projet->headMonthDebut,
                'headMonthFin' => $projet->headMonthFin,
                'headDayDebut' => $projet->headDayDebut,
                'headDayFin' => $projet->headDayFin,
                'project_description' => $projet->project_description,
                'totalSessionHour' => $this->getSessionHour($projet->idProjet),
                'general_note' => $this->getNote($projet->idProjet),
                'idModule' => $projet->idModule,
                'restaurations' => $this->getRestauration($projet->idProjet),
                'apprs' => $this->getApprListProjet($projet->idProjet)
            ];
        }

        return response()->json([
            'projets' => $projets,
            'projectDates' => $dateProjects,
            'projetFormCount' => $projects->count()
        ]);
    }
    public function confirm($idProjet)
    {
        try {
            DB::table('projets')->where('idProjet', $idProjet)->update([
                'project_is_active' => 1,
                'project_is_reserved' => 0,
                'project_is_repported' => 0,
                'project_is_trashed' => 0,
                'project_is_cancelled' => 0,
                'project_is_closed' => 0,
            ]);

            return response()->json(['success' => 'Succès']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur inconnue']);
        }
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


    public function confidentialite()
    {
        return view('formateurs.politiques.confidentialite');
    }

    public function condition()
    {
        return view('formateurs.politiques.condition');
    }

    // Suppression formateur par cfp
    public function hardDelete($id)
    {
        $query = DB::table('users')->where('id', $id);

        if ($query->first()) {
            $chekProject = DB::table('project_forms')->where('idFormateur', $id)->count();

            if ($chekProject <= 0) {
                DB::transaction(function () use ($query, $id) {
                    DB::table('cfp_formateurs')->where('idFormateur', $id)->where('idCfp', Customer::idCustomer())->delete();
                    DB::table('formateurs')->where('idFormateur', $id)->delete();
                    DB::table('forms')->where('idFormateur', $id)->delete();
                    $query->delete();
                });
                return back()->with('success', 'Formateur supprimé avec succès !');
            } else
                return back()->with('error', 'Suppression impossible !');
        } else
            return back()->with('error', 'Formateur introuvable !');
    }

    // Ajout apprenant avy ao @ getIdFormateur
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
                    'idEmploye' => $idApprenant
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
                'idEmploye' => $idApprenant
            ]);

            return response()->json(['success' => 'Apprenant ajouté avec succès']);
        } elseif (count($checkAppr) >= 1 && count($check) >= 1) {
            return response()->json(['error' => 'Employée déjà inscrit à la session']);
        }
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
            return response()->json(['success' => 'Succès']);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Erreur inconnue !']);
        }
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
            return redirect()->route('projetForms.detailForm', ['idProjet' => $idProjet]);
        }
        return view('formateurs.projets.photo_momentum', compact('images', 'idProjet'));
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

            return back()->with('success', 'Photos téléchargées avec succs');
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
}
