<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RewardsController extends Controller
{
    public function getRewardsCatalogueContents($idModuleReward)
    {
        $results = DB::table('reward_catalogue_contents as rcc')
            ->select('rcc.id', 'rcc.id_module_reward', 'rcc.idModule', 'mdls.moduleName as module_name', 'mdls.description as module_description')
            ->join('mdls', 'rcc.idModule', 'mdls.idModule')
            ->where('id_module_reward', $idModuleReward)
            ->orderBy('moduleName', 'asc')
            ->get();

        if (count($results) <= 0) {
            return 0;
        }

        return $results;
    }


    public function index()
    {
        $enterprises = DB::table('cfp_etps as ce')
            ->join('customers as cst', 'ce.idEtp', 'cst.idCustomer')
            ->select('ce.idEtp', 'cst.customerName as etp_name', 'cst.customerEmail as etp_email', 'cst.logo as etp_logo')
            ->where('ce.idCfp', Customer::idCustomer())
            ->orderBy('cst.customerName', 'asc')
            ->get();

        $modules = DB::table('mdls')
            ->select('idModule', 'moduleName as module_name', 'description as module_description', 'module_image')
            ->where('moduleName', '!=', 'Default module')
            ->where('idCustomer', Customer::idCustomer())
            ->orderBy('moduleName', 'asc')
            ->get();

        $rewardTypes = DB::table('reward_types')->select('id', 'name')->get();
        $rewardScopes = DB::table('reward_scopes')->select('id', 'name', 'description')->get();

        if (count($enterprises) <= 0) {
            return response()->json([
                'status' => 404,
                'message' => 'Aucun résultat !'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'reward_types' => $rewardTypes,
            'reward_scopes' => $rewardScopes,
            'catalogues' => [
                'module_count' => count($modules),
                'modules' => $modules
            ],
            'etps' => [
                'entreprise_count' => count($enterprises),
                'entreprises' => $enterprises
            ]
        ], 200);
    }

    public function getAllRewardsCfp()
    {
        $allrewards = DB::table('v_reward_results_cfp')
            ->select('id_module_reward', 'reward_name', 'start_date', 'expired_date', 'reward_description', 'reward_place_number', 'reward_reduction', 'normal_price_per_place', 'price_reduction', 'price_with_reduction', 'id_reward_scope', 'etp_name', 'reward_scope_name', 'reward_scope_description', 'id_reward_type', 'reward_type_name')
            ->where('idCfp', Customer::idCustomer())
            ->where('expired_date', '>=', Carbon::now())
            ->orderBy('expired_date', 'asc')
            ->get();

        if (count($allrewards) <= 0) {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }

        $rewards = [];

        foreach ($allrewards as $reward) {
            $rewards[] = [
                'id_module_reward' => $reward->id_module_reward,
                'reward_name' => $reward->reward_name,
                'expired_date' => $reward->expired_date,
                'start_date' => $reward->start_date,
                'etp_name' => $reward->etp_name,
                'reward_description' => $reward->reward_description,
                'reward_place_number' => $reward->reward_place_number,
                'reward_reduction' => $reward->reward_reduction,
                'normal_price_per_place' => $reward->normal_price_per_place,
                'price_reduction' => $reward->price_reduction,
                'price_with_reduction' => $reward->price_with_reduction,
                'id_reward_scope' => $reward->id_reward_scope,
                'reward_scope_description' => $reward->reward_scope_description,
                'id_reward_type' => $reward->id_reward_type,
                'reward_type_name' => $reward->reward_type_name,
                'reward_catalogue_contents' => $this->getRewardsCatalogueContents($reward->id_module_reward)
            ];
        }

        return response()->json([
            'status' => 200,
            'rewards' => [
                'reward_count' => count($rewards),
                'reward_items' => $rewards
            ]
        ], 200);
    }

    public function store(Request $req)
    {
        $req->validate([
            'reward_name' => 'required|min:2|max:250',
            'reward_type' => 'required|exists:reward_types,id',
            'idEntreprise' => 'required|exists:customers,idCustomer',
            'place_number' => 'required|numeric',
            'expired_date' => 'required|date',
            'start_date' => 'required|date',
            'normal_price_per_place' => 'required|numeric',
            'description' => 'required|min:2',
            'reward_scope' => 'required|exists:reward_scopes,id'
        ]);

        // Rewards avec réduction
        if ($req->reward_type == 2) {
            $req->validate(['reward_percentage' => 'required|numeric']);

            $reduction = $req->reward_percentage;
        } elseif ($req->reward_type == 1) {
            $reduction = 0;
        }

        try {
            DB::transaction(function () use ($req, $reduction) {
                $idModuleReward = DB::table('module_rewards')->insertGetId([
                    'name' => $req->reward_name,
                    'expired_date' => $req->expired_date,
                    'start_date' => $req->start_date,
                    'description' => $req->description,
                    'place_number' => $req->place_number,
                    'reduction' => $reduction,
                    'normal_price_per_place' => $req->normal_price_per_place,
                    'idCfp' => Customer::idCustomer(),
                    'idEtp' => $req->idEntreprise,
                    'id_reward_scope' => $req->reward_scope,
                    'id_reward_type' => $req->reward_type
                ]);

                if ($req->reward_scope == 1) {
                    DB::table('module_reward_fulls')->insert([
                        'id_module_reward' => $idModuleReward
                    ]);
                } elseif ($req->reward_scope == 2) {
                    DB::table('module_reward_less')->insert([
                        'id_module_reward' => $idModuleReward
                    ]);
                    $req->validate(['idModule' => 'required']);

                    if (count($req->idModule) > 0) {

                        for ($i = 0; $i < count($req->idModule); $i++) {
                            DB::table('reward_catalogue_contents')->insert([
                                'id_module_reward' => $idModuleReward,
                                'idModule' => $req->idModule[$i]
                            ]);
                        }
                    }
                }
            });


            return response()->json([
                'status' => 200,
                'message' => 'Ajout avec succès'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $reward = DB::table('v_reward_results_cfp')
            ->select('id_module_reward', 'reward_name', 'start_date', 'expired_date', 'reward_description', 'reward_place_number', 'reward_reduction', 'normal_price_per_place', 'price_reduction', 'price_with_reduction', 'id_reward_scope', 'etp_name', 'reward_scope_name', 'reward_scope_description', 'id_reward_type', 'reward_type_name')
            ->where('idCfp', Customer::idCustomer())
            ->where('expired_date', '>=', Carbon::now())
            ->where('id_module_reward', $id)
            ->orderBy('expired_date', 'asc')
            ->first();
        return response()->json([
            'status' => 200,
            'reward' => $reward
        ], 200);
    }


    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                DB::table('reward_catalogue_contents')->where('id_module_reward', $id)->delete();
                DB::table('module_reward_fulls')->where('id_module_reward', $id)->delete();
                DB::table('module_reward_less')->where('id_module_reward', $id)->delete();
                $deleted = DB::table('module_rewards')->where('id', $id)->delete();
                if ($deleted === 0) {
                    return response()->json([
                        'status' => 404,
                        'message' => "Introuvable"
                    ], 404);
                }
            });
            return response()->json([
                'status' => 200,
                'message' => 'Supprimé avec succès'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
