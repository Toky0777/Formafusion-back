<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function getAllEntreprise()
    {
        $entreprises = DB::table('v_collaboration_cfp_etps')
            ->select('idEtp as id', 'etp_name as name', 'etp_logo as logo', 'etp_email as email')
            ->where('idCfp', Customer::idCustomer())
            ->orderBy('etp_name')
            ->get();

        return response($entreprises, 200);
    }

    public function getEntrepriseByIds(Request $request)
    {
        $etpIds = $request->input('etpIds', []);
        if (!is_array($etpIds)) {
            $etpIds = [$etpIds];
        }

        $entreprises = DB::table('v_collaboration_cfp_etps')
            ->select('idEtp as id', 'etp_name as name', 'etp_logo as logo', 'etp_email as email')
            ->whereIn('idEtp', $etpIds)
            ->orderBy('etp_name')
            ->distinct()
            ->get();

        return response($entreprises, 200);
    }
}
