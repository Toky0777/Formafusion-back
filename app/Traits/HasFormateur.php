<?php
namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasFormateur{
    public function isFormatorHasExperience($idFormateur): bool{
        $query = DB::table('experiences')->where('idFormateur', $idFormateur);

        if($query->count() > 0){
            return true;
        }else{
            return false;
        }
    }

    public function isFormatorHasDegree($idFormateur): bool{
        $query = DB::table('diplomes')->where('idFormateur', $idFormateur);

        if($query->count() > 0){
            return true;
        }else{
            return false;
        }
    }

    public function isFormatorHasSkill($idFormateur): bool{
        $query = DB::table('competences')->where('idFormateur', $idFormateur);

        if($query->count() > 0){
            return true;
        }else{
            return false;
        }
    }

    public function isFormatorHasLanguage($idFormateur): bool{
        $query = DB::table('langues')->where('idFormateur', $idFormateur);

        if($query->count() > 0){
            return true;
        }else{
            return false;
        }
    }
}