<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditWalletService
{
    /**
     * List of account types eligible for initial credit in 'type_entreprises' table
     */
    private const ELIGIBLE_ACCOUNT_TYPES = [1, 2, 4, 5, 6, 7];

    /**
     * Initial credit amount for new accounts (1000 credits)
     */
    private const INITIAL_CREDIT_AMOUNT = 1000;

    /**
     * Check if account type is eligible for initial credit
     * 
     * @param int $accountType
     * 
     * @return bool
     */
    public function isEligibleForInitialCredit(int $accountType): bool
    {
        return in_array($accountType, self::ELIGIBLE_ACCOUNT_TYPES);
    }

    /**
     * Generate unique transaction reference
     * 
     * @param string $prefix
     * 
     * @return string
     */
    private function generateTransactionRef(string $prefix = 'CREDIT'): string
    {
        return $prefix . '-' . uniqid();
    }

    /**
     * Credit user account with initial amount and record transaction
     * 
     * @param User $user, int $accountType
     * 
     * @return void
     */
    public function creditNewAccount(User $user, int $accountType): void
    {
        if (!$this->isEligibleForInitialCredit($accountType)) {
            return;
        }

        DB::transaction(function () use ($user) {
            // Credit wallet
            DB::table('credits_wallet')->insert([
                'idUser' => $user->id,
                'solde' => self::INITIAL_CREDIT_AMOUNT,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Record transaction history
            DB::table('transaction_history')->insert([
                'idUser' => $user->id,
                'transaction_ref' => $this->generateTransactionRef(),
                'montant' => self::INITIAL_CREDIT_AMOUNT,
                'typeTransaction' => 'credit',
                'description' => "Credit de compte de cadeau {$user->name} suite à l'ouverture d'un compte",
                'created_at' => now()
            ]);
        });
    }
}
