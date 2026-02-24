<?php

namespace App\Traits;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

trait ReferentQuery
{
    public function getReferent($key)
    {
        if (Customer::typeCustomer() == 1) {
            $referents = DB::table('v_employe_alls')
                ->select(
                    'idEmploye',
                    'idCustomer',
                    'role_id',
                    'matricule as ref_matricule',
                    'initialName as ref_initial_name',
                    'name as ref_name',
                    'firstName as ref_firstname',
                    'phone as ref_phone',
                    'email as ref_email',
                    'cin as ref_cin',
                    'adresse as ref_adresse',
                    'sexe as ref_sexe',
                    'fonction as ref_fonction',
                    'photo as ref_photo',
                    'idSexe',
                    'isActive',
                    'hasRole',
                    'phone as ref_phone'
                )
                ->where('idCustomer', Customer::idCustomer())
                ->where(function ($query) use ($key) {
                    $query->where('name', 'like', "%$key%")
                        ->orWhere('firstName', 'like', "%$key%")
                        ->orWhere(DB::raw('CONCAT(name, " ", COALESCE(firstName, ""))'), 'like', "%$key%");
                })
                ->where('role_id', 8)
                ->orderBy('isActive', 'desc')
                ->get();
        } else {
            $referents = DB::table('v_employe_alls')
                ->select('idEmploye', 'idCustomer', 'role_id', 'matricule as ref_matricule', 'initialName as ref_initial_name', 'name as ref_name', 'firstName as ref_firstname', 'phone as ref_phone', 'email as ref_email', 'cin as ref_cin', 'adresse as ref_adresse', 'sexe as ref_sexe', 'fonction as ref_fonction', 'photo as ref_photo', 'idSexe', 'isActive', 'hasRole', 'phone as ref_phone')
                ->where('idCustomer', Customer::idCustomer())
                ->where('role_id', 9)
                ->orderBy('isActive', 'desc')
                ->get();
        }
        return $referents;
    }

    public function getReferentCustomer($key)
    {
        $allEtps = DB::table('v_collaboration_cfp_etps')
            ->where('idCfp', Customer::idCustomer())
            ->orderBy('etp_name', 'ASC')
            ->pluck('idEtp');

        $referents = DB::table('employes as E')
            ->join('users as U', 'U.id', 'E.idEmploye')
            ->join('role_users as RU', 'RU.user_id', 'U.id')
            ->join('customers as C', 'C.idCustomer', 'E.idCustomer')
            ->select('U.name', 'U.firstName', 'U.email', 'U.phone', 'U.matricule', 'U.photo', 'U.id', 'C.customerName')
            ->whereIn('E.idCustomer', $allEtps)
            ->whereIn('RU.role_id', [6, 9])
            ->where(function ($query) use ($key) {
                $query->where('U.name', 'like', "%$key%")
                    ->orWhere('U.firstName', 'like', "%$key%")
                    ->orWhere(DB::raw('CONCAT(U.name, " ", COALESCE(U.firstName, ""))'), 'like', "%$key%");
            })
            ->get();

        return $referents;
    }
}