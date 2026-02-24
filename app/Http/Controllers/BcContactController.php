<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BcContactController extends Controller
{

    public function index()
    {
        $contacts = DB::table('bc_contacts')->get();
        return response()->json($contacts);
    }

    public function show($id)
    {
        $contact = DB::table('bc_contacts')->where('idContact', $id)->first();

        if (!$contact) {
            return response()->json(['message' => 'Contact non trouvé'], 404);
        }

        return response()->json($contact);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contact_name' => 'required|string|max:100',
            'contact_mail' => 'nullable|max:100',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        $id = DB::table('bc_contacts')->insertGetId($validated);

        return response()->json([
            'message' => 'Contact créé avec succès',
            'idContact' => $id
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $contact = DB::table('bc_contacts')->where('idContact', $id)->first();

        if (!$contact) {
            return response()->json(['message' => 'Contact non trouvé'], 404);
        }

        $validated = $request->validate([
            'contact_name' => 'sometimes|required|string|max:100',
            'contact_mail' => 'nullable|email|max:100',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        if (empty($validated)) {
            return response()->json(['message' => 'Aucune donnée à mettre à jour'], 400);
        }

        DB::table('bc_contacts')->where('idContact', $id)->update($validated);

        return response()->json(['message' => 'Contact mis à jour avec succès']);
    }


    public function destroy($id)
    {
        $deleted = DB::table('bc_contacts')->where('idContact', $id)->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Contact non trouvé'], 404);
        }

        return response()->json(['message' => 'Contact supprimé avec succès']);
    }
}
