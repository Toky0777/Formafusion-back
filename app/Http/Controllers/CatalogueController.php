<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ModuleCibleRequest;
use App\Http\Requests\ModuleObjectifRequest;
use App\Http\Requests\ModulePrerequisRequest;
use App\Http\Requests\ModulePrestationRequest;
use App\Http\Requests\ModuleRequest;
use App\Models\Customer;
use App\Services\ModuleService;
use App\Traits\HasModule;
use App\Traits\LearnerQuery;
use App\Traits\MarketPlaceQuery;
use App\Http\Controllers\ModuleRessourceController;
use App\Models\ModuleRessource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\error;

class CatalogueController extends Controller
{
    use HasModule, LearnerQuery, MarketPlaceQuery;

    //CFP
    private function getModuleGrouped($module_statut)
    {
        $query = DB::table('v_modules')
            ->select('nomDomaine as domaine', DB::raw('GROUP_CONCAT(CONCAT(moduleName, "``" , COALESCE(prix, "null"), "``" ,
                                            idModule, "``" , COALESCE(dureeH, "null"), "``" , COALESCE(dureeJ, "null"), "``", moduleStatut, "``", 
                                            COALESCE(module_image, "null"), "``",module_is_complete, "``", module_level_name )SEPARATOR "~&") as modules'))
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleName', '!=', 'Default module')
            ->where('moduleStatut', $module_statut)
            ->groupBy('idDomaine')
            ->orderBy('nomDomaine');

        $typeCustomer = Customer::typeCustomer();

        if ($typeCustomer == 1) {
            $getModules = $query->where('idTypeModule', 1)->get();
        } elseif ($typeCustomer == 2) {
            $getModules = $query->where('idTypeModule', 2)->get();
        }

        $get_module_grouped = [];
        foreach ($getModules as $mdl) {
            $modules = [];
            if (isset($mdl->modules)) {
                $get_module_name = explode('~&', $mdl->modules);
                foreach ($get_module_name as $name) {
                    $get_info_module = explode('``', $name);

                    $testObjectif = (!empty($this->listObjectifs($get_info_module[2])) ? 1 : 0);
                    $testPrestation = (!empty($this->listPrestations($get_info_module[2])) ? 1 : 0);
                    $testProgramme = (!empty($this->listProgrammes($get_info_module[2])) ? 1 : 0);
                    $testCible = (!empty($this->listCibles($get_info_module[2])) ? 1 : 0);
                    $testPrerequis = (!empty($this->listPrerequis($get_info_module[2])) ? 1 : 0);
                    $testImgMdl = (($get_info_module[6] != 'null') ? 1 : 0);

                    // Calcul de la somme des indicateurs
                    $testSumQuality = $testObjectif + $testPrestation + $testProgramme + $testCible + $testPrerequis + $testImgMdl;

                    $modules[] = [
                        'module_name' => $get_info_module[0],
                        'prix' => $get_info_module[1],
                        //'prixGroupe' => $get_info_module[2],
                        'idModule' => $get_info_module[2],
                        'dureeJ' => $get_info_module[4],
                        'dureeH' => $get_info_module[3],
                        'moduleStatut' => $get_info_module[5],
                        'module_image' => $get_info_module[6],
                        'module_is_complete' => $get_info_module[7],
                        'module_level_name' => $get_info_module[8],
                        'objectifs' => $this->listObjectifs($get_info_module[2]),
                        'prestations' => $this->listPrestations($get_info_module[2]),
                        'programmes' => $this->listProgrammes($get_info_module[2]),
                        'cibles' => $this->listCibles($get_info_module[2]),
                        'prerequis' => $this->listPrerequis($get_info_module[2]),
                        'testSumQuality' => $testSumQuality,
                        'totalFormed' => $this->countLearnerByModule($get_info_module[2]),
                        'ressourceCount' => ModuleRessource::countByModuleId($get_info_module[2]),
                    ];
                }

                $get_module_grouped[] = [
                    'domaine' => $mdl->domaine,
                    'modules' => $modules,
                    'count_module' => count($modules),

                ];
            }
        }

        return $get_module_grouped;
    }

