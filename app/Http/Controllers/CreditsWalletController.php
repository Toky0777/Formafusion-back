<?php

namespace App\Http\Controllers;

use App\Models\CreditsWallet;
use App\Models\User;
use App\Services\Qcm\QcmNavigationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreditsWalletController extends Controller
{
    # Services part added 18-02-2025
    private QcmNavigationService $navigationService;

    public function __construct(
        QcmNavigationService $navigationService
    ) {
        $this->navigationService = $navigationService;
    }
    # Services part added 18-02-2025

    /**
     * Afficher le portefeuille en crédits d'un utilisateur
     * 
     * @param $id (id du propriétaire du portefeuille)
     */
    public function show_user_credit_wallet($id)
    {
        $credit_wallet = (new CreditsWallet)->user_credit_wallet($id);

        if ($credit_wallet) {
            return response()->json(['solde' => $credit_wallet->solde]);
        } else {
            return response()->json(['error' => 'Portefeuille introuvable'], 404);
        }
    }

    /**
     * Fonction menant au formulaire d'opération de transaction pour les crédits (v2)
     * 
     * @param $id (id de l'utilisateur)
     */
    public function showWalletOperationForm($id)
    {
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté

        $all_user = User::all();

        return response()->json([
            'userId' => $id,
            'extends_containt' => $extends_containt,
            'all_user' => $all_user,
        ]);
    }

    /**
     * Fonction menant au formulaire d'opération de transaction pour les employés d'une entreprise (v2)
     * 
     * @param $id (id de l'utilisateur)
     */
    public function showWalletOperationFormMultiEmp($id)
    {
        $extends_containt = $this->navigationService->determineLayout(); // Variable pour stocker les extends selon l'utilisateur connecté

        $all_user = User::all();

        return response()->json([
            'userId' => $id,
            'extends_containt' => $extends_containt,
            'all_user' => $all_user,
        ]);
    }

    /**
     * Méthode pour créditer le compte d'un utilisateur
     * 
     * @param $request (montant à créditer), $id (id de l'utilisateur)
     */
    public function crediterCompte(Request $request, $id)
    {
        $montant = $request->input('montant'); # Montant à créditer sur le compte de l'utilisateur

        try {
            $wallet = CreditsWallet::crediter($id, $montant);
            return response()->json([
                'message' => 'Compte crédité avec succès',
                'wallet' => $wallet,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Méthode pour débiter le compte d'un utilisateur
     * 
     * @param $request (montant à débiter), $id (id de l'utilisateur)
     */
    public function debiterCompte(Request $request, $id)
    {
        $montant = $request->input('montant'); # Montant à débiter sur le compte de l'utilisateur

        try {
            $wallet = CreditsWallet::debiter($id, $montant);
            return response()->json([
                'message' => 'Compte débité avec succès',
                'wallet' => $wallet,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Méthode permettant à une entreprise de créditer ses employés avec la somme de crédit
     * voulue pour chacun des employés de l'entreprise déduit du compte de crédit de l'entreprise (v2)
     * 
     * @param $request, $id (id de l'entreprise), nalana aloha le $id pour le moment
     */
    public function crediterCompteEmp(Request $request)
    {
        $id = $request->input('id'); // Récupérer l'ID de l'entreprise à partir de la requête
        $montant = $request->input('montant'); // Montant à créditer sur chaque compte des employés de l'entreprise

        try {
            $wallet = new CreditsWallet();
            $operation = $wallet->share_credits($id, $montant);
            return response()->json([
                'message' => 'Approvisionnement des comptes des employés réussis',
                'operation' => $operation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
