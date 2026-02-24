<?php
namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait DeleteQuery{
    public function deleteProspect($customerName){
        DB::table('prospects')->where('prospect_name', $customerName)->delete();
    }
}