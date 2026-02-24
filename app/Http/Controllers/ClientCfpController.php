<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Customer;
use App\Traits\GetQuery;
use App\Services\CfpService;
use Illuminate\Http\Request;
use App\Services\CustomerService;
use Illuminate\Support\Facades\DB;
use App\Services\EntrepriseService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ClientCfpController extends Controller
{

    private function getProjectIntra($idCfp)
    {
        $intra = DB::table('intras')
            ->select('idProjet', 'idEtp', 'idCfp')
            ->where('idCfp', $idCfp)
            ->where('idEtp', Customer::idCustomer())
            ->get();

        return $intra;
    }

    private function getProjectInter($idCfp)
    {
        $inter = DB::table('inter_entreprises as ie')
            ->join('inters', 'ie.idProjet', 'inters.idProjet', 'inters.idCfp')
            ->where('idEtp', Customer::idCustomer())
            ->where('inters.idCfp', $idCfp)
            ->get();
        return $inter;
    }


    public function index()
    {
        $cfps = DB::table('v_collaboration_cfp_etps')
            ->select('idCfp', 'idTypeEtp', 'cfp_name', 'cfp_nif', 'cfp_stat', 'cfp_logo', 'cfp_email', 'cfp_phone',)
            ->where('idEtp', Customer::idCustomer())
            ->get();

        $result = [];

        foreach ($cfps as $cfp) {
            $result[] = [
                'idCfp' => $cfp->idCfp,
                'idTypeEtp' => $cfp->idTypeEtp,
                'cfp_name' => $cfp->cfp_name,
                'cfp_logo' => $cfp->cfp_logo,
                'cfp_email' => $cfp->cfp_email,
                'cfp_phone' => $cfp->cfp_phone,
                'project' => count($this->getProjectInter($cfp->idCfp)) +  count($this->getProjectIntra($cfp->idCfp))
            ];
        }

        if (count($result) <= 0) {
            return response()->json([
                'status' => 204,
                'message' => "Aucun centre de formation"
            ], 204);
        }


        return response()->json([
            'status' => 200,
            'cfps' => $result
        ]);
    }

    public function show($idCfp)
    {
        $cfp = DB::table('v_collaboration_cfp_etps')
            ->select('idCfp', 'idTypeEtp', 'cfp_name', 'cfp_nif', 'cfp_stat', 'cfp_logo', 'cfp_email', 'cfp_phone', 'cfp_description', 'cfp_siteweb')
            ->where('idCfp', $idCfp)
            ->where('idEtp', Customer::idCustomer())
            ->first();



        return response()->json([
            'status' => 200,
            'cfp' => $cfp,

        ]);
    }

    public function destroy($idCfp)
    {
        $project_intra = $this->getProjectIntra($idCfp);
        $project_inter = $this->getProjectInter($idCfp);

        if (count($project_intra) > 0 || count($project_inter) > 0) {
            return response()->json([
                'status' => 500,
                'message' => 'Suppression impossible'
            ], 500);
        } else {
            DB::table('cfp_etps')
                ->where('idEtp', Customer::idCustomer())
                ->where('idCfp', $idCfp)
                ->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Supprimé avec succèss'
            ]);
        }
    }
}
