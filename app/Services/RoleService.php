<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RoleService
{
    public function getRoleId(int $userId): ?int
    {
        return DB::table('role_users')
            ->where('user_id', $userId)
            ->where('hasRole', 1)
            ->value('role_id');
    }

    public function getUserType(int $userId): ?string
    {
        $role = DB::table('role_users')
            ->select('role_id')
            ->where('user_id', $userId)
            ->where('hasRole', 1)
            ->whereIn('role_id', [3, 6, 8, 9])
            ->first();

        if ($role) {
            return match ($role->role_id) {
                3 => 'superAdminCfp',
                8 => 'adminCfp',
                6 => 'superAdminEtp',
                9 => 'adminEtp',
                default => null
            };
        }

        // Formateur
        if (DB::table('role_users')->where('user_id', $userId)->where('role_id', 5)->exists()) {
            return 'formateur';
        }

        // Particulier ou apprenant
        if (
            DB::table('particuliers')->where('idParticulier', $userId)->exists() ||
            DB::table('employes')->join('role_users', 'role_users.user_id', '=', 'employes.idEmploye')
            ->where('role_users.user_id', $userId)
            ->where('role_users.role_id', 4)
            ->exists()
        ) {
            return 'particulier';
        }

        return null;
    }
}
