<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function authenticate(string $email, string $password): ?User
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function getCountrySettings(): ?object
    {
        return DB::table('countriess as c')
            ->select(
                'c.code as country_code',
                'n.description as nif_description',
                'n.id as id_nif_name',
                'cu.unit as currency_unit',
                'c.name as country_name',
                'n.name as nif_name',
                'cu.symbol as currency_code',
                'sn.id as id_stat_name',
                'sn.name as stat_name'
            )
            ->join('currencies as cu', 'c.id_currency', '=', 'cu.id')
            ->join('nif_names as n', 'c.id_nif_name', '=', 'n.id')
            ->join('country_fulls as cf', 'cf.id', 'c.id')
            ->join('stat_names as sn', 'cf.id_stat_name', 'sn.id')
            ->first();
    }
}
