<?php

namespace App\Services\Qcm;

use App\Models\CreditsWallet;
use Illuminate\Support\Facades\Auth;

class QcmCreditService
{
    private CreditsWallet $creditsWallet;

    /**
     * Constructor method
     * 
     * @param CreditsWallet $creditsWallet
     */
    public function __construct(CreditsWallet $creditsWallet)
    {
        $this->creditsWallet = $creditsWallet;
    }

    /**
     * Validate and process credits for a user (v1)
     * 
     * @param int $userId The user ID
     * @param int|null $idEtp The establishment ID
     * @param float $price The price of the test
     * 
     * @return array
     */
    // public function validateAndProcessCredits(int $userId, ?int $idEtp, float $price): array
    // {
    //     $user = Auth::user();

    //     // Determine the wallet ID based on user role
    //     $walletId = $user->hasRole('Particulier')
    //         ? $userId
    //         : ($user->hasRole('Employe') || $user->hasRole('EmployeEtp')
    //             ? $idEtp
    //             : $userId);

    //     try {
    //         // Get user wallet
    //         $userWallet = CreditsWallet::where('idUser', $walletId)->first();

    //         if (!$userWallet) {
    //             return [
    //                 'success' => false,
    //                 'message' => "Aucun portefeuille de crédits n'a été trouvé pour votre compte."
    //             ];
    //         }

    //         // Check if user has enough credits
    //         $hasEnoughCredits = $userWallet->solde >= $price;

    //         if (!$hasEnoughCredits) {
    //             return [
    //                 'success' => false,
    //                 'message' => "Vous n'avez pas assez de crédits pour passer ce test. " .
    //                     "Solde actuel: {$userWallet->solde} crédit(s). " .
    //                     "Prix du test: {$price} crédit(s)."
    //             ];
    //         }

    //         // Process payment if timer not started
    //         if (!session()->has('qcm_timer')) {
    //             // For employees, pass their user ID as initiator. For others, pass null
    //             $initiatorId = $user->hasRole(['Employe', 'EmployeEtp']) ? $userId : null;

    //             $this->creditsWallet::debiter($walletId, $price, $initiatorId);
    //             session(['qcm_payment_processed' => true]);
    //         }

    //         return [
    //             'success' => true,
    //             'wallet' => $userWallet,
    //             'hasEnoughCredits' => true
    //         ];
    //     } catch (\Exception $e) {
    //         return [
    //             'success' => false,
    //             'message' => "Une erreur est survenue lors du traitement des crédits: " . $e->getMessage()
    //         ];
    //     }
    // }

    /**
     * Validate and process credits for a user (v2)
     * 
     * @param int $userId The user ID
     * @param int|null $idEtp The establishment ID
     * @param float $price The price of the test
     * 
     * @return array
     */
    public function validateAndProcessCredits(int $userId, ?int $idEtp, float $price): array
    {
        $user = Auth::user();

        // Determine the wallet ID based on user role
        $walletId = $user->hasRole('Particulier')
            ? $userId
            : ($user->hasRole('Employe') || $user->hasRole('EmployeEtp')
                ? $idEtp
                : $userId);

        try {
            // Get user wallet
            $userWallet = CreditsWallet::where('idUser', $walletId)->first();

            if (!$userWallet) {
                return [
                    'success' => false,
                    'message' => "Aucun portefeuille de crédits n'a été trouvé pour votre compte."
                ];
            }

            // Check if user has enough credits
            $hasEnoughCredits = $userWallet->solde >= $price;

            if (!$hasEnoughCredits) {
                return [
                    'success' => false,
                    'message' => "Vous n'avez pas assez de crédits pour passer ce test. " .
                        "Solde actuel: {$userWallet->solde} crédit(s). " .
                        "Prix du test: {$price} crédit(s)."
                ];
            }

            // Process payment if timer not started
            if (!session()->has('qcm_timer')) {
                $initiatorId = null;
                if ($user->hasRole('Employe') || $user->hasRole('EmployeEtp')) {
                    $initiatorId = $user->id;  // Use the authenticated employee's ID
                }

                $this->creditsWallet::debiter($walletId, $price, $initiatorId);
                session(['qcm_payment_processed' => true]);
            }

            return [
                'success' => true,
                'wallet' => $userWallet,
                'hasEnoughCredits' => true
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "Une erreur est survenue lors du traitement des crédits: " . $e->getMessage()
            ];
        }
    }
}
