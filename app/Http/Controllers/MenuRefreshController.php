<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MenuRefreshController extends Controller
{
    public function index()
    {
        return view('CFP.refresh.index');
    }

    public function getByModule($idModule)
    {
        $menus = DB::table('menu_refresh')
            ->where('menu_refresh.idModule', $idModule)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $menus
        ]);
    }

    public function store(Request $request)
    {
        try {
            // Validation des données
            $validator = Validator::make($request->all(), [
                'menu' => 'required|string|max:200',
                'title' => 'nullable|string|max:200',
                'idModule' => 'required|integer|exists:modules,idModule'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Insertion avec Query Builder
            $menuId = DB::table('menu_refresh')->insertGetId([
                'menu' => $request->menu,
                'title' => $request->title ?? $request->menu,
                'idModule' => $request->idModule
            ]);

            // Récupérer le menu créé
            $newMenu = DB::table('menu_refresh')
                ->where('idMenuRefresh', $menuId)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Menu ajouté avec succès',
                'data' => $newMenu
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du menu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $menuId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'menu' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier que le menu existe
            $menu = DB::table('menu_refresh')->where('idMenuRefresh', $menuId)->first();

            if (!$menu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu non trouvé'
                ], 404);
            }

            // Mettre à jour le menu
            $updated = DB::table('menu_refresh')
                ->where('idMenuRefresh', $menuId)
                ->update([
                    'menu' => $request->input('menu')
                ]);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Menu mis à jour avec succès',
                    'data' => [
                        'idMenuRefresh' => $menuId,
                        'menu' => $request->input('menu')
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune modification effectuée'
                ], 400);
            }
        } catch (Exception $e) {
            Log::error('Erreur lors de la mise à jour du menu: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du menu'
            ], 500);
        }
    }

    public function destroy($menuId): JsonResponse
    {
        try {
            // Vérifier que le menu existe
            $menu = DB::table('menu_refresh')->where('idMenuRefresh', $menuId)->first();

            if (!$menu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu non trouvé'
                ], 404);
            }

            // Vérifier s'il y a des sous-menus
            // $hasSubmenus = DB::table('menu_refresh')
            //     ->where('parent_id', $menuId)
            //     ->exists();

            // if ($hasSubmenus) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Impossible de supprimer ce menu car il contient des sous-menus'
            //     ], 400);
            // }

            // Supprimer le menu
            $deleted = DB::table('menu_refresh')->where('idMenuRefresh', $menuId)->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Menu supprimé avec succès'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la suppression du menu'
                ], 400);
            }
        } catch (Exception $e) {
            Log::error('Erreur lors de la suppression du menu: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du menu'
            ], 500);
        }
    }

    // public function getModule()
    // {
    //     $modules = DB::table('v_module_cfps')
    //         ->select('idModule', 'moduleName', 'module_image', 'reference')
    //         ->where('idCustomer', Customer::idCustomer())
    //         ->where('moduleName', '!=', 'Default module')
    //         ->where('moduleStatut', 1)
    //         ->get();

    //     return view('CFP.refresh.module', compact('modules'));
    // }

    public function getModule()
    {
        $modules = DB::table('v_module_cfps')
            ->select('idModule', 'moduleName', 'module_image', 'reference')
            ->where('idCustomer', Customer::idCustomer())
            ->where('moduleName', '!=', 'Default module')
            ->where('moduleStatut', 1)
            ->get();

        return response()->json([
            'success' => true,
            'modules' => $modules
        ]);
    }


    // public function showModule($idModule)
    // {
    //     $menu = DB::table('menu_refresh')
    //         ->join('mdls', 'menu_refresh.idModule', '=', 'mdls.idModule')
    //         ->select('menu_refresh.*', 'mdls.moduleName as module_name')
    //         ->where('menu_refresh.idMenuRefresh', $idModule)
    //         ->first();

    //     $module = DB::table('mdls')
    //         ->select('idModule', 'moduleName')
    //         ->where('idModule', $idModule)
    //         ->first();

    //     return view('CFP.refresh.menu', compact('menu', 'module'));
    // }

    public function showModule($idModule)
    {
        $menu = DB::table('menu_refresh')
            ->join('mdls', 'menu_refresh.idModule', '=', 'mdls.idModule')
            ->select('menu_refresh.*', 'mdls.moduleName as module_name')
            ->where('menu_refresh.idMenuRefresh', $idModule)
            ->first();

        $module = DB::table('mdls')
            ->select('idModule', 'moduleName')
            ->where('idModule', $idModule)
            ->first();

        return response()->json([
            'menu' => $menu,
            'module' => $module,
        ]);
    }


    public function getMenusByModule($idModule)
    {
        try {
            $menus = DB::table('menu_refresh')
                ->where('idModule', $idModule)
                ->orderBy('menu', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $menus
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des menus: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getModuleEmp()
    {
        $modules = DB::table('v_projet_emps')
            ->select('idModule', 'module_name', 'module_image', 'project_reference')
            ->where('idEtp', Customer::idCustomer())
            ->where('module_name', '!=', 'Default module')
            ->where('moduleStatut', 1)
            ->distinct()
            ->get();

        return response()->json([
            'success' => true,
            'modules' => $modules
        ]);
    }

    public function getModuleForm()
    {
        try {
            $modules = DB::table('v_projet_form')
                ->select('idModule', 'module_name', 'module_image', 'project_reference')
                ->where('idCfp', Customer::idCustomer())
                ->where('module_name', '!=', 'Default module')
                ->where('moduleStatut', 1)
                ->distinct()
                ->get();

            if ($modules->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun module trouvé.',
                    'modules' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'modules' => $modules
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des modules.',
                'error'   => $e->getMessage(), // 🔒 en prod, tu peux enlever ce détail
                // 'customer' => Customer::idCustomer()
            ], 500);
        }
    }

    public function getModuleFormateur()
    {
        try {
            $modules = DB::table('v_projet_form')
                ->select('idModule', 'module_name', 'module_image', 'project_reference')
                ->where('idFormateur', Auth::user()->id)
                ->where('module_name', '!=', 'Default module')
                ->where('moduleStatut', 1)
                ->distinct()
                ->get();

            if ($modules->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun module trouvé.',
                    'modules' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'modules' => $modules
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des modules.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function   getModuleApprenant()
    {
        try {
            $modules = DB::table('v_projet_form')
                ->select('idModule', 'module_name', 'module_image', 'project_reference')
                ->where('idFormateur', Auth::user()->id)
                ->where('module_name', '!=', 'Default module')
                ->where('moduleStatut', 1)
                ->distinct()
                ->get();

            if ($modules->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun module trouvé.',
                    'modules' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'modules' => $modules
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des modules.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
