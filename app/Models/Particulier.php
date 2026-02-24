<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Particulier extends Model
{
    use HasFactory;

    protected $table = "particuliers";
    protected $primaryKey = 'idParticulier';
    public $timestamps = false;

    public static function getParticulier($id){
        $particulier = DB::table('particuliers as p')
            ->select('p.idParticulier as idCustomer', DB::raw('3 AS idTypeCustomer'), 'name AS customer_name', 'email AS customer_email', DB::raw('"Particulier" AS customer_type'), DB::raw('NULL AS customer_nif'), 'user_addr_lot as customer_addr_lot')
            ->join('users as u', 'p.idParticulier', 'u.id')
            ->where('p.idParticulier', $id)
            ->first();

        return $particulier;
    }
}
