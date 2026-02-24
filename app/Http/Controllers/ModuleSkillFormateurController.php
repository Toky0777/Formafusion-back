<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuleSkillFormateurController extends Controller
{

    public function getModuleTrainer()
    {
        $modules = DB::table('v_projet_form')
            ->select('idFormateur', 'idModule', 'module_name')
            ->where('idFormateur', auth()->user()->id)
            ->where('module_name', '!=', 'Default module')
            ->groupBy('module_name')
            ->get();

        $skills = DB::table('module_skills as ms')
            ->select('ms.id', 'ms.name', 'ms.idModule')
            ->join('mdls', 'ms.idModule', 'mdls.idModule')
            ->get();

        $modulesWithSkills = $modules->map(function ($module) use ($skills) {
            $module->skills = $skills->where('idModule', $module->idModule)->values();
            return $module;
        });


        if (count($modules) < 0) {
            return response()->json([
                'status' => 204,
                'message' => "Aucun résultat"
            ], 200);
        }

        return response()->json([
            'status' => 200,
            'modules' => [
                'modules_count' => count($modules),
                'modules_items' => $modulesWithSkills
            ]
        ], 200);
    }

    public function store(Request $req, $idModule)
    {
        $req->validate([
            'name' => 'required|min:2|max:150'
        ]);

        $moduleExists = DB::table('v_projet_form')
            ->where('idFormateur', auth()->user()->id)
            ->where('idModule', $idModule)
            ->exists();

        if (!$moduleExists) {
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
        $moduleExists = DB::table('v_projet_form')
            ->where('idFormateur', auth()->user()->id)
            ->where('idModule', $idModule)
            ->exists();
        if (!$moduleExists) {
            return response()->json([
                'status' => 404,
                'message' => 'Module introuvable !'
            ], 404);
        }
        $skillExists = DB::table('module_skills')
            ->where('idModule', $idModule)
            ->where('id', $idSkill)
            ->exists();

        if (!$skillExists) {
            return response()->json([
                'status' => 404,
                'message' => 'Skill introuvable !'
            ], 404);
        }
        DB::table('module_skills')
            ->where('id', $idSkill)
            ->update([
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
        $moduleExists = DB::table('v_projet_form')
            ->where('idFormateur', auth()->user()->id)
            ->where('idModule', $idModule)
            ->exists();

        if (!$moduleExists) {
            return response()->json([
                'status' => 404,
                'message' => 'Module introuvable !'
            ], 404);
        }

        $skill = DB::table('module_skills')
            ->where('idModule', $idModule)
            ->where('id', $idSkill)
            ->first();

        if (!$skill) {
            return response()->json([
                'status' => 404,
                'message' => 'Skill introuvable !'
            ], 404);
        }

        DB::table('module_skills')
            ->where('id', $idSkill)
            ->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Supprimé avec succès'
        ], 200);
    }
}