    public function countModuleByStatus($status)
    {
        $moduleCount = DB::table('mdls')
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleStatut', $status)
            ->where('moduleName', '!=', "Default module")
            ->count();
        return $moduleCount;
    }

    public function countModulePublicByStatus($status)
    {
        $moduleCount = DB::table('mdls')
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleStatut', $status)
            ->where('moduleName', '!=', "Default module")
            ->where('is_public', 1)
            ->count();
        return $moduleCount;
    }

    public function getModuleByDomaine($idDomaine, $status = null)
    {
        $query = DB::table('v_modules')
            ->select(
                'idModule',
                'idDomaine',
                'nomDomaine',
                'module_image',
                'reference',
                'moduleName',
                'module_subtitle',
                'description',
                'minApprenant',
                'maxApprenant',
                'dureeJ',
                'dureeH',
                'prix',
                'moduleStatut',
                'module_level_name',
                'is_public'
            )
            ->where('idCustomer', Customer::idCustomer())
            ->where('idDomaine', $idDomaine)
            ->where('moduleName', '!=', 'Default module');

        if (!is_null($status)) {
            $query->where('moduleStatut', $status);
        }

        $modules = $query->orderBy('nomDomaine', 'asc')->get();

        // Ajouter les données supplémentaires pour chaque module
        foreach ($modules as $module) {
            $module->totalFormed = $this->countLearnerByModule($module->idModule);
            $module->sumQuality = $this->getSumQualityForModule($module->idModule);
            $module->testSumQuality = $this->getEval($module->idModule);
            $module->ressourceCount = ModuleRessource::countByModuleId($module->idModule);
            $this->getEval($module->idModule);
        }

        return $modules;
    }

    public function getModulePublicByDomaine($idDomaine, $status = null, $search = null)
    {
        $query = DB::table('v_modules')
            ->select(
                'idModule',
                'idDomaine',
                'nomDomaine',
                'module_image',
                'reference',
                'moduleName',
                'module_subtitle',
                'description',
                'minApprenant',
                'maxApprenant',
                'dureeJ',
                'dureeH',
                'prix',
                'moduleStatut',
                'module_level_name',
            )
            ->where('idCustomer', Customer::idCustomer())
            ->where('idDomaine', $idDomaine)
            ->where('moduleName', '!=', 'Default module')
            ->where('is_public', 1);

        if (!is_null($status)) {
            $query->where('moduleStatut', $status);
        }


        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('moduleName', 'like', "%{$search}%")
                    ->orWhere('module_subtitle', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $modules = $query->orderBy('nomDomaine', 'asc')->get();
        foreach ($modules as $module) {
            $module->totalFormed = $this->countLearnerByModule($module->idModule);
            $module->sumQuality = $this->getSumQualityForModule($module->idModule);
            $module->testSumQuality = $this->getEval($module->idModule);
            $module->ressourceCount = ModuleRessource::countByModuleId($module->idModule);
        }

        return $modules;
    }


    public function getCountModuleByStatus()
    {
        try {
            return response()->json([
                'online' => $this->countModulePublicByStatus(1),
                'offline' => $this->countModulePublicByStatus(0),
                'trash' => $this->countModulePublicByStatus(2)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du chargement des données',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getModulePublicByStatus($status, Request $request)
    {
        try {
            $search = $request->query('search', null);

            $domainsQuery = DB::table('v_modules')
                ->select('idDomaine', 'nomDomaine')
                ->where('idCustomer', Customer::idCustomer())
                ->where('moduleName', '!=', 'Default module')
                // ->where('moduleStatut', $status)
                // ->where('is_public', 1)
                ->where(function ($q) use ($status) {
                    $q->where('moduleStatut', $status)
                        ->orWhere('is_public', 1);
                })
                ->groupBy('idDomaine', 'nomDomaine')
                ->orderBy('nomDomaine', 'asc');

            // Si un terme de recherche est fourni
            if ($search) {
                $domainsQuery->where(function ($q) use ($search) {
                    $q->where('moduleName', 'like', "%{$search}%")
                        ->orWhere('module_subtitle', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $domains = $domainsQuery->get();

            $data = [];

            foreach ($domains as $domaine) {
                $modules = $this->getModulePublicByDomaine($domaine->idDomaine, $status, $search);

                if (count($modules) > 0) {
                    $data[] = [
                        'idDomaine' => $domaine->idDomaine,
                        'nomDomaine' => $domaine->nomDomaine,
                        'modules' => $modules
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur interne du serveur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getModulesByStatus($status)
    {
        try {
            // Récupérer les domaines qui ont des modules AVEC LE STATUT SPÉCIFIÉ
            $domains = DB::table('v_modules')
                ->select('idDomaine', 'nomDomaine')
                ->where('idCustomer', Customer::idCustomer())
                ->where('moduleName', '!=', 'Default module')
                ->where('moduleStatut', $status)
                ->where(function ($query) use ($status) {
                    $query->where('moduleStatut', $status)
                        ->orWhere('is_public', 1); // Inclut aussi les modules publics
                })
                // ->where('is_public', 0)
                ->groupBy('idDomaine', 'nomDomaine')
                ->orderBy('nomDomaine', 'asc')
                ->get();

            $data = [];

            foreach ($domains as $domaine) {
                $modules = $this->getModuleByDomaine($domaine->idDomaine, $status);

                if (count($modules) > 0) {
                    $data[] = [
                        'idDomaine' => $domaine->idDomaine,
                        'nomDomaine' => $domaine->nomDomaine,
                        'modules' => $modules
                    ];
                }
            }

            $counts = [
                'online' => $this->countModuleByStatus(1),
                'offline' => $this->countModuleByStatus(0),
                'trash' => $this->countModuleByStatus(2)
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'counts' => $counts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur interne du serveur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getModulesOnline()
    {
        return $this->getModulesByStatus(1);
    }

    public function getModulesOffline()
    {
        return $this->getModulesByStatus(0);
    }

    public function getModulesTrashline()
    {
        return $this->getModulesByStatus(2);
    }

    public function index()
    {
        try {
            // Récupération de tous les domaines
            $domains = DB::table('v_modules')
                ->select('idModule', 'idDomaine', 'nomDomaine', 'module_image', 'reference', 'moduleName', 'module_subtitle', 'description', 'minApprenant', 'maxApprenant', 'dureeJ', 'dureeH', 'prix', 'module_level_name', 'module_image',  DB::raw('COUNT(idDomaine) as count'))
                ->where('idCustomer', Customer::idCustomer())
                ->where('moduleName', '!=', 'Default module')
                ->where('is_public', 0)
                ->groupBy('idModule', 'idDomaine', 'nomDomaine', 'module_image', 'reference', 'moduleName', 'module_subtitle', 'description', 'minApprenant', 'maxApprenant', 'dureeJ', 'dureeH', 'prix', 'module_level_name', 'module_image')
                ->orderBy('nomDomaine', 'asc')
                ->get();

            $data = [];

            foreach ($domains as $domaine) {
                $data[] = [
                    'idModule' => $domaine->idModule,
                    'idDomaine' => $domaine->idDomaine,
                    'nomDomaine' => $domaine->nomDomaine,
                    'reference' => $domaine->reference,
                    'moduleName' => $domaine->moduleName,
                    'module_subtitle' => $domaine->module_subtitle,
                    'description' => $domaine->description,
                    'minApprenant' => $domaine->minApprenant,
                    'maxApprenant' => $domaine->maxApprenant,
                    'dureeJ' => $domaine->dureeJ,
                    'dureeH' => $domaine->dureeH,
                    'prix' => $domaine->prix,
                    'module_level_name' => $domaine->module_level_name,
                    'module_image' => $domaine->module_image,
                    'modules' => $this->getModuleByDomaine($domaine->idDomaine), // pas de filtre fixe
                    'module_count' => $domaine->count
                ];
            }

            // Récupérer les compteurs
            $counts = [
                'online'  => $this->countModuleByStatus(1),
                'offline' => $this->countModuleByStatus(0),
                'trash'   => $this->countModuleByStatus(2)
            ];
            return response()->json([
                'success' => true,
                'data'   => $data,
                'counts' => $counts
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur interne du serveur',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function updateImage(Request $req, $id, ModuleService $mdl)
    {
        $query = $mdl->getModule($id, Customer::idCustomer());

        if ($query->exists()) {
            $mdl->updateModuleImage($id, Customer::idCustomer(), $query, $req->image);
            $module = DB::table('mdls')->where('idModule', $id)->get()->first();
            return response()->json([
                'status' => 200,
                'message' => 'Image ajoutée avec succès',
                'imagePath' => asset('storage/img/modules/' . $module->module_image)
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !"
            ]);
        }
    }

    public function store(Request $req, ModuleRequest $reqModule, ModuleService $mdls)
    {
        try {
            $mdl = DB::transaction(function () use ($req, $mdls) {
                $typeCustomer = Customer::typeCustomer();

                if ($typeCustomer == 1) {
                    $module = $mdls->storeMdls($req->module_reference, $req->module_tag, $req->name, $req->subtitle, $req->module_dureeH, $req->module_dureeJ, $req->module_min_appr, $req->module_max_appr, 1, Customer::idCustomer(), $req->id_domaine_formation, $req->idLevel, $req->module_icone);
                    $mdls->storeModule($module, $req->module_price, $req->module_prix_groupe);
                } else if ($typeCustomer == 2) {
                    $module = $mdls->storeMdls($req->module_reference, $req->module_tag, $req->name, $req->subtitle, $req->module_dureeH, $req->module_dureeJ, $req->module_min_appr, $req->module_max_appr, 2, Customer::idCustomer(), $req->id_domaine_formation, $req->idLevel);
                    $mdls->storeModuleInterne($module);
                }

                return $module;
            });

            return response()->json([
                "status" => 200,
                "message" => "Module ajouté avec succès.",
                'module_id' => $mdl
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => 400,
                "message" => "Ajout impossible"
            ]);
        }
    }

    public function storeModulePublic(Request $req, ModuleRequest $reqModule, ModuleService $mdls)
    {
        try {
            DB::transaction(function () use ($req, $mdls) {
                $typeCustomer = Customer::typeCustomer();

                if ($typeCustomer == 1) {
                    $module = $mdls->storeMdlsPublic($req->module_reference, $req->module_tag, $req->name, $req->subtitle, $req->module_dureeH, $req->module_dureeJ, $req->module_min_appr, $req->module_max_appr, 1, Customer::idCustomer(), $req->id_domaine_formation, $req->idLevel);
                    $mdls->storeModule($module, $req->module_price, $req->module_prix_groupe);
                } else if ($typeCustomer == 2) {
                    $module = $mdls->storeMdlsPublic($req->module_reference, $req->module_tag, $req->name, $req->subtitle, $req->module_dureeH, $req->module_dureeJ, $req->module_min_appr, $req->module_max_appr, 2, Customer::idCustomer(), $req->id_domaine_formation, $req->idLevel);
                    $mdls->storeModuleInterne($module);
                }
            });

            return response()->json([
                "status" => 200,
                "message" => "Module ajouté avec succès.",
                "name" => $req->name
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => 400,
                "message" => "Ajout impossible"
            ]);
        }
    }

    public function makeOnline($id, ModuleService $mdls)
    {
        $changed = $mdls->changeStatus($id, Customer::idCustomer(), 1, 0);

        if ($changed) {
            return response()->json([
                "status" => 200,
                "message" => "Succès"
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !"
            ]);
        }
    }

    public function makeOffline($id, ModuleService $mdls)
    {
        $changed = $mdls->changeStatus($id, Customer::idCustomer(), 0, 1);

        if ($changed) {
            return response()->json([
                "status" => 200,
                "message" => "Succès"
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !"
            ]);
        }
    }

    public function makeTrashed($id, ModuleService $mdls)
    {
        $changed = $mdls->changeStatus($id, Customer::idCustomer(), 2, 0);

        if ($changed) {
            return response()->json([
                "status" => 200,
                "message" => "Succès"
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !"
            ]);
        }
    }

    public function makeIsPublic($idModule)
    {
        try {
            $module = DB::table('mdls')->where('idModule', $idModule)->first();

            if ($module) {
                DB::table('mdls')->where('idModule', $idModule)->update([
                    'is_public' => 1
                ]);

                return response()->json([
                    "status" => 200,
                    "message" => "Module rendu public avec succès"
                ]);
            } else {
                return response()->json([
                    "status" => 204,
                    "message" => "Module introuvable !"
                ], 204);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function makeIsPrivate($idModule)
    {
        try {
            $module = DB::table('mdls')->where('idModule', $idModule)->first();

            if ($module) {
                DB::table('mdls')->where('idModule', $idModule)->update([
                    'is_public' => 0
                ]);

                return response()->json([
                    "status" => 200,
                    "message" => "Module rendu privé avec succès"
                ]);
            } else {
                return response()->json([
                    "status" => 204,
                    "message" => "Module introuvable !"
                ], 204);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function makeIsRestore($idModule)
    {
        try {
            $module = DB::table('mdls')->where('idModule', $idModule)->first();

            if ($module) {
                DB::table('mdls')->where('idModule', $idModule)->update([
                    'is_public' => 0
                ]);

                return response()->json([
                    "status" => 200,
                    "message" => "Module restauré dans le catalogue interne avec succès"
                ]);
            } else {
                return response()->json([
                    "status" => 204,
                    "message" => "Module introuvable !"
                ], 204);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }


    public function restore($idModule, ModuleService $mdls)
    {
        $changed = $mdls->changeStatus($idModule, Customer::idCustomer(), 0, 0);

        if ($changed) {
            return response()->json([
                "status" => 200,
                "message" => "Succès",
                "module" => [
                    "idModule" => $idModule,
                    "status" => 0 // ou 1 si tu restores en ligne
                ]
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !"
            ]);
        }
    }


    public function destroy($id)
    {
        try {
            $module = DB::table('mdls')->where('idModule', $id)->where('idCustomer', Customer::idCustomer());

            if ($module->exists()) {
                DB::transaction(function () use ($id, $module) {
                    DB::table('prerequis_modules')->where('idModule', $id)->delete();
                    DB::table('cible_modules')->where('idModule', $id)->delete();
                    DB::table('programmes')->where('idModule', $id)->delete();
                    DB::table('prestation_modules')->where('idModule', $id)->delete();
                    DB::table('objectif_modules')->where('idModule', $id)->delete();
                    DB::table('module_ressources')->where('idModule', $id)->delete();

                    if (Customer::typeCustomer() == 1) {
                        DB::table('modules')->where('idModule', $id)->delete();
                    } elseif (Customer::typeCustomer() == 2) {
                        DB::table('module_internes')->where('idModule', $id)->delete();
                    }
                    $module->delete();
                });

                return response()->json([
                    "status" => 200,
                    "message" => "Module supprimé avec succès"
                ]);
            } else {
                return response()->json([
                    "status" => 404,
                    "message" => "Module introuvable !"
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                "status" => 400,
                "message" => "suppression impossible",
                "error" => $e->getMessage(),
            ]);
        }
    }

    public function edit($id, ModuleService $mdl)
    {
        $module = $mdl->getModule($id, Customer::idCustomer());

        if ($module->exists()) {
            $domaines = $this->domaines();
            $levels = $this->levels();

            return response()->json([
                'status' => 200,
                'module' => $module->first(),
                'domaines' => $domaines,
                'levels' => $levels
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !"
            ]);
        }
    }

    public function update(Request $req, ModuleRequest $reqModule, $id, ModuleService $mdls)
    {
        try {
            $module = $mdls->getModule($id, Customer::idCustomer());

            if ($module->exists()) {
                DB::transaction(function () use ($req, $id, $mdls) {
                    $mdls->updateMdls(
                        $id,
                        $req->module_reference,
                        $req->module_tag,
                        $req->name,
                        $req->subtitle,
                        $req->module_description,
                        $req->module_dureeH,
                        $req->module_dureeJ,
                        $req->module_min_appr,
                        $req->module_max_appr,
                        Customer::idCustomer(),
                        $req->id_domaine_formation,
                        $req->idLevel,
                        $req->module_icone,
                    );

                    if (Customer::typeCustomer() == 1) {
                        $mdls->updateModule($id, $req->module_price, $req->module_prix_groupe);
                    }
                });

                return response()->json([
                    "status" => 200,
                    "message" => "Module modifi avec succès"
                ]);
            } else {
                return response()->json([
                    "status" => 404,
                    "message" => "Module introuvable !"
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                "status" => 400,
                "message" => "Modification impossible" . $e->getMessage()
            ]);
        }
    }

    // public function updateImage(Request $req, $id, ModuleService $mdl)
    // {
    //     $query = $mdl->getModule($id, Customer::idCustomer());

    //     if ($query->exists()) {
    //         $mdl->updateModuleImage($id, Customer::idCustomer(), $query, $req->image);
    //         $module = DB::table('mdls')->where('idModule', $id)->get()->first();
    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Image ajoutée avec succès',
    //             'imagePath' => asset('storage/img/modules/' . $module->module_image)
    //         ]);
    //     } else {
    //         return response()->json([
    //             "status" => 404,
    //             "message" => "Module introuvable !"
    //         ]);
    //     }
    // }

    // Objectifs
    public function addObjectif($id, ModuleObjectifRequest $req, ModuleService $mdl)
    {
        $module = $mdl->getModule($id, Customer::idCustomer());

        if ($module) {
            $objectifCree = $mdl->storeObjectif($id, $req->validated()['name']);

            return response()->json([
                "status" => 200,
                "message" => "Objectif ajouté avec succès",
                "idObjectif" => $objectifCree->idObjectif,
                "objectif" => $objectifCree->objectif,
                "idModule" => $objectifCree->idModule,
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !"
            ]);
        }
    }


    public function getObjectif($id, ModuleService $mdl)
    {
        $module = $mdl->getModule($id, Customer::idCustomer());

        if ($module->exists()) {
            $objectifs = $this->objectifs($id);

            return response()->json([
                "status" => 200,
                "objectifs" => $objectifs
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !"
            ]);
        }
    }

    public function deleteObjectif($idModule, $idObjectif, ModuleService $mdl)
    {
        $mdlObjectif = DB::table('objectif_modules')->where('idModule', $idModule)->where('idObjectif', $idObjectif);

        if ($mdlObjectif->exists()) {
            $mdl->destroyObjectif($idModule, $idObjectif);

            return response()->json([
                "status" => 200,
                "message" => "Objectif supprimée avec succès"
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Objectif introuvable !"
            ]);
        }
    }

    public function showObjectif($module, $objectif)
    {
        $mdlObjectif = DB::table('objectif_modules')->where('idModule', $module)->where('idObjectif', $objectif);

        if ($mdlObjectif->exists()) {
            return response()->json([
                "status" => 200,
                "objectif" => $mdlObjectif->first()
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Objectif introuvable !"
            ], 404);
        }
    }

    public function updateObjectif($module, $objectif, Request $req)
    {
        $req->validate([
            'module_objectif' => 'required|min:2|max:255'
        ]);

        $mdlObjectif = DB::table('objectif_modules')->where('idModule', $module)->where('idObjectif', $objectif);

        if (!$mdlObjectif->exists()) {
            return response()->json([
                "status" => 404,
                "message" => " Introuvable !"
            ], 404);
        }

        $mdlObjectif->update(['objectif' => $req->module_objectif]);

        $upload = DB::table("objectif_modules")
            ->where('idObjectif', $objectif)
            ->first();

        return response()->json([
            "status" => 200,
            "message" => "Succès",
            "objectif" => $upload
        ]);
    }

    // Prestations
    public function addPrestation($id, ModulePrestationRequest $req, ModuleService $mdl)
    {
        $module = $mdl->getModule($id, Customer::idCustomer());

        if ($module->exists()) {
            $mdl->storePrestation($id, $req->validated()['prestation_name']);

            return response()->json([
                "status" => 200,
                "message" => "Préstation ajoutée avec succès"
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !"
            ]);
        }
    }

    public function getPrestation($id, ModuleService $mdl)
    {
        $module = $mdl->getModule($id, Customer::idCustomer());

        if ($module->exists()) {
            $prestations = $this->prestations($id);

            return response()->json([
                "status" => 200,
                "prestations" => $prestations
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !"
            ]);
        }
    }

    public function deletePrestation($idModule, $idPrestation, ModuleService $mdl)
    {
        $mdlPrestation = DB::table('prestation_modules')->where('idModule', $idModule)->where('idPrestation', $idPrestation);

        if ($mdlPrestation->exists()) {
            $mdl->destroyPrestation($idModule, $idPrestation);

            return response()->json([
                "status" => 200,
                "message" => "Préstation supprimée avec succès"
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Préstation introuvable !"
            ]);
        }
    }

    public function showPrestation($module, $prestation)
    {
        $mdlPrestation = DB::table('prestation_modules')->where('idModule', $module)->where('idPrestation', $prestation);

        if ($mdlPrestation->exists()) {
            return response()->json([
                "status" => 200,
                "prestation" => $mdlPrestation->first()
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Préstation introuvable !"
            ], 404);
        }
    }

    public function updatePrestation($module, $prestations, Request $req)
    {
        $req->validate([
            'module_prestation' => 'required|min:2|max:255'
        ]);

        $prestation = DB::table('prestation_modules')->where('idModule', $module)->where('idPrestation', $prestations);

        if (!$prestation->exists()) {
            return response()->json([
                "status" => 404,
                "message" => " Introuvable !"
            ], 404);
        }

        $prestation->update(['prestation_name' => $req->module_prestation]);

        $upload = DB::table("prestation_modules")
            //  ->where('idModule', $module)
            ->where('idPrestation', $prestations)
            ->first();


        return response()->json([
            "status" => 200,
            "message" => "Succès",
            "prestation" => $upload
        ]);
    }

    // Prerequis
    public function addPrerequis($id, ModulePrerequisRequest $req, ModuleService $mdl)
    {
        $module = $mdl->getModule($id, Customer::idCustomer());

        if ($module->exists()) {
            $mdl->storePrerequis($id, $req->validated()['prerequis_name']);

            return response()->json([
                "status" => 200,
                "message" => "Prérequis ajoutée avec succès"
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !"
            ]);
        }
    }

    public function getPrerequis($id, ModuleService $mdl)
    {
        $module = $mdl->getModule($id, Customer::idCustomer());

        if ($module->exists()) {
            $prerequis = $this->prerequis($id);

            return response()->json([
                "status" => 200,
                "prerequis" => $prerequis
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !"
            ]);
        }
    }

    public function deletePrerequis($idModule, $idPrerequis, ModuleService $mdl)
    {
        $requirement = DB::table('prerequis_modules')->where('idModule', $idModule)->where('idPrerequis', $idPrerequis);

        if ($requirement->exists()) {
            $mdl->destroyPrerequis($idModule, $idPrerequis);

            return response()->json([
                "status" => 200,
                "message" => "Prérequis supprimée avec succès"
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Prérequis introuvable !"
            ]);
        }
    }

    public function showPrerequis($module, $prerequis)
    {
        $requirement = DB::table('prerequis_modules')->where('idModule', $module)->where('idPrerequis', $prerequis);

        if ($requirement->exists()) {
            return response()->json([
                "status" => 200,
                "prerequis" => $requirement->first()
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Prérequis introuvable !"
            ], 404);
        }
    }

    public function updatePrerequis($module, $prerequis, Request $req)
    {
        $req->validate([
            'module_requirement' => 'required|min:2|max:255'
        ]);

        $requirement = DB::table('prerequis_modules')->where('idModule', $module)->where('idPrerequis', $prerequis);

        if (!$requirement->exists()) {
            return response()->json([
                "status" => 404,
                "message" => " Introuvable !"
            ], 404);
        }

        $requirement->update(['prerequis_name' => $req->module_requirement]);

        $upload = DB::table("prerequis_modules")
            ->where('idPrerequis', $prerequis)
            ->first();

        return response()->json([
            "status" => 200,
            "message" => "Succès",
            "prerequis" => $upload

        ]);
    }

    // Cibles
    public function addCible($idModule, ModuleCibleRequest $req, ModuleService $mdl)
    {
        $module = $mdl->getModule($idModule, Customer::idCustomer());

        if ($module->exists()) {
            $mdl->storeCible($idModule, $req->validated()['cible_name']);

            return response()->json([
                "status" => 200,
                "message" => "Cible ajoute avec succès"
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !"
            ]);
        }
    }

    public function getCible($idModule, ModuleService $mdl)
    {
        $module = $mdl->getModule($idModule, Customer::idCustomer());

        if ($module->exists()) {
            $cibles = $this->cibles($idModule);

            return response()->json([
                "status" => 200,
                "cibles" => $cibles
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !"
            ]);
        }
    }

    public function deleteCible($idModule, $idCible, ModuleService $mdl)
    {
        $cible = DB::table('cible_modules')->where('idModule', $idModule)->where('idCible', $idCible);

        if ($cible->exists()) {
            $mdl->destroyCible($idModule, $idCible);

            return response()->json([
                "status" => 200,
                "message" => "Cible supprimée avec succès"
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Cible introuvable !"
            ], 404);
        }
    }

    public function showCible($module, $cible)
    {
        $cible = DB::table('cible_modules')->where('idModule', $module)->where('idCible', $cible);

        if ($cible->exists()) {
            return response()->json([
                "status" => 200,
                "cible" => $cible->first()
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Cible introuvable !"
            ], 404);
        }
    }

    public function updateCible($module, $cibleId, Request $req)
    {
        $req->validate([
            'module_cible' => 'required|min:2|max:255'
        ]);

        $cible = DB::table('cible_modules')->where('idModule', $module)->where('idCible', $cibleId);

        if (!$cible->exists()) {
            return response()->json([
                "status" => 404,
                "message" => "Cible introuvable !"
            ], 404);
        }

        $cible->update(['cible' => $req->module_cible]);

        $upload = DB::table("cible_modules")
            ->where('idCible', $cibleId)
            ->first();

        return response()->json([
            "status" => 200,
            "message" => "Succès",
            "cible" => $upload
        ]);
    }

    // Qualités
    public function getSumQualityForModule($idModule)
    {
        try {
            $testObjectif = (!empty($this->listObjectifs($idModule))) ? 1 : 0;
            $testPrestation = (!empty($this->listPrestations($idModule))) ? 1 : 0;
            $testProgramme = (!empty($this->listProgrammes($idModule))) ? 1 : 0;
            $testCible = (!empty($this->listCibles($idModule))) ? 1 : 0;
            $testPrerequis = (!empty($this->listPrerequis($idModule))) ? 1 : 0;

            // Vérifier si le module a une image
            $module = DB::table('mdls')->where('idModule', $idModule)->first();
            $testImgMdl = ($module && $module->module_image && $module->module_image !== 'null') ? 1 : 0;

            return $testObjectif + $testPrestation + $testProgramme + $testCible + $testPrerequis + $testImgMdl;
        } catch (\Exception $e) {
            \Log::error('Erreur dans getSumQualityForModule: ' . $e->getMessage());
            return 0;
        }
    }

    public function getSumQuality($id)
    {
        try {
            $qualityScore = $this->getSumQualityForModule($id);

            return response()->json([
                'status' => 200,
                'sumQuality' => $qualityScore
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur serveur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // étoile
    public function getModuleScores($idModule)
    {
        return response()->json($this->getEval($idModule));
    }
}
