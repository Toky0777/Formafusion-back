<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\MobileMoneyAcount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceAccountController extends Controller
{
    public function index()
    {
        $id = Auth::user()->id;

        $bankacounts = DB::table('bankacounts')
            ->join('ville_codeds', 'bankacounts.ba_idPostal', 'ville_codeds.id')
            ->select('bankacounts.id as idAcount', 'bankacounts.*', 'ville_codeds.*')
            ->where('ba_idCustomer', Customer::idCustomer())
            ->get();

        $mobile_money = MobileMoneyAcount::where('mm_idCustomer', Customer::idCustomer())->get();

        $companies = Company::where('idCustomer', Customer::idCustomer())->get();

        $contacts = DB::table('invoice_contacts as ic')
            ->select('ic.*')
            ->leftJoin('cfp_etps', 'ic.idEtp', '=', 'cfp_etps.idEtp')
            ->where('cfp_etps.idCfp', Customer::idCustomer())
            ->get();

        return response()->json([
            'status' => 200,
            'id' => $id,
            'idCustomer' => Customer::idCustomer(),
            'refConnected' => $this->getRoleIdRef(),
            'ville_codeds' => $this->getAllVilleCoded(),
            'bankacounts' => $bankacounts,
            'mobile_money' => $mobile_money,
            'companies' => $companies,
            'contacts' => $contacts
        ]);
    }

    private function getRoleIdRef()
    {
        $refConnected = DB::table('users')
            ->select('role_users.role_id')
            ->join('role_users', 'users.id', '=', 'role_users.user_id')
            ->where('users.id',  Customer::idCustomer())
            ->where('role_users.role_id', 3)
            ->first();
        return $refConnected;
    }

    private function getAllVilleCoded()
    {
        return DB::table('ville_codeds')->orderBy('ville_codeds.vi_code_postal', 'asc')->get();
    }
}
