<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Salle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SubContractorController extends Controller
{

    public function idCfp()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idCustomer;
    }

    public function isInCollaboration($cfp_id)
    {
        $CfpId = Customer::idCustomer();
        $exists = DB::table('sub_contractors')
            ->where('idCfp', $CfpId)
            ->where('idSubContractor', $cfp_id)
            ->exists();

        return $exists;
    }

    public function getCfps()
    {
        $cfps = DB::table('customers as C')
            ->join('role_users as RU', 'RU.user_id', 'C.idCustomer')
            ->select('C.idCustomer as cfp_id', 'C.customerName as cfp_name', 'C.customerEmail as cfp_email', 'C.logo as cfp_logo', 'C.idTypeCustomer', 'C.customerPhone as cfp_phone')
            ->where('C.idTypeCustomer', 1)
            ->where('RU.isActive', 1)
            ->where('C.customerEmail', '!=', null)
            //->where('C.customerName', 'like', '%'.$name.'%')
            ->whereNot('C.idCustomer', Customer::idCustomer())
            ->orderBy('C.customerName', 'asc')
            ->get();
        $data = [];
        foreach ($cfps as $c) {
            $data[] = [
                'cfp_id' => $c->cfp_id,
                'cfp_name' => $c->cfp_name,
                'cfp_phone' => $c->cfp_phone,
                'cfp_email' => $c->cfp_email,
                'cfp_logo' => $c->cfp_logo,
                'idTypeCustomer' => $c->idTypeCustomer,
                'isInCollaboration' => $this->isInCollaboration($c->cfp_id)
            ];
        }
        if (count($cfps) <= 0) {
            return response()->json([
                'status' => 204,
                'message' => 'Aucun résultat !'
            ], 204);
        }

        return response()->json([
            'status' => 200,
            'cfps' => [
                'cfp_count' => count($data),
                'cfp_items' => $data
            ]
        ]);
    }

    // existing CFP
    public function addSubcontractor(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|min:2|max:150',
            'cfp_id' => 'required|exists:customers,idCustomer'
        ]);

        DB::transaction(function () use ($request) {
            DB::table('sub_contractors')->insert([
                'idSubContractor' => $request->cfp_id,
                'idCfp' => Customer::idCustomer()
            ]);

            // invitation email no mipetraka eto
        });

        return response()->json([
            'status' => 200,
            'message' => 'Invitation envoyée avec succès'
        ], 200);
    }

    // new account
    public function addNewSubcontractor(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|min:2|max:150',
            'firstname' => 'nullable|min:2|max:200',
            'nif' => 'nullable|min:5|max:70|unique:customers,nif'
        ]);

        DB::transaction(function () use ($request) {
            $user = DB::table('users')->insertGetId([
                'name' => $request->name,
                'firstName' => $request->firstname,
                'email' => $request->email,
                'password' => Hash::make('12345678')
            ]);

            DB::table('customers')->insert([
                'idCustomer' => $user,
                'customerName' => $request->name,
                'nif' => $request->nif,
                'customerEmail' => $request->email,
                'idSecteur'     => 7,
                'idTypeCustomer' => 1
            ]);

            DB::table('cfps')->insert([
                'idCustomer' => $user
            ]);

            DB::table('role_users')->insert([
                'role_id'   => 3,
                'user_id'   => $user,
                'hasRole'   => 1,
                'isActive'  => 1
            ]);

            $fonction = DB::table('fonctions')->insertGetId([
                'fonction' => "default_fonction",
                'idCustomer' => $user
            ]);

            DB::table('employes')->insert([
                'idEmploye'     => $user,
                'idCustomer'    => $user,
                'idSexe'        => 1,
                'idNiveau'      => 1,
                'idFonction'    => $fonction
            ]);

            $module = DB::table('mdls')->insertGetId([
                'moduleName' => "Default module",
                'idDomaine' => 1,
                'idCustomer' => $user,
                'idTypeModule' => 1
            ]);

            DB::table('modules')->insert([
                'idModule' => $module
            ]);

            $this->storeSalle($user);

            DB::table('sub_contractors')->insert([
                'idSubContractor' => $user,
                'idCfp' => Customer::idCustomer()
            ]);

            // invitation email no mipetraka eto
        });

        return response()->json([
            'status' => 200,
            'message' => 'Invitation envoyée avec succès'
        ], 200);
    }

    public function storeSalle($idCustomer): void
    {
        DB::transaction(function () use ($idCustomer) {
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
    public function getSubContractor($idCustomer): mixed
    {
        $query = DB::table('v_list_sub_contractors')
            ->select('idSubContractor', 'sub_name', 'sub_email', 'sub_logo', 'sub_initial_name', 'cfp_ville')
            ->where('id_cfp', $idCustomer)
            ->orderBy('sub_name', 'asc');

        return $query;
    }
    public function index()
    {
        $subcontractors = $this->getSubContractor(Customer::idCustomer())->get();

        return response()->json([
            'status' => 200,
            'subcontractors' => $subcontractors, 
            'count' => count($subcontractors)
        ]);
    }

    public function getAssign($idProjet)
    {
        $query = DB::table('v_list_sub_contractor_addeds')
            ->where('idProjet', $idProjet);

        if (isset($query->first()->idCfp) && $this->idCfp() == $query->first()->idCfp) {
            $query->select('idSubContractor', 'sub_name as cfp_name', 'sub_initial_name as cfp_initial_name', 'sub_logo as cfp_logo', 'sub_email as cfp_email');
        } else {
            $query->select('idCfp as idSubContractor', 'cfp_name', 'cfp_initial_name', 'cfp_logo', 'cfp_email');
        }

        if ($query->first()) {
            return response()->json(['cfp' => $query->first()]);
        } else {
            return response(['error' => 'Introuvable !']);
        }
    }

    public function assign($idProjet, $idSubContractor)
    {
        $query = DB::table('project_sub_contracts')
            ->select('idProjet', 'idSubContractor')
            ->where('idProjet', $idProjet);

        $checkForm = DB::table('project_forms')->select('idProjet', 'idFormateur')->where('idProjet', $idProjet)->count();

        if ($query->count() <= 0) {
            DB::transaction(function () use ($query, $idProjet, $idSubContractor, $checkForm) {
                $query->insert([
                    'idProjet' => $idProjet,
                    'idSubContractor' => $idSubContractor
                ]);

                if ($checkForm > 0) {
                    DB::table('project_forms')->where('idProjet', $idProjet)->delete();
                }
            });

             return $this->getAssign($idProjet);

        } else {
            return response()->json(['error' => 'Sous-traitant déjas inscrit au projet !']);
        }
    }

    public function removeAssign($idSubContractor)
    {
        $query = DB::table('project_sub_contracts')->where('idSubContractor', $idSubContractor);

        if ($query->first()) {
            $query->delete();

            return response(['success' => 'Opération éffectuée avec succès'], 200);
        } else {
            return response(['error' => 'Introuvable !'], 204);
        }
    }
}
