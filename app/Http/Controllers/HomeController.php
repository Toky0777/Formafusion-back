<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\EvaluationChaudController;
use App\Models\Customer;
use App\Models\Invoice;
use App\Traits\ProjectQuery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\StudentQuery;
use Carbon\Carbon;

class HomeController extends Controller
{
    use StudentQuery, ProjectQuery;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $EvaluationChaud;
    public function __construct(EvaluationChaudController $evaluation)
    {
        $this->middleware('auth');
        $this->EvaluationChaud = $evaluation;
    }



    public function getIdCustomer()
    {
        return response()->json(['idCustomer' => Customer::idCustomer()]);
    }

    public function getIdEmploye()
    {
        return response()->json(['idCustomer' => Auth::id()]);
    }

    public function indexEmp()
    {
        if (!Auth::check()) {
            abort(401, 'Vous devez être authentifié pour accéder à cette ressource.');
        }

        $userId = Auth::user()->id;
        $projects = DB::table('v_projet_emps AS projet')
            ->leftJoin('module_ressources', 'module_ressources.idModule', '=', 'projet.idModule')
            ->select(
                'projet.idProjet',
                'projet.dateDebut',
                'projet.dateFin',
                'projet.cfp_name',
                'projet.module_name',
                'projet.project_description',
                DB::raw('COUNT(module_ressources.idModuleRessource) as nb_module_ressources')
            )
            ->where('projet.idEmploye', $userId)
            ->where('projet.dateDebut', '>=', now()->subMonths(3))
            ->groupBy(
                'projet.idProjet',
                'projet.dateDebut',
                'projet.dateFin',
                'projet.cfp_name',
                'projet.module_name',
                'projet.project_description'
            )
            ->orderBy('dateDebut', 'asc')
            ->get();

        $count_cfp = DB::table('customers AS cu')
            ->join('v_projet_emps AS projet', 'cu.idCustomer', '=', 'projet.idCfp')
            ->where('projet.idEmploye', $userId)
            ->count();

        $count_prog = DB::table('v_projet_emps AS projet')->where('projet.idEmploye', $userId)->count();

        return response()->json([
            'projects' => $projects,
            'count_prog' => $count_prog,
            'count_cfp' => $count_cfp,
        ]);
    }

