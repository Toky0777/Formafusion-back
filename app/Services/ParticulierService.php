<?php

namespace App\Services;

use App\Interfaces\InvitationInterface;
use App\Interfaces\UserRegisterInterface;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ParticulierService implements UserRegisterInterface, InvitationInterface
{
    public function register($req): mixed
    {
        try {
            DB::beginTransaction();
            $particulier = new User();
            $particulier->name = $req->part_name;
            $particulier->firstName = $req->part_firstname;
            $particulier->email = $req->customer_email;
            $particulier->cin = $req->part_cin;
            $particulier->password = Hash::make($req->password);
            $particulier->save();

            DB::table('particuliers')->insert([
                'idParticulier' => $particulier->id
            ]);

            DB::table('role_users')->insert([
                'role_id'  => 10,
                'user_id'  => $particulier->id,
                'isActive' => 1,
                'hasRole' => 1,
                'user_is_in_service' => 1
            ]);

            DB::commit();

            return $particulier;
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // invitation d'un Particulier par une tièrces personne
    public function inviteCustomer($req): bool
    {
        return true;
    }

    // ajout + invitation d'un Particulier par une tièrce personne
    public function inviteNewCustomer($req): mixed
    {
        return true;
    }

    public function getCustomerName($name): array
    {
        $customers = DB::table('particuliers as p')
            ->select('p.idParticulier as idCustomer', DB::raw('3 AS idTypeCustomer'), 'name AS customer_name', 'email AS customer_email', DB::raw('"Particulier" AS customer_type'), DB::raw('NULL AS customer_nif'), 'user_addr_lot as customer_addr_lot', DB::raw("'Particulier' as type_customer_desc"), DB::raw("NULL as idTypeEtp"))
            ->join('users as u', 'p.idParticulier', 'u.id')
            ->leftJoin('cfp_particuliers AS cp', 'cp.idParticulier', 'p.idParticulier')
            // ->whereNull('cp.is_sent')
            ->where('name', 'like', $name . '%')
            ->groupBy('u.id', 'name', 'email')
            ->get();

        return $customers->toArray();
    }



    // récupération de toutes les PARTICULIERS en collaboration avec un CFP(Clients dans CFP)
    public function getAll($idCfp): mixed
    {
        $particuliers = DB::table('v_collaboration_cfp_particuliers')
            ->select('idParticulier', 'part_initial_name', 'part_name', 'part_firstname', 'part_photo', 'part_phone', 'part_email', 'idCfp', 'date_collaboration')
            ->where('idCfp', $idCfp)
            ->orderBy('part_name', 'asc')
            ->get();

        return $particuliers;
    }

    public function getParticularByKey($cfpId, $key)
    {
        return DB::table('cfp_particuliers as P')
            ->select('U.name', 'U.firstName', 'U.photo', 'U.phone', 'U.email')
            ->join('users as U', 'U.id', 'P.idParticulier')
            ->where('P.idCfp', $cfpId)
            ->where(function ($query) use ($key) {
                $query->where('U.name', 'like', "%$key%")
                    ->orWhere('U.firstName', 'like', "%$key%")
                    ->orWhere(DB::raw('CONCAT(U.name, " ", COALESCE(U.firstName, ""))'), 'like', "%$key%");
            });
    }
}
