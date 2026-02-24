<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ModuleInterneController extends Controller
{
    public function idEtp()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idCustomer;
    }

    public function search($name)
    {
        $onlineModules = DB::table('v_module_etps')
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
                'idCustomer',
                'initialName',
                'cfpName as nameCfp',
                'logo as cfpLogo',
                'nomDomaine',
                'idDomaine',
                'module_is_complete'
            )
            ->where('idCustomer', $this->idEtp())
            ->where('moduleStatut', 1)
            ->where('moduleName', '!=', 'Default module')
            ->where('moduleName', 'like', '%' . $name . '%')
            ->get();

        $offlineModules = DB::table('v_module_etps')
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
                'idCustomer',
                'initialName',
                'cfpName as nameCfp',
                'logo as cfpLogo',
                'nomDomaine',
                'idDomaine',
                'module_is_complete'
            )
            ->where('idCustomer', $this->idEtp())
            ->where('moduleStatut', 0)
            ->where('moduleName', '!=', 'Default module')
            ->where('moduleName', 'like', '%' . $name . '%')
            ->get();

        $onlineModulesHmtl = '';

        foreach ($onlineModules as $key => $value) {
            $onlineModulesHmtl .= view('components.catalogue-card-etp', ['m' => $value])->render();
        }

        $offlineModulesHtml = '';

        foreach ($offlineModules as $key => $value) {
            $offlineModulesHtml .= view('components.catalogue-card-etp', ['m' => $value])->render();
        }


        return response()->json([
            'onlineModules' => $onlineModules,
            'offlineModules' => $offlineModules,
            'onlineModulesHmtl' => $onlineModulesHmtl ?? null,
            'offlineModulesHtml' => $offlineModulesHtml ?? null,
        ]);
    }
    public function index()
    {
        $domaines = DB::table('v_module_etps')
            ->select('idDomaine', 'nomDomaine AS domaine_name', 'moduleStatut AS module_status')
            ->groupBy('idDomaine', 'nomDomaine', 'moduleStatut')
            ->where('idCustomer', $this->idEtp())
            ->where('moduleName', '!=', 'Default module')
            ->orderBy('nomDomaine', 'asc')
            ->get();

        $onlineModules = DB::table('v_module_etps')
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

                'idCustomer',
                'initialName',
                'cfpName as nameCfp',
                'logo as cfpLogo',
                'nomDomaine',
                'idDomaine',
                'module_is_complete'
            )
            ->where('idCustomer', $this->idEtp())
            ->where('moduleStatut', 1)
            ->where('moduleName', '!=', 'Default module')
            ->orderBy('moduleName', 'desc')
            ->get();

        $allOnlineModules = DB::table('v_module_etps')
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

                'idCustomer',
                'initialName',
                'cfpName as nameCfp',
                'logo as cfpLogo',
                'nomDomaine',
                'idDomaine',
                'module_is_complete'
            )
            ->where('idCustomer', $this->idEtp())
            ->where('moduleStatut', 1)
            ->where('moduleName', '!=', 'Default module')
            ->orderBy('moduleName', 'desc')
            ->get();

        $offlineModules = DB::table('v_module_etps')
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

                'idCustomer',
                'initialName',
                'cfpName as nameCfp',
                'logo as cfpLogo',
                'nomDomaine',
                'idDomaine',
                'module_is_complete'
            )
            ->where('idCustomer', $this->idEtp())
            ->where('moduleStatut', 0)
            ->where('moduleName', '!=', 'Default module')
            ->orderBy('moduleName', 'desc')
            ->get();


        $allOfflineModules = DB::table('v_module_etps')
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

                'idCustomer',
                'initialName',
                'cfpName as nameCfp',
                'logo as cfpLogo',
                'nomDomaine',
                'idDomaine',
                'module_is_complete'
            )
            ->where('idCustomer', $this->idEtp())
            ->where('moduleStatut', 0)
            ->where('moduleName', '!=', 'Default module')
            ->orderBy('moduleName', 'desc')
            ->get();

        $trashedModules = DB::table('v_module_etps')
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

                'idCustomer',
                'initialName',
                'cfpName as nameCfp',
                'logo as cfpLogo',
                'nomDomaine',
                'idDomaine',
                'module_is_complete'
            )
            ->where('idCustomer', $this->idEtp())
            ->where('moduleStatut', 2)
            ->where('moduleName', '!=', 'Default module')
            ->orderBy('moduleName', 'desc')
            ->get();

        $countOnline = DB::table('v_module_etps')
            ->where('idCustomer', $this->idEtp())
            ->where('moduleStatut', 1)
            ->where('moduleName', '!=', 'Default module')
            ->orderBy('moduleName', 'desc')
            ->count();

        $countOffline = DB::table('v_module_etps')
            ->where('idCustomer', $this->idEtp())
            ->where('moduleStatut', 0)
            ->where('moduleName', '!=', 'Default module')
            ->orderBy('moduleName', 'desc')
            ->count();

        $badgeOnline = DB::table('v_module_etps')
            ->where('idCustomer', $this->idEtp())
            ->where('moduleStatut', 1)
            ->where('moduleName', '!=', 'Default module')
            ->where('module_is_complete', '!=', 1) // Vérifier si au moins un module_is_complete est différent de zéro
            ->exists();

        $badgeOffline = DB::table('v_module_etps')
            ->where('idCustomer', $this->idEtp())
            ->where('moduleStatut', 0)
            ->where('moduleName', '!=', 'Default module')
            ->where('module_is_complete', '!=', 1) // Vérifier si au moins un module_is_complete est différent de zéro
            ->exists();

        $customer = DB::table('v_detail_customers')->select('idCustomer', 'initialName', 'customerName as name', 'customer_addr_lot as adress', 'customerPhone as phone', 'description', 'siteWeb', 'logo', 'customerEmail as email')->where('idCustomer', $this->idEtp())->first();

        return view('ETP.moduleInternes.index', compact(['onlineModules', 'offlineModules', 'trashedModules', 'customer', 'domaines', 'countOnline', 'countOffline', 'badgeOnline', 'badgeOffline', 'allOnlineModules', 'allOfflineModules']));
    }

    public function getDomainFormations()
    {
        $domaineFormations = DB::select('SELECT idDomaine, nomDomaine FROM domaine_formations');

        return response()->json(['domaineFormations' => $domaineFormations]);
    }

    public function create()
    {
        return view('ETP.moduleInternes.create');
    }

    public function store(Request $req)
    {
        $validation = Validator::make($req->all(), [
            'module_name' => 'required|min:2|max:200',
            'module_subtitle' => 'required|min:2|max:200',
            'id_domaine_formation' => 'required'
        ]);

        $typeModule = DB::table('type_modules')->select('idTypeModule', 'typeModule')->get();

        if ($validation->fails()) {
            return response()->json($validation->messages());
        } else {
            try {
                DB::beginTransaction();

                DB::table('mdls')->insert([

                    'reference' => $req->module_reference,
                    'module_tag' => $req->module_tag,
                    'moduleName' => $req->module_name,
                    'module_subtitle' => $req->module_subtitle,
                    'dureeH' => $req->module_dureeH,
                    'dureeJ' => $req->module_dureeJ,
                    'minApprenant' => $req->module_min_appr,
                    'maxApprenant' => $req->module_max_appr,
                    'idTypeModule' => $typeModule[1]->idTypeModule,
                    'moduleStatut' => 0,
                    'idCustomer' => $this->idEtp(),
                    'idDomaine' => $req->id_domaine_formation

                ]);

                $mdl = DB::table('mdls')->select('idModule')->orderBy('idModule', 'desc')->first();

                DB::table('module_internes')->insert([
                    'idModule' => $mdl->idModule,

                ]);

                DB::commit();

                return response()->json(["success" => "Module interne ajouté avec succès."]);
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

            DB::table('module_internes')->where('idModule', $idModule)->delete();
            DB::table('mdls')->where('idModule', $idModule)->delete();

            DB::commit();

            return response()->json(['success' => 'ok']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'not ok']);
        }
    }


    public function show($idModule)
    {
        if ('module_is_complete' !== 1) {
            DB::table('mdls')->where('idModule', $idModule)->update([
                'module_is_complete' => 1
            ]);
        }
        $module = DB::table('v_module_etps')
            //->select('idModule', 'reference', 'moduleName', 'prerequis', 'description', 'cible', 'minApprenant', 'objectif', 'dureeH', 'dureeJ', 'maxApprenant', 'idCustomer', 'etpName')
            ->select('idModule', 'module_image', 'reference AS module_reference', 'moduleName AS module_name', 'nomDomaine AS domaine_name', 'description AS module_description', 'minApprenant', 'dureeH', 'dureeJ', 'maxApprenant', 'idCustomer', 'cfpName as nameCfp', 'logo as cfpLogo')
            ->where('idModule', $idModule)
            ->first();

        // $programmes = DB::select("SELECT idCustomer, idProgramme, idModule, titre, pDescription, reference, moduleName, moduleDescription, cible, objectif, dureeH, dureeJ  FROM v_programme_etps WHERE idCustomer = ?", [$customer[0]->idCustomer]);
        $module_ressources = DB::table('module_ressources')->select('idModuleRessource', 'module_ressource_name', 'module_ressource_extension', 'idModule')->where('idModule', $idModule)->get();  // <=========== à Revoir
        // dd($module);
        return view('ETP.moduleInternes.detail', compact(["module", "module_ressources"]));
    }

    public function edit($idModule)
    {
        $module = DB::table('v_module_etps')
            ->select('idModule', 'reference', 'moduleName', 'module_tag', 'module_subtitle', 'description', 'minApprenant', 'dureeH', 'dureeJ', 'maxApprenant', 'idCustomer', 'nomDomaine AS module_domaine_name')
            ->where('idModule', $idModule)
            ->first();

        $domaineFormations = DB::select('SELECT idDomaine, nomDomaine FROM domaine_formations');

        return response()->json([
            'module' => $module,
            'domaineFormations' => $domaineFormations
        ]);
    }

    public function update(Request $req, $idModule)
    {
        $validation = Validator::make($req->all(), [
            'module_name' => 'required|min:2|max:200',
            'id_domaine_formation' => 'required'
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
                    'idDomaine' => $req->id_domaine_formation
                ]);

                // DB::table('module_internes')->where('idModule', $idModule)->update([
                //     'prix' => $req->module_price,
                //     'prixGroupe' => $req->module_prix_groupe
                // ]);

                DB::commit();

                return response()->json(['success' => 'Modification éffectuée avec succès' . $idModule]);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Erreur inconnue !']);
            }
        }
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

    // public function updateImgMdl(Request $req, $idModule)
    // {
    //     $module = DB::table('mdls')->select('module_image')->where('idModule', $idModule)->first();

    //     if ($module != null) {
    //         $folder = 'img/modules/' . $module->module_image;

    //         if (File::exists($folder)) {
    //             File::delete($folder);
    //         }

    //         $folderPath = public_path('img/modules/');

    //         $image_parts = explode(";base64,", $req->image);
    //         $image_type_aux = explode("image/", $image_parts[0]);
    //         $image_type = $image_type_aux[1];
    //         $image_base64 = base64_decode($image_parts[1]);

    //         $imageName = uniqid() . '.png';
    //         $imageFullPath = $folderPath . $imageName;

    //         file_put_contents($imageFullPath, $image_base64);

    //         DB::table('mdls')->where('idModule', $idModule)->update([
    //             'module_image' => $imageName,
    //         ]);
    //         return response()->json([
    //             'success' => 'Image Uploaded Successfully',
    //             'imageName' =>  $imageName
    //         ]);
    //     }
    // }

    public function updateImgMdl(Request $req, $idModule)
    {
        $module = DB::table('mdls')->select('module_image')->where('idModule', $idModule)->first();

        $driver = new Driver();

        $manager = new ImageManager($driver);

        if ($module != null) {
            $folder = 'img/modules/' . $module->module_image;

            // Delete old image from DigitalOcean Space
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

            // Upload the image to DigitalOcean Space
            Storage::disk('do')->put($filePath, $image, 'public');

            // Update the database with the new image name
            DB::table('mdls')->where('idModule', $idModule)->update([
                'module_image' => $imageName,
            ]);

            return response()->json([
                'success' => 'Image Uploaded Successfully',
                'imageName' => $imageName
            ]);
        }
    }

    public function getAllModuleEtp() //<---- A modifier
    {
        $modules = DB::table('v_module_etps')->select('idModule', 'moduleName')->where('idCustomer', $this->idEtp())->get();

        if(count($modules) <= 0){
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }else{
            $allModule = [];
            foreach ($modules as $mdl) {
                $allModule[] = [
                    'id' => $mdl->idModule,
                    'name' => $mdl->moduleName,
                ];
            }
    
            return response()->json([
                'status' => 200,
                'modules' =>     $allModule
            ]);
        }
    }

    public function getFirstModuleInternes()
    {
        $modules = DB::table('mdls')->select('idModule', 'moduleName AS module_name', 'module_image')->where('idCustomer', $this->idEtp())
            ->where('moduleName', '<>', 'Default module')
            ->where('idTypeModule', '=', 2)
            ->orderBy('moduleName', 'asc')->get();

        return response()->json(['modules' => $modules]);
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

    public function detailModules($idModule)
    {
        $module = DB::table('mdls')
            ->select('mdls.idModule', 'reference', 'moduleName', 'description', 'module_image', 'minApprenant', 'maxApprenant', 'dureeJ', 'dureeH', 'nomDomaine', 'prix', 'prixGroupe')
            ->join('domaine_formations', 'domaine_formations.idDomaine', 'mdls.idDomaine')
            ->join('modules', 'modules.idModule', 'mdls.idModule')
            ->where('mdls.idModule', $idModule);

        if($module->exists()){
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
        }else{
            return response()->json([
                'status' => 404,
                'message' => 'Introuvable !'
            ], 404);
        }
    }
}