    public function indexCfp(FactureController $invoices)
    {
        $idCfp = Customer::idCustomer();
        $CustomerName = DB::table('customers')->where('idCustomer', $idCfp)->value('customerName');
        $currentMonth = date('m');
        $currentYear = date('Y');
        // les projets du mois courant
        $current_month_projects = $this->getCfpProjects([$currentMonth], ['Terminé', 'Cloturé'], $idCfp, $currentYear);

        // les apprenants de ces projets
        $apprenants = $this->getStudents($current_month_projects->pluck('idProjet'));
        $total_trained = count($apprenants);

        // les séances du jour (tous projets)
        $today_sessions = $this->getTodaySessions()
            ->where(function ($query) use ($idCfp) {
                $query->where('p.idCfp', $idCfp)
                    ->orWhere('p.idCfp_inter', $idCfp)
                    ->orWhere('p.idSubContractor', $idCfp);
            })
            ->get();
        $tomorrow_sessions = $this->getTomorrowSessions()
            ->where(function ($query) use ($idCfp) {
                $query->where('p.idCfp', $idCfp)
                    ->orWhere('p.idCfp_inter', $idCfp)
                    ->orWhere('p.idSubContractor', $idCfp);
            })
            ->get();
        $count_today_sessions = $today_sessions->count();
        $count_tomorrow_sessions = $tomorrow_sessions->count();
        //projet en cours
        $ongoing_projects = $this->getProject('En cours', 'idCfp', 'etp_name')->get();
        $project_session_undetermined = $this->getProjectSessionCfp()->get();

        $encoursCount = $this->countProjectByStatus("En cours")
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('idCfp', Customer::idCustomer())
                        ->orWhere('idCfp_inter', Customer::idCustomer())
                        ->orWhere('idSubContractor', Customer::idCustomer());
                });
            })
            ->count();


        // reservation
        $reservations = $this->getReservations();

        $restantSaine = $invoices->getRestantSaine();
        $total_douteuse = $invoices->getSumByStatus(11);
        $images = $this->getImagesCfp();
        return response()->json([
            'status' => 200,
            'customer_name' => $CustomerName,
            'en_cours' => $encoursCount,
            'impayees' => $restantSaine,
            'today_sessions' => $today_sessions,
            'tomorrow_sessions' => $tomorrow_sessions,
            'encours_projects' => $ongoing_projects,
            'reservations' => $reservations,
            'count_today_sessions' => $count_today_sessions,
            'count_tomorrow_sessions' => $count_tomorrow_sessions,
            'bon_commandes' => $this->getBonCommande(),
            'images' => $images,
            'project_session_undetermined' => $project_session_undetermined
        ]);
    }

    public function indexEtp()
    {
        $idCfp = Customer::idCustomer();
        $CustomerName = DB::table('customers')->where('idCustomer', $idCfp)->value('customerName');
        $currentMonth = date('m');
        $currentYear = date('Y');

        $current_month_projects = $this->getCfpProjects([$currentMonth], ['Terminé', 'Cloturé'], $idCfp, $currentYear);

        // les apprenants de ces projets
        $apprenants = $this->getStudents($current_month_projects->pluck('idProjet'));
        $total_trained = count($apprenants);

        // les séances du jour (tous projets)
        $today_sessions = $this->getTodaySessions()
            ->where('p.idEtp', Customer::idCustomer())
            ->get();

        //projet
        $projetsPlanifie = $this->getProject('Planifié', 'idEtp', 'cfp_name')->get();
        $projetsEnCours = $this->getProject('En cours', 'idEtp', 'cfp_name')->get();
        $recentProjetsTermines = $this->getProject('Terminé', 'idEtp', 'cfp_name')
            ->limit(5)
            ->get();

        //Count project
        $encoursCount = $this->countProjectByStatus("En cours")
            ->where('idEtp', Customer::idCustomer())
            ->count();

        // gallery photo
        $images = $this->getImages();

        return response()->json([
            'status' => 200,
            'customer_name' => $CustomerName,
            'en_cours' => $encoursCount,
            'total_trained' => $total_trained,
            'today_sessions' => $today_sessions,
            'projetsPlanifie' => $projetsPlanifie,
            'projetsEnCours' => $projetsEnCours,
            'recentProjetsTermines' => $recentProjetsTermines,
            'recentProjetsTermines' => $recentProjetsTermines,
            'images' => $images,
        ]);
    }

    public function indexApp()
    {
        $userId = Auth::user()->id;
        // les séances du jour (tous projets)
        $today_sessions = $this->getSessionsApp('today')
            ->where('p.idEmploye', $userId)
            ->get();

        $upcoming_sessions = $this->getSessionsApp('upcoming')
            ->where('p.idEmploye', $userId)
            ->get();

        return response()->json([
            'status' => 200,
            'today_sessions' => $today_sessions,
            'upcoming_sessions' => $upcoming_sessions,
        ]);
    }

    public function indexForm()
    {
        $idFormateur = Auth::user()->id;
        $CustomerName = DB::table('users')->where('id', $idFormateur)->value('name');
        $currentMonth = date('m');
        $currentYear = date('Y');

        // Projets du formateur du mois courant, terminés ou clôturés
        $current_month_projects = DB::table('v_projet_form')
            ->where('idFormateur', $idFormateur)
            ->whereIn('project_status', ['Terminé', 'Cloturé'])
            ->where('headMonthFin', $currentMonth)
            ->where('headYear', $currentYear)
            ->get();

        // Apprenants de ces projets
        $apprenants = DB::table('v_apprenant_etp_alls')
            ->whereIn('idProjet', $current_month_projects->pluck('idProjet'))
            ->get();
        $total_trained = $apprenants->count();

        // Séances du jour pour les projets du formateur
        $today = date('Y-m-d');
        $today_sessions = DB::table('v_seances as s')
            ->join('v_projet_form as p', 's.idProjet', '=', 'p.idProjet')
            ->leftJoin(DB::raw('(SELECT idProjet, name, firstName, form_phone FROM v_formateur_cfps GROUP BY idProjet) as f'), 'f.idProjet', '=', 'p.idProjet')
            ->where('p.idFormateur', $idFormateur)
            ->whereDate('s.dateSeance', $today)
            ->where('p.module_name', '!=', 'Default module')
            ->select(
                's.idSeance',
                's.dateSeance',
                's.heureDebut',
                's.heureFin',
                'p.idProjet',
                'p.module_name',
                'p.ville',
                'p.salle_quartier',
                'p.etp_name',
                'f.name as formateur_name',
                'f.firstName as formateur_firstName',
                'f.form_phone as formateur_phone',
                DB::raw('(SELECT COUNT(*) FROM detail_apprenants WHERE idProjet = p.idProjet) as nb_apprenants')
            )
            ->orderBy('s.heureDebut', 'asc')
            ->get();

        // Projets planifiés, en cours, terminés (5 derniers) du formateur
        $projetsPlanifie = $this->getProjectFrom($idFormateur, 'Planifié')
            ->orderBy('p.dateFin', 'asc')
            ->get();

        $projetsEnCours = $this->getProjectFrom($idFormateur, 'En cours')
            ->orderBy('p.dateFin', 'asc')
            ->get();

        $recentProjetsTermines = $this->getProjectFrom($idFormateur, 'Terminé')
            ->orderBy('p.dateFin', 'desc')
            ->limit(5)
            ->get();

        // // Nombre de projets en cours
        $encoursCount = $this->getProjectFrom($idFormateur, 'En cours')
            ->count();

        // // Images liées aux projets du formateur
        $images = DB::table('v_images as I')
            ->select('I.idImages', 'I.idProjet', 'I.img_url', 'I.module_name', 'I.img_description', 'I.img_created_at')
            ->join('v_projet_form as p', 'p.idProjet', '=', 'I.idProjet')
            ->where('p.idFormateur', $idFormateur)
            ->limit(10)
            ->get();

        foreach ($images as $img) {
            $img->img_created_human = $this->getHumanDate($img->img_created_at);
        }

        return response()->json([
            'status' => 200,
            'customer_name' => $CustomerName,
            'en_cours' => $encoursCount,
            'total_trained' => $total_trained,
            'today_sessions' => $today_sessions,
            'projetsPlanifie' => $projetsPlanifie,
            'projetsEnCours' => $projetsEnCours,
            'recentProjetsTermines' => $recentProjetsTermines,
            'images' => $images,
        ]);
    }

    private function calculateInvoiceTotals()
    {
        // Récupération des factures
        $invoicesQuery = Invoice::with(['entrepriseFromVcollaboration', 'particulier', 'status'])
            ->where('idCustomer', Customer::idCustomer())
            ->standard()
            ->doesntHave('deletedInvoices')
            ->whereNotIn('invoice_status', [1, 4, 9])
            ->orderBy('idInvoice', 'desc');

        $invoices = $invoicesQuery->get();

        // Calcul des totaux
        $total_montant = $invoices->sum('invoice_total_amount');
        $total_paye = $invoices->pluck('payments')->flatten()->sum('amount');
        $restantDu = $total_montant - $total_paye;

        return [$total_paye, $restantDu];
    }

    private function getSessionsByDate($date)
    {
        return DB::table('v_seances as s')
            ->join('v_projet_cfps as p', 's.idProjet', '=', 'p.idProjet')
            ->leftJoin('v_formateur_cfps as f', function ($join) {
                $join->on('f.idProjet', '=', 'p.idProjet');
            })
            ->whereDate('s.dateSeance', $date)
            ->where('p.module_name', '!=', 'Default module')
            ->where('p.project_is_active', 1)
            ->whereNotIn('p.project_status', ['Cloturé', 'Supprimé', 'Annulé', 'Reporté'])
            ->select(
                's.idSeance',
                's.dateSeance',
                's.heureDebut',
                's.heureFin',
                's.modalite',
                'p.idProjet',
                'p.ville',
                'p.idEtp',
                'p.module_name',
                'p.salle_quartier',
                'p.etp_name',
                'f.name as formateur_name',
                'f.firstName as formateur_firstName',
                'f.form_phone as formateur_phone',
                DB::raw('(SELECT COUNT(*) FROM detail_apprenants WHERE idProjet = p.idProjet) as nb_apprenants')
            )
            ->orderBy('s.heureDebut', 'asc');
    }

    private function getTodaySessions()
    {
        $today = date('Y-m-d');
        return $this->getSessionsByDate($today);
    }

    private function getTomorrowSessions()
    {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        return $this->getSessionsByDate($tomorrow);
    }

    private function getSessionsApp($type = 'today')
    {
        $now = date('Y-m-d');

        $seances = DB::table('v_seances as s')
            ->join('v_projet_emps as p', 's.idProjet', '=', 'p.idProjet')
            ->leftJoin(DB::raw('(SELECT idProjet, name, firstName, form_phone FROM v_formateur_cfps GROUP BY idProjet) as f'), 'f.idProjet', '=', 'p.idProjet')
            ->where('p.module_name', '!=', 'Default module')
            ->where('p.project_is_active', 1)
            ->select(
                's.idSeance',
                's.dateSeance',
                's.heureDebut',
                's.heureFin',
                'p.idProjet',
                'p.module_name',
                'p.ville',
                'p.salle_quartier',
                'p.etp_name',
                'f.name as formateur_name',
                'f.firstName as formateur_firstName',
                'f.form_phone as formateur_phone'
            )
            ->orderBy('s.dateSeance', 'asc')
            ->orderBy('s.heureDebut', 'asc');

        if ($type === 'today') {
            $seances->whereDate('s.dateSeance', $now);
        } elseif ($type === 'upcoming') {
            $seances->whereDate('s.dateSeance', '>', $now);
        }

        return $seances;
    }

    private function getProject($status, $column = 'idCfp', $customer = 'etp_name')
    {
        $idUser = Customer::idCustomer();

        $projects = DB::table('v_projet_cfps as p')
            ->leftJoin('v_formateur_cfps as f', 'f.idProjet', '=', 'p.idProjet')
            ->where("p.$column", $idUser)
            ->where('p.project_status', $status)
            ->where('p.module_name', '!=', 'Default module')
            ->select(
                'p.idProjet',
                'p.module_name',
                'p.project_description as module_description',
                'p.dateDebut',
                'p.dateFin',
                "p.$customer",
                'f.name as formateur_name',
                'p.ville',
                'p.idEtp',
                'p.total_ht',
                'f.photoForm',
                DB::raw('(SELECT COUNT(*) FROM detail_apprenants WHERE idProjet = p.idProjet) as nb_apprenants')
            )
            ->groupBy(
                'p.idProjet',
                'p.module_name',
                'p.project_description',
                'p.dateDebut',
                'p.dateFin',
                'p.etp_name',
                'f.name',
                'p.ville',
                'p.total_ht'
            )
            ->where('p.project_is_active', 1)
            ->orderBy('p.dateFin', 'desc');

        return $projects;
    }

    public function countProjectByStatus($status)
    {
        return DB::table('v_projet_cfps')
            ->select('idProjet')
            ->where('module_name', '!=', 'Default module')
            ->where('project_is_active', 1)
            ->where('project_status', $status);
    }

    private function getReservations()
    {
        $idCfp = Auth::user()->id;

        $reservations = DB::table('inter_entreprises as I')
            ->join('projets as P', 'P.idProjet', '=', 'I.idProjet')
            ->join('customers as C', 'C.idCustomer', '=', 'I.idEtp')
            ->join('mdls as M', 'M.idModule', '=', 'P.idModule')
            ->leftJoin('reservation_responsable as R', 'R.idReservation', '=', 'I.id')
            ->where('P.idCustomer', $idCfp)
            ->select(
                'I.id',
                'C.customerName',
                'M.moduleName as module_name',
                'P.dateDebut',
                'I.nbPlaceReserved',
                DB::raw("CONCAT(R.nom, ' ', R.prenom) as responsable_name")
            )
            ->orderBy('P.dateDebut', 'desc')
            ->get();

        return $reservations;
    }

    private function getBonCommande()
    {
        $bonCommande = DB::table('bon_commandes as bc')
            ->select(
                'bc.idBC',
                'bc.idDevis',
                'bcs.idStatus',
                'bcs.status_name',
                'bcs.status_color',
                'bc.numero as numero_bc',
                'bc.montant as montant_bc',
                'bc.date as date_bc',
                'cu.idCustomer as idEtp',
                'cu.customerName as etp_name',
                DB::raw('GROUP_CONCAT(CONCAT(idp.item_description, " - ", idp.item_qty, " - ", idp.item_unit_price, " - ", idp.item_total_price) SEPARATOR " | ") as details_devis')
            )
            ->join('invoices as i', 'bc.idDevis', '=', 'i.idInvoice')
            ->join('customers as cu', 'i.idEntreprise', '=', 'cu.idCustomer')
            ->join('bc_status as bcs', 'bc.idStatus', '=', 'bcs.idStatus')
            ->join('invoice_details_profo as idp', 'bc.idDevis', '=', 'idp.idInvoice')
            ->where('bc.idCfp', Customer::idCustomer())
            ->groupBy(
                'bc.idBC'
            )
            ->orderBy('bc.date', 'desc')
            ->limit(5)
            ->get();

        return $bonCommande;
    }

    private function getImages()
    {
        $images = DB::table('v_images as I')
            ->select('I.idImages', 'I.idProjet', 'I.img_url', 'I.module_name', 'I.img_description', 'I.img_created_at', 'p.dateDebut', 'p.dateFin', 'p.ville', 'p.cfp_name')
            ->join('v_projet_cfps as p', 'p.idProjet', '=', 'I.idProjet')
            ->where('p.idEtp', Customer::idCustomer())
            ->limit(10)
            ->get();

        // Ajout du champ "img_created_human"
        foreach ($images as $img) {
            $img->img_created_human = $this->getHumanDate($img->img_created_at);
        }

        return $images;
    }
    private function getImagesCfp()
    {
        $images = DB::table('v_images as I')
            ->select('I.idImages', 'I.idProjet', 'I.img_url', 'I.module_name', 'I.img_description', 'I.img_created_at', 'p.dateDebut', 'p.dateFin', 'p.li_name as ville', 'p.cfp_name')
            ->join('v_projet_cfps as p', 'p.idProjet', '=', 'I.idProjet')
            ->where('I.idCustomer', Customer::idCustomer())
            ->orderByDesc('I.img_created_at')
            ->limit(10)
            ->get();

        // Ajout du champ "img_created_human"
        foreach ($images as $img) {
            $img->img_created_human = $this->getHumanDate($img->img_created_at);
        }

        return $images;
    }


    private function getHumanDate($date)
    {
        $carbon = Carbon::parse($date);
        $now = Carbon::now();

        if ($carbon->isToday()) {
            $diff = $carbon->diffInHours($now);
            if ($diff < 1) {
                return 'il y a quelques minutes';
            }
            return "il y a $diff heure" . ($diff > 1 ? 's' : '');
        } elseif ($carbon->isYesterday()) {
            return 'hier';
        } else {
            $diffDays = $carbon->diffInDays($now);
            if ($diffDays < 30) {
                return "il y a $diffDays jour" . ($diffDays > 1 ? 's' : '');
            } else {
                $diffMonths = $carbon->diffInMonths($now);
                return "il y a $diffMonths mois";
            }
        }
    }

    private function getProjectFrom($idForm, $status)
    {
        $projets = DB::table('v_projet_form as p')
            ->leftJoin('v_formateur_cfps as f', 'f.idProjet', '=', 'p.idProjet')
            ->where('p.idFormateur', $idForm)
            ->where('p.project_status', $status)
            ->where('p.module_name', '!=', 'Default module')
            ->select(
                'p.idProjet',
                'p.module_name',
                'p.project_description as module_description',
                'p.dateDebut',
                'p.dateFin',
                "p.etp_name",
                'f.name as formateur_name',
                'p.ville',
                'p.total_ht',
                DB::raw('(SELECT COUNT(*) FROM detail_apprenants WHERE idProjet = p.idProjet) as nb_apprenants')
            )
            ->groupBy(
                'p.idProjet',
                'p.module_name',
                'p.project_description',
                'p.dateDebut',
                'p.dateFin',
                'p.etp_name',
                'f.name',
                'p.ville',
                'p.total_ht'
            )
            ->where('p.project_is_active', 1);

        return $projets;
    }
    // private function getProjectSessionCfp()
    // {
    //     $data = [];
    //     $idUser = Customer::idCustomer();
    //     $projects = DB::table('v_projet_cfps as p')
    //         ->leftJoin('v_formateur_cfps as f', 'f.idProjet', '=', 'p.idProjet')
    //         ->where('p.idCfp', $idUser)
    //         ->where('p.project_status','!=','Supprimé')
    //         ->where('p.module_name', '!=', 'Default module')
    //         ->select(
    //             'p.idProjet',
    //             'p.module_name',
    //             'p.project_description as module_description',
    //             'p.dateDebut',
    //             'p.dateFin',
    //             'p.idCfp',
    //             'f.name as formateur_name',
    //             'p.ville',
    //             'p.idEtp',
    //             'p.total_ht',
    //             'f.photoForm',
    //             DB::raw('(SELECT COUNT(*) FROM detail_apprenants WHERE idProjet = p.idProjet) as nb_apprenants')
    //         )
    //         ->groupBy(
    //             'p.idProjet',
    //             'p.module_name',
    //             'p.project_description',
    //             'p.dateDebut',
    //             'p.dateFin',
    //             'p.etp_name',
    //             'f.name',
    //             'p.ville',
    //             'p.total_ht'
    //         )
    //         // ->where('p.project_is_active', 1)
    //         ->orderBy('p.dateFin', 'desc');

    //         foreach($projects as $p){
    //             $session = $this->getSessionByIdProject($p->idProjet);
    //             if($session){
    //                 $data[] =[
    //             'idProjet' =>$p->idProjet,
    //             'module_name' =>$p->module_name,
    //             'module_description' =>$p->module_description,
    //             'dateDebut' =>$p->dateDebut,
    //             'dateFin' =>$p->dateFin,
    //             'idCfp' =>$p->idCfp,
    //             'formateur_name' =>$p->formateur_name,
    //             'ville' =>$p->ville,
    //             'idEtp' =>$p->idEtp,
    //             'total_ht' =>$p->total_ht,
    //             'photoForm'=>$p->photoForm,
    //             'nb_apprenants'=>$p->nb_apprenants
    //                 ];
    //             }
    //         }
    //     return $projects;
    // }
    // private function getSessionByIdProject($idProjet){
    //     return DB::table('v_projet_cfps as p')
    //         ->join('seances as s', 'p.idProjet', '=', 's.idProjet')
    //         ->where('p.idProjet', $idProjet)
    //         ->where(function ($query) {
    //             $query->where('s.is_reported', 1)
    //                 ->where('s.is_report_undetermined', 1);
    //         })
    //         ->exists();
    // }
    private function getProjectSessionCfp()
    {
        $idUser = Customer::idCustomer();

        $projects = DB::table('v_projet_cfps as p')
            ->leftJoin('v_formateur_cfps as f', 'f.idProjet', '=', 'p.idProjet')
            ->where('p.idCfp', $idUser)
            ->where('p.project_status', '!=', 'Supprimé')
            ->where('p.module_name', '!=', 'Default module')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('seances as s')
                    ->whereRaw('s.idProjet = p.idProjet')
                    ->where(function ($q) {
                        $q->where('s.is_reported', 1)
                            ->where('s.is_report_undetermined', 1);
                    });
            })
            ->select(
                'p.idProjet',
                'p.module_name',
                'p.project_description as module_description',
                'p.dateDebut',
                'p.dateFin',
                'p.etp_name',
                'f.name as formateur_name',
                'p.ville',
                'p.idEtp',
                'p.total_ht',
                'f.photoForm',
                DB::raw('(SELECT COUNT(*) FROM detail_apprenants WHERE idProjet = p.idProjet) as nb_apprenants')
            )
            ->groupBy(
                'p.idProjet',
                'p.module_name',
                'p.project_description',
                'p.dateDebut',
                'p.dateFin',
                'p.idCfp',
                'f.name',
                'p.ville',
                'p.idEtp',
                'p.total_ht',
                'f.photoForm'
            )
            ->orderBy('p.dateFin', 'desc');
        return $projects;
    }
}
