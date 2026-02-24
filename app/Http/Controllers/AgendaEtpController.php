<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View as ViewView;

class AgendaEtpController extends Controller
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

        $status = DB::table('v_seances_etp')
            ->select('project_status', DB::raw('COUNT(idProjet) AS projet_nb'))
            ->where('project_is_trashed', 0)
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_intra', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(function ($query) {
                        $query->whereIn('project_type', ['Intra', 'Inter'])
                            ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                    });
            })

            //
            ->groupBy('project_status')
            ->orderBy('project_status', 'asc')
            ->get();

        $seanceCount = DB::table('v_seances_etp')
            ->select('idSeance')
            ->where('project_is_trashed', 0)
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_intra', Customer::idCustomer())
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

        $formateurs = DB::table('v_union_formateurs')
            ->select('idFormateur', 'name', 'firstName')
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })

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

    public function getEventsGroupBy() // <=== Sélectionne tous les évenements(séances) en fonction du customer connecté dont les séances sont groupé par module pour chaque journée...
    {
        $seances = DB::table('v_seances_etp')
            ->select(
                'idSeance',
                'dateSeance',
                'idCfp',
                'idTypeProjet',
                'dateSeance',
                'id_google_seance',
                'heureDebut',
                'heureFin',
                'idSalle',
                'v_seances_etp.idProjet AS idProjet',
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
                DB::raw('COUNT(*) AS nb_seances'),
                DB::raw("GROUP_CONCAT( dateSeance, 'T', heureDebut) AS seance_start"),
                DB::raw("GROUP_CONCAT( dateSeance, 'T', heureFin) AS seance_end"),
            )

            ->leftJoin('project_sub_contracts AS psc', 'v_seances_etp.idProjet', '=', 'psc.idProjet')
            ->leftJoin('customers AS sub', 'psc.idSubContractor', '=', 'sub.idCustomer')
            ->leftJoin('customers AS ce', 'v_seances_etp.idCfp', '=', 'ce.idCustomer')
            ->leftJoin('lieux as li', 'li.idLieu', '=', 'v_seances_etp.idLieu')
            ->leftJoin('ville_codeds as vcd', 'vcd.id', '=', 'li.idVilleCoded')

            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_intra', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })

            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(
                        function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        }
                    );
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
                    'idCfp' => $seance->idCfp,
                    'idEtp' => Customer::idCustomer(),
                    'start' => $start[0],
                    'end' => isset($end[0]) ? end($end) : null,
                    'idProjet' => $seance->idProjet,
                    'idSalle' => $seance->idSalle,
                    'idModule' => $seance->idModule,
                    'text' => $seance->project_title,
                    'description' => $seance->project_description,
                    'idCalendar' => $seance->id_google_seance,      //id reliant à Google calendar
                    'salle' => $seance->salle_name,
                    'module' => $seance->module_name,
                    'ville' => $seance->ville_name,
                    'formateurs' => $this->getFormProject($seance->idProjet),
                    'formateur_internes' => $this->getFormInterneProject($seance->idProjet),
                    'imgModule' => $this->getFieldsProject($seance->idProjet)->module_image,
                    'statut' => $this->getFieldsProject($seance->idProjet)->project_status,
                    'typeProjet' => $this->getFieldsProject($seance->idProjet)->project_type,
                    'nameCfp' => $this->getNameCfp(Customer::idCustomer(), $seance->idCfp),
                    'codePostal' => $seance->vi_code_postal,
                    'reference' => $seance->project_reference,
                    'idFormateur' => $this->getIdFormProject($seance->idProjet),
                    'quartier' => $seance->salle_quartier,
                    'modalite' => $seance->modalite,
                    'nb_seances' =>  $seance->nb_seances,
                ];
            }
            
            return response()->json([
                'status' => 200,
                'seances' => $events
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }
    }

    public function getEvents()
    {
        $seances = DB::table('v_seances_etp')
            ->select(
                'idSeance',
                'dateSeance',
                'idCfp',
                'idTypeProjet',
                'dateSeance',
                'id_google_seance',
                'heureDebut',
                'heureFin',
                'idSalle',
                'v_seances_etp.idProjet AS idProjet',
                'project_reference',
                'salle_name',
                'salle_quartier',
                'project_title',
                'project_description',
                'idModule',
                'module_name',
                'modalite',
                'vcd.ville_name',
                'vcd.vi_code_postal'
            )

            ->leftJoin('project_sub_contracts AS psc', 'v_seances_etp.idProjet', '=', 'psc.idProjet')
            ->leftJoin('customers AS sub', 'psc.idSubContractor', '=', 'sub.idCustomer')
            ->leftJoin('customers AS ce', 'v_seances_etp.idCfp', '=', 'ce.idCustomer')
            ->leftJoin('lieux as li', 'li.idLieu', '=', 'v_seances_etp.idLieu')
            ->leftJoin('ville_codeds as vcd', 'vcd.id', '=', 'li.idVilleCoded')

            ->where('project_is_trashed', 0)
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_intra', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })

            //->where('idEtp_intra', Customer::idCustomer())
            ->where(function ($query) {
                $query->where('project_type', 'Interne')
                    ->orWhere(
                        function ($query) {
                            $query->whereIn('project_type', ['Intra', 'Inter'])
                                ->whereIn('project_status', ['En cours', 'Terminé', 'Planifié', 'Annulé', 'Cloturé']);
                        }
                    );
            })
            ->get();

        if (count($seances) > 0) {

            foreach ($seances as $seance) {

                $events[] =  [
                    'idSeance' => $seance->idSeance,
                    'idCfp' => $seance->idCfp,
                    'idEtp' => Customer::idCustomer(),
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
                    'ville' => $seance->ville_name,
                    'formateurs' => $this->getFormProject($seance->idProjet),
                    'formateur_internes' => $this->getFormInterneProject($seance->idProjet),
                    'imgModule' => $this->getFieldsProject($seance->idProjet)->module_image,
                    'statut' => $this->getFieldsProject($seance->idProjet)->project_status,
                    'typeProjet' => $this->getFieldsProject($seance->idProjet)->project_type,
                    'nameCfp' => $this->getNameCfp(Customer::idCustomer(), $seance->idCfp),
                    'codePostal' => $seance->vi_code_postal,
                    'reference' => $seance->project_reference,
                    'idFormateur' => $this->getIdFormProject($seance->idProjet),
                    'quartier' => $seance->salle_quartier,
                    'modalite' => $seance->modalite,
                ];
            }
            
            return response()->json([
                'status' => 200,
                'seances' => $events
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }
    }


    public function getIdEtpCfps()
    {

        $allId = [];
        $allId = DB::select("SELECT idCfp FROM `v_collaboration_etp_cfps` WHERE idEtp = ?", [Customer::idCustomer()]);

        $ids = array_map(function ($allId) {
            return (int)$allId->idCfp;
        }, $allId);
        //$ids = array_merge($ids,[Customer::idCustomer()]);
        return $ids;
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

    public function getApprenantProjectInter($idProjet)
    {

        $apprs = DB::table('v_list_apprenant_inter_added')
            ->select('*')
            ->where('idProjet', $idProjet)
            ->get();

        return count($apprs);
    }


    public function getEventResources()
    {

        $formateurs = DB::table('v_union_formateurs')
            ->select('idFormateur', 'name', 'firstName')
            ->where(function ($query) {
                $query->where('idEtp', Customer::idCustomer())
                    ->orWhere('idEtp_inter', Customer::idCustomer());
            })
            ->groupBy('idFormateur')
            ->get();
      
        if($formateurs->count()>0){
        foreach ($formateurs as $formateur) {
            $allFormateur[] =  [
                'id'    => 'FM_' . $formateur->idFormateur,
                'name'  => $formateur->firstName
            ];
            }
        } else {
            $allFormateur =[];
        }
       
        $modules = DB::table('v_module_etps')
            ->select('idModule', 'moduleName')
            ->where('idCustomer', Customer::idCustomer())
            ->get();
        
        if($modules->count()>0){     
        foreach ($modules as $mdl) {
            $allModule[] = [
                'id'    => 'MD_' . $mdl->idModule,
                'name'  => $mdl->moduleName,
            ];
        }
        } else {
            $allModule = [];
        }

        // $moduleExts = DB::select("SELECT idModule,moduleName  FROM v_module_cfps WHERE  IN idCustomer = ?", $this->getIdEtpCfps() );
        $moduleExts = DB::table('v_module_cfps')
            ->select('idModule', 'moduleName')
            ->where('moduleName', '!=', 'Default module')
            ->whereIn('idCustomer', $this->getIdEtpCfps())
            ->get();

        if($moduleExts->count()>0){
            foreach ($moduleExts as $mdl) {
                $allModuleExt[] = [
                    'id'    => 'MD_' . $mdl->idModule,
                    'name'  => $mdl->moduleName,
                ];
            }
        } else{           
            $allModuleExt = [];
        }

        /* $etps = DB::select("SELECT idEtp, etp_name  FROM v_collaboration_cfp_etps WHERE idCfp = ?", [$this->idEtp()] );
        $allEtp = [];
        foreach($etps as $etp){
            $allEtp[] = [              
                'id'    => 'ETP_' . $etp->idEtp, 
                'name'  => $etp->etp_name,
            ];
        }*/

        return response()->json([
            // 'projets'     => $allproject,
            'formateurs'    => $allFormateur,
            //'salles'        => $allSalle,
            'modules'       => $allModule,
            'module_externes' => $allModuleExt,
            // 'etps'          => $allEtp,
            //'materiels'   => $materiels,
        ]);
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

    private function getPrestation($idModule)
    {
        $materiel = DB::table('prestation_modules')
            ->select('prestation_name')
            ->where('idModule', $idModule)
            ->get();
        return $materiel->toArray();
    }

    private function getNameCfp($idEtp, $idCfp) // Seulement CFP mandat et non CFP sous traitant
    {
        $nameCfp = DB::table('v_collaboration_etp_cfps')
            ->select('etp_name')
            ->where('idEtp', $idEtp)
            ->where('idCfp', $idCfp)
            ->get();
        return $nameCfp;
    }

    private function getIdFormProject($idProjet) // formateur cfp selon le projet...
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idFormateur')
            ->groupBy('idFormateur', 'name', 'firstName', 'photoForm', 'initialNameForm')
            ->where('idProjet', $idProjet)
            ->pluck('idFormateur');

        return $forms;
    }


    public function getFormInterneProject($idProjet)
    {
        $forms = DB::table('v_formateur_internes')
            ->select('idEmploye as idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
            ->groupBy('idEmploye', 'name', 'firstName', 'photoForm', 'initialNameForm')
            ->where('idProjet', $idProjet)->get();

        return $forms->toArray();
    }

    public function getFormProject($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
            ->distinct()
            ->where('idProjet', $idProjet)
            ->get();
        return $forms->toArray();
    }


    public function getApprenantProjectInterne($idProjet)
    {
        $apprs = DB::table('v_list_apprenants')
            ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_photo', 'emp_matricule', 'emp_phone', 'etp_name')
            ->where('idProjet', $idProjet)
            ->where('idEtp', Customer::idCustomer())
            ->orderBy('emp_name', 'asc')
            ->get();

        return count($apprs);
    }

    public function getFieldsProject($idProjet)
    {

        $projet = DB::table('v_union_projets')
            ->select('idProjet', 'idCfp_intra', 'dateDebut', 'dateFin', 'project_title', 'etp_name', 'ville', 'project_status', 'project_type', 'module_image', 'project_reference', 'idEtp', 'paiement')
            ->where('idProjet', $idProjet)
            ->first();
        return $projet;
    }

    public function listProjetForms(Request $req)
    {
        $idFormateur = explode(',', $req->idFormateur);

        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);

        $projetForms = DB::table('v_union_seanceEtps')
            ->select('idProjet', 'idSession', 'projectName', 'moduleName', 'dateSeance', 'heureDebut', 'heureFin', 'ville', 'salle', 'nameForm', 'firstNameForm', 'cfpName as customerName', 'type', 'paiement', 'moduleName', 'sessionName')
            ->where('idEtp', $customer[0]->idCustomer)
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
}
