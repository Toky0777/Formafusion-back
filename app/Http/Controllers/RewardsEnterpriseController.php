<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RewardsEnterpriseController extends Controller
{
    public function index()
    {
        $results = DB::table('v_reward_results')
            ->select('id_module_reward', 'reward_name', 'start_date', 'expired_date', 'reward_description', 'reward_place_number', 'reward_reduction', 'normal_price_per_place', 'price_reduction', 'price_with_reduction', 'idCfp', 'cfp_name', 'cfp_email', 'cfp_logo', 'cfp_phone_number', 'id_reward_scope', 'reward_scope_name', 'id_reward_type', 'reward_type_name', 'cfp_referent_name', 'cfp_referent_firstname', 'cfp_referent_photo')
            ->where('idEtp', Customer::idCustomer())
            ->where('expired_date', '>=', Carbon::now())
            ->orderBy('expired_date', 'asc')
            ->get();

        if (count($results) <= 0) {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }

        $rewards = [];

        foreach ($results as $result) {
            $rewards[] = [
                'id_module_reward' => $result->id_module_reward,
                'reward_name' => $result->reward_name,
                'expired_date' => $result->expired_date,
                'start_date' => $result->start_date,
                'reward_description' => $result->reward_description,
                'reward_place_number' => $result->reward_place_number,
                'reward_reduction' => $result->reward_reduction,
                'normal_price_per_place' => $result->normal_price_per_place,
                'price_reduction' => $result->price_reduction,
                'price_with_reduction' => $result->price_with_reduction,
                'idCfp' => $result->idCfp,
                'cfp_name' => $result->cfp_name,
                'cfp_email' => $result->cfp_email,
                'cfp_logo' => $result->cfp_logo,
                'cfp_phone_number' => $result->cfp_phone_number,
                'id_reward_scope' => $result->id_reward_scope,
                'reward_scope_name' => $result->reward_scope_name,
                'id_reward_type' => $result->id_reward_type,
                'reward_type_name' => $result->reward_type_name,
                'cfp_referent_name' => $result->cfp_referent_name,
                'cfp_referent_firstname' => $result->cfp_referent_firstname,
                'cfp_referent_photo' => $result->cfp_referent_photo,
                'reward_catalogue_contents' => $this->getRewardsCatalogueContents($result->id_module_reward)
            ];
        }

        return response()->json([
            'status' => 200,
            'rewards' => [
                'reward_count' => count($results),
                'reward_items' => $rewards
            ]
        ], 200);
    }

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

    public function countRewardByEntreprise()
    {
        $total = DB::table('v_reward_results')
            ->select('id_module_reward')
            ->where('idEtp', Customer::idCustomer())
            ->where('expired_date', '>=', Carbon::now())
            ->orderBy('expired_date', 'asc')
            ->count();

        return response()->json($total, 200);
    }
}
