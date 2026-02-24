<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditFormRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    //
    public function index()
    {

        $contacts = DB::table('contacts')
            ->select(
                'idContact',
                'contact_name',
                'contact_email',
                'idLieu',
                'contact_tel'
            )->get();
        return response()->json([
            'contacts' => $contacts,
        ]);
    }
    public function store(EditFormRequest $req)
    {

        try {
            DB::table('contacts')->insert([
                'contact_name' =>    $req->input('name'),
                'contact_email' =>    $req->input('email'),
                'idLieu' =>    $req->input('idLieu'),
                'contact_tel' =>    $req->input('phone'),
            ]);

            return response()->json(['success' => 'Succès']);
        } catch (Exception $e) {
            Log::error("Erreur dans storeSalle : " . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function destroy($idContact)
    {
        try {
            DB::table('contacts')
                ->where('idContact', $idContact)
                ->delete();
            return response()->json(['success' => 'Succès']);
        } catch (Exception $e) {
            Log::error("Erreur dans storeSalle : " . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    public function update(Request $req, $idContact)
    {

        try {
            DB::table('contacts')
                ->where('idContact', $idContact)
                ->update([
                    'contact_name' =>    $req->name,
                    'contact_email' =>    $req->email,
                    'contact_tel' =>    $req->phone,
                ]);
            return response()->json(['success' => 'Succès']);
        } catch (Exception $e) {
            Log::error("Erreur dans storeContact : " . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
            throw $e;
        }
    }
}
