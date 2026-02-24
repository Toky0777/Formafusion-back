<?php
namespace App\Services;

use App\Interfaces\ApprenantInterface;
use App\Models\Customer;
use App\Traits\GetQuery;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Traits\StoreQuery;
use Illuminate\Support\Facades\Auth;

class ApprenantService implements ApprenantInterface{
    use StoreQuery, GetQuery;

    // enregistrement via FORMATEUR
    public function storeForm($req, EmployeService $emp): mixed
    {
        try{
            DB::transaction(function() use($req){
                $user = $this->storeUser($req);
                $this->roleUser(4, $user, 1, 1, 1);
                $this->storeFEmp($user, Auth::user()->id);
            });

            return true;
        }catch(Exception $e){
            return false;
        }
    }

public function show($id): object
{
    $apprenant = DB::table('v_apprenant_etp_alls')
        ->select('idEmploye', 'emp_name', 'emp_firstname', 'emp_email', 'emp_phone', 'emp_matricule', 'emp_fonction', 'user_addr_lot', 'user_addr_quartier', 'user_addr_code_postal', 'emp_initial_name', 'emp_photo', 'etp_name', 'idVille', 'ville')
        ->where('idEmploye', $id)
        ->first();

    if (!$apprenant) {
        throw new \Exception("Apprenant introuvable");
    }

    return $apprenant;
}


}