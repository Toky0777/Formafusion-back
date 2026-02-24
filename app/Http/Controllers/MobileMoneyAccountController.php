<?php

namespace App\Http\Controllers;

use App\Http\Requests\MobileMoneyAcountRequest;
use App\Models\Customer;
use App\Models\MobileMoneyAcount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileMoneyAccountController extends Controller
{
    public function show($id)
    {
        $account = MobileMoneyAcount::find($id);

        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        return response()->json($account);
    }

    public function store(MobileMoneyAcountRequest $request)
    {
        $validated = $request->validated();

        $validated['mm_idCustomer'] = Customer::idCustomer();

        try {
            DB::beginTransaction();
            $data = MobileMoneyAcount::create($validated);
            DB::commit();
            return response()->json([
                "success" => "Compte mobile money ajouté avec succès.",
                "data" => $data
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["error" => "Erreur inconnue ! " . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'mm_phone' => 'required|string|max:20',
            'mm_operateur' => 'required|string|max:50',
            'mm_titulaire' => 'required|string|max:100',
        ]);

        try {
            $account = MobileMoneyAcount::findOrFail($id);
            $account->update($validated);

            return response()->json([
                'status' => 200,
                'message' => "Compte mobile money mis à jour avec succès.",
                'data' => $account,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage(),
            ], 500);
        }
    }



    public function destroy($id)
    {
        try {
            $account = MobileMoneyAcount::findOrFail($id);
            $account->delete();

            return response()->json([
                'status' => 200,
                'message' => "Compte mobile money supprimé avec succès."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => 'Ce compte mobile money est rattaché à des paiements et ne peut pas être supprimé.'
            ]);
        }
    }
}
