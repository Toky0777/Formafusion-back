<?php

namespace App\Services;

use App\Http\Requests\CfpRegisterRequest;
use App\Interfaces\InvitationInterface;
use App\Interfaces\LieuInterface;
use App\Interfaces\UserRegisterInterface;
use App\Models\Customer;
use App\Models\Employe;
use App\Models\RoleUser;
use App\Models\Salle;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravelcm\Subscriptions\Models\Plan;

class CfpService implements UserRegisterInterface, LieuInterface, InvitationInterface
{
    public function register($req): mixed
    {
        try {
            DB::beginTransaction();

            $user = new User();
            $user->name = $req->referent_name;
            $user->email = $req->customer_email;
            $password = Hash::make($req->password);
            $user->password = $password;
            $user->save();

            $cst = new Customer();
            $cst->idCustomer = $user->id;
            $cst->customerName = $req->customer_name;
            $cst->customerEmail = $req->customer_email;
            $cst->idSecteur = 7;
            $cst->idTypeCustomer = 1;
            $cst->idVilleCoded = 1;
            $cst->save();

            DB::table('mdls')->insert([
                'moduleName' => "Default module",
                'idDomaine' => 1,
                'idCustomer' => $user->id,
                'idTypeModule' => 1
            ]);

            $this->store($user->id);

            $idFonction = DB::table('fonctions')->insertGetId([
                'fonction' => "default_fonction",
                'idCustomer' => $user->id
            ]);

            $mdl = DB::table('mdls')->select('idModule')->orderBy('idModule', 'desc')->first();

            DB::table('modules')->insert([
                'idModule' => $mdl->idModule
            ]);

            $customer = DB::table('customers')->select('idCustomer')->orderBy('idCustomer', 'desc')->first();

            DB::table('cfps')->insert([
                'idCustomer' => $customer->idCustomer
            ]);

            $usr = DB::table('users')->select('id')->orderBy('id', 'desc')->first();
            $sexe = DB::table('sexes')->select('idSexe')->get();

            $emp = new Employe();
            $emp->idEmploye = $user->id;
            $emp->idNiveau = 6;
            $emp->idCustomer = $customer->idCustomer;
            $emp->idSexe = $sexe[0]->idSexe;
            $emp->idFonction = $idFonction;
            $emp->save();

            RoleUser::insert([
                'role_id'  => 3,
                'user_id'  => $usr->id,
                'isActive' => 1,
                'hasRole' => 1
            ]);
            //Abonnement
            $plan = Plan::find(1);
            $user->newPlanSubscription('main', $plan);

            DB::commit();
            return $user;
        } catch (Exception $e) {
            return back()->with("error", "Une erreur s'est produite, veuillez réessayer plus tard !" . $e->getMessage());
        }
    }

    public function store($idCustomer): void
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

    // invitation d'une CFP par une tièrce personne
    public function inviteCustomer($req): bool
    {
        return true;
    }

    // ajout + invitation d'un CFP par une tièrce personne
    public function inviteNewCustomer($req): bool
    {
        return true;
    }

    public function getCustomerName($name): array
    {
        $customers = DB::table('customers AS c')
            ->select('c.idCustomer', 'c.idTypeCustomer', 'c.customerName AS customer_name', 'c.customerEmail AS customer_email', 'typeCustomer as customer_type', 'nif as customer_nif', 'customer_addr_lot', DB::raw("'Centre de formation' as type_customer_desc"), DB::raw("NULL as idTypeEtp"))
            ->join('type_customers as tc', 'c.idTypeCustomer', 'tc.idTypeCustomer')
            ->leftJoin('cfp_etps', 'cfp_etps.idCfp', 'c.idCustomer')
            ->where('c.idTypeCustomer', 1)
            // ->whereNull('cfp_etps.isSent')
            ->where('c.customerName', 'like', $name . '%')
            ->groupBy('c.idCustomer', 'c.idTypeCustomer', 'c.customerName', 'c.customerEmail', 'typeCustomer', 'nif', 'customer_addr_lot')
            ->get();

        return $customers->toArray();
    }
}
