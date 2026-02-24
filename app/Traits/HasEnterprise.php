<?php
namespace App\Traits;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

trait HasEnterprise{
    public function isCollaboratedIntra($idEtp): bool{
        $query = DB::table('intras')->where('idEtp', $idEtp);

        if($query->exists()){
            return true;
        }else{
            return false;
        }
    }

    public function isCollaboratedInter($idEtp): bool{
        $query = DB::table('inter_entreprises')->where('idEtp', $idEtp);

        if($query->exists()){
            return true;
        }else{
            return false;
        }
    }

    // check customer invited
    public function isCustomerInvited($idCustomer, $idTypeCustomer)
    {
        switch ($idTypeCustomer) {
            case 1:
                $check = DB::table('cfp_etps')
                    ->select('idCfp', 'isSent')
                    ->where('idEtp', Customer::idCustomer())
                    ->where('idCfp', $idCustomer)
                    ->first();
                break;
            case 2:
                $check = DB::table('cfp_etps')
                    ->select('idEtp', 'isSent')
                    ->where('idCfp', Customer::idCustomer())
                    ->where('idEtp', $idCustomer)
                    ->first();
                break;
            case 3:
                $check = DB::table('cfp_particuliers')
                    ->select('idParticulier', 'is_sent')
                    ->where('idCfp', Customer::idCustomer())
                    ->where('idParticulier', $idCustomer)
                    ->first();
                break;
            default:
                $check = null;
        }

        if ($check)
            return true;
        else
            return false;
    }
}