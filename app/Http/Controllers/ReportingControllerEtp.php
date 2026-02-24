<?php

namespace App\Http\Controllers;


use App\Exports\FinanceExport;
use App\Traits\ProjectQuery;
use App\Traits\StudentQuery;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
// use FormationExcelExport;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session; // Use Session facade for clarity
use Illuminate\Support\Facades\Validator;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FormationExcelExport;
use App\Exports\ClientExcelExport;
use App\Exports\CoursExportEtp;

class ReportingControllerEtp extends Controller
{
    use ProjectQuery;
    use StudentQuery;
    //Formation

    public function formation(Request $request)
    {
        
        $idEtp = Auth::user()->id;
        $createdCfp = Auth::user()->created_at->format('m-d-Y');
        $all_learner = DB::table('v_apprenant_information')
            ->select('emp_matricule', 'module_name', 'emp_name', 'emp_firstname', 'emp_fonction', 'salle_name', 'salle_quartier', 'project_status', 'project_type', 'etp_name', 'cfp_name', 'dateDebut', 'dateFin', 'dureeH')
            ->where('idEtp', $idEtp)
            ->get();
        $all_etp_formation = DB::table('v_apprenant_information')
            ->select('idModule', 'module_name')
            ->where('idEtp', $idEtp)
            ->whereNotNull('module_name')
            ->distinct()
            ->get();

        $data_filter = ['Tous les dates', 'Tous les formation'];
        $latestDate = DB::table('v_apprenant_information')->max('dateDebut');
        $earliestDate = DB::table('v_apprenant_information')->min('dateDebut');
        if (!is_null($earliestDate)) {
            $formatedEarliestDate = Carbon::createFromFormat('Y-m-d', $earliestDate)->format('m-d-Y');
        } else {
            $formatedEarliestDate = 'Date non disponible';
        }

        if (!is_null($latestDate)) {
            $formatedLatestDate = Carbon::createFromFormat('Y-m-d', $latestDate)->format('m-d-Y');
        } else {
            $formatedLatestDate = 'Date non disponible';
        }

        $request = Session()->put('data', $all_learner);
        $request = Session()->put('data_filter', $data_filter);

        // dd($earliestDate, $latestDate);
        return response()->json([
            'all_learner' => $all_learner, 
            'all_etp_formation' => $all_etp_formation, 
            'data_filter' => $data_filter, 
            'formatedEarliestDate' => $formatedEarliestDate, 
            'formatedLatestDate' => $formatedLatestDate
        ]);
    }
    public function filterFormation(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'daterange' => 'required',
            'formation' => 'required'
        ]);
        $idEtp = Auth::user()->id;
        $createdCfp = Auth::user()->created_at->format('m-d-Y');

        $returnDate = explode(" - ", $request->daterange);
        $date1 = Carbon::createFromFormat('m/d/Y', $returnDate[0])->format('Y-m-d');
        $date2 = Carbon::createFromFormat('m/d/Y', $returnDate[1])->format('Y-m-d');

        $query = DB::table('v_apprenant_information')
            ->select('emp_matricule', 'module_name', 'emp_name', 'emp_firstname', 'emp_fonction', 'salle_name', 'salle_quartier', 'project_status', 'project_type', 'etp_name', 'cfp_name', 'dateDebut', 'dateFin', 'dureeH')
            ->where('idEtp', $idEtp)
            ->where('dateDebut', '>=', $date1)
            ->where('dateDebut', '<=', $date2);
        if ($request->formation !== 'all') {
            $query->where('idModule', $request->formation);
        }
        $all_learner = $query->get();

        if ($request->formation !== 'all') {
            $queryModules = DB::table('mdls')->select('moduleName')->where('idModule', $request->formation)->first();
            $moduleName = $queryModules->moduleName;
        } else {
            $moduleName = 'Tous les formation';
        }
        $data_filter = [$request->daterange, $moduleName];

        $latestDate = DB::table('v_apprenant_information')->max('dateDebut');
        $earliestDate = DB::table('v_apprenant_information')->min('dateDebut');
        if (!is_null($earliestDate)) {
            $formatedEarliestDate = Carbon::createFromFormat('Y-m-d', $earliestDate)->format('m-d-Y');
        } else {
            $formatedEarliestDate = 'Date non disponible';
        }

        if (!is_null($latestDate)) {
            $formatedLatestDate = Carbon::createFromFormat('Y-m-d', $latestDate)->format('m-d-Y');
        } else {
            $formatedLatestDate = 'Date non disponible';
        }

        $all_etp_formation = DB::table('v_apprenant_information')
            ->select('idModule', 'module_name')
            ->where('idEtp', $idEtp)
            ->whereNotNull('module_name')
            ->distinct()
            ->get();

        $request=Session()->put('data', $all_learner);
        $request=Session()->put('data_filter', $data_filter);

        return response()->json([
            'all_learner' => $all_learner, 
            'all_etp_formation' => $all_etp_formation, 
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
        return Excel::download(new FormationExcelExport($request->session()->get('data')), 'FormationETP.xlsx');
    }
    public function exportPdf()
    {

        $all_learner = session()->get('data');
        $data_filter = session()->get('data_filter');
        $pdf = PDF::loadView('ETP.reportings.dataForm', compact(['all_learner', 'data_filter']))->setPaper('a4', 'landscape')->setOption(['defaultFont' => 'Helvetica']);
        return $pdf->download('reportingformationETP.pdf');
    }

    // Apprenant
    public function apprenantEtp(Request $request)
    {
        $idEtp = Auth::user()->id;
        $createdCfp = Auth::user()->created_at->format('m-d-Y');
        $all_learner = DB::table('v_apprenant_information')
            ->select('emp_matricule', 'module_name', 'emp_name', 'emp_firstname', 'emp_fonction', 'salle_name', 'salle_quartier', 'project_status', 'project_type', 'etp_name', 'cfp_name', 'dateDebut', 'dateFin', 'dureeH', 'taux_de_presence')
            ->where('idEtp', $idEtp)
            ->get();
        $all_etp_formation = DB::table('v_apprenant_information')
            ->select('idModule', 'module_name')
            ->where('idEtp', $idEtp)
            ->whereNotNull('module_name')
            ->distinct()
            ->get();

        $data_filter = ['Tous les dates', 'Tous les formation'];
        $latestDate = DB::table('v_apprenant_information')->max('dateDebut');
        $earliestDate = DB::table('v_apprenant_information')->min('dateDebut');
        if (!is_null($earliestDate)) {
            $formatedEarliestDate = Carbon::createFromFormat('Y-m-d', $earliestDate)->format('m-d-Y');
        } else {
            $formatedEarliestDate = 'Date non disponible';
        }

        if (!is_null($latestDate)) {
            $formatedLatestDate = Carbon::createFromFormat('Y-m-d', $latestDate)->format('m-d-Y');
        } else {
            $formatedLatestDate = 'Date non disponible';
        }

        $request=Session()->put('data', $all_learner);
        $request=Session()->put('data_filter', $data_filter);

        return response()->json([
            'all_learner' => $all_learner, 
            'all_etp_formation' => $all_etp_formation, 
            'data_filter' => $data_filter, 
            'formatedEarliestDate' => $formatedEarliestDate, 
            'formatedLatestDate' => $formatedLatestDate
        ]);
    }
public function exportAppEtpXl(Request $request)
{
    $data = $request->input('data');

    if (empty($data) || !is_array($data)) {
        return response()->json(['error' => 'Aucune donnée à exporter.'], 422);
    }

    return Excel::download(new FormationExcelExport($data), 'FormationETP.xlsx');
}
public function exportAppEtpPdf(Request $request)
{
    set_time_limit(300);
    ini_set('memory_limit', '1024M');

    if (!$request->isJson()) {
        return response()->json(['error' => 'Le contenu doit être au format JSON'], 400);
    }

    $requestData = $request->json()->all();

    if (!array_key_exists('data', $requestData)) {
        return response()->json(['error' => 'La clé "data" est manquante dans la requête'], 400);
    }

    $all_learner = $requestData['data'];

    if (!is_array($all_learner)) {
        return response()->json(['error' => 'Les données doivent être un tableau'], 400);
    }

    if (empty($all_learner)) {
        return response()->json(['error' => 'Aucune donnée à exporter'], 422);
    }

    try {
        $data_filter = ['Filtres appliqués', 'Export direct'];
        
        // Debug: Vérifiez les données reçues
        \Log::info('Données reçues pour export PDF:', [
            'count' => count($all_learner),
            'first_item' => !empty($all_learner) ? $all_learner[0] : 'empty'
        ]);

        $pdf = Pdf::loadView('ETP.reportings.dataForm', compact('all_learner', 'data_filter'))
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', false)
            ->setOption('isPhpEnabled', false)
            ->setOption('defaultFont', 'Helvetica');

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="reportingformationETP.pdf"');

    } catch (\Exception $e) {
        \Log::error('Erreur lors de la génération du PDF: '.$e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => 'Erreur lors de la génération du PDF: '.$e->getMessage()], 500);
    }
}



    // Centre de formation
    public function client(Request $request)
    {
        $idEtp = Auth::user()->id;
        $createdCfp = Auth::user()->created_at->format('m-d-Y');
        $all_learner = DB::table('v_apprenant_information')
            ->select('emp_matricule', 'module_name', 'emp_name', 'emp_firstname', 'emp_fonction', 'salle_name', 'salle_quartier', 'project_status', 'project_type', 'idCfp', 'id_cfp', 'cfp_name', 'dateDebut', 'dateFin', 'dureeH')
            ->where('idEtp', $idEtp)
            ->get();
        $all_cfp = DB::table('v_apprenant_information')
            ->select('idCfp', 'cfp_name', 'idModule')
            ->where('idEtp', $idEtp)
            ->whereNotNull('cfp_name')
            ->distinct()
            ->get();

        $data_filter = ['Tous les dates', 'Tous les formation'];

        $request = Session()->put('data', $all_learner);
        $request = Session()->put('data_filter', $data_filter);

        return response()->json([
            'all_learner' => $all_learner, 
            'all_cfp' => $all_cfp, 
            'data_filter' => $data_filter
        ]);
    }

    public function exportXlCl(Request $request)
    {
        $data = $request->input('data');
        
        if (!$data) {
            return response()->json(['error' => 'Aucune donnée fournie.'], 422);
        }

        // Décoder si les données sont envoyées comme JSON string
        $decodedData = is_string($data) ? json_decode($data, true) : $data;
        
    return Excel::download(
        new ClientExcelExport(collect($decodedData)),
        'FormationETP.xlsx',
        \Maatwebsite\Excel\Excel::XLSX
    );
    }
