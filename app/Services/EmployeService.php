<?php

namespace App\Services;

use App\Interfaces\EmployeInterface;
use App\Models\Customer;
use App\Models\Employe;
use App\Traits\GetQuery;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Traits\StoreQuery;
use Illuminate\Support\Facades\Auth;

class EmployeService implements EmployeInterface
{
    use StoreQuery, GetQuery;

    public function store($id, $idNiveau, $idCustomer, $idSexe, $idFonction): void
    {
        $emp = new Employe();
        $emp->idEmploye = $id;
        $emp->idNiveau = $idNiveau;
        $emp->idCustomer = $idCustomer;
        $emp->idSexe = $idSexe;
        $emp->idFonction = $idFonction;
        $emp->save();
    }

    public function countEmployee($key): int
    {
        $checkEtpGrp = DB::table('etp_groupes')->where('idEntreprise', Customer::idCustomer())->exists();

        if ($checkEtpGrp) {
            return DB::table('v_union_emp_grps')
                ->select('idEmploye')
                ->where('idEntrepriseParent', Customer::idCustomer())
                ->where(function ($query) use ($key) {
                    $query->where('emp_name', 'LIKE', "%$key%")
                        ->orWhere('emp_firstname', 'LIKE', "%$key%");
                })
                ->count();
        } else {
            return DB::table('v_employe_alls')
                ->select('idEmploye')
                ->where(function ($query) use ($key) {
                    $query->where('name', 'LIKE', "%$key%")
                        ->orWhere('firstName', 'LIKE', "%$key%");
                })
                ->where('idCustomer', Customer::idCustomer())
                ->where('role_id', 4)
                ->count();
        }
    }

    public function getEmployee($key)
    {
        $checkEtpGrp = DB::table('etp_groupes')->where('idEntreprise', Customer::idCustomer())->exists();

        if ($checkEtpGrp) {
            return DB::table('v_union_emp_grps')
                ->select('idEmploye', 'idEntreprise as idEtp', 'emp_matricule', 'emp_name', 'emp_firstname', 'emp_email', 'emp_phone', 'emp_fonction', 'user_is_in_service')
                ->where('idEntrepriseParent', Customer::idCustomer())
                ->where(function ($query) use ($key) {
                    $query->where('emp_name', 'LIKE', "%$key%")
                        ->orWhere('emp_firstname', 'LIKE', "%$key%");
                })
                ->get();
        } else {
            return DB::table('v_employe_alls')
                ->select('idEmploye', 'idCustomer as idEtp', 'matricule', 'name', 'firstname', 'email', 'phone', 'fonction', 'user_is_in_service')
                ->where(function ($query) use ($key) {
                    $query->where('name', 'LIKE', "%$key%")
                        ->orWhere('firstName', 'LIKE', "%$key%");
                })
                ->where('idCustomer', Customer::idCustomer())
                ->where('role_id', 4)
                ->get();
        }
    }
}
