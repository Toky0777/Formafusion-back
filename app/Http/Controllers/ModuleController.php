<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Traits\LearnerQuery;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\Response;

class ModuleController extends Controller
{
    use LearnerQuery;

    public function searchOnLine($name)
    {
        $onlineModules = DB::table('v_module_cfps')
            ->select(
                'idModule',
                'module_image',
                'reference',
                'moduleName',
                'moduleStatut',
                'description',
                'minApprenant',
                'dureeH',
                'dureeJ',
                'maxApprenant',
                'prix',
                'prixGroupe',
                'idCustomer',
                'initialName',
                'cfpName as nameCfp',
                'logo as cfpLogo',
                'nomDomaine',
                'idDomaine',
                'module_level_name',
                'module_is_complete'
            )
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleStatut', 1)
            ->where('moduleName', '!=', 'Default module')
            ->where('moduleName', 'like', '%' . $name . '%')
            ->get();

        $onlineModulesHmtl = '';

        if ($onlineModules) {

            foreach ($onlineModules as $key => $value) {
                $onlineModulesHmtl .= view('components.catalogue-search', ['m' => $value])->render();
            }
        }

        return response()->json([
            'onlineModules' => $onlineModules,
            'onlineModulesHmtl' => $onlineModulesHmtl,
        ]);
    }

    public function searchOffLine($name)
    {
        $offlineModules = DB::table('v_module_cfps')
            ->select(
                'idModule',
                'module_image',
                'reference',
                'moduleName',
                'moduleStatut',
                'description',
                'minApprenant',
                'dureeH',
                'dureeJ',
                'maxApprenant',
                'prix',
                'prixGroupe',
                'idCustomer',
                'initialName',
                'cfpName as nameCfp',
                'logo as cfpLogo',
                'nomDomaine',
                'idDomaine',
                'module_level_name',
                'module_is_complete'
            )
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleStatut', 0)
            ->where('moduleName', '!=', 'Default module')
            ->where('moduleName', 'like', '%' . $name . '%')
            ->get();

        // Convertir chaque module en objet
        $offlineModules = collect($offlineModules)->map(function ($module) {
            return (object) $module;
        });

        $idCustomer = Customer::idCustomer();

        Log::info($idCustomer);
        Log::info($name);
        Log::info($offlineModules); // Vérifie que c'est bien une collection d'objets

        $offlineModulesHtml = '';

        if ($offlineModules) {
            foreach ($offlineModules as $key => $value) {
                $value->testSum = 0; // Ajoute une valeur par défaut pour éviter l'erreur
                $offlineModulesHtml .= view('components.catalogue-search', ['m' => $value])->render();
            }
        }

        return response()->json([
            'offlineModules' => $offlineModules,
            'offlineModulesHtml' => $offlineModulesHtml,
        ]);
    }

    public function index()
    {
        $domaines = DB::table('v_module_cfps')
            ->select('idDomaine', 'nomDomaine AS domaine_name', 'moduleStatut AS module_status')
            ->groupBy('idDomaine', 'nomDomaine', 'moduleStatut')
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleName', '!=', 'Default module')
            ->orderBy('nomDomaine', 'asc')
            ->get();

        $countTrashline = DB::table('v_module_cfps')
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleStatut', 2)
            ->where('moduleName', '!=', 'Default module')
            ->orderBy('moduleName', 'desc')
            ->count();

        $countOnline = DB::table('v_module_cfps')
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleStatut', 1)
            ->where('moduleName', '!=', 'Default module')
            ->orderBy('moduleName', 'desc')
            ->count();

        $countOffline = DB::table('v_module_cfps')
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleStatut', 0)
            ->where('moduleName', '!=', 'Default module')
            ->orderBy('moduleName', 'desc')
            ->count();

        $badgeOnline = DB::table('v_module_cfps')
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleStatut', 1)
            ->where('moduleName', '!=', 'Default module')
            ->where('module_is_complete', '!=', 1)
            ->exists();

        $badgeOffline = DB::table('v_module_cfps')
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleStatut', 0)
            ->where('moduleName', '!=', 'Default module')
            ->where('module_is_complete', '!=', 1)
            ->exists();

        $onlineModules = $this->getModuleGrouped(1);

        $offlineModules = $this->getModuleGrouped(0);

        $trashedModules = $this->getModuleGrouped(2);

        $customer = DB::table('v_detail_customers')->select('idCustomer', 'initialName', 'customerName as name', 'customer_addr_lot as adress', 'customerPhone as phone', 'description', 'siteWeb', 'logo', 'customerEmail as email')->where('idCustomer', Customer::idCustomer())->first();

        return view('CFP.modules.index', compact(['onlineModules', 'offlineModules', 'trashedModules', 'customer', 'domaines', 'countOnline', 'countOffline', 'countTrashline', 'badgeOnline', 'badgeOffline']));
    }

    private function listObjectifs($idModule)
    {

        $idObjectifs = DB::table('objectif_modules')
            ->select('idObjectif')
            ->where('idModule', $idModule)
            ->get();
        return $idObjectifs->toArray();
    }

    private function listPrestations($idModule)
    {

        $idPrestations = DB::table('prestation_modules')
            ->select('idPrestation')
            ->where('idModule', $idModule)
            ->get();
        return $idPrestations->toArray();
    }

    private function listProgrammes($idModule)
    {

        $idProgrammes = DB::table('programmes')
            ->select('idProgramme')
            ->where('idModule', $idModule)
            ->get();
        return $idProgrammes->toArray();
    }

    private function listCibles($idModule)
    {

        $idCibles = DB::table('cible_modules')
            ->select('idCible')
            ->where('idModule', $idModule)
            ->get();
        return $idCibles->toArray();
    }

    private function listPrerequis($idModule)
    {

        $idPrerequis = DB::table('prerequis_modules')
            ->select('idPrerequis')
            ->where('idModule', $idModule)
            ->get();
        return $idPrerequis->toArray();
    }

    private function getModuleGrouped($module_statut)
    {
        $getModules = DB::table('v_module_cfps')
            ->select('nomDomaine as domaine', DB::raw('GROUP_CONCAT(CONCAT(moduleName, "``" , COALESCE(prix, "null"), "``" , 
                                        idModule, "``" , COALESCE(dureeH, "null"), "``" , COALESCE(dureeJ, "null"), "``", moduleStatut, "``", 
                                        COALESCE(module_image, "null"), "``",module_is_complete, "``", module_level_name )SEPARATOR "~&") as modules'))
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleName', '!=', 'Default module')
            ->where('moduleStatut', $module_statut)
            ->groupBy('idDomaine')
            ->orderBy('nomDomaine')
            ->get();

        //dd($getModules);

        $get_module_grouped = [];
        foreach ($getModules as $mdl) {
            $modules = [];
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
                    'totalFormed' => $this->countLearnerByModule($get_info_module[2])
                ];
            }
            // dd($get_info_module[6]);
            $get_module_grouped[] = [
                'domaine' => $mdl->domaine,
                'modules' => $modules,
                'count_module' => count($modules),

            ];
        }
        // dd($modules);
        //dd($get_module_grouped);

        return $get_module_grouped;
    }

    public function store(Request $req)
    {
        $validation = Validator::make($req->all(), [
            'module_name' => 'required|min:2|max:200',
            'module_subtitle' => 'required|min:2|max:200',
            'id_domaine_formation' => 'required',
            'idLevel' => 'required'
        ]);

        if ($validation->fails()) {
            return response()->json($validation->messages());
        } else {
            try {
                DB::beginTransaction();

                $idModule = DB::table('mdls')->insertGetId([
                    'reference' => $req->module_reference,
                    'module_tag' => $req->module_tag,
                    'moduleName' => $req->module_name,
                    'module_subtitle' => $req->module_subtitle,
                    'dureeH' => $req->module_dureeH,
                    'dureeJ' => $req->module_dureeJ,
                    'minApprenant' => $req->module_min_appr,
                    'maxApprenant' => $req->module_max_appr,
                    'idTypeModule' => 1,
                    'moduleStatut' => 0,
                    'module_image' => $req->module_icone,
                    'idCustomer' => Customer::idCustomer(),
                    'idDomaine' => $req->id_domaine_formation,
                    'idLevel' => $req->idLevel
                ]);

                $mdl = DB::table('mdls')->select('idModule')->orderBy('idModule', 'desc')->first();

                DB::table('modules')->insert([
                    'idModule' => $mdl->idModule,
                    'prix' => $req->module_price,
                    'prixGroupe' => $req->module_prix_groupe
                ]);

                DB::commit();


                return response()->json([
                    "status" => 200,
                    "success" => "Module ajouté avec succès.",
                    "idModule" => $idModule
                ]);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(["error" => "Erreur inconnue !" . $e->getMessage()]);
            }
        }
    }

    public function makeOnline($idModule)
    {
        $mdl = DB::table('mdls')->where('idModule', $idModule)->update([
            'moduleStatut' => 1,
            'module_is_complete' => 0,
        ]);
        if ($mdl) {
            return response()->json(["success" => "Succès"]);
        } else {
            return response()->json(["error" => "Erreur inconnue !"]);
        }
    }

    public function makeOffline($idModule)
    {

        $make = DB::table('mdls')->where('idModule', $idModule)->update([
            'moduleStatut' => 0,
            'module_is_complete' => 0,
        ]);

        if ($make) {
            return response()->json(["success" => "succèss"]);
        } else {
            return response()->json(["error" => "Opération impossible, module déjas utilisé !"]);
        }
    }

    public function makeTrashed($idModule)
    {
        $mdl = DB::table('mdls')->where('idModule', $idModule)->update([
            'moduleStatut' => 2
        ]);

        if ($mdl == true) {
            return response()->json(["success" => "Succès"]);
        } else {
            return response()->json(["error" => "Erreur inconnue !"]);
        }
    }

    public function restoreModule($idModule)
    {
        $mdl = DB::table('mdls')->where('idModule', $idModule)->update([
            'moduleStatut' => 0
        ]);

        if ($mdl == true) {
            return response()->json(["success" => "Succès"]);
        } else {
            return response()->json(["error" => "Erreur inconnue !"]);
        }
    }

    public function destroy($idModule)
    {
        try {
            DB::beginTransaction();

            DB::table('prerequis_modules')->where('idModule', $idModule)->delete();
            DB::table('cible_modules')->where('idModule', $idModule)->delete();
            DB::table('programmes')->where('idModule', $idModule)->delete();
            DB::table('prestation_modules')->where('idModule', $idModule)->delete();
            DB::table('objectif_modules')->where('idModule', $idModule)->delete();
            DB::table('modules')->where('idModule', $idModule)->delete();
            DB::table('mdls')->where('idModule', $idModule)->delete();

            DB::commit();

            return response()->json(['success' => 'Le catalogue a été supprimé avec succès.']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Ce catalogue est rattaché à d\'autres éléments et ne peut pas être supprimé.'
            ]);
        }
    }


    public function edit($idModule)
    {
        $query = DB::table('v_module_cfps')
            ->select('idModule', 'reference', 'moduleName', 'module_tag', 'module_subtitle', 'description', 'minApprenant', 'dureeH', 'dureeJ', 'maxApprenant', 'prix', 'prixGroupe', 'idCustomer', 'nomDomaine AS module_domaine_name', 'idLevel', 'module_level_name')
            ->where('idModule', $idModule);

        if ($query->first()) {
            $domaineFormations = DB::select('SELECT idDomaine, nomDomaine FROM domaine_formations');

            $moduleLevels = DB::table('module_levels')->select('idLevel', 'module_level_name')->get();

            return response()->json([
                'module' => $query->first(),
                'domaineFormations' => $domaineFormations,
                'moduleLevels' => $moduleLevels
            ]);
        } else {
            return response(['message' => "Module introuvable !"], Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $req, $idModule)
    {
        $validation = Validator::make($req->all(), [
            'module_name' => 'required|min:2|max:200',
            'id_domaine_formation' => 'required',
            'idLevel' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json(['error' => $validation->messages()]);
        } else {
            try {
                DB::beginTransaction();

                DB::table('mdls')->where('idModule', $idModule)->update([
                    'reference' => $req->module_reference,
                    'moduleName' => $req->module_name,
                    'module_tag' => $req->module_tag,
                    'module_subtitle' => $req->module_subtitle,
                    'description' => $req->module_description,
                    'dureeH' => $req->module_dureeH,
                    'dureeJ' => $req->module_dureeJ,
                    'minApprenant' => $req->module_min_appr,
                    'maxApprenant' => $req->module_max_appr,
                    'idDomaine' => $req->id_domaine_formation,
                    'idLevel' => $req->idLevel
                ]);

                DB::table('modules')->where('idModule', $idModule)->update([
                    'prix' => $req->module_price,
                    'prixGroupe' => $req->module_prix_groupe
                ]);

                DB::commit();

                return response()->json(['success' => 'Modification éffectuée avec succès' . $idModule]);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Erreur inconnue !']);
            }
        }
    }

    public function show($idModule)
    {
        if ('module_is_complete' !== 1) {
            DB::table('mdls')->where('idModule', $idModule)->update([
                'module_is_complete' => 1
            ]);
        }
        $module = DB::table('v_module_cfps')
            ->select('idModule', 'module_image', 'reference AS module_reference', 'moduleName AS module_name', 'nomDomaine AS domaine_name', 'idDomaine', 'description AS module_description', 'minApprenant', 'dureeH', 'dureeJ', 'maxApprenant', 'prix AS module_price', 'prixGroupe', 'idCustomer', 'cfpName as nameCfp', 'logo as cfpLogo', 'idLevel', 'module_level_name', 'module_subtitle', 'moduleStatut')
            ->where('idModule', $idModule)
            ->first();
        $testImgMdl = (!empty($module->module_image) ? 1 : 0);
        $testObjectif = (!empty($this->listObjectifs($idModule)) ? 1 : 0);
        $testPrestation = (!empty($this->listPrestations($idModule)) ? 1 : 0);
        $testProgramme = (!empty($this->listProgrammes($idModule)) ? 1 : 0);
        $testCible = (!empty($this->listCibles($idModule)) ? 1 : 0);
        $testPrerequis = (!empty($this->listPrerequis($idModule)) ? 1 : 0);
        // Calcul de la somme des indicateurs

        //dd($testObjectif, $testPrestation, $testProgramme, $testCible, $testPrerequis);
        $testSumQuality = $testObjectif + $testPrestation + $testProgramme + $testCible + $testPrerequis + $testImgMdl;

        $module_ressources = DB::table('module_ressources')
            ->select('idModuleRessource', 'taille', 'module_ressource_name', 'module_ressource_extension', 'idModule')
            ->where('idModule', $idModule)
            ->get();

        $badge = DB::table('badges')
            ->select('idBadge', 'file_path', 'file_name')
            ->where('idModule', $idModule)
            ->first();

        return view('CFP.modules.components.detailCatalogueOld', compact(["module", "module_ressources", "testSumQuality", "badge"]));
    }

    public function updateImage(Request $req, $idModule)
    {
        $validate = Validator::make($req->all(), [
            'module_image' => 'required|image|mimes:png,jpg,webp,gif|max:6144'
        ]);

        if ($validate->fails()) {
            return back()->with('error', $validate->messages());
        } else {
            $module = DB::table('mdls')->select('module_image')->where('idModule', $idModule)->first();

            if ($req->hasFile('module_image')) {
                $folder = 'img/modules/' . $module->module_image;

                if (File::exists($folder)) {
                    File::delete($folder);
                }

                $file = $req->module_image;
                $extension = $file->getClientOriginalExtension();
                $fileName = time() . '.' . $extension;


                $manager = new ImageManager(new Driver());
                $image = $manager->read($file->getRealPath());

                $hauteur = 150;
                // Calculer la proportion
                $proportion = $hauteur / $image->height();

                // Calculer la hauteur en fonction de la proportion
                $largeur = $image->width() * $proportion;

                // $image->resize(width: 200, height: 200);
                $image->resize($largeur, $hauteur);
                $image->save(public_path('img/modules/' . $fileName));

                DB::table('mdls')->where('idModule', $idModule)->update([
                    'module_image' => $fileName
                ]);
            }

            return redirect('/cfp/modules/' . $idModule);
        }
    }

    public function updateImgMdl(Request $req, $idModule)
    {
        $module = DB::table('mdls')->select('module_image')->where('idModule', $idModule)->first();

        $driver = new Driver();

        $manager = new ImageManager($driver);

        if ($module != null) {
            if (!empty($module->module_image)) {
                Storage::disk('do')->delete('img/modules/' . $module->module_image);
            }

            $image_parts = explode(";base64,", $req->image);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $image = $manager->read($image_base64)->toWebp(25);

            $imageName = uniqid() . '.webp';
            $filePath = 'img/modules/' . $imageName;

            Storage::disk('do')->put($filePath, $image, 'public');

            DB::table('mdls')->where('idModule', $idModule)->update([
                'module_image' => $imageName,
            ]);

            return response()->json([
                'success' => 'Image Uploaded Successfully',
                'imageName' => $imageName
            ]);
        }
    }

    // Objectifs
    public function addObjectif($idModule, Request $req)
    {
        $validate = Validator::make($req->all(), [
            'objectif' => 'required|min:2|max:255',
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->messages()]);
        } else {
            $insert = DB::table('objectif_modules')->insert([
                "objectif" => $req->objectif,
                "idModule" => $idModule
            ]);

            if ($insert) {
                return response()->json(["success" => "Objectif ajouté avec succès"]);
            } else {
                return response()->json(["error" => "Erreur inconnue"]);
            }
        }
    }

    public function getObjectif($idModule)
    {
        $objectifs = DB::table('objectif_modules')->select('idObjectif', 'objectif', 'idModule')->where('idModule', $idModule)->get();

        return response()->json(['objectifs' => $objectifs]);
    }

    public function deleteObjectif($idObjectif)
    {
        $delete = DB::table('objectif_modules')->where('idObjectif', $idObjectif)->delete();

        if ($delete) {
            return response()->json(["success" => "Supprimé avec succès"]);
        } else {
            return response()->json(["error" => "Erreur inconnue"]);
        }
    }

    // Prestations
    public function addPrestation($idModule, Request $req)
    {
        $validate = Validator::make($req->all(), [
            'prestation_name' => 'required|min:2|max:255',
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->messages()]);
        } else {
            $insert = DB::table('prestation_modules')->insert([
                "prestation_name" => $req->prestation_name,
                "idModule" => $idModule
            ]);

            if ($insert) {
                return response()->json(["success" => "Succès"]);
            } else {
                return response()->json(["error" => "Erreur inconnue"]);
            }
        }
    }

    public function getPrestation($idModule)
    {
        $prestations = DB::table('prestation_modules')->select('idPrestation', 'prestation_name')->where('idModule', $idModule)->get();

        return response()->json(['prestations' => $prestations]);
    }

    public function deletePrestation($idPrestation)
    {
        $delete = DB::table('prestation_modules')->where('idPrestation', $idPrestation)->delete();

        if ($delete) {
            return response()->json(["success" => "Supprimée avec succès"]);
        } else {
            return response()->json(["error" => "Erreur inconnue"]);
        }
    }

    // Cibles
    public function addCible($idModule, Request $req)
    {
        $validate = Validator::make($req->all(), [
            'cible' => 'required|min:2|max:255'
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->messages()]);
        } else {
            $insert = DB::table('cible_modules')->insert([
                "cible" => $req->cible,
                "idModule" => $idModule
            ]);

            if ($insert) {
                return response()->json(["success" => "public cible ajouté avec succès"]);
            } else {
                return response()->json(["error" => "Erreur inconnue"]);
            }
        }
    }

    public function getCible($idModule)
    {
        $cibles = DB::table('cible_modules')->select('idCible', 'cible')->where('idModule', $idModule)->get();

        return response()->json(['cibles' => $cibles]);
    }

    public function deleteCible($idCible)
    {
        $delete = DB::table('cible_modules')->where('idCible', $idCible)->delete();

        if ($delete) {
            return response()->json(["success" => "Supprimé avec succès"]);
        } else {
            return response()->json(["error" => "Erreur inconnue"]);
        }
    }

    public function getDomainFormations()
    {
        $domaineFormations = DB::select('SELECT idDomaine, nomDomaine FROM domaine_formations');

        return response()->json(['domaineFormations' => $domaineFormations]);
    }

    public function getAllModuleCfp()
    {
        $modules = DB::table('v_module_cfps')->select('idModule', 'moduleName', 'nomDomaine as domaine_name')->where('idCustomer', Customer::idCustomer())->get();

        $allModule = [];
        foreach ($modules as $mdl) {
            $allModule[] = [
                'id' => $mdl->idModule,
                'name' => $mdl->moduleName,
                'domaine_name' => $mdl->domaine_name
            ];
        }

        return response()->json([
            'modules' =>     $allModule
        ]);
    }

    public function allModuleCfp()
    {
        $modules = DB::table('mdls')->select('idModule as id', 'moduleName as name')->where('idCustomer', Customer::idCustomer())->whereNot('moduleName', 'Default module')->get();

        return response()->json($modules, 200);
    }

    public function storeFirst(Request $req)
    {
        $validate = Validator::make($req->all(), [
            'module_reference'  => 'required|min:2|max:200',
            'module_name'       => 'required|min:2|max:200'
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->messages()]);
        } else {
            try {
                DB::beginTransaction();

                $idModule = DB::table('mdls')->insertGetId([
                    'reference'     => $req->module_reference,
                    'moduleName'    => $req->module_name,
                    'idDomaine'     => 1,
                    'idCustomer'    => Customer::idCustomer(),
                    'idTypeModule'  => 1
                ]);

                DB::table('modules')->insert([
                    'idModule' => $idModule
                ]);

                DB::commit();
                return response()->json(['success' => 'Succès']);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['error' => $e->getMessage()]);
            }
        }
    }

    public function getFirstModules()
    {
        $modules = DB::table('mdls')->select('idModule', 'moduleName AS module_name', 'module_image')->where('idCustomer', Customer::idCustomer())->where('moduleName', '<>', 'Default module')->orderBy('moduleName', 'asc')->get();

        return response()->json(['modules' => $modules]);
    }

    public function getAllModulesByKey(Request $req)
    {
        $modules = DB::table('mdls')
            ->select('idModule', 'moduleName AS module_name', 'module_image')
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleName', '<>', 'Default module');

        if ($req->key) {
            $modules->where('moduleName', 'like', "%{$req->key}%");
        }

        $modules = $modules->orderBy('moduleName', 'asc')->get();

        return response()->json(['modules' => $modules]);
    }

    // AVIS SUR LE MODULE
    public function avis()
    {
        return view('CFP.modules.components.catalogue.avis');
    }


    // Prerequis
    public function addPrerequis($idModule, Request $req)
    {
        $validate = Validator::make($req->all(), [
            'prerequis_name' => 'required|min:2|max:255',
        ]);

        if ($validate->fails()) {
            return response()->json(['error' => $validate->messages()]);
        } else {
            $insert = DB::table('prerequis_modules')->insert([
                "prerequis_name" => $req->prerequis_name,
                "idModule" => $idModule
            ]);

            if ($insert) {
                return response()->json(["success" => "Succès"]);
            } else {
                return response()->json(["error" => "Erreur inconnue"]);
            }
        }
    }

    public function getPrerequis($idModule)
    {
        $prerequis = DB::table('prerequis_modules')->select('idPrerequis', 'prerequis_name')->where('idModule', $idModule)->get();

        return response()->json(['prerequis' => $prerequis]);
    }

    public function deletePrerequis($idPrerequis)
    {
        $delete = DB::table('prerequis_modules')->where('idPrerequis', $idPrerequis)->delete();

        if ($delete) {
            return response()->json(["success" => "Supprimée avec succès"]);
        } else {
            return response()->json(["error" => "Erreur inconnue"]);
        }
    }

    public function detailModule($idModule)
    {
        $module = DB::table('mdls')
            ->select('mdls.idModule', 'reference', 'moduleName', 'description', 'module_image', 'minApprenant', 'maxApprenant', 'dureeJ', 'dureeH', 'nomDomaine', 'prix', 'prixGroupe')
            ->join('domaine_formations', 'domaine_formations.idDomaine', 'mdls.idDomaine')
            ->join('modules', 'modules.idModule', 'mdls.idModule')
            ->where('mdls.idModule', $idModule);

        if ($module->exists()) {
            $objectifs = DB::table('objectif_modules')
                ->select('objectif', 'idObjectif')
                ->where('idModule', $idModule)
                ->get();

            $programmes = DB::table('programmes')->select('idProgramme', 'program_title', 'program_description')->where('idModule', $idModule)->get();

            return response()->json([
                'status' => 200,
                'module' => $module->first(),
                'objectifs' => $objectifs,
                'programmes' => $programmes,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ], 404);
        }
    }

    public function getModuleLevel()
    {
        $query = DB::table('module_levels')->select('idLevel', 'module_level_name');

        if ($query->count() <= 0) {
            return response(['messages' => "Aucun résultat trouvé !"], 404);
        }

        return response(['levels' => $query->get()]);
    }

    public function getSumQuality($idModule)
    {

        $module = DB::table('v_module_cfps')
            ->select('idModule', 'module_image', 'reference AS module_reference', 'moduleName AS module_name', 'nomDomaine AS domaine_name', 'idDomaine', 'description AS module_description', 'minApprenant', 'dureeH', 'dureeJ', 'maxApprenant', 'prix AS module_price', 'prixGroupe', 'idCustomer', 'cfpName as nameCfp', 'logo as cfpLogo', 'idLevel', 'module_level_name', 'module_subtitle', 'moduleStatut')
            ->where('idModule', $idModule)
            ->first();
        $testImgMdl = (!empty($module->module_image) ? 1 : 0);
        $testObjectif = (!empty($this->listObjectifs($idModule)) ? 1 : 0);
        $testPrestation = (!empty($this->listPrestations($idModule)) ? 1 : 0);
        $testProgramme = (!empty($this->listProgrammes($idModule)) ? 1 : 0);
        $testCible = (!empty($this->listCibles($idModule)) ? 1 : 0);
        $testPrerequis = (!empty($this->listPrerequis($idModule)) ? 1 : 0);
        // Calcul de la somme des indicateurs
        $testSumQuality = $testObjectif + $testPrestation + $testProgramme + $testCible + $testPrerequis + $testImgMdl;
        return response()->json(['testSumQuality' => $testSumQuality]);;
    }
}
