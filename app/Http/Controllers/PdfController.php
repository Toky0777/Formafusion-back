<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Dompdf\Dompdf;
use Dompdf\Options;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    // Ces méthodes sont conservées car elles sont utilisées directement par la fonction exportPdf
    public function getModules($idDomaine, $id)
    {
        $get_mod = DB::table('v_module_cfps')
            ->select('idDomaine', 'moduleName', 'idModule', 'prix', 'dureeJ', 'dureeH', 'moduleStatut', 'module_image', 'module_is_complete', 'cfpName', 'logo', 'module_level_name')
            ->where('idCustomer', $id)
            ->whereNot('moduleName', 'Default module')
            ->where('idDomaine', $idDomaine)
            ->get();

        $modules = [];
        foreach ($get_mod as $gm) {
            $modules[] = [
                'idDomaine' => $gm->idDomaine,
                'idModule' => $gm->idModule,
                'module_name' => $gm->moduleName,
                'prix' => $gm->prix,
                'dureeJ' => $gm->dureeJ,
                'dureeH' => $gm->dureeH,
                'moduleStatut' => $gm->moduleStatut,
                'module_image' => $gm->module_image,
                'module_is_complete' => $gm->module_is_complete,
                'cfp_name' => $gm->cfpName,
                'logo_cfp' => $gm->logo,
                'module_level_name' => $gm->module_level_name,
                'note' => $this->getEval($gm->idModule)
            ];
        }

        return $modules;
    }

    private function getProjectByModule($idModule)
    {
        $projects = DB::table('v_projet_cfps')
            ->select('idProjet')
            ->where('idModule', $idModule)
            ->pluck('idProjet');
        return $projects;
    }

    private function getEval($idModule)
    {
        $projectIds = $this->getProjectByModule($idModule);

        $result = DB::table('eval_chauds')
            ->select(
                DB::raw('SUM(firstNotes.generalApreciate) as sumFirstNotes'),
                DB::raw('COUNT(DISTINCT firstNotes.idEmploye) as totalEmployees')
            )
            ->fromSub(function ($query) use ($projectIds) {
                $query->select('idEmploye', 'idProjet', 'generalApreciate')
                    ->from('eval_chauds')
                    ->whereIn('idProjet', $projectIds)
                    ->whereNotNull('generalApreciate')
                    ->groupBy('idEmploye', 'idProjet');
            }, 'firstNotes')
            ->first();

        $average = $result->totalEmployees > 0 ? $result->sumFirstNotes / $result->totalEmployees : 0;

        return [
            'totalEmployees' => $result->totalEmployees,
            'average' => round($average, 1)
        ];
    }

    public function getPrograms($idModule)
    {
        return DB::table('programmes')
            ->select('program_title', 'program_description', 'idModule')
            ->where('idModule', $idModule)
            ->get();
    }

    private function getModuleDomaine($idDomaine)
    {
        return DB::table('v_module_cfps')->select('idDomaine')->where('idDomaine', $idDomaine)->where('moduleStatut', 1)->count();
    }

    public function getProjectInterCfp($idProjet)
    {
        $project_cfp = DB::table('v_projet_cfps_inters')
            ->select(
                'idProjet',
                'dateDebut as dateDebut',
                'dateFin as dateFin',
                'project_title',
                'module_name as moduleName',
                'ville',
                'project_status',
                'project_description',
                'project_type',
                'logo_cfp',
                'idCfp_inter',
                'idCfp_inter'
            )
            ->where('project_status', "Planifié")
            ->where('idProjet', $idProjet)
            ->where('project_type', 'Inter')
            ->orderBy('dateDebut')
            ->first();
        return $project_cfp;
    }

    public function getProjectCfp($idModule)
    {
        $project_cfp = DB::table('v_projet_cfps_inters')
            ->select(
                'idProjet',
                'dateDebut as dateDebut',
                'dateFin as dateFin',
                'project_title',
                'module_name as moduleName',
                'ville_name as ville',
                'project_status',
                'project_description',
                'project_type',
                'logo_cfp',
                'idCfp_inter',
                'idCfp_inter'
            )
            ->where('project_status', "Planifié")
            ->where('idModule', $idModule)
            ->where('project_type', 'Inter')
            ->orderBy('dateDebut')
            ->get();
        return $project_cfp;
    }

    // Méthodes utilitaires pour la conversion des dates (si elles ne sont pas déjà dans un helper)
    private function monthConverted($date)
    {
        return Carbon::parse($date)->locale('fr')->translatedFormat('j F Y');
    }

    private function dateConverted($date)
    {
        return Carbon::parse($date)->locale('fr')->translatedFormat('j F Y');
    }

    // Méthodes pour les sessions, formulaires, places (si spécifiques au contexte PDF)
    // Sinon, ces méthodes pourraient être déplacées dans ProjectDetailController ou un Service/Repository
    private function sessionsGroupedByDate($idProjet, $idModule)
    {
        // Cette logique doit être implémentée ou récupérée depuis ProjectDetailController si elle est générique
        // Pour l'instant, je la laisse ici si elle a une spécificité pour le PDF
        return []; // Placeholder
    }

    private function getForms($idProjet)
    {
        // Cette logique doit être implémentée ou récupérée depuis ProjectDetailController si elle est générique
        return []; // Placeholder
    }

    private function getNbPlace($idProjet)
    {
        // Cette logique doit être implémentée ou récupérée depuis ProjectDetailController si elle est générique
        return 0; // Placeholder
    }

    private function placeIsAvailable($idProjet)
    {
        // Cette logique doit être implémentée ou récupérée depuis ProjectDetailController si elle est générique
        return true; // Placeholder
    }

    public function exportPdf($id)
    {
        $module = DB::table('v_module_cfps AS M')
            ->join('customers AS C', 'C.idCustomer', '=', 'M.idCustomer')
            ->select(
                'idModule',
                'module_image',
                'reference',
                'moduleName',
                'moduleStatut',
                'M.description',
                'minApprenant',
                'dureeH',
                'dureeJ',
                DB::raw('COALESCE(maxApprenant, 0) as maxApprenant'),
                'prix',
                'prixGroupe',
                'M.idCustomer',
                'initialName',
                'cfpName as nameCfp',
                'C.logo as etp_logo',
                'nomDomaine',
                'idDomaine',
                'module_is_complete',
                'module_subtitle',
                'module_level_name'
            )
            ->whereNot('moduleName', 'Default module')
            ->where('idModule', $id)
            ->orderBy('moduleName', 'desc')
            ->first();

        if (!$module) {
            \Log::warning("Module with ID {$id} not found for PDF export.");
            return response()->json(['error' => 'Module not found'], 404);
        }

        $idCustomer = $module->idCustomer;
        $cfp = DB::table('customers')->select('*')->where('idCustomer', $idCustomer)->first();
        $cibles = DB::table('cible_modules')->where('idModule', $module->idModule)->pluck('cible');
        $prerequis = DB::table('prerequis_modules')->where('idModule', $module->idModule)->pluck('prerequis_name');
        $all_domaines = DB::table('domaine_formations')->select('idDomaine', 'nomDomaine')->orderBy('nomDomaine')->get();

        $domaines = [];
        foreach ($all_domaines as $doma) {
            $domaines[] = [
                'idDomaine' => $doma->idDomaine,
                'nomDomaine' => $doma->nomDomaine,
                'nb_module' => $this->getModuleDomaine($doma->idDomaine)
            ];
        }

        $objectifs = DB::table('objectif_modules')
            ->select('idObjectif', 'objectif', 'idModule')
            ->where('idModule', $module->idModule)
            ->get();

        $projects_with_sessions = [];
        $project_cfp = $this->getProjectCfp($id);

        // NOTE: Les méthodes sessionsGroupedByDate, getForms, getNbPlace, placeIsAvailable
        // devraient être implémentées ici si elles sont spécifiques au contexte PDF,
        // ou appelées depuis ProjectDetailController si elles sont génériques.
        foreach ($project_cfp as $p) {
            $projects_with_sessions[$p->idProjet] = [
                'project' => $p,
                'sessionsGroupedByDate' => $this->sessionsGroupedByDate($p->idProjet, $id), // À implémenter
                'projectStartDate' => $this->monthConverted($p->dateDebut),
                'projectEndDate' => $this->dateConverted($p->dateFin),
                'forms' => $this->getForms($p->idProjet), // À implémenter
                'ville' => $p->ville,
                'nbPlace' => $this->getNbPlace($p->idProjet), // À implémenter
                'availability' => $this->placeIsAvailable($p->idProjet) // À implémenter
            ];
        }

        $prog = $this->getPrograms($id);
        $note = $this->getEval($id);

        $get_domaines = DB::table('v_module_cfps')
            ->select('idDomaine', 'nomDomaine')
            ->where('moduleStatut', 1)
            ->where('idCustomer', $idCustomer)
            ->whereNot('moduleName', "Default module")
            ->groupBy('idDomaine', 'nomDomaine')
            ->get();

        $onlineModules = [];
        foreach ($get_domaines as $d) {
            $modules = $this->getModules($d->idDomaine, $idCustomer);
            $onlineModules[] = [
                'idDomaine' => $d->idDomaine,
                'nomDomaine' => $d->nomDomaine,
                "modules" => $modules
            ];
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('debugKeepTemp', true);
        $options->set('debugCss', true);
        $options->set('logOutputFile', storage_path('logs/dompdf_debug.log'));

        $dompdf = new Dompdf($options);

        $html = view('CFP.Reporting.module', [
            'module' => $module,
            'cfp' => $cfp,
            'cibles' => $cibles,
            'prerequis' => $prerequis,
            'domaines' => $domaines,
            'objectifs' => $objectifs,
            'programmes' => $prog,
            'projects_with_sessions' => $projects_with_sessions,
            'note' => $note,
            'onlineModules' => $onlineModules
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return Response::make($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"fiche_module_{$id}.pdf\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
