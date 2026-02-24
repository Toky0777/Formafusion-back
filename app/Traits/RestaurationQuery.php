<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait RestaurationQuery
{
    public function getRestaurationByProject($idProjet)
    {
        $restaurations = DB::table('project_restaurations')
            ->select('idRestauration', 'paidBy')
            ->where('idProjet', $idProjet)
            ->get()
            ->toArray();
        return $restaurations;
    }
}