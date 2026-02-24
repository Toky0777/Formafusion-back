<?php

namespace App\Http\Controllers;

use App\Models\Seance;
use App\Models\SeanceInterne;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SeanceInterneController extends Controller
{
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

    public function getAllSeances($idProjet)
    {
        $seances = DB::table('v_seances_etp')  //----> A modifier
            ->select('idSeance', 'dateSeance', 'id_google_seance', 'heureDebut', 'heureFin', 'idSalle', 'idProjet', 'salle_name', 'salle_quartier', 'project_title', 'project_description', 'idModule', 'module_name', 'ville')
            ->where('idEtp', $this->idEtp()) //  A modifier idEtp
            ->where('idProjet', $idProjet)
            ->get();

        if (count($seances) > 0) {

            foreach ($seances as $seance) {
                $events[] =  [
                    'idSeance' => $seance->idSeance,      //<===== idSeance
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
                    'apprCount' => $this->getApprenantProject($seance->idProjet),
                    'imgModule' => $this->getFieldsProject($seance->idProjet)->module_image,
                    'statut' => $this->getFieldsProject($seance->idProjet)->project_status,
                    'nameEtp' => $this->getFieldsProject($seance->idProjet)->etp_name,
                    'typeProjet' => $this->getFieldsProject($seance->idProjet)->project_type,

                ];
            }
        } else {
            return response()->json(['pas de donnee']);
        }
        return response()->json(['seances' => $events]);
    }

    public function update(Request $req, $idSeance)
    {
        $req->validate([
            'dateSeance' => 'required|date',
            'heureDebut' => 'required',
            'heureFin' => 'required|after:heureDebut'
        ]);

        $query = DB::table('seances')->where('idSeance', $idSeance);

        if($query->exists()){
            $query->update([
                'dateSeance' => Carbon::parse($req->dateSeance)->format('Y-m-d'),
                'heureDebut' => $req->heureDebut,
                'heureFin' =>   $req->heureFin,
                //'id_google_seance' => $req->id_google_seance,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Succès'
            ]);
        }else{
            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ], 404);
        }
    }

    public function destroy($idSeance)
    {
        $query = DB::table('seances')->where('idSeance', $idSeance);
        
        if($query->exists()){
            $query->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Succès'
            ]);
        }else{
            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ], 404);
        }
    }

    // Récupère le dernier élément de la vue v_seances???
    public function getLastFieldVueSeances()
    {
        $lastVueSeance = DB::table('v_seances_etp')->latest('idSeance')->first(); // <--- A modifier

        return response()->json(['seance' => $lastVueSeance]);
    }








    

    public function idEtp()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idCustomer;
    }



    public function getFormProject($idProjet)
    {
        $forms = DB::table('v_formateur_internes')
            ->select('idEmploye', 'name AS form_name', 'firstName AS form_firstname', 'photoForm AS form_photo', 'initialNameForm AS form_initial_name')
            ->groupBy('idEmploye', 'name', 'firstName', 'photoForm', 'initialNameForm')
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

    public function getFieldsProject($idProjet)
    {

        $projet = DB::table('v_projet_etps')
            ->select('idProjet', 'dateDebut', 'dateFin', 'project_title', 'etp_name', 'ville', 'project_status', 'project_type', 'module_image', 'project_reference', 'modalite', 'idEtp')
            ->where('idProjet', $idProjet)
            ->first();
        return $projet;
    }

    public function getSeanceAndTotalTime($idProjet)
    {
        $seances = DB::table('v_seances')
            ->select('idSeance', 'dateSeance', 'heureDebut', 'heureFin', 'idProjet', 'idModule', DB::raw("TIME_FORMAT(SEC_TO_TIME(TIME_TO_SEC(intervalle_raw)), '%H:%i') AS intervalle_raw"))
            ->where('idProjet', $idProjet)
            ->orderBy('dateSeance', 'asc')
            ->get();
        $nbSeance = count($seances);

        $totalSession = DB::table('v_seances')
            ->selectRaw("IFNULL(TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(intervalle_raw))), '%H:%i'), '00:00') as sumHourSession")
            ->where('idProjet', $idProjet)
            ->groupBy('idProjet')
            ->first();

        return response()->json([
            //'seances' => $seances,
            'nbSeance' => $nbSeance,
            'totalSession' => $totalSession
        ]);
    }
}
