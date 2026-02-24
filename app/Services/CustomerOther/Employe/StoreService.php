<?php

namespace App\Services\CustomerOther\Employe;

use App\Models\Customer;
use App\Models\Employe;
use App\Models\RoleUser;
use App\Models\User;
use App\Interfaces\CustomerOther\EmployeInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StoreService implements EmployeInterface
{
    public function store($req): void
    {
        DB::transaction(function () use ($req) {
            $user = new User();
            $user->matricule = $req->emp_matricule;
            $user->name = $req->emp_name;
            $user->firstName = $req->emp_firstname;
            $user->email = $req->email;
            $user->phone = $req->emp_phone;
            $user->password = Hash::make('0000@#');
            $user->save();

            $this->storeEmploye($req, $user->id);
            $this->storeRoleUser($user->id);
        });
    }

    public function storeEmploye($req, $idUser)
    {
        $emp = new Employe();
        $emp->idEmploye = $idUser;
        $emp->idSexe = 1;
        $emp->idNiveau = 6;
        $emp->idCustomer = Customer::idCustomer();
        $emp->idFonction = $this->getFonction();
        $emp->save();
    }

    public function getFonction()
    {
        $fonction = DB::table('fonctions')->select('idFonction')->where('idCustomer', Customer::idCustomer())->first();

        return $fonction->idFonction;
    }

    public function storeRoleUser($idUser)
    {
        RoleUser::insert([
            'role_id'  => 4,
            'user_id'  => $idUser,
            'isActive' => 1,
            'hasRole' => 1
        ]);
    }

    public function getAll(): mixed
    {
        $employes = DB::table('v_employe_alls')
            ->select('*')
            ->where('idCustomer', Customer::idCustomer())
            ->where('role_id', 4)
            ->orderBy('name', 'asc')
            ->get();

        if(count($employes) <= 0){
            return null;
        }

        return $employes;
    }
}
