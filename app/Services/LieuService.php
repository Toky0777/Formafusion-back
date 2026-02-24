<?php
namespace App\Services;

use App\Interfaces\LieuInterface;
use App\Models\Salle;
use Illuminate\Support\Facades\DB;

class LieuService implements LieuInterface{
    public function store($idCustomer): void
    {
        DB::transaction(function() use($idCustomer){
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
}