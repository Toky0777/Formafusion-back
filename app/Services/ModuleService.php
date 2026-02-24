<?php

namespace App\Services;

use App\Interfaces\ModuleRepository;
use App\Models\Module;
use App\Traits\HasModule;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;

class ModuleService implements ModuleRepository
{
    use HasModule;

    public function storeMdls(

        $reference = null,
        $tag = null,
        $name,
        $subtitle,
        $durationHour = null,
        $durationDay = null,
        $minAppr = null,
        $maxAppr = null,
        $idTypeModule,
        $idCustomer,
        $idDomaineFormation,
        $idLevel,
        $icone = null

    ): mixed {
        $idModule = DB::table('mdls')->insertGetId([
            'reference' => $reference,
            'module_tag' => $tag,
            'moduleName' => $name,
            'module_subtitle' => $subtitle,
            'dureeH' => $durationHour,
            'dureeJ' => $durationDay,
            'minApprenant' => $minAppr,
            'maxApprenant' => $maxAppr,
            'idTypeModule' => $idTypeModule,
            'moduleStatut' => 0,
            'idCustomer' => $idCustomer,
            'idDomaine' => $idDomaineFormation,
            'idLevel' => $idLevel,
            'module_image' => $icone
        ]);

        return $idModule;
    }

    public function storeMdlsPublic(

        $reference = null,
        $tag = null,
        $name,
        $subtitle,
        $durationHour = null,
        $durationDay = null,
        $minAppr = null,
        $maxAppr = null,
        $idTypeModule,
        $idCustomer,
        $idDomaineFormation,
        $idLevel

    ): mixed {
        $idModule = DB::table('mdls')->insertGetId([
            'reference' => $reference,
            'module_tag' => $tag,
            'moduleName' => $name,
            'module_subtitle' => $subtitle,
            'dureeH' => $durationHour,
            'dureeJ' => $durationDay,
            'minApprenant' => $minAppr,
            'maxApprenant' => $maxAppr,
            'idTypeModule' => $idTypeModule,
            'moduleStatut' => 0,
            'idCustomer' => $idCustomer,
            'idDomaine' => $idDomaineFormation,
            'idLevel' => $idLevel,
            'is_public' => 1
        ]);

        return $idModule;
    }

    public function storeModule($idModule, $price = null, $priceGroup = null): void
    {
        DB::table('modules')->insert([
            'idModule' => $idModule,
            'prix' => $price,
            'prixGroupe' => $priceGroup
        ]);
    }

    public function storeModuleInterne($idModule): void
    {
        DB::table('module_internes')->insert([
            'idModule' => $idModule
        ]);
    }

    public function changeStatus($idModule, $idCustomer, $status, $isComplete): bool
    {
        $module = DB::table('mdls')->where('idModule', $idModule)->where('idCustomer', $idCustomer);

        if ($module->exists()) {
            $module->update([
                'moduleStatut' => $status,
                'module_is_complete' => $isComplete,
            ]);

            return true;
        } else {
            return false;
        }
    }

    public function getModule($id, $idCustomer): mixed
    {
        $query = DB::table('v_modules')
            ->select('idModule', 'reference as module_reference', 'moduleName as module_name', 'module_tag', 'module_subtitle', 'description as module_description', 'minApprenant', 'dureeH', 'dureeJ', 'maxApprenant', 'prix as module_price', 'prixGroupe as module_price_group', 'idCustomer', 'nomDomaine AS module_domain_name', 'idLevel', 'module_level_name', 'module_image')
            ->where('idModule', $id)
            ->where('idCustomer', $idCustomer);

        return $query;
    }

    public function updateMdls($idModule, $reference = null, $tag = null, $name, $subtitle, $description, $durationHour = null, $durationDay = null, $minAppr = null, $maxAppr = null, $idCustomer, $idDomaineFormation, $idLevel, $icone = null): void
    {
        DB::table('mdls')
            ->where('idModule', $idModule)
            ->where('idCustomer', $idCustomer)
            ->update([
                'reference' => $reference,
                'moduleName' => $name,
                'module_tag' => $tag,
                'module_subtitle' => $subtitle,
                'description' => $description,
                'dureeH' => $durationHour,
                'dureeJ' => $durationDay,
                'minApprenant' => $minAppr,
                'maxApprenant' => $maxAppr,
                'idDomaine' => $idDomaineFormation,
                'idLevel' => $idLevel,
               'module_image' => $icone
            ]);
    }

    public function updateModuleImage($idModule, $idCustomer, $query, $imageFile): void
    {
        $module = $query->first();

        $manager = new ImageManager(new Driver());

        $image_parts = explode(";base64,", $imageFile);
        $image_base64 = base64_decode($image_parts[1]);
        $image = $manager->read($image_base64)->toWebp(25);
        $imageName = uniqid() . '.webp';
        $filePath = 'img/modules/' . $imageName;

        DB::transaction(function () use ($idModule, $module, $filePath, $image, $imageName) {
            if (!empty($module->module_image)) {
                Storage::disk('do')->delete('img/modules/' . $module->module_image);
            }

            Storage::disk(config('app.env') === 'local' ? 'public' : 'do')->put($filePath, $image);

            DB::table('mdls')->where('idModule', $idModule)->update([
                'module_image' => $imageName,
            ]);
        });
    }

    public function updateModule($idModule, $price = null, $priceGroup = null): void
    {
        DB::table('modules')
            ->where('idModule', $idModule)
            ->update([
                'prix' => $price,
                'prixGroupe' => $priceGroup
            ]);
    }

    public function storeObjectif($idModule, $name)
    {
        $id = DB::table('objectif_modules')->insertGetId([
            "objectif" => $name,
            "idModule" => $idModule
        ]);
        return DB::table('objectif_modules')->where('idObjectif', $id)->first();
    }

    public function destroyObjectif($idModule, $idObjectif): void
    {
        DB::table('objectif_modules')->where('idObjectif', $idObjectif)->delete();
    }

    public function storePrestation($idModule, $name)
    {

        DB::table('prestation_modules')->insert([
            "prestation_name" => $name,
            "idModule" => $idModule
        ]);
    }

    public function destroyPrestation($idModule, $idPrestation): void
    {
        DB::table('prestation_modules')->where('idPrestation', $idPrestation)->delete();
    }

    public function storePrerequis($idModule, $name)
    {
        DB::table('prerequis_modules')->insert([
            "prerequis_name" => $name,
            "idModule" => $idModule
        ]);
    }

    public function destroyPrerequis($idModule, $idPrerequis): void
    {
        DB::table('prerequis_modules')->where('idPrerequis', $idPrerequis)->delete();
    }

    public function storeCible($idModule, $name)
    {
        DB::table('cible_modules')->insert([
            "cible" => $name,
            "idModule" => $idModule
        ]);
    }

    public function destroyCible($idModule, $idCible): void
    {
        DB::table('cible_modules')->where('idCible', $idCible)->delete();
    }

    public function getSumQuality($idModule, $idCustomer): mixed
    {
        $module = $this->getModule($idModule, $idCustomer)->first();

        $testImgMdl = (!empty($module->module_image) ? 1 : 0);
        $testObjectif = (!empty($this->listObjectifs($idModule)) ? 1 : 0);
        $testPrestation = (!empty($this->listPrestations($idModule)) ? 1 : 0);
        $testProgramme = (!empty($this->listProgrammes($idModule)) ? 1 : 0);
        $testCible = (!empty($this->listCibles($idModule)) ? 1 : 0);
        $testPrerequis = (!empty($this->listPrerequis($idModule)) ? 1 : 0);
        $testTitle = (!empty($module->module_name) ? 1 : 0);
        $testSubtitle = (!empty($module->module_subtitle) ? 1 : 0);
        $testPrice = (!empty($module->module_price) ? 1 : 0);


        $testSumQuality = $testPrice + $testSubtitle + $testTitle + $testObjectif + $testPrestation + $testProgramme + $testCible + $testPrerequis + $testImgMdl;

        return $testSumQuality;
    }

    public function storeProgram($idModule, $title, $description): void
    {
        DB::table('programmes')->insertGetId([
            'program_title' => $title,
            'program_description' => $description,
            'idModule' => $idModule
        ]);
    }

    public function destroyProgram($idModule, $idProgramme): void
    {
        DB::table('programmes')->where('idProgramme', $idProgramme)->delete();
    }

    public function updateProgram($idModule, $idProgramme, $title, $description): void
    {
        DB::table('programmes')->where('idProgramme', $idProgramme)->update([
            'program_title' => $title,
            'program_description' => $description,
            'idProgramme' => $idProgramme
        ]);
    }
}
