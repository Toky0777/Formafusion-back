<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AgendaCfpController extends Controller
{
    public function index()
    {
        $mois = [
            1 => 'Janvier',
            2 => 'Février',
            3 => 'Mars',
            4 => 'Avril',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juillet',
            8 => 'Août',
            9 => 'Septembre',
            10 => 'Octobre',
            11 => 'Novembre',
            12 => 'Décembre'
        ];

        $idCustomer = Customer::idCustomer();

        $year = date('Y'); // Date en cours...

        $status = DB::table('v_seances')
            ->select('project_status', DB::raw('COUNT(v_seances.idProjet) AS projet_nb'))

            ->leftJoin('project_sub_contracts AS psc', 'v_seances.idProjet', '=', 'psc.idProjet')
            ->leftJoin('customers AS sub', 'psc.idSubContractor', '=', 'sub.idCustomer')
            ->leftJoin('customers AS ce', 'v_seances.idCfp', '=', 'ce.idCustomer')

            ->where('project_is_trashed', 0)

            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->where('project_is_trashed', 0)
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            ->whereYear('dateSeance', $year)
            ->groupBy('project_status')
            ->orderBy('project_status', 'asc')
            ->get();

        $seanceCount = DB::table('v_seances')

            ->leftJoin('project_sub_contracts AS psc', 'v_seances.idProjet', '=', 'psc.idProjet')
            ->leftJoin('customers AS sub', 'psc.idSubContractor', '=', 'sub.idCustomer')
            ->leftJoin('customers AS ce', 'v_seances.idCfp', '=', 'ce.idCustomer')

            ->where('project_is_trashed', 0)

            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->where('project_is_trashed', 0)
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            ->whereYear('dateSeance', $year)
            ->count();

        $formateurs = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'name', 'firstName')
            ->where('idCfp', Customer::idCustomer())
            ->groupBy('idFormateur')
            ->get();

        return response()->json([
            'idCustomer' => $idCustomer,
            'status' => $status,
            'seanceCount' => $seanceCount,
            'formateurs' => $formateurs,
            'mois' => $mois
        ]);
    }

    public function getEventsGroupBy() // <=== Sélectionne tous les évenements(séances) en fonction du customer connecté dont les séances sont groupé par module pour chaque journée pour annuels...
    {
        $seances = DB::table('v_seances')
            ->select(
                'idSeance',
                'dateSeance',
                'idTypeProjet',
                'id_google_seance',
                'idDossier',
                'heureDebut',
                'heureFin',
                'idSalle',
                'etp_name',
                'idEtp',
                'v_seances.idProjet AS idProjet',
                'project_status',
                'project_reference',
                'salle_name',
                'salle_quartier',
                'project_title',
                'project_description',
                'idModule',
                'module_name',
                'module_image',
                'modalite',
                'vcd.ville_name',
                'vcd.vi_code_postal',
                'ce.customerName AS nameCfp',
                DB::raw('COUNT(*) AS nb_seances'),
                DB::raw("GROUP_CONCAT( dateSeance, 'T', heureDebut) AS seance_start"),
                DB::raw("GROUP_CONCAT( dateSeance, 'T', heureFin) AS seance_end"),
            )

            ->leftJoin('project_sub_contracts AS psc', 'v_seances.idProjet', '=', 'psc.idProjet')
            ->leftJoin('customers AS sub', 'psc.idSubContractor', '=', 'sub.idCustomer')
            ->leftJoin('customers AS ce', 'v_seances.idCfp', '=', 'ce.idCustomer')
            ->leftJoin('lieux as li', 'li.idLieu', '=', 'v_seances.idLieu')
            ->leftJoin('ville_codeds as vcd', 'vcd.id', '=', 'li.idVilleCoded')

            ->where(function ($query) {
                $query
                    ->where('idCfp', Customer::idCustomer())
                    ->orWhere('psc.idSubContractor', Customer::idCustomer());
            })

            ->groupBy(DB::raw('DATE(dateSeance)'), 'idModule', 'module_name', 'modalite')
            ->orderByRaw('DATE(dateSeance)')
            ->where('project_is_trashed', 0)
            ->get();

        if (count($seances) > 0) {
            foreach ($seances as $seance) {
                $start = explode(',', $seance->seance_start);
                $endParts = explode(';', $seance->seance_end);
                $endString = implode(',', $endParts);
                $end = explode(',', $endString);

                $events[] =  [
                    'idSeance' => $seance->idSeance,
                    'idProjet' => $seance->idProjet,
                    'idCfp' => Customer::idCustomer(),
                    'idEtp' => $seance->idEtp,
                    'start' => $start[0],
                    'end' => isset($end[0]) ? end($end) : null,
                    'idSalle' => $seance->idSalle,
                    'idModule' => $seance->idModule,
                    'text' => $seance->project_title,
                    'description' => $seance->project_description,
                    'idCalendar' => $seance->id_google_seance,      //id reliant à Google calendar
                    'salle' => $seance->salle_name,
                    'module' => $seance->module_name,
                    'ville' => $seance->ville_name,
                    'imgModule' => $seance->module_image,
                    'nameEtp' => $seance->etp_name,
                    'statutProjet' => $seance->project_status,
                    'codePostal' => $seance->vi_code_postal,
                    'reference' => $seance->project_reference,
                    'quartier' => $seance->salle_quartier,
                    'modalite' => $seance->modalite,
                    'nb_seances' =>  $seance->nb_seances,
                    'nameCfp' => $seance->nameCfp,
                    'backColor' => $seance->id_google_seance ? "rgba(204, 229, 244, 0.63)" : "rgba(246, 241, 216, 0.5)",
                ];
            }

            return response()->json([
                'status' => 200,
                'seances' => $events
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }
    }

    public function getEvents() // <=== Sélectionne tous les évenements(séances) en fonction du customer connecté pour formateur...
    {
        // $date = 2025;
        $seances = DB::table('v_seances')

            ->select(
                'idSeance',
                'dateSeance',
                'etp_name',
                'idEtp',
                'module_image',
                'idTypeProjet',
                'id_google_seance',
                'idDossier',
                'heureDebut',
                'heureFin',
                'idSalle',
                'v_seances.idProjet AS idProjet',
                'project_status',
                'project_reference',
                'salle_name',
                'salle_quartier',
                'project_title',
                'project_description',
                'idModule',
                'module_name',
                'modalite',
                'vcd.ville_name',
                'vcd.vi_code_postal',
                'ce.customerName AS nameCfp',
            )

            ->leftJoin('project_sub_contracts AS psc', 'v_seances.idProjet', '=', 'psc.idProjet')
            ->leftJoin('customers AS sub', 'psc.idSubContractor', '=', 'sub.idCustomer')
            ->leftJoin('customers AS ce', 'v_seances.idCfp', '=', 'ce.idCustomer')
            ->leftJoin('lieux as li', 'li.idLieu', '=', 'v_seances.idLieu')
            ->leftJoin('ville_codeds as vcd', 'vcd.id', '=', 'li.idVilleCoded')
            ->where(function ($query) {
                $query
                    ->where('idCfp', Customer::idCustomer())
                    ->orWhere('psc.idSubContractor', Customer::idCustomer());
            })


            ->where('project_is_trashed', 0)
            ->get();

        if (count($seances) > 0) {
            foreach ($seances as $seance) {
                $events[] =  [
                    'idSeance' => $seance->idSeance,
                    'idProjet' => $seance->idProjet,
                    'idCfp' => Customer::idCustomer(),
                    'idEtp' => $seance->idEtp,
                    'end' => $seance->dateSeance . "T" . $seance->heureFin,
                    'start' => $seance->dateSeance . "T" . $seance->heureDebut,
                    'idSalle' => $seance->idSalle,
                    'idModule' => $seance->idModule,
                    'text' => $seance->project_title,
                    'description' => $seance->project_description,
                    'idCalendar' => $seance->id_google_seance,      //id reliant à Google calendar
                    'salle' => $seance->salle_name,
                    'module' => $seance->module_name,
                    'ville' => $seance->ville_name,
                    'imgModule' => $seance->module_image,
                    'nameEtp' => $seance->etp_name,
                    'codePostal' => $seance->vi_code_postal,
                    'statutProjet' => $seance->project_status,
                    'reference' => $seance->project_reference,
                    'idFormateur' => $this->getIdFormProject($seance->idProjet),
                    'quartier' => $seance->salle_quartier,
                    'modalite' => $seance->modalite,
                    'nameCfp' => $seance->nameCfp,
                ];
            }

            return response()->json([
                'status' => 200,
                'seances' => $events
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }
    }
    public function getEventsSemaine() // <=== Sélectionne tous les évenements(séances) en fonction du customer connecté pour semaine...
    {
        // $date = 2025;
        $seances = DB::table('v_seances')

            ->select(
                'idSeance',
                'dateSeance',
                'etp_name',
                'idEtp',
                'module_image',
                'idTypeProjet',
                'id_google_seance',
                'idDossier',
                'heureDebut',
                'heureFin',
                'idSalle',
                'v_seances.idProjet AS idProjet',
                'project_status',
                'project_reference',
                'salle_name',
                'salle_quartier',
                'project_title',
                'project_description',
                'idModule',
                'module_name',
                'modalite',
                'vcd.ville_name',
                'vcd.vi_code_postal',
                'ce.customerName AS nameCfp',
            )

            ->leftJoin('project_sub_contracts AS psc', 'v_seances.idProjet', '=', 'psc.idProjet')
            ->leftJoin('customers AS sub', 'psc.idSubContractor', '=', 'sub.idCustomer')
            ->leftJoin('customers AS ce', 'v_seances.idCfp', '=', 'ce.idCustomer')
            ->leftJoin('lieux as li', 'li.idLieu', '=', 'v_seances.idLieu')
            ->leftJoin('ville_codeds as vcd', 'vcd.id', '=', 'li.idVilleCoded')
            ->where(function ($query) {
                $query
                    ->where('idCfp', Customer::idCustomer())
                    ->orWhere('psc.idSubContractor', Customer::idCustomer());
            })


            ->where('project_is_trashed', 0)
            ->get();

        if (count($seances) > 0) {
            foreach ($seances as $seance) {
                $events[] =  [
                    'idSeance' => $seance->idSeance,
                    'idProjet' => $seance->idProjet,
                    'idCfp' => Customer::idCustomer(),
                    'idEtp' => $seance->idEtp,
                    'end' => $seance->dateSeance . "T" . $seance->heureFin,
                    'start' => $seance->dateSeance . "T" . $seance->heureDebut,
                    'idSalle' => $seance->idSalle,
                    'idModule' => $seance->idModule,
                    'text' => $seance->project_title,
                    'description' => $seance->project_description,
                    'idCalendar' => $seance->id_google_seance,      //id reliant à Google calendar
                    'salle' => $seance->salle_name,
                    'module' => $seance->module_name,
                    'ville' => $seance->ville_name,
                    'imgModule' => $seance->module_image,
                    'nameEtp' => $seance->etp_name,
                    'codePostal' => $seance->vi_code_postal,
                    'idFormateur' => $this->getIdFormProject($seance->idProjet),
                    'statutProjet' => $seance->project_status,
                    'reference' => $seance->project_reference,
                    'quartier' => $seance->salle_quartier,
                    'modalite' => $seance->modalite,
                    'nameCfp' => $seance->nameCfp,
                ];
            }

            return response()->json([
                'status' => 200,
                'seances' => $events
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }
    }


    private function getPrestation($idModule)
    {
        $materiel = DB::table('prestation_modules')
            ->select('prestation_name')
            ->where('idModule', $idModule)
            ->get();
        return $materiel->toArray();
    }

    public function getIdFormateurByProject($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->where('idProjet', $idProjet)
            ->distinct()
            ->pluck('idFormateur');
        return response()->json([
            'FormateursId' => $forms
        ]);
    }

    private function getIdFormProject($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->where('idProjet', $idProjet)
            ->distinct()
            ->pluck('idFormateur');
        return $forms;
    }

    public function getConfigApi()
    {
        return response()->json([
            'googleConfig' => [
                'CLIENT_ID' => config('services.google.client_id'),
                'API_KEY' => config('services.google.api_key'),
                'DISCOVERY_DOC' => config('services.google.discovery_doc'),
                'SCOPES' => config('services.google.scopes'),
            ]
        ]);
    }

    public function countSeance($month, $year)
    {
        $countSeance = DB::select("SELECT COUNT(idSeance) AS nbSeance FROM v_union_seanceCfps WHERE MONTH(dateSeance) = ? AND YEAR(dateSeance) = ?", [$month, $year]);

        return response()->json($countSeance);
    }

    public function getRessource($idSession)
    {
        $ressources = DB::select("SELECT idSession, idCfp, nomRessource, number FROM v_union_ressources WHERE idCfp = ? AND idSession = ?", [Customer::idCustomer(), $idSession]);
        $newRessource = [];

        foreach ($ressources as $res) {
            $newRessource[] =  $res->nomRessource;
        }
        return $newRessource;
    }

    public function getApprenant($idSession)
    {
        $countApprs = DB::select('SELECT COUNT(idEmploye) AS nombreAppr FROM detail_apprenants WHERE idSession = ?', [$idSession]);

        foreach ($countApprs as $appr) {
            $newCountAppr = $appr->nombreAppr;
        }
        return $newCountAppr;
    }

    public function getFormProject($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name', 'email')
            ->groupBy('idFormateur', 'name', 'firstName', 'photoForm', 'initialNameForm')
            ->where('idProjet', $idProjet)->get();

        return $forms->toArray();
    }

    public function getApprenantProjectIntra($idProjet)
    {
        $apprs = DB::table('v_list_apprenants')
            ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_photo', 'emp_matricule', 'emp_phone', 'etp_name')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();

        return count($apprs);
    }
    public function getApprenantProjectInter($idProjet)
    {

        $apprs = DB::table('v_list_apprenant_inter_added')
            ->select('*')
            ->where('idProjet', $idProjet)
            ->get();

        return count($apprs);
    }

    public function getFieldsProject($idProjet)
    {

        $projet = DB::table('v_projet_cfps')
            ->select('idProjet', 'dateDebut', 'dateFin', 'project_title', 'etp_name', 'ville', 'project_status', 'project_type', 'module_image', 'paiement', 'project_reference', 'modalite', 'idEtp')
            ->where('idProjet', $idProjet)
            ->first();
        return $projet;
    }
    public function getEtpProjectInter($idProjet, $idCfp_inter)
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
        return $etp->toArray();
    }


    public function setStatut($year)
    {

        $status = DB::table('v_seances')
            ->select('project_status', DB::raw('COUNT(v_seances.idProjet) AS projet_nb'))

            ->leftJoin('project_sub_contracts AS psc', 'v_seances.idProjet', '=', 'psc.idProjet')
            ->leftJoin('customers AS sub', 'psc.idSubContractor', '=', 'sub.idCustomer')
            ->leftJoin('customers AS ce', 'v_seances.idCfp', '=', 'ce.idCustomer')

            ->where('project_is_trashed', 0)

            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->where('project_is_trashed', 0)
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            ->whereYear('dateSeance', $year)
            ->groupBy('project_status')
            ->orderBy('project_status', 'asc')
            ->get();

        $seanceCount = DB::table('v_seances')

            ->leftJoin('project_sub_contracts AS psc', 'v_seances.idProjet', '=', 'psc.idProjet')
            ->leftJoin('customers AS sub', 'psc.idSubContractor', '=', 'sub.idCustomer')
            ->leftJoin('customers AS ce', 'v_seances.idCfp', '=', 'ce.idCustomer')

            ->where('project_is_trashed', 0)

            ->where(function ($query) {
                $query->where('idCfp', Customer::idCustomer())
                    ->where('project_is_trashed', 0)
                    ->orWhere('idSubContractor', Customer::idCustomer());
            })
            ->whereYear('dateSeance', $year)
            ->count();

        return response()->json([
            'status'     => $status,
            'seanceCount' => $seanceCount,

        ]);
    }

    public function listProjetForms(Request $req)
    {
        $idFormateur = explode(',', $req->idFormateur);

        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);

        $projetForms = DB::table('v_union_seanceCfps')
            ->select('idProjet', 'idSession', 'projectName', 'moduleName', 'dateSeance', 'heureDebut', 'heureFin', 'ville', 'salle', 'nameForm', 'firstNameForm', 'etpName as customerName', 'type', 'paiement', 'moduleName', 'sessionName')
            ->where('idCfp', $customer[0]->idCustomer)
            ->whereIn('idFormateur', $idFormateur)
            ->get();

        $events = [];
        foreach ($projetForms as $p) {
            $events[] = [
                'idProjet' => $p->idProjet,
                'idSession' => $p->idSession,
                'title' => $p->projectName . " - " . $p->moduleName,
                'start' => $p->dateSeance . "T" . $p->heureDebut,
                'end' => $p->dateSeance . "T" . $p->heureFin,
                'heureDebut' => $p->heureDebut,
                'heureFin' => $p->heureFin,
                'ville' => $p->ville,
                'salle' => $p->salle,
                'nameForm' => $p->nameForm,
                'firstNameForm' => $p->firstNameForm,
                'customerName' => $p->customerName,
                'type' => $p->type,
                'paiement' => $p->paiement,
                'sessionName' => $p->sessionName
            ];
        }

        return response()->json($events);
    }

    public function getEventResources()
    {
        $allFormateur = [];
        //$formateurs = DB::select("SELECT idFormateur, firstName FROM v_formateur_cfps WHERE  idCfp = ? GROUP BY idFormateur ", [Customer::idCustomer()]);
        $formateurs = Db::table('v_formateur_cfps')
            ->select('idFormateur', 'firstName')
            ->where('idCfp', Customer::idCustomer())
            ->groupBy('idFormateur')
            ->get();
        foreach ($formateurs as $formateur) {
            $allFormateur[] =  [
                'id'    => 'FM_' . $formateur->idFormateur,
                'name'  => $formateur->firstName
            ];
        }
        $salles = [];
        /* $salles = DB::select(
            "SELECT idSalle, salle_name  FROM salles  INNER JOIN customers 
        ON customers.idCustomer = salles.idCustomer 
        WHERE customers.idCUstomer = ? AND salle_name != ?",
            [Customer::idCustomer(), "In situ"]

        );*/
        $salleInSitus = [];
        /*$salleInSitus = DB::table('customers')
            ->join('salles', 'salles.idCustomer', '=', 'customers.idCustomer')
            ->select('salles.idSalle', 'salles.idCustomer', 'salles.salle_name', 'customers.customerName as customer_name')
            ->where('salles.salle_name', 'In situ')
            //->where('salles.idCustomer', 29)
            ->get();*/

        // dd($salleInSitus);
        $allSalle = [];
        foreach ($salles as $salle) {
            $allSalle[] =  [
                'id'    => 'SL_' . $salle->idSalle,
                'name'  => $salle->salle_name
            ];
        }

        foreach ($salleInSitus as $salle) {
            $allSalleInSitu[] =  [
                'id'    => 'IS_' . $salle->idCustomer,
                'name'  => $salle->customer_name
            ];
        }

        //dd($allSalleInSitu, $allSalle);

        $modules = DB::table('v_module_cfps')
            ->select('idModule', 'moduleName')
            ->where('moduleName', '!=', 'Default module')
            ->where('idCustomer', Customer::idCustomer())
            ->get();
        $allModule = [];
        foreach ($modules as $mdl) {
            $allModule[] = [
                'id'    => 'MD_' . $mdl->idModule,
                'name'  => $mdl->moduleName,
            ];
        }

        $etps = DB::select("SELECT idEtp, etp_name  FROM v_collaboration_cfp_etps WHERE idCfp = ?", [Customer::idCustomer()]);

        // $etps = DB::table('v_collaboration_cfp_etps_seances')
        //     ->select('idEtp', 'etp_name')
        //     ->where('idProjet', '!=', 'null')
        //     ->where('idSeance', '!=', 'null')
        //     ->where('idCfp', Customer::idCustomer())
        //     ->groupBy('idEtp')
        //     ->get();

        $allEtp = [];
        foreach ($etps as $etp) {
            $allEtp[] = [
                'id'    => 'ETP_' . $etp->idEtp,
                'name'  => $etp->etp_name,
            ];
        }


        return response()->json([
            // 'projets'     => $allproject,
            'formateurs'    => $allFormateur,
            //'salles'        => $allSalle,
            //'salleInSitus'  => $allSalleInSitu,
            'modules'       => $allModule,
            'etps'          => $allEtp,
            //'materiels'   => $materiels,
        ]);
    }

    public function updateEventForms(Request $request)
    {
        try {
            DB::beginTransaction();

            DB::table('project_forms')
                ->where('idProjet', $request->idProjet)->update([
                    'idFormateur' => $request->idFormateur
                ]);

            DB::table('seances')
                ->where('idSeance', $request->idSeance)->update([
                    'dateSeance' => Carbon::createFromFormat('Y-m-d', $request->date)->toIso8601String(),
                    //'heureDebut'=>$request->start,
                    //'heureFin'=>  $request->end,
                    //'idProjet' => $request->idProjet,
                ]);

            DB::commit();

            return response()->json([
                'idProjet' => $request->idProjet,
                'dateSeance' => $request->date,
                'heureDebut' => $request->start,
                'heureFin' =>  $request->end,
                'status' => 200
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    //AgendaMonth
    // public function monthAgenda()
    // {
    //     return view('CFP.Agendas.month');
    // }

    // public function add()
    // {
    //     return view('CFP.Agendas.addSession');
    // }
}