// app/Http/Controllers/ReportingControllerEtp.php

public function exportPdfCl(Request $request)
{
   set_time_limit(300); // 5 minutes
    ini_set('memory_limit', '1024M');  // Pas de limite de mémoire

    // 2. Vérifier que la requête contient bien des données JSON
    if (!$request->isJson()) {
        return response()->json(['error' => 'Le contenu doit être au format JSON'], 400);
    }

    // 3. Récupérer toutes les données JSON
    $requestData = $request->json()->all();

    // 4. Vérifier la présence des données
    if (!array_key_exists('data', $requestData)) {
        return response()->json(['error' => 'La clé "data" est manquante dans la requête'], 400);
    }

    $all_learner = $requestData['data'];

    // 5. Valider le format des données
    if (!is_array($all_learner)) {
        return response()->json(['error' => 'Les données doivent être un tableau'], 400);
    }

    if (empty($all_learner)) {
        return response()->json(['error' => 'Aucune donnée à exporter'], 422);
    }

    try {
        $data_filter = ['Filtres appliqués', 'Export direct'];

        // Créer le PDF
        $pdf = Pdf::loadView('ETP.reportings.dataCfp', compact('all_learner', 'data_filter'))
            ->setPaper('a4', 'landscape')
            // Désactiver le parseur HTML5 et PHP si tu n'en as pas besoin pour alléger la mémoire
            ->setOption('isHtml5ParserEnabled', false) // Réduire la consommation mémoire
            ->setOption('isPhpEnabled', false) // Réduire la consommation mémoire
            ->setOption('defaultFont', 'Helvetica');

        // Retourner le fichier PDF en réponse
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="reportingformationETP.pdf"');

    } catch (\Exception $e) {
        // Gestion des erreurs, capture d'informations supplémentaires pour le débogage
        // \Log::error('Erreur lors de la génération du PDF: '.$e->getMessage(), [
        //     'request_data' => $requestData,
        //     'all_learner_count' => count($all_learner),
        // ]);
        return response()->json(['error' => 'Erreur lors de la génération du PDF: '.$e->getMessage()], 500);
    }
}

    // Cours 
    public function cours(Request $request)
    {
        $idEtp = Auth::user()->id;
        $createdCfp = Auth::user()->created_at->format('m-d-Y');
        $all_learner = DB::table('v_apprenant_information')
            ->select('emp_matricule', 'module_name', 'emp_name', 'emp_firstname', 'emp_fonction', 'salle_name', 'salle_quartier', 'project_status', 'project_type', 'etp_name', 'cfp_name', 'dateDebut', 'dateFin', 'dureeH')
            ->where('idEtp', $idEtp)
            ->get();
        $all_etp_formation = DB::table('v_apprenant_information')
            ->select('idModule', 'module_name')
            ->where('idEtp', $idEtp)
            ->whereNotNull('module_name')
            ->distinct()
            ->get();

        $data_filter = ['Tous les dates', 'Tous les formation'];
        $latestDate = DB::table('v_apprenant_information')->max('dateDebut');
        $earliestDate = DB::table('v_apprenant_information')->min('dateDebut');
        if (!is_null($earliestDate)) {
            $formatedEarliestDate = Carbon::createFromFormat('Y-m-d', $earliestDate)->format('m-d-Y');
        } else {
            $formatedEarliestDate = 'Date non disponible';
        }

        if (!is_null($latestDate)) {
            $formatedLatestDate = Carbon::createFromFormat('Y-m-d', $latestDate)->format('m-d-Y');
        } else {
            $formatedLatestDate = 'Date non disponible';
        }

        $request = Session()->put('data', $all_learner);
        $request = Session()->put('data_filter', $data_filter);

        return response()->json([
            'all_learner' => $all_learner, 
            'all_etp_formation' => $all_etp_formation, 
            'data_filter' => $data_filter, 
            'formatedEarliestDate' => $formatedEarliestDate, 
            'formatedLatestDate' => $formatedLatestDate
        ]);
    }

