<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ModuleRessource extends Model
{
    use HasFactory;
    public static function countByModuleId($idModule)
    {
        return DB::table('module_ressources')
            ->where('idModule', $idModule)
            ->count();
    }
}
