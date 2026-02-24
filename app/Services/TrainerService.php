<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class TrainerService
{
    // public function getTrainer($key, $idCustomer)
    // {
    //     $typeCusstomer = $this->customerService->getTypeCustomer($idCustomer);
    //     if ($typeCusstomer == 1) {
    //         $trainers = DB::table('v_formateur_cfps')
    //             ->select('idFormateur', 'idCfp', 'isActiveFormateur AS form_is_active', 'isActiveCfp AS cfp_is_active', 'initialNameForm AS form_initial_name', 'photoForm AS form_photo', 'name AS form_name', 'firstName AS form_firstname', 'email AS form_email', 'isActive AS user_is_active', 'form_phone')
    //             ->where('idCfp', $idCustomer)
    //             ->where(function ($query) use ($key) {
    //                 $query->where('name', 'like', "%$key%")
    //                     ->orWhere('firstName', 'like', "%$key%")
    //                     ->orWhere(DB::raw('CONCAT(name, " ", COALESCE(firstName, ""))'), 'like', "%$key%");
    //             })
    //             ->groupBy('idFormateur', 'idCfp', 'isActiveFormateur', 'isActiveCfp', 'initialNameForm', 'photoForm', 'name', 'firstName', 'email', 'isActive', 'form_phone')
    //             ->orderBy('isActive', 'desc')
    //             ->get();
    //     } else if ($typeCusstomer == 2) {
    //         $trainers = DB::table('formateur_internes')
    //             ->join('forms', 'formateur_internes.idFormateur', 'forms.idFormateur')
    //             ->join('employes', 'formateur_internes.idEmploye', 'employes.idEmploye')
    //             ->join('sexes', 'employes.idSexe', 'sexes.idSexe')
    //             ->join('type_formateurs', 'forms.idTypeFormateur', 'type_formateurs.idTypeFormateur')
    //             ->join('users', 'forms.idFormateur', 'users.id')
    //             ->where('formateur_internes.idEntreprise', '=', $idCustomer)
    //             ->get();
    //     }

    //     return $trainers;
    // }
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function countTrainerCfp($key, $idCustomer)
    {
        return DB::table('cfp_formateurs as CF')
            ->select('U.name', 'U.firstName', 'U.photo')
            ->join('users as U', 'CF.idFormateur', 'U.id')
            ->where('CF.idCfp', '=', $idCustomer)
            ->where(function ($query) use ($key) {
                $query->where('U.name', 'like', "%$key%")
                    ->orWhere('U.firstName', 'like', "%$key%");
            })
            ->count();
    }

    public function getTrainerCfp($key, $idCustomer)
    {
        return DB::table('cfp_formateurs as CF')
            ->select('U.name', 'U.firstName', 'U.photo', 'U.email', 'U.phone')
            ->join('users as U', 'CF.idFormateur', 'U.id')
            ->where('CF.idCfp', '=', $idCustomer)
            ->where(function ($query) use ($key) {
                $query->where('U.name', 'like', "%$key%")
                    ->orWhere('U.firstName', 'like', "%$key%");
            })
            ->get();
    }
}
