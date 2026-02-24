<?php

namespace App\Traits;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

trait CustomerQuery
{
    public function getCustomerByProject($idProjet){
        $idEtp = DB::table('v_projet_cfps')
                        ->where('idProjet', $idProjet)
                        ->pluck('idEtp');
        
        $customers = DB::table('customers')
                        ->select('idCustomer', 'logo', 'customerName')
                        ->whereIn('idCustomer', $idEtp)
                        ->get();

        return $customers;
    }
}