<?php

namespace App\Traits;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

trait TrainerQuery
{
    public function getTrainer($key)
    {
        if (Customer::typeCustomer() == 1) {
            $trainers = DB::table('v_formateur_cfps')
                ->select('idFormateur', 'idCfp', 'isActiveFormateur AS form_is_active', 'isActiveCfp AS cfp_is_active', 'initialNameForm AS form_initial_name', 'photoForm AS form_photo', 'name AS form_name', 'firstName AS form_firstname', 'email AS form_email', 'isActive AS user_is_active', 'form_phone')
                ->where('idCfp', Customer::idCustomer())
                ->where(function ($query) use ($key) {
                    $query->where('name', 'like', "%$key%")
                        ->orWhere('firstName', 'like', "%$key%")
                        ->orWhere(DB::raw('CONCAT(name, " ", COALESCE(firstName, ""))'), 'like', "%$key%");
                })
                ->groupBy('idFormateur', 'idCfp', 'isActiveFormateur', 'isActiveCfp', 'initialNameForm', 'photoForm', 'name', 'firstName', 'email', 'isActive', 'form_phone')
                ->orderBy('isActive', 'desc')
                ->get();
        } else {
            $trainers = DB::table('formateur_internes')
                ->join('forms', 'formateur_internes.idFormateur', 'forms.idFormateur')
                ->join('employes', 'formateur_internes.idEmploye', 'employes.idEmploye')
                ->join('sexes', 'employes.idSexe', 'sexes.idSexe')
                ->join('type_formateurs', 'forms.idTypeFormateur', 'type_formateurs.idTypeFormateur')
                ->join('users', 'forms.idFormateur', 'users.id')
                ->where('formateur_internes.idEntreprise', '=', Customer::idCustomer())
                ->get();
        }

        return $trainers;
    }

    public function getTrainerByProject($idProjet){
        $learners = DB::table('v_formateur_cfps')
                        ->select('photoForm', 'idFormateur', 'initialNameForm')
                        ->where('idProjet', $idProjet)
                        ->get();
        return $learners;
    }
}