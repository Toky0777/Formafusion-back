<?php

namespace App\Services;

use App\Interfaces\SubcontractorInterface;
use Illuminate\Support\Facades\DB;

class SubcontractorRepository implements SubcontractorInterface
{
    public function index($idCustomer): mixed
    {
        $query = DB::table('v_list_sub_contractors')
            ->select('idSubContractor', 'sub_name', 'sub_email', 'sub_logo', 'sub_initial_name')
            ->where('id_cfp', $idCustomer)
            ->orderBy('sub_name', 'asc');

        return $query;
    }

    public function edit($idCustomer, $idSubcontractor): mixed
    {
        $query = $this->index($idCustomer)->where('idSubContractor', $idSubcontractor);

        return $query;
    }
}