public function exportXlCours(Request $request)
{
    $data = $request->input('data'); // ou json_decode si tu envoies un JSON
    return Excel::download(new CoursExportEtp($data), 'CoursETP.xlsx');
}

public function exportPdfCours(Request $request)
{
   set_time_limit(300); // 5 minutes
    ini_set('memory_limit', '1024M');  // Pas de limite de mémoire

    // 2. Vérifier que la requête contient bien des données JSON
    if (!$request->isJson()) {
        return response()->json(['error' => 'Le contenu doit être au format JSON'], 400);
    }

    // 3. Récupérer toutes les données JSON
    $requestData = $request->json()->all();

    // 4. Vérifier la présence des données
    if (!array_key_exists('data', $requestData)) {
        return response()->json(['error' => 'La clé "data" est manquante dans la requête'], 400);
    }

    $all_learner = $requestData['data'];

    // 5. Valider le format des données
    if (!is_array($all_learner)) {
        return response()->json(['error' => 'Les données doivent être un tableau'], 400);
    }

    if (empty($all_learner)) {
        return response()->json(['error' => 'Aucune donnée à exporter'], 422);
    }

    try {
        $data_filter = ['Filtres appliqués', 'Export direct'];

        // Créer le PDF
        $pdf = Pdf::loadView('ETP.reportings.dataCours', compact('all_learner', 'data_filter'))
            ->setPaper('a4', 'landscape')
            // Désactiver le parseur HTML5 et PHP si tu n'en as pas besoin pour alléger la mémoire
            ->setOption('isHtml5ParserEnabled', false) // Réduire la consommation mémoire
            ->setOption('isPhpEnabled', false) // Réduire la consommation mémoire
            ->setOption('defaultFont', 'Helvetica');

        // Retourner le fichier PDF en réponse
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="reportingformationETP.pdf"');

    } catch (\Exception $e) {
        // Gestion des erreurs, capture d'informations supplémentaires pour le débogage
        // \Log::error('Erreur lors de la génération du PDF: '.$e->getMessage(), [
        //     'request_data' => $requestData,
        //     'all_learner_count' => count($all_learner),
        // ]);
        return response()->json(['error' => 'Erreur lors de la génération du PDF: '.$e->getMessage()], 500);
    }
}


