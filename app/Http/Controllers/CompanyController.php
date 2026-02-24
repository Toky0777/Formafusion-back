<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::where('idCustomer', Customer::idCustomer())->get();

        if(count($companies) <= 0){
            return response()->json([
                'status' => 404,
                'companies' => 'Aucun éléments !'
            ], 404);
        }else{
            return response()->json([
                'status' => 200,
                'companies' => $companies
            ]);
        }
    }

    public function show()
    {
        $companies = Company::all();

        if(count($companies) <= 0){
            return response()->json([
                'status' => 404,
                'companies' => 'Aucun éléments !'
            ], 404);
        }else{
            return response()->json([
                'status' => 200,
                'companies' => $companies
            ]);
        }
    }

    public function create()
    {
        $idCustomer = DB::table('customers')->where('idCustomer', Customer::idCustomer())->value('idCustomer');

        return response()->json([
            'status' => 200,
            'idCustomer' => $idCustomer
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'idCustomer' => 'required|exists:customers,idCustomer',
            'name' => 'required|string|max:255',
            // 'nif' => 'required|string|max:255|unique:companies,nif',
            // 'stat' => 'required|string|max:255|unique:companies,stat',
            'rcs' => 'required|string|max:255|unique:companies,rcs',
            'adresse' => 'required|string|max:255',
            'mail' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'website' => 'nullable|max:255',
        ]);
        try {
            Company::create($request->all());
            return response()->json([
                'status' => 200,
                'message' => "Company created successfully."
            ]);
        } catch (Exception $e) {
    return response()->json([
        'status' => 400,
        'message' => $e->getMessage(), // ici tu renvoies le vrai message de l'erreur
    ], 400);
}
    }

    public function edit(Company $company)
    {
        $idCustomer = DB::table('customers')->where('idCustomer', Customer::idCustomer())->value('idCustomer');

        return response()->json([
            'status' => 200,
            'company' => $company,
            'idCustomer' => $idCustomer
        ]);
    }

    public function update(Request $request, Company $company)
    {
        $request->validate([
            'idCustomer' => 'required',
            'name' => 'required|string',
            'nif' => 'required|string',
            'stat' => 'required|string',
            'rcs' => 'required|string',
            'adresse' => 'required',
            'mail' => 'required',
            'phone' => 'required',
            'website' => 'nullable',
        ]);

        $company->update($request->all());

        return response()->json([
            'status' => 200,
            'message' => "Company updated successfully."
        ]);
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return response()->json([
            'status' => 200,
            'message' => "Une adressse a été bien supprimé."
        ]);
    }
}
