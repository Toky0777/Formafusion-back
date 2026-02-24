<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\GetQuery;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Services\CfpService;
use App\Services\CustomerService;
use App\Services\EntrepriseService;
use App\Traits\HasEnterprise;
use Exception;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    use GetQuery, HasEnterprise;

    public function index(EntrepriseService $etp, $idTypeEtp)
    {
        if (in_array($idTypeEtp, [1, 2, 4, 5, 6, 7])) {
            switch ($idTypeEtp) {
                case 1:
                    $allEtps = $etp->getAllEnterprises(Customer::idCustomer(), 1);
                    break;
                case 2:
                    $allEtps = $etp->getAllEnterprises(Customer::idCustomer(), 2);
                    break;
                case 4:
                    $allEtps = $etp->getAllEnterprises(Customer::idCustomer(), 4);
                    break;
                case 5:
                    $allEtps = $etp->getAllEnterprises(Customer::idCustomer(), 5);
                    break;
                case 6:
                    $allEtps = $etp->getAllEnterprises(Customer::idCustomer(), 6);
                    break;
                case 7:
                    $allEtps = $etp->getAllEnterprises(Customer::idCustomer(), 7);
                    break;
            }
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'introuvable !'
            ], 404);
        }

        // filtre par lettre
        $filters = $etp->letterFilterEnterprises($allEtps);
        $filteredEtps = $filters['filteredEtps'];
        $firstLetter = $filters['firstLetter'];
        $enabledLetters = $filters['enabledLetters'];

        $villeCodeds = $this->getVilleCodeds();
        $typeEntreprises = $this->getTypeEntreprise()->whereIn('idTypeEtp', [1, 4, 5, 6, 7])->get();

        if (count($allEtps) <= 0) {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun élement trouvé !'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'allEtps' => $allEtps,
            'filteredEtps' => $filteredEtps,
            'firstLetter' => $firstLetter,
            'enabledLetters' => $enabledLetters,
            'ville_codeds' => $villeCodeds,
            'typeEntreprises' => $typeEntreprises
        ]);
    }

    public function searchName(string $name, EntrepriseService $etp)
    {
        $etps = $etp->index(Customer::idCustomer())->where('etp_name', 'like', '%' . $name . '%')->get();

        if (count($etps) <= 0) {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun élement trouvé !'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'etps' => $etps
        ]);
    }

    public function getAllEtps(EntrepriseService $etp)
    {
        $etps = $etp->index(Customer::idCustomer())->get();

        if (count($etps) <= 0) {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun élement trouvé !'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'etps' => $etps
        ]);
    }

    public function getAllFrais()
    {
        $frais = DB::table('frais')
            ->select('idFrais', 'Frais', 'exemple')
            ->get();

        return response()->json(['frais' => $frais]);
    }

    public function edit($id, EntrepriseService $etp)
    {
        $etp = $etp->edit(Customer::idCustomer(), $id);

        if ($etp->exists()) {
            return response()->json([
                'status' => 200,
                'entreprise' => $etp->first(),
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Entreprise introuvable !'
            ], 404);
        }
    }


    public function update(Request $req, $id, EntrepriseService $etp, CustomerService $cst)
    {
        if (in_array($req->idTypeEtp, [1, 4, 5, 6, 7])) {
            $req->validate([
                'etp_name' => 'required|min:2|max:200',
                'etp_email' => 'required|email',
                'idTypeEtp' => 'required|exists:type_entreprises,idTypeEtp'
            ]);
        } else {
            $req->validate([
                'etp_name' => 'required|min:2|max:200',
                'etp_email' => 'required|email'
            ]);
        }

        $entreprise = $etp->edit(Customer::idCustomer(), $id);

        if ($entreprise->exists()) {
            try {
                DB::transaction(function () use ($id, $req, $cst, $etp) {
                    $cst->update(
                        Customer::idCustomer(),
                        $id,
                        $req->etp_nif,
                        $req->etp_stat,
                        $req->etp_rcs,
                        $req->etp_name,
                        $req->etp_phone,
                        $req->etp_email,
                        $req->etp_addr_lot,
                        $req->etp_addr_quartier,
                        $req->etp_ville,
                        $req->etp_referent_name,
                        $req->etp_referent_firstname
                    );

                    if (in_array($req->idTypeEtp, [1, 4, 5, 6, 7])) {
                        $etp->updateEntreprise(Customer::idCustomer(), $id, $req->idTypeEtp);
                    }
                });


                return response()->json([
                    'status' => 200,
                    'message' => 'Modifiée avec succès',
                    'entreprise' => [
                        'id'                    => $id,
                        'etp_nif'               => $req->etp_nif,
                        'etp_stat'              => $req->etp_stat,
                        'etp_rcs'               => $req->etp_rcs,
                        'etp_name'              => $req->etp_name,
                        'etp_phone'             => $req->etp_phone,
                        'etp_email'             => $req->etp_email,
                        'etp_addr_lot'          => $req->etp_addr_lot,
                        'etp_addr_quartier'     => $req->etp_addr_quartier,
                        'etp_ville'             => $req->etp_ville,
                        'etp_referent_name'     => $req->etp_referent_name,
                        'etp_referent_firstname' => $req->etp_referent_firstname
                    ]
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'status' => 400,
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Entreprise introuvable !'
            ], 404);
        }
    }


    public function updateLogo(Request $req, $id, EntrepriseService $etp, CustomerService $cst)
    {
        $query = $etp->edit(Customer::idCustomer(), $id);

        if ($query->exists()) {
            $cst->updateLogo(Customer::idCustomer(), $id, $query, $req->image);

            return response()->json([
                'status' => 200,
                'message' => 'Logo ajouté avec succès'
            ]);
        } {
            return response()->json([
                'status' => 404,
                'message' => 'Entreprise introuvable !'
            ], 404);
        }
    }

    public function destroy($id, EntrepriseService $etp)
    {
        $entreprise = $etp->edit(Customer::idCustomer(), $id);

        if ($entreprise->exists()) {
            if ($this->isCollaboratedIntra($id) || $this->isCollaboratedInter($id)) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Suppression impossible, Ce client est déjà associé à un projet !'
                ]);
            }

            $etp->destroy(Customer::idCustomer(), $id);

            return response()->json([
                'status' => 200,
                'message' => "Client supprimé avec succès",
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Entreprise introuvable !'
            ], 404);
        }
    }

    private function getModuleDomaine($idDomaine)
    {
        return DB::table('v_module_cfps')->select('idDomaine')->where('idDomaine', $idDomaine)->where('moduleStatut', 1)->count();
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
    public function getPrograms($idModule)
    {
        $programmes = DB::table('programmes')
            ->select('program_title', 'program_description', 'idModule')
            ->where('idModule', $idModule)
            ->get();

        return $programmes;
    }
    public function getEval($idModule)
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
    public function getProjectByModule($idModule)
    {
        $projects = DB::table('v_projet_cfps')
            ->select('idProjet')
            ->where('idModule', $idModule)
            ->pluck('idProjet');
        return $projects;
    }
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

    public function exportPdf($id)
    {
        $module = DB::table('v_module_cfps AS M')
            ->join('customers AS C', 'C.idCustomer', '=', 'M.idCustomer')
            ->select('idModule', 'module_image', 'reference', 'moduleName', 'moduleStatut', 'M.description', 'minApprenant', 'dureeH', 'dureeJ', DB::raw('COALESCE(maxApprenant, 0) as maxApprenant'), 'prix', 'prixGroupe', 'M.idCustomer', 'initialName', 'cfpName as nameCfp', 'C.logo as etp_logo', 'nomDomaine', 'idDomaine', 'module_is_complete', 'module_subtitle', 'module_level_name')
            ->whereNot('moduleName', 'Default module')
            ->where('idModule', $id)
            ->orderBy('moduleName', 'desc')
            ->first();
        $idCustomer = $module->idCustomer;

        $cfp = DB::table('customers')->select('*')->where('idCustomer', $idCustomer)->first();

        $cibles = DB::table('cible_modules')->where('idModule', $module->idModule)->get('cible');
        $prerequis = DB::table('prerequis_modules')
            ->where('idModule', $module->idModule)
            ->get('prerequis_name');
        $all_domaines = DB::table('domaine_formations')->select('idDomaine', 'nomDomaine')->orderBy('nomDomaine')->get();
        $domaines = [];

        foreach ($all_domaines as $doma) {
            $domaines[] = [
                'idDomaine' => $doma->idDomaine,
                'nomDomaine' => $doma->nomDomaine,
                'nb_module' => $this->getModuleDomaine($doma->idDomaine)
            ];
        }
        $objectifs = DB::table('objectif_modules')->select('idObjectif', 'objectif', 'idModule')->where('idModule', $module->idModule)->get();

        $projects_with_sessions = [];

        $project_cfp = $this->getProjectCfp($id);

        foreach ($project_cfp as $p) {
            $projects_with_sessions[$p->idProjet] = [
                'project' => $p,
                'sessionsGroupedByDate' => $this->sessionsGroupedByDate($p->idProjet, $id),
                'projectStartDate' => $this->monthConverted($p->dateDebut),
                'projectEndDate' => $this->dateConverted($p->dateFin),
                'forms' => $this->getForms($p->idProjet),
                'ville' => $p->ville,
                'nbPlace' => $this->getNbPlace($p->idProjet),
                'availability' => $this->placeIsAvailable($p->idProjet)
            ];
        }
        $prog = $this->getPrograms($id);
        if (Auth::user() && Auth::user()->id != 1) {
            $type_customer = Customer::where('idCustomer', Auth::user()->id)->first();
        }
        $note = $this->getEval($id);

        $get_domaines = DB::table('v_module_cfps')->select('idDomaine', 'nomDomaine')->where('moduleStatut', 1)->where('idCUstomer', $idCustomer)->whereNot('moduleName', "Default module")->groupBy('idDomaine')->get();

        $onlineModules = [];
        if (count($get_domaines) < 4) {
            foreach ($get_domaines as $domaine) {
                $get_module_domaine = DB::table('v_module_cfps')
                    ->join('domaine_formations', 'domaine_formations.idDomaine', 'v_module_cfps.idDomaine')
                    ->where('v_module_cfps.idDomaine', $domaine->idDomaine)
                    ->get();

                foreach ($get_module_domaine as $modulenew) {
                    $modules[] = [
                        'idDomaine' => $modulenew->idDomaine,
                        'idModule' => $modulenew->idModule,
                        'module_name' => $modulenew->moduleName,
                        'prix' => $modulenew->prix,
                        'dureeJ' => $modulenew->dureeJ,
                        'dureeH' => $modulenew->dureeH,
                        'moduleStatut' => $modulenew->moduleStatut,
                        'module_image' => $modulenew->module_image,
                        'module_is_complete' => $modulenew->module_is_complete,
                        'cfp_name' => $modulenew->cfpName,
                        'logo_cfp' => $modulenew->logo,
                        'note' => $this->getEval($modulenew->idModule)
                    ];
                    $onlineModules[] = [
                        'idDomaine' => $modulenew->idDomaine,
                        'nomDomaine' => $modulenew->nomDomaine,
                        "modules" => $modules
                    ];
                }
            }
        }

        foreach ($get_domaines as $d) {
            $onlineModules[] = [
                'idDomaine' => $d->idDomaine,
                'nomDomaine' => $d->nomDomaine,
                "modules" => $this->getModules($d->idDomaine, $idCustomer)
            ];
        }
        $pdf = PDF::loadView('pdf.projetDetailPDF', compact(['domaines', 'module', 'cibles', 'cfp', 'prerequis', 'objectifs', 'prog', 'projects_with_sessions', 'note', 'onlineModules', 'id']));
        return $pdf->download($module->moduleName . '.pdf');
    }
}
