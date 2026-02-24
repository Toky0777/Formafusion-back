<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Google\Service\PeopleService\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuleSkillController extends Controller
{
    public function getModuleCfp()
    {
        $modules = DB::table('mdls')
            ->select('idModule', 'moduleName')
            ->where('moduleName', '!=', 'Default Module')
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleStatut', 1)
            ->orderBy('moduleName', 'asc')
            ->get();

        $skills = DB::table('module_skills as ms')
            ->select('ms.id', 'ms.name', 'ms.idModule')
            ->join('mdls', 'ms.idModule', 'mdls.idModule')
            ->where('mdls.idCustomer', Customer::idCustomer())
            ->get();

        if (count($modules) <= 0) {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }

        $modulesWithSkills = $modules->map(function ($module) use ($skills) {
            $module->skills = $skills->where('idModule', $module->idModule)->values();
            return $module;
        });

        return response()->json([
            'status' => 200,
            'modules' => [
                'modules_count' => count($modules),
                'modules_items' => $modulesWithSkills
            ]
        ], 200);
    }

    public function index($idModule)
    {
        $module = DB::table('mdls')->where('idModule', $idModule)->where('idCustomer', Customer::idCustomer());

        if (!$module->exists()) {
            return response()->json([
                'status' => 404,
                'message' => 'Module introuvable !',
                'ok'
            ], 404);
        } else {
            $skills = DB::table('module_skills')
                ->select('id', 'name', 'idModule')
                ->where('idModule', $idModule)
                ->orderBy('name', 'asc')
                ->get();

            if (count($skills) <= 0) {
                return response()->json([
                    'status' => 204,
                    'message' => 'Aucun résultat !'
                ], 204);
            }

            return response()->json([
                'status' => 200,
                'skills' => [
                    'skill_count' => count($skills),
                    'skill_items' => $skills
                ]
            ], 200);
        }
    }

    public function store(Request $req, $idModule)
    {
        $req->validate([
            'name' => 'required|min:2|max:150'
        ]);

        $module = DB::table('mdls')->where('idModule', $idModule)->where('idCustomer', Customer::idCustomer());

        if (!$module->exists()) {
            return response()->json([
                'status' => 404,
                'message' => 'Module introuvable !'
            ], 404);
        } else {
            $check = DB::table('module_skills')
                ->where('idModule', $idModule)
                ->count();

            if ($check < 9) {
                $idSkill = DB::table('module_skills')->insertGetId([
                    'name' => $req->name,
                    'idModule' => $idModule
                ]);

                $newSkill = DB::table('module_skills')->where('id', $idSkill)->first();

                return response()->json([
                    'status' => 200,
                    'message' => 'Ajouté avec succès',
                    'data' => $newSkill
                ], 200);
            } else {
                return response()->json([
                    'status' => 500,
                    'message' => 'Vous avez atteint le nombre maximal autorisé !'
                ], 500);
            }
        }
    }

    public function edit(Request $req, $idModule, $idSkill)
    {
        $req->validate([
            'name' => 'required|min:2|max:150'
        ]);

        $skill = DB::table('module_skills as ms')
            ->join('mdls', 'ms.idModule', 'mdls.idModule')
            ->where('mdls.idCustomer', Customer::idCustomer())
            ->where('ms.idModule', $idModule)
            ->where('ms.id', $idSkill);

        if (!$skill->exists()) {
            return response()->json([
                'status' => 404,
                'message' => 'Skill introuvable !'
            ], 404);
        }

        $skill->update([
            'name' => $req->name
        ]);
        $updatedSkill = DB::table('module_skills')
            ->where('id', $idSkill)
            ->first();

        return response()->json([
            'status' => 200,
            'message' => 'Modifié avec succès',
            'skill' => $updatedSkill
        ], 200);
    }


    public function destroy($idModule, $idSkill)
    {
        $skill = DB::table('module_skills as ms')
            ->join('mdls', 'ms.idModule', 'mdls.idModule')
            ->where('mdls.idCustomer', Customer::idCustomer())
            ->where('ms.idModule', $idModule)
            ->where('ms.id', $idSkill);

        if (!$skill->exists()) {
            return response()->json([
                'status' => 404,
                'message' => 'Skill introuvable !'
            ], 404);
        } else {
            $skill->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Supprimé avec succès'
            ], 200);
        }
    }
}
