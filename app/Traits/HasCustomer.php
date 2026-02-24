<?php
namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasCustomer{
    public function defaultModule($idCustomer): object{
        $query = DB::table('mdls')->select('idModule')->where('idCustomer', $idCustomer)->first();

        return $query;
    }

    public function publicSalle(): object{
        $query = DB::table('v_list_salles')->select('idSalle')->where('idLieuType', 1)->first();

        return $query;
    }
}