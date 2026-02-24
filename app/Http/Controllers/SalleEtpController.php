<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalleEtpController extends Controller
{
    public function getVillesByPostalCode(Request $request)
    {
        $codePostal = $request->input('cp');

        if (!$codePostal) {
            return response()->json([]);
        }

        $villes = DB::table('ville_codeds')
            ->join('villes', 'villes.idVille', '=', 'ville_codeds.idVille')
            ->where('ville_codeds.vi_code_postal', 'LIKE', $codePostal . '%')
            ->get(['ville_codeds.id', 'ville_codeds.ville_name']);

        return response()->json($villes);
    }
}