private function buildMonthlyArray(array $projectsByMonth, string $type = 'total_ttc'): array
{
    $result = [];

    foreach ($projectsByMonth as $month => $projects) {
        if ($type === 'students') {
            $projectIds = collect($projects)->pluck('idProjet')->toArray();

            $result[$month] = is_array($projectIds)
                ? count($this->getStudents($projectIds))
                : 0;
        } else {
            $result[$month] = collect($projects)->sum($type);
        }
    }

    return $result;
}


    // Chiffre d'affaire
    public function chiffreAEtp()
{
    $user = Auth::user();
    $etpId = $user->id;
    $currentMonth = Carbon::now()->month;
    $currentYear = Carbon::now()->year;
    $lastYear = $currentYear - 1;

    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $remainingMonths = range($currentMonth, 12);

    // Données actuelles et année précédente
    $currentMonthProjects = $this->getEtpProjects($currentMonth, 'Terminé', $etpId);
    $lastYearCurrentMonthProjects = $this->getEtpProjects($currentMonth, 'Terminé', $etpId, $lastYear);

    $totalCost = $currentMonthProjects->sum('total_ttc');
    $lastYearTotalCost = $lastYearCurrentMonthProjects->sum('total_ttc');

    $currentYearProjects = $this->getEtpProjectsByYear('Terminé', $etpId);
    $lastYearProjects = $this->getEtpProjectsByYear('Terminé', $etpId, $lastYear);

    // Apprenants
    $currentStudentIds = $this->getStudents($currentYearProjects->pluck('idProjet')->toArray());
    $lastYearStudentIds = $this->getStudents($lastYearProjects->pluck('idProjet')->toArray());

    $totalTrained = count($currentStudentIds);
    $lastTotalTrained = count($lastYearStudentIds);

    $uniqueTrained = collect($currentStudentIds)->unique()->count();
    $lastUniqueTrained = collect($lastYearStudentIds)->unique()->count();

    // Projets en cours ou planifiés pour les mois restants
    $upcomingProjects = $this->getEtpProjects($remainingMonths, ['En cours', 'Planifié'], $etpId);
    $totalYtdCost = $currentYearProjects->sum('total_ttc');

    $costPerEmployee = $uniqueTrained ? $totalYtdCost / $uniqueTrained : 0;
    $lastCostPerEmployee = $lastUniqueTrained ? $lastYearTotalCost / $lastUniqueTrained : 0;

    // Données par mois
    $projectsByMonth = $this->groupProjectsByMonth($currentYearProjects);
    $lastYearProjectsByMonth = $this->groupProjectsByMonth($lastYearProjects);
    $forecastProjectsByMonth = $this->groupProjectsByMonth($upcomingProjects);

    $monthlyCosts = $this->buildMonthlyArray($projectsByMonth, 'total_ttc');
    $monthlyStudents = $this->buildMonthlyArray($projectsByMonth, 'students');
    $monthlyForecast = $this->buildMonthlyArray($forecastProjectsByMonth, 'total_ttc');
    $monthlyLastYear = $this->buildMonthlyArray($lastYearProjectsByMonth, 'total_ttc');

    // Ajustement des mois manquants
    for ($i = 0; $i < 12; $i++) {
        $monthlyCosts[$i] = $monthlyCosts[$i] ?? 0;
        $monthlyForecast[$i] = $monthlyForecast[$i] ?? ($i < $currentMonth ? 'null' : 0);
        $monthlyStudents[$i] = $monthlyStudents[$i] ?? 0;
        $monthlyLastYear[$i] = $monthlyLastYear[$i] ?? 0;
    }

    ksort($monthlyCosts);
    ksort($monthlyForecast);
    ksort($monthlyStudents);
    ksort($monthlyLastYear);

    $lastYearYTD = collect(array_slice($monthlyLastYear, 0, $currentMonth))->sum();

    return response()->json([
        'months' => $months,
        'project_by_month' => $projectsByMonth,
        'finished_data' => $monthlyCosts,
        'forecast_data' => $monthlyForecast,
        'total_trained' => $totalTrained,
        'unique_trained' => $uniqueTrained,
        'total_cost' => $totalCost,
        'total_YTD' => $totalYtdCost,
        'cost_by_employee' => $costPerEmployee,
        'current_year_projects' => $currentYearProjects,
        'histogram_data' => $monthlyStudents,
        'last_total_trained' => $lastTotalTrained,
        'last_unique_trained' => $lastUniqueTrained,
        'last_total_cost' => $lastYearTotalCost,
        'last_cost_by_employee' => $lastCostPerEmployee,
        'last_year_YTD' => $lastYearYTD,
        'last_year_prices' => $monthlyLastYear,
        'notifications' => $user->unreadNotifications,
    ]);
}
}
