<?php
namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait UpdateQuery{
    public function updateOpportunity($idProspect, $idEtp){
        DB::table('opportunites')
            ->where('id_prospect', $idProspect)
            ->update([
                'id_prospect' => null,
                'idEtp' => $idEtp,
            ]);
    }

    public function updateRoleUser($idUser, $isActive, $userIsInService){
        DB::table('role_users')
            ->where('user_id', $idUser)
            ->where('hasRole', 1)
            ->update([
                'isActive' => $isActive,
                'user_is_in_service' => $userIsInService
            ]);
    }
}