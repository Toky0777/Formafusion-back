<?php

namespace App\Traits;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

trait EmployeQuery
{
    public function getEmploye($key){
        $checkEtpGrp = DB::table('etp_groupes')->where('idEntreprise', Customer::idCustomer())->exists();

        return $checkEtpGrp ? $this->getEmployeEtpGrouped($key) : $this->getEmployeEtpSingle($key);
    }

    public function getEmployeEtpGrouped($key){
        $employes = DB::table('v_union_emp_grps')
                ->select('idEmploye', 'idEntreprise as idEtp', 'idEntrepriseParent', 'etp_name_parent', 'etp_name', 'emp_name', 'emp_firstname', 'emp_email', 'emp_matricule', 'emp_phone', 'emp_photo', 'emp_cin', 'emp_sexe', 'emp_is_active', 'emp_has_role', 'user_is_in_service')
                ->where('idEntrepriseParent', Customer::idCustomer())
                ->where(function ($query) use($key){
                    $query->where('emp_name', 'like', "%$key%")
                          ->orWhere('emp_firstname', 'like', "%$key%");
                })
                ->orderBy('emp_name', 'asc')
                ->get();

        return $employes;
    }

    public function getEmployeEtpSingle($key){
        $employes = DB::table('v_employe_alls')
                ->select('idEmploye', 'idCustomer as idEtp', 'customerName as etp_name', 'role_id', 'matricule as emp_matricule', 'initialName as emp_initial_name', 'user_is_in_service', 'name as emp_name', 'firstName as emp_firstname', 'phone as emp_phone', 'email as emp_email', 'cin as emp_cin', 'adresse', 'sexe', 'photo as emp_photo')
                ->where('idCustomer', Customer::idCustomer())
                ->where('role_id', 4)
                ->where(function ($query) use($key){
                    $query->where('name', 'like', "%$key%")
                          ->orWhere('firstname', 'like', "%$key%");
                })
                ->orderBy('idEmploye', 'desc')
                ->get();

        return $employes;
    }
}