<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ModuleProgramRequest;
use App\Models\Customer;
use App\Services\ModuleService;
use App\Traits\HasModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProgrammeController extends Controller
{
    use HasModule;

    public function store(ModuleProgramRequest $req, $id, ModuleService $mdl)
    {
        $mdodule = $mdl->getModule($id, Customer::idCustomer());

        if ($mdodule->exists()) {
            $programId = $mdl->storeProgram(
                $id,
                $req->validated()['program_title'],
                $req->validated()['program_description'] ?? null
            );

            return response()->json([
                'status' => 200,
                'message' => 'Programme ajouté avec succès',
                'programId' => $programId
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Module introuvable !'
            ]);
        }
    }
    public function getProgrammes($idModule, ModuleService $mdl)
    {
        $module = $mdl->getModule($idModule, Customer::idCustomer());

        if ($module->exists()) {
            $programmes = $this->programs($idModule);

            return response()->json([
                "status" => 200,
                "programmes" => $programmes
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !",
                "idCustomer" => Customer::idCustomer(),
                "idModule" => $idModule
            ]);
        }
    }

    public function getProgrammesFormateur($idModule, ModuleService $mdl)
    {
        $module = $mdl->getModule($idModule, Auth::user()->id);

        if ($module) {
            $programmes = $this->programs($idModule);

            return response()->json([
                "status" => 200,
                "programmes" => $programmes
            ]);
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !",
                "idCustomer" => Auth::user()->id,
                "idModule" => $idModule
            ]);
        }
    }



    // public function destroy(ModuleService $mdl, $idModule, $idProgramme)
    // {
    //     $module = $mdl->getModule($idModule, Customer::idCustomer());

    //     if ($module) {
    //         $programme = DB::table('programmes')
    //             ->where('idModule', $idModule)
    //             ->where('idProgramme', $idProgramme);

    //         if ($programme->exists()) {
    //             $mdl->destroyProgram($idModule, $idProgramme);

    //             return response()->json([
    //                 "status" => 200,
    //                 "message" => "Programme supprimé avec succès"
    //             ]);
    //         } else {
    //             return response()->json([
    //                 "status" => 404,
    //                 "message" => "Programme introuvable !"
    //             ]);
    //         }
    //     } else {
    //         return response()->json([
    //             "status" => 404,
    //             "message" => "Module introuvable !"
    //         ]);
    //     }
    // }

    public function destroy(ModuleService $mdl, $idModule, $idProgramme)
    {
        $module = $mdl->getModule($idModule, Customer::idCustomer());

        if (!$module) {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !"
            ]);
        }

        $programme = DB::table('programmes')
            ->where('idModule', $idModule)
            ->where('idProgramme', $idProgramme);

        if (!$programme->exists()) {
            return response()->json([
                "status" => 404,
                "message" => "Programme introuvable !"
            ]);
        }

        // Vérifier s'il y a des contenus liés
        $contentsExist = DB::table('module_program_contents')
            ->where('idProgramme', $idProgramme)
            ->exists();

        if ($contentsExist) {
            return response()->json([
                "status" => 400,
                "message" => "Veuillez d'abord supprimer tous les contenus liés avant de supprimer le programme."
            ]);
        }

        // Supprimer le programme
        $mdl->destroyProgram($idModule, $idProgramme);

        return response()->json([
            "status" => 200,
            "message" => "Programme supprimé avec succès"
        ]);
    }

    public function edit($idModule, $idProgramme, ModuleService $mdl)
    {
        $module = $mdl->getModule($idModule, Customer::idCustomer());

        if ($module->exists()) {
            $programme = $this->program($idModule, $idProgramme);

            if ($programme->exists()) {
                return response()->json([
                    "status" => 200,
                    "programme" => $programme->first()
                ]);
            } else {
                return response()->json([
                    "status" => 404,
                    "message" => "Programme introuvable !"
                ]);
            }
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !"
            ]);
        }
    }

    public function update($id, ModuleProgramRequest $req, $idProgramme, ModuleService $mdl)
    {

        $module = $mdl->getModule($id, Customer::idCustomer());

        if ($module->exists()) {
            $programme = $this->program($id, $idProgramme);

            if ($programme->exists()) {
                $mdl->updateProgram(
                    $id,
                    $idProgramme,
                    $req->validated()['program_title'],
                    $req->validated()['program_description'] ?? null
                );

                return response()->json([
                    "status" => 200,
                    "message" => 'Programme modifié avec succès'
                ]);
            } else {
                return response()->json([
                    "status" => 404,
                    "message" => "Programme introuvable !"
                ]);
            }
        } else {
            return response()->json([
                "status" => 404,
                "message" => "Module introuvable !"
            ]);
        }
    }
}
