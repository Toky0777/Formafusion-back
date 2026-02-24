<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgendaEmpController extends Controller
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

        $seances = DB::table('v_seances_appr')
            ->select('idSeance', 'idCfp', 'idEtp_intra', 'dateSeance', 'idTypeProjet', 'id_google_seance', 'heureDebut', 'heureFin', 'idSalle', 'idProjet', 'salle_name', 'salle_quartier', 'project_title', 'project_description', 'idModule', 'module_name', 'ville')
            ->whereIn('project_type', ['Intra', 'Inter', 'Interne'])
            ->whereIn('project_status', ['Reporté', 'En cours', 'Terminé', 'Planifié', 'Annulé'])
            ->where(
                'idEmploye',
                $this->getIdEmploye()
            )
            //->whereYear('dateSeance', $year)
            ->groupBy('idSeance')
            ->get();

        $seanceCount = count($seances);

        return response()->json([
            'mois' => $mois, 
            'seanceCount' => $seanceCount,
            'seances' => $seances
        ]);
    }


    public function getEvent()
    {
        $projets = DB::table('v_seance_appr')
            ->select('idSeance', 'idProjet', 'dateSeance', 'project_is_active', 'heureDebut', 'heureFin', 'project_type', 'idFormateur', 'nameForm', 'firstNameForm', 'nomSalle AS salle', 'ville', 'module_name', 'projectName', 'customerName', 'idEmploye', 'formation')
            ->where('idEmploye', Auth::user()->id)
            ->where('project_is_active', 1)
            ->get();

        $events = [];

        foreach ($projets as $p) {
            $events[] = [
                'id' => $p->idProjet,
                'calendarId' => $p->idFormateur,
                'title' => $p->module_name,
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
                    'Client' => $p->customerName,
                    'Ville' => $p->ville,
                    'Type' => $p->project_type,
                    // 'Financement' => $p->paiement,
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

    private function getIdEmploye()
    {
        return  Auth::user()->id;
    }

    public function getEvents()
    {

        $seances = DB::table('v_seances_appr')
            ->select('idSeance', 'idCfp', 'idEtp_intra', 'dateSeance', 'idTypeProjet', 'id_google_seance', 'heureDebut', 'heureFin', 'idSalle', 'idProjet', 'salle_name', 'salle_quartier', 'project_title', 'project_description', 'idModule', 'module_name', 'ville')
            ->whereIn('project_type', ['Intra', 'Inter', 'Interne'])
            ->whereIn('project_status', ['Reporté', 'En cours', 'Terminé', 'Planifié', 'Annulé'])
            ->where(
                'idEmploye',
                $this->getIdEmploye()
            )
            ->groupBy('idSeance')
            ->get();

        foreach ($seances as $seance) {
            $events[] =  [
                'idSeance' => $seance->idSeance,
                'idCfp' => $seance->idCfp,
                'idEtp' => $seance->idEtp_intra,
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
                'apprCount' => $this->getApprenantProject($seance->idProjet),
                'apprCountInter' => $this->getApprenantProjectInter($seance->idProjet),
                'apprCountIntra' => $this->getApprenantProjectIntra($seance->idProjet),
                'imgModule' => $this->getFieldsProject($seance->idProjet)->module_image,
                'statut' => $this->getFieldsProject($seance->idProjet)->project_status,
                'nameEtp' => $this->getFieldsProject($seance->idProjet)->etp_name,
                'nameEtps' => $this->getEtpProjectInter($seance->idProjet, $seance->idCfp),
                'materiels' => $this->getPrestation($seance->idModule),
                'typeProjet' => $this->getFieldsProject($seance->idProjet)->project_type,
            ];
        }
        return response()->json(['seances' => $events]);
    }

    public function getFormProject($idProjet)
    {
        $forms = DB::table('v_formateur_cfps')
            ->select('idFormateur', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
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

        return count([$apprs]);
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

    public function getApprenantProjectInter($idProjet)
    {

        $apprs = DB::table('v_list_apprenant_inter_added')
            ->select('*')
            ->where('idProjet', $idProjet)
            ->get();

        return count([$apprs]);
    }

    public function getFieldsProject($idProjet)
    {

        $projet = DB::table('v_projet_emps')
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

    public function countSeance($month, $year)
    {
        $countSeance = DB::select("SELECT COUNT(idSeance) AS nbSeance FROM v_seances_appr WHERE MONTH(dateSeance) = ? AND YEAR(dateSeance) = ?", [$month, $year]);

        return response()->json($countSeance);
    }
}
