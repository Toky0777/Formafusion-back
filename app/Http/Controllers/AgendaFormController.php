<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgendaFormController extends Controller
{
    public function getIdCustomer()
    {
        return response()->json(['idCustomer' => Auth::user()->id]);
    }

    public function getEvent()
    {
        $projets = DB::select("SELECT idProjet, idSeance, projectName, moduleName, dateSeance, heureDebut, heureFin, ville, salle, idFormateur, nameForm, firstNameForm, etpName AS customerName, type, paiement, moduleName FROM v_union_seanceForms WHERE idFormateur = ? AND isActiveProjet = ?", [Auth::user()->id, 1]);
        $events = [];

        foreach ($projets as $p) {
            $events[] = [
                'id' => $p->idProjet,
                'calendarId' => $p->idFormateur,
                'title' => $p->moduleName,
                'category' => 'time',
                'dueDateClass' => "",
                'start' => $p->dateSeance . "T" . $p->heureDebut,
                'end' => $p->dateSeance . "T" . $p->heureFin,
                'body' => "a",
                'isReadOnly' => true,
                'raw' => [
                    // 'url' => '/img/entreprises/'.$p->etpLogo,
                    'Formateur' => $p->nameForm . " " . $p->firstNameForm,
                    'Salle' => $p->salle,
                    // 'Participant' => $this->getApprenant($p->idSession),
                    'Entreprise' => $p->customerName,
                    'Ville' => $p->ville,
                    'Type' => $p->type,
                    'Financement' => $p->paiement,
                    // 'Ressources' => $this->getRessource($p->idSession)
                ]
            ];
        }

        return response()->json(['events' => $events]);
    }

    private function getPrestation($idModule)
    {
        $materiel = DB::table('prestation_modules')
            ->select('prestation_name')
            ->where('idModule', $idModule)
            ->get();
        return $materiel->toArray();
    }

    public function getEvents()
    {
        $idFormateur = Auth::user()->id;
        $seances = DB::table('v_seances_form')
            ->select('idSeance', 'idCfp', 'idEtp_intra', 'dateSeance', 'idTypeProjet', 'id_google_seance', 'heureDebut', 'heureFin', 'idSalle', 'idProjet', 'salle_name', 'salle_quartier', 'project_title', 'project_description', 'idModule', 'module_name', 'ville')
            ->where('idFormateur', $idFormateur)
            ->get();
        foreach ($seances as $seance) {
            $events[] =  [
                'idSeance' => $seance->idSeance,      //<===== idSeance
                'idCfp' => $seance->idCfp,
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
                
                'imgModule' => $this->getFieldsProject($seance->idProjet)->module_image,
                'statut' => $this->getFieldsProject($seance->idProjet)->project_status,
                'nameEtp' => $this->getFieldsProject($seance->idProjet)->etp_name,
                
                'materiels' => $this->getPrestation($seance->idModule),
                'typeProjet' => $this->getFieldsProject($seance->idProjet)->project_type,
            ];
        }
        //dd($seances);
        return response()->json(['seances' => $events]);
    }

  public function getFormProject($idProjet)
{
    $forms = DB::table('v_formateur_cfps')
        ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
        ->where('idProjet', $idProjet)
        ->distinct()
        ->get();

    return $forms->toArray();
}


    private function getFieldsProject($idProjet)
    {

        $projet = DB::table('v_projet_form')
            ->select('idProjet', 'dateDebut', 'dateFin', 'project_title', 'etp_name', 'ville', 'project_status', 'project_type', 'module_image', 'project_reference', 'modalite', 'idEtp')
            ->where('idProjet', $idProjet)
            ->first();
        return $projet;
    }

    public function getApprenantProjectIntra($idProjet)
    {
        $apprs = DB::table('v_list_apprenants')
            ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_photo', 'emp_matricule', 'emp_phone', 'etp_name')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();

        return count([$apprs]);
    }

    public function getApprenantProjectInter($idProjet)
    {

        $apprs = DB::table('v_list_apprenant_inter_added')
            ->select('*')
            ->where('idProjet', $idProjet)
            ->get();

        return count([$apprs]);
    }

    public function getEtpProjectInter($idProjet, $idCfp_inter)
    {
        if ($idCfp_inter == null) {
            $etp = DB::table('v_projet_form')
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

    public function countSeance($month, $year)
    {
        $countSeance = DB::select("SELECT COUNT(idSeance) AS nbSeance FROM v_union_seanceForms WHERE MONTH(dateSeance) = ? AND YEAR(dateSeance) = ?", [$month, $year]);
        return response()->json($countSeance);
    }

    public function index()
    {
        $idCustomer=$this->getIdCustomer();

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

        return response()->json([
            'idCustomer' => $idCustomer,
            'mois' => $mois
        ]);
    }

    public function indexCalendar()
    {
        return view('formateurs.agendas.calendar');
    }

    public function getAllSeances($idProjet)
    {
        $userId = Auth::user()->id;
        $seances[] = DB::table('v_seances_form')
            ->select('idSeance', 'idFormateur', 'dateSeance', 'id_google_seance', 'heureDebut', 'heureFin', 'idSalle', 'idProjet', 'salle_name', 'salle_quartier', 'project_title', 'project_description', 'idModule', 'module_name', 'ville')
            ->where('idFormateur', $userId)
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
        return response()->json(['seances' => $events]);
    }

    public function getApprenantProject($idProjet)
    {
        $apprs = DB::table('v_list_apprenants')
            ->select('idEmploye', 'emp_initial_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_photo', 'emp_matricule', 'emp_phone', 'etp_name')
            ->where('idProjet', $idProjet)
            ->orderBy('emp_name', 'asc')
            ->get();

        return count([$apprs]);
    }



    public function update(Request $req, $idSeance)
    {
        /*$req->validate([
            'dateSeance' => 'required|after_or_equal:today',
            'heureDebut' => 'required',
            'heureFin' => 'required|after:heureDebut',
            //'idFormateur' => 'required',
        ]);*/

        $update = DB::table('seances')
            ->where('idSeance', $idSeance)
            ->update([
                'dateSeance' => Carbon::parse($req->dateSeance)->format('Y-m-d'),
                'heureDebut' => $req->heureDebut,
                'heureFin' =>   $req->heureFin,

                //'id_google_seance' => $req->id_google_seance,
            ]);

        if ($update == 1) {
            return response()->json(['success' => 'Succès...']);
        } else {
            return response()->json(['error' => 'Erreur inconnue !']);
        }
    }

    public function destroy($idSeance)
    {
        $delete = DB::table('seances')->where('idSeance', $idSeance)->delete();

        if ($delete == 1) {
            return response()->json(['success' => 'Succès']);
        } else {
            return response()->json(['error' => 'Erreur inconnue !']);
        }
    }

    // Récupère le dernier élément de la vue v_seances
    public function getLastFieldVueSeances()
    {

        $lastVueSeance = DB::table('v_seances')->latest('idSeance')->first();

        return response()->json(['seance' => $lastVueSeance]);
    }


    public function store(Request $req)
    {
        $req->validate([
            'dateSeance' => 'required|date',
            'heureDebut' => 'required',
            'heureFin' => 'required|after:heureDebut',
            'idProjet' => 'required',
        ]);

        $insert = DB::table('seances')->insert([
            'dateSeance' => $req->dateSeance,
            'heureDebut' => $req->heureDebut,
            'heureFin' => $req->heureFin,
            'idProjet' => $req->idProjet,
            //'intervalle' => $req->intervalle,
        ]);

        return response()->json($insert);
    }
}
