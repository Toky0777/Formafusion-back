<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
use LengthException;
use Illuminate\Http\Request;
use App\Exports\FinanceExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FormationExcelExport;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;

class ReportingController extends Controller
{

    public function idCfp()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idCustomer;
    }
    public function formation(Request $request)
    {
        $idCfp = Customer::idCustomer();
        $createdCfp = Auth::user()->created_at->format('m-d-Y');
        $all_learner = DB::table('v_apprenant_information as V')
            ->join('mdls as M', 'M.idModule', 'V.idModule')
            ->select('V.emp_matricule', 'V.module_name', 'V.emp_name', 'V.emp_firstname', 'V.emp_fonction', 'V.salle_name', 'V.salle_quartier', 'V.project_status', 'V.project_type', 'V.etp_name', 'V.cfp_name', 'V.dateDebut', 'V.dateFin', 'V.dureeH')
            ->where('V.idCfp', $idCfp)
            ->where('M.idCustomer', $idCfp)
            ->get();

        $all_cfp_formation = DB::table('mdls')
            ->select('moduleName as module_name', 'idModule')
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleStatut', 1)
            ->get();

        $data_filter = ['Tous les dates', 'Tous les formation'];
        $latestDate = DB::table('v_apprenant_information')->max('dateDebut');
        $earliestDate = DB::table('v_apprenant_information')->min('dateDebut');

        if (is_Null($latestDate) || is_Null($earliestDate)) {
            $formatedEarliestDate = Carbon::today()->format('m-d-Y');
            $formatedLatestDate = Carbon::today()->format('m-d-Y');
        } else {
            $formatedEarliestDate = Carbon::createFromFormat('Y-m-d', $earliestDate)->format('m-d-Y');
            $formatedLatestDate = Carbon::createFromFormat('Y-m-d', $latestDate)->format('m-d-Y');
        }

        // $request->session()->put('data', $all_learner);
        // $request->session()->put('data_filter', $data_filter);

        return response()->json([
            'all_learner' => $all_learner, 
            'all_cfp_formation' => $all_cfp_formation, 
            'formatedEarliestDate' => $formatedEarliestDate, 
            'formatedLatestDate' => $formatedLatestDate, 
            'data_filter' => $data_filter
        ]);
    }
    public function historique(Request $request)
    {
        $idCfp = Auth::user()->id;
        $createdCfp = Auth::user()->created_at->format('m-d-Y');
        $all_learner = DB::table('v_apprenant_information')
            ->select('emp_matricule', 'module_name', 'emp_name', 'emp_firstname', 'emp_fonction', 'salle_name', 'salle_quartier', 'project_status', 'project_type', 'etp_name', 'cfp_name', 'dateDebut', 'dateFin', 'dureeH')
            ->where('idCfp', $idCfp)
            ->get();
        $all_cfp_formation = DB::table('v_apprenant_information')
            ->select('idModule', 'module_name')
            ->where('idCfp', $idCfp)
            ->whereNotNull('module_name')
            ->distinct()
            ->get();

        $data_filter = ['Tous les dates', 'Tous les formation'];
        $latestDate = DB::table('v_apprenant_information')->max('dateDebut');
        $earliestDate = DB::table('v_apprenant_information')->min('dateDebut');

        if (is_Null($latestDate) || is_Null($earliestDate)) {
            $formatedEarliestDate = Carbon::today()->format('m-d-Y');
            $formatedLatestDate = Carbon::today()->format('m-d-Y');
        } else {
            $formatedEarliestDate = Carbon::createFromFormat('Y-m-d', $earliestDate)->format('m-d-Y');
            $formatedLatestDate = Carbon::createFromFormat('Y-m-d', $latestDate)->format('m-d-Y');
        }

        $request->session()->put('data', $all_learner);
        $request->session()->put('data_filter', $data_filter);

        return view('CFP.Reporting.formation.historique', compact(['all_learner', 'all_cfp_formation', 'formatedEarliestDate', 'formatedLatestDate', 'data_filter']));
    }

    public function searchName(string $name)
    {
        $apprenants = DB::table('v_apprenant_information')
            ->select('idEmploye', 'emp_matricule', 'module_name', 'emp_name', 'emp_firstname', 'emp_fonction', 'salle_name', 'salle_quartier', 'project_status', 'project_type', 'etp_name', 'cfp_name', 'dateDebut', 'dateFin', 'dureeH')
            ->where('idCfp', $this->idCfp()) // Utilisez une seule condition
            ->where('role_id', 4)
            ->where(function ($query) use ($name) {
                $query->where('emp_name', 'LIKE', '%' . $name . '%')
                    ->orWhere('emp_firstname', 'LIKE', '%' . $name . '%');
            })
            ->groupBy('idEmploye', 'emp_matricule', 'module_name', 'emp_name', 'emp_firstname', 'emp_fonction', 'salle_name', 'salle_quartier', 'project_status', 'project_type', 'etp_name', 'cfp_name', 'dateDebut', 'dateFin', 'dureeH')
            ->get();

        return response()->json(['apprenants' => $apprenants]);
    }

    public function filterFormation(Request $request)
    {
        // Validation des champs
        $validate = Validator::make($request->all(), [
            'daterange' => 'required',
            'formation' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validate->errors()
            ]);
        }

        // Obtenir l'ID du CFP connecté
        $idCfp = Auth::user()->id;

        try {
            // Extraction des dates à partir du champ 'daterange'
            $returnDate = explode(" - ", $request->daterange);

            // Vérification que le tableau contient bien deux dates
            if (count($returnDate) !== 2) {
                throw new \Exception('Le format du champ de la plage de dates est incorrect.');
            }

            // Convertir les dates au format 'Y-m-d' pour la base de données
            $date1 = Carbon::createFromFormat('m/d/Y', trim($returnDate[0]))->format('Y-m-d');
            $date2 = Carbon::createFromFormat('m/d/Y', trim($returnDate[1]))->format('Y-m-d');
        } catch (\Exception $e) {
            // Retourner une erreur en cas de problème de formatage
            return response()->json([
                'status' => 400,
                'message' => 'Format de date incorrect : ' . $e->getMessage()
            ]);
        }

        // Construction de la requête
        $query = DB::table('v_apprenant_information')
            ->select('emp_matricule', 'module_name', 'emp_name', 'emp_firstname', 'emp_fonction', 'salle_name', 'salle_quartier', 'project_status', 'project_type', 'etp_name', 'cfp_name', 'dateDebut', 'dateFin', 'dureeH')
            ->where('idCfp', $idCfp)
            ->where('dateDebut', '>=', $date1)
            ->where('dateDebut', '<=', $date2);

        // Si une formation spécifique est sélectionnée, on ajoute cette condition à la requête
        if ($request->formation !== 'all') {
            $query->where('idModule', $request->formation);
        }

        // Récupérer les résultats
        $all_learner = $query->get();

        // Obtenir le nom du module si un filtre spécifique est appliqué
        if ($request->formation !== 'all') {
            $queryModules = DB::table('mdls')->select('moduleName')->where('idModule', $request->formation)->first();
            $moduleName = $queryModules ? $queryModules->moduleName : 'Formation inconnue';
        } else {
            $moduleName = 'Tous les formations';
        }
        $data_filter = [$request->daterange, $moduleName];

        // Récupérer les dates les plus récentes et les plus anciennes
        $latestDate = DB::table('v_apprenant_information')->max('dateDebut');
        $earliestDate = DB::table('v_apprenant_information')->min('dateDebut');

        // Si les dates existent, formater sinon prendre la date du jour
        if (is_null($latestDate) || is_null($earliestDate)) {
            $formatedEarliestDate = Carbon::today()->format('m-d-Y');
            $formatedLatestDate = Carbon::today()->format('m-d-Y');
        } else {
            $formatedEarliestDate = Carbon::createFromFormat('Y-m-d', $earliestDate)->format('m-d-Y');
            $formatedLatestDate = Carbon::createFromFormat('Y-m-d', $latestDate)->format('m-d-Y');
        }

        // Récupérer toutes les formations disponibles pour le CFP
        $all_cfp_formation = DB::table('mdls')
            ->select('moduleName as module_name', 'idModule')
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleStatut', 1)
            ->get();

        // Stocker les données dans la session
        // $request->session()->put('data', $all_learner);
        // $request->session()->put('data_filter', $data_filter);

        // Retourner la vue avec les données
        return response()->json([
            'status' => 200,
            'all_learner' => $all_learner, 
            'all_cfp_formation' => $all_cfp_formation, 
            'formatedEarliestDate' => $formatedEarliestDate, 
            'formatedLatestDate' => $formatedLatestDate, 
            'data_filter' => $data_filter
        ]);
    }

    public function exportFinanceXl()
    {
        return Excel::download(new FinanceExport, 'Finance.xlsx');
    }
    public function exportXl(Request $request)
    {
        return Excel::download(new FormationExcelExport($request->session()->get('data')), 'Formation.xlsx');
    }
    
    public function exportPdf()
    {
        $all_learner = session()->get('data');
        $data_filter = session()->get('data_filter');
        $pdf = PDF::loadView('CFP.Reporting.formation.dataExportPdf', compact(['all_learner', 'data_filter']))->setPaper('a4', 'landscape')->setOption(['defaultFont' => 'Helvetica']);
        return $pdf->download('reportingformation.pdf');
    }
}
