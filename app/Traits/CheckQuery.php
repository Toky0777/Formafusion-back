<?php
namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait CheckQuery{
    public function checkProspect($idCustomer, $customerName){
        $check = DB::table('prospects')
                ->select('prospect_name', 'id')
                ->where('idCustomer', $idCustomer)
                ->where('prospect_name', $customerName)
                ->first();

        return $check;
    }
        public function checkRoleUser($id)
    {
        $role = DB::table('role_users')->select('role_id')->where('user_id', $id)->first();

        return $role;
    }
}