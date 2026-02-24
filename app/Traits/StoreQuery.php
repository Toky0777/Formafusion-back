<?php

namespace App\Traits;

use App\Models\Employe;
use App\Models\RoleUser;
use App\Models\Salle;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

trait StoreQuery
{
    public function storeUser($req)
    {
        try {
            $user = new User();
            $user->matricule = $req->emp_matricule;
            $user->name = $req->emp_name;
            $user->firstName = $req->emp_firstname;
            $user->email = $req->emp_email;
            $user->phone = $req->emp_phone;
            $user->password = Hash::make('0000@#');
            $user->save();

            return $user->id;
        } catch (Exception $e) {
            return null;
        }
    }

    // store "employes" hampidirin'ilay CFP
    public function storeCEmp($idUser, $idCfp): void
    {
        DB::table('c_emps')->insert([
            'idEmploye' => $idUser,
            'id_cfp' => $idCfp
        ]);
    }



    // store "employes" hampidirin'ilay FORMATEIUR
    public function storeFEmp($idUser, $idFormateur): void
    {
        DB::table('f_emps')->insert([
            'idEmploye' => $idUser,
            'date_ajout' => Carbon::now(),
            'id_formateur' => $idFormateur,
        ]);
    }

    public function storeLearner($idUser): void
    {
        DB::table('apprenants')->insert([
            'idEmploye' => $idUser
        ]);
    }

    // store lieux et salles pour cfp et etp
    public function storeSalle($idCustomer): void
    {
        DB::transaction(function () use ($idCustomer) {
            $idLieu = DB::table('lieux')->insertGetId([
                'li_name' => "Default",
                'idVille' => 1,
                'idLieuType' => 2,
                'idVilleCoded' => 1
            ]);

            DB::table('lieu_privates')->insert([
                'idLieu' => $idLieu,
                'idCustomer' => $idCustomer
            ]);

            Salle::insert([
                'salle_name' => 'In situ',
                'idLieu' => $idLieu
            ]);
        });
    }

    public function roleUser($roleId, $userId, $isActive, $hasRole, $userIsInService)
    {
        RoleUser::insert([
            'role_id'  => $roleId,
            'user_id'  => $userId,
            'isActive' => $isActive,
            'hasRole' => $hasRole,
            'user_is_in_service' => $userIsInService
        ]);
    }

    public function mdls($name, $idDomaine, $idCustomer, $idType)
    {
        $id = DB::table('mdls')->insertGetId([
            'moduleName' => $name,
            'idDomaine' => $idDomaine,
            'idCustomer' => $idCustomer,
            'idTypeModule' => $idType
        ]);

        return $id;
    }

    public function module($idModule)
    {
        DB::table('modules')->insert(['idModule' => $idModule]);
    }

    public function moduleInterne($idModule)
    {
        DB::table('module_internes')->insert(['idModule' => $idModule]);
    }

    public function fonctions($name, $idCustomer)
    {
        $id = DB::table('fonctions')->insertGetId([
            'fonction' => $name,
            'idCustomer' => $idCustomer
        ]);

        return $id;
    }

    public function cfp($idCustomer)
    {
        DB::table('cfps')->insert([
            'idCustomer' => $idCustomer
        ]);
    }

    public function particulier($idParticulier)
    {
        DB::table('particuliers')->insert([
            'idParticulier' => $idParticulier
        ]);
    }

    public function entreprise($idEtp, $idTypeEtp)
    {
        DB::table('entreprises')->insert([
            'idCustomer' => $idEtp,
            'idTypeEtp' => $idTypeEtp
        ]);
    }

    public function etpPrivate($idEtp)
    {
        DB::table('etp_singles')->insert(['idEntreprise' => $idEtp]);
    }

    public function etpGroupe($idEtp)
    {
        DB::table('etp_groupes')->insert(['idEntreprise' => $idEtp]);
    }

    public function cfpEtp($idEtp, $idCfp, $isActiveEtp, $isActiveCfp)
    {
        DB::table('cfp_etps')->insert([
            'idEtp' => $idEtp,
            'idCfp' => $idCfp,
            'dateCollaboration' => Carbon::now(),
            'activiteEtp' => $isActiveEtp,
            'activiteCfp' => $isActiveCfp,
            'isSent' => 1
        ]);
    }

    public function cfpParticulier($idCfp, $idParticulier, $isActiveCfp)
    {
        DB::table('cfp_particuliers')->insert([
            'idCfp' => $idCfp,
            'idParticulier' => $idParticulier,
            'is_sent' => 1,
            'is_active_cfp' => $isActiveCfp,
            'is_active_particulier' => 0,
            'date_collaboration' => Carbon::now()
        ]);
    }
}
