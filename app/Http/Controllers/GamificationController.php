<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GamificationController extends Controller
{
    public function index()
    {
        $topFive = DB::table('v_classement')
            ->limit(5)
            ->get();

        $getExpById = DB::table('v_classement')
            ->where('idEmploye', Auth::user()->id)
            ->first();

        $niveauGamification = DB::table('niveau_gamification')
            ->get()
            ->map(function ($niveau) {
                $colors = [
                    1 => "green",
                    2 => "blue",
                    3 => "yellow",
                    4 => "orange",
                    5 => "purple",
                    6 => "pink",
                    7 => "red",
                ];

                $niveau->color = $colors[$niveau->numero] ?? "gray";
                return $niveau;
            });

        return response()->json([
            'topFive' => $topFive,
            'getExpById' => $getExpById ?? ['libelle_niveau' => 'Apprenant engagé'],
            'niveauGamification' => $niveauGamification,
            'authId' => Auth::user()->id
        ], 200, [], JSON_PRETTY_PRINT);
    }
}
