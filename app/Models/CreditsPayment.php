<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CreditsPayment extends Model
{
    use HasFactory;

    protected $table = 'credits_payments';
    protected $primaryKey = 'idCreditPayment';

    protected $fillable = [
        'user_id',
        'pack_credits_id',
        'reference', // Utilisation de 'reference' au lieu de 'id_order'
        'amount_paid',
        'currency',
        'payment_type',
        'status'
    ];

    // Relation avec le modèle User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relation avec CreditsPacks
    public function creditsPack()
    {
        return $this->belongsTo(CreditsPacks::class, 'pack_credits_id', 'idPackCredit');
    }

    /**
     * Method for getting all the transactions of payments for the list of payments on json (v3) avec vérification des utilisateurs connectés
     * with filter
     */
    public function getAllTransactionList($startDate = null, $minAmount = null, $maxAmount = null)
    {
        $user_auth = Auth::user();

        try {
            // Requête de base avec les relations
            // Si l'utilisateur est un Referent / Etp ou un particulier
            if ($user_auth->hasRole('Referent') || $user_auth->hasRole('Particulier')) {
                $query = CreditsPayment::with(['creditsPack'])
                    ->where('user_id', $user_auth->id);
            } elseif ($user_auth->hasRole('SuperAdmin')) {
                $query = CreditsPayment::with(['creditsPack']);
            }
            // $query = CreditsPayment::with(['creditsPack']);

            // Filtrage par date de début
            if ($startDate) {
                $query->whereDate('created_at', '=', $startDate);
            }

            // Filtrage par montant minimum
            if ($minAmount !== null) {
                $query->where('amount_paid', '>=', $minAmount);
            }

            // Filtrage par montant maximum
            if ($maxAmount !== null) {
                $query->where('amount_paid', '<=', $maxAmount);
            }

            // Trier par date de création décroissante
            $query->orderBy('created_at', 'desc');

            // Récupérer les transactions
            $transactions = $query->orderBy('created_at', 'desc')
                ->get();

            // Vérifier si aucune transaction n'est trouvée
            if ($transactions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune transaction trouvée.',
                    'data' => []
                ], 404);
            }

            // Traitement des transactions pour ajouter les détails de l'utilisateur
            $transactions = $transactions->map(function ($transaction) {
                // Récupérer l'utilisateur lié à la transaction
                $user = User::find($transaction->user_id);

                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Utilisateur non trouvé pour cette transaction'
                    ], 404);
                }

                // Déterminer les détails de l'utilisateur en fonction de son rôle
                $user_data = [];
                if ($user->hasRole('Referent')) {
                    // Si l'utilisateur est un Referent, chercher dans Customer
                    $customer = Customer::where('idCustomer', $user->id)->first();

                    if ($customer) {
                        $user_data = [
                            'name' => $customer->customerName,
                            'email' => $customer->customerEmail,
                            'phone' => $customer->customerPhone,
                            'address' => "{$customer->customer_addr_lot}, {$customer->customer_addr_quartier}, {$customer->customer_addr_rue}, {$customer->customer_addr_code_postal}",
                        ];
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Les informations du client sont introuvables pour l\'utilisateur Referent'
                        ], 404);
                    }
                } elseif ($user->hasRole('Particulier')) {
                    // Si l'utilisateur est un Particulier, chercher directement dans User
                    $user_data = [
                        'name' => $user->firstName . ' ' . $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'address' => "{$user->user_addr_lot}, {$user->user_addr_quartier}, {$user->user_addr_rue}, {$user->user_addr_code_postal}",
                    ];
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Rôle de l\'utilisateur non reconnu'
                    ], 400);
                }

                // Ajouter les détails du pack de crédits
                $pack_data = $transaction->creditsPack ? [
                    'type_pack' => $transaction->creditsPack->type_pack,
                    'description_pack' => $transaction->creditsPack->description_pack,
                    'credits' => $transaction->creditsPack->credits,
                    'pack_price' => $transaction->creditsPack->pack_price,
                    'currency' => $transaction->creditsPack->currency,
                ] : null;

                // Ajouter les données supplémentaires à la transaction
                $transaction->user_details = $user_data;
                $transaction->credits_pack = $pack_data;

                return $transaction;
            });

            // Retourner la réponse avec les données enrichies
            return response()->json([
                'success' => true,
                'total_transactions' => $transactions->count(),
                'transactions' => $transactions
            ], 200);
        } catch (Exception $e) {
            // Gérer les erreurs exceptionnelles
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fonction pour avoir la liste des transactions de paiements effectués for dashboard
     */
    public function getAllTransaction()
    {
        $query = DB::table('credits_payments')
            ->join('users', 'credits_payments.user_id', '=', 'users.id')
            ->select(
                'credits_payments.reference',
                'credits_payments.user_id',
                'credits_payments.amount_paid',
                'credits_payments.currency',
                'credits_payments.payment_type',
                'credits_payments.status',
                'credits_payments.created_at',
                'users.name',
                'users.email',
                'users.phone',
                'users.matricule',
                'users.firstName'
            );

        // Count the total number of transactions
        $total_transactions = $query->count();

        // Return null if no transactions are found
        if ($total_transactions === 0) {
            return null;
        }

        // Retrieve the list of transactions
        $transaction_list = $query->get();

        return [
            'total_transactions' => $total_transactions,
            'transaction_list' => $transaction_list
        ];
    }

    /**
     * Fonction pour avoir les chiffres d'affaires à travers le temps (par mois pour ce cas)
     * en differenciant les devises : USD ou MGA actuallement
     * avec paramètre
     */
    public function getMonthSaleRevenue()
    {
        $monthly_revenue = DB::table('credits_payments')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month")
            ->selectRaw("SUM(CASE WHEN currency = 'MGA' THEN amount_paid ELSE 0 END) as revenue_mga")
            ->selectRaw("SUM(CASE WHEN currency = 'USD' THEN amount_paid ELSE 0 END) as revenue_usd")
            ->where('status', 'paid')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return $monthly_revenue;
    }

    /**
     * Fonction pour avoir les chiffres d'affaires selon la devise
     */
    public function getSaleRevenueByCurrency()
    {
        $revenue_by_currency = DB::table('credits_payments')
            ->select('currency', DB::raw('SUM(amount_paid) as revenue'))
            ->where('status', 'paid')
            ->groupBy('currency')
            ->get();

        return $revenue_by_currency;
    }

    /**
     * Fonction pour avoir les chiffres d'affaires selon le type de payement et la devise
     */
    public function getSaleRevenueByPayType()
    {
        $revenue_by_pay_type = DB::table('credits_payments')
            ->select(
                'payment_type',
                DB::raw("SUM(CASE WHEN currency = 'MGA' THEN amount_paid ELSE 0 END) as revenue_mga"),
                DB::raw("SUM(CASE WHEN currency = 'USD' THEN amount_paid ELSE 0 END) as revenue_usd")
            )
            ->where('status', 'paid')
            ->groupBy('payment_type')
            ->get();

        return $revenue_by_pay_type;
    }

    /**
     * Fonction pour avoir les chiffres d'affaires total selon la devise sans api
     */
    public function getTotalSaleRevenueWithoutApi()
    {
        $conversion_rate = 4000; // Taux fixe de 1 USD vers MGA

        $revenues = DB::table('credits_payments')
            ->selectRaw("SUM(CASE WHEN currency = 'MGA' THEN amount_paid ELSE 0 END) as revenue_mga")
            ->selectRaw("SUM(CASE WHEN currency = 'USD' THEN amount_paid ELSE 0 END) as revenue_usd")
            ->where('status', 'paid')
            ->first();

        // Convertir les revenus en USD vers MGA et calculer le total en MGA
        $total_revenue_in_mga = $revenues->revenue_mga + ($revenues->revenue_usd * $conversion_rate);

        return [
            'revenue_mga' => $revenues->revenue_mga,
            'revenue_usd' => $revenues->revenue_usd,
            'total_revenue_in_mga' => $total_revenue_in_mga
        ];
    }

    /**
     * Fonction pour avoir les chiffres d'affaires total selon la devise avec api (v2)
     */
    public function getTotalSaleRevenueWithApi()
    {
        try {
            // Obtenir le taux de conversion USD vers MGA via une API
            $response = Http::get('https://api.exchangerate-api.com/v4/latest/USD');

            if ($response->successful()) {
                $conversion_rate = $response->json()['rates']['MGA'] ?? 4600; // Valeur par défaut si la clé manque
            } else {
                throw new \Exception("Échec de l'API, utilisation de la valeur par défaut");
            }
        } catch (ConnectionException $e) {
            // Si la connexion Internet est indisponible
            $conversion_rate = 4600;
        } catch (\Exception $e) {
            // Gestion d'autres erreurs potentielles
            $conversion_rate = 4600;
        }

        // Récupérer les chiffres d'affaires en MGA et USD
        $revenues = DB::table('credits_payments')
            ->selectRaw("SUM(CASE WHEN currency = 'MGA' THEN amount_paid ELSE 0 END) as revenue_mga")
            ->selectRaw("SUM(CASE WHEN currency = 'USD' THEN amount_paid ELSE 0 END) as revenue_usd")
            ->where('status', 'paid')
            ->first();

        // Convertir les revenus USD en MGA et calculer le total en MGA
        $total_revenue_in_mga = $revenues->revenue_mga + ($revenues->revenue_usd * $conversion_rate);

        return [
            'conversion_rate' => $conversion_rate,
            'revenue_mga' => $revenues->revenue_mga,
            'revenue_usd' => $revenues->revenue_usd,
            'total_revenue_in_mga' => $total_revenue_in_mga
        ];
    }

    /**
     * Filtre par mois des chiffres d'affaire
     * 
     * @param $currency, $paymentType, $dateRange
     */
    public function getFilteredMonthSaleRevenue($currency = null, $paymentType = null, $dateRange = null)
    {
        $query = DB::table('credits_payments')
            ->where('status', 'paid');

        // Currency filter
        if ($currency && $currency !== 'all') {
            $query->where('currency', $currency);
        }

        // Payment Type filter
        if ($paymentType && $paymentType !== 'all') {
            $query->where('payment_type', $paymentType);
        }

        // Date Range filter
        if ($dateRange && $dateRange !== 'all') {
            if ($dateRange === 'year') {
                $query->whereYear('created_at', now()->year);
            } elseif ($dateRange === 'month') {
                $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            }
        }

        $monthly_revenue = $query
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month")
            ->selectRaw("SUM(CASE WHEN currency = 'MGA' THEN amount_paid ELSE 0 END) as revenue_mga")
            ->selectRaw("SUM(CASE WHEN currency = 'USD' THEN amount_paid ELSE 0 END) as revenue_usd")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return $monthly_revenue;
    }

    /**
     * Filtre par type de payements
     * 
     * @param $currency, $paymentType, $dateRange
     */
    public function getFilteredSaleRevenueByPayType($currency = null, $paymentType = null, $dateRange = null)
    {
        $query = DB::table('credits_payments')
            ->where('status', 'paid');

        // Currency filter
        if ($currency && $currency !== 'all') {
            $query->where('currency', $currency);
        }

        // Payment Type filter
        if ($paymentType && $paymentType !== 'all') {
            $query->where('payment_type', $paymentType);
        }

        // Date Range filter
        if ($dateRange && $dateRange !== 'all') {
            if ($dateRange === 'year') {
                $query->whereYear('created_at', now()->year);
            } elseif ($dateRange === 'month') {
                $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            }
        }

        $revenue_by_pay_type = $query
            ->select(
                'payment_type',
                DB::raw("SUM(CASE WHEN currency = 'MGA' THEN amount_paid ELSE 0 END) as revenue_mga"),
                DB::raw("SUM(CASE WHEN currency = 'USD' THEN amount_paid ELSE 0 END) as revenue_usd")
            )
            ->groupBy('payment_type')
            ->get();

        return $revenue_by_pay_type;
    }

    /**
     * Method for getting the invoice of a payment details
     * 
     * @param $idInvoice
     */
    public function getInvoicePayment($idInvoice)
    {
        try {
            // Trouver la facture ou lancer une exception
            $one_invoice = self::with('creditsPack')->findOrFail($idInvoice);

            // Récupérer l'utilisateur associé via user_id
            $user = User::find($one_invoice->user_id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found for this invoice'
                ], 404);
            }

            // Déterminer le rôle de l'utilisateur
            $user_data = [];
            if ($user->hasRole('Referent')) {
                // Si l'utilisateur est un Referent, chercher dans Customer
                $customer = Customer::where('idCustomer', $user->id)->first();

                if ($customer) {
                    $user_data = [
                        'name' => $customer->customerName,
                        'email' => $customer->customerEmail,
                        'phone' => $customer->customerPhone,
                        'address' => "{$customer->customer_addr_lot}, {$customer->customer_addr_quartier}, {$customer->customer_addr_rue}, {$customer->customer_addr_code_postal}",
                    ];
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Customer details not found for Referent user'
                    ], 404);
                }
            } elseif ($user->hasRole('Particulier')) {
                // Si l'utilisateur est un Particulier, chercher directement dans User
                $user_data = [
                    'name' => $user->firstName . ' ' . $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => "{$user->user_addr_lot}, {$user->user_addr_quartier}, {$user->user_addr_rue}, {$user->user_addr_code_postal}",
                ];
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'User role not recognized'
                ], 400);
            }

            // Ajouter les informations sur le pack de crédits
            $pack_data = $one_invoice->creditsPack ? [
                'type_pack' => $one_invoice->creditsPack->type_pack,
                'description_pack' => $one_invoice->creditsPack->description_pack,
                'credits' => $one_invoice->creditsPack->credits,
                'pack_price' => $one_invoice->creditsPack->pack_price,
                'currency' => $one_invoice->creditsPack->currency,
            ] : null;

            // Retourner la réponse avec les données enrichies
            return response()->json([
                'success' => true,
                'invoice' => $one_invoice,
                'user' => $user_data,
                'credits_pack' => $pack_data,
            ], 200);
        } catch (ModelNotFoundException $e) {
            // Gérer le cas où la facture n'est pas trouvée
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }
    }

    /**
     * Method for getting one credit's transaction details (v2)
     * 
     * @param int $idTransaction
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCreditsTransactionPurchase($idTransaction)
    {
        // Retrieve the transaction with the associated credits pack and user
        $transaction = CreditsPayment::with(['creditsPack', 'user'])
            ->where('idCreditPayment', $idTransaction)
            ->first();

        // Check if the transaction exists
        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found.',
            ], 404);
        }

        // Initialize variables for user/enterprise details
        $userDetails = null;

        if ($transaction->user) {
            // If user exists, check the role
            if ($transaction->user->hasRole('Referent')) {
                // Get enterprise data from the related Customer
                $customer = Customer::where('idCustomer', $transaction->user->id)->first();

                if ($customer) {
                    $userDetails = [
                        'type' => 'Referent',
                        'id' => $customer->idCustomer,
                        'name' => $customer->customerName,
                        'email' => $customer->customerEmail,
                        'phone' => $customer->customerPhone,
                        'nif' => $customer->nif,
                        'stat' => $customer->stat,
                        'siteWeb' => $customer->siteWeb,
                        'address' => [
                            'lot' => $customer->customer_addr_lot,
                            'quartier' => $customer->customer_addr_quartier,
                            'rue' => $customer->customer_addr_rue,
                            'code_postal' => $customer->customer_addr_code_postal,
                        ],
                    ];
                }
            } else {
                // If user is a regular individual
                $userDetails = [
                    'type' => 'Particulier',
                    'id' => $transaction->user->id,
                    'name' => $transaction->user->name,
                    'email' => $transaction->user->email,
                    'phone' => $transaction->user->phone,
                ];
            }
        }

        // Prepare the merged result
        $result = [
            'transaction' => [
                'id' => $transaction->idCreditPayment,
                'reference' => $transaction->reference,
                'amount_paid' => $transaction->amount_paid,
                'currency' => $transaction->currency,
                'payment_type' => $transaction->payment_type,
                'status' => $transaction->status,
            ],
            'credits_pack' => [
                'id' => $transaction->creditsPack->idPackCredit ?? null,
                'name' => $transaction->creditsPack->type_pack ?? null,
                'credits' => $transaction->creditsPack->credits ?? null,
                'price' => optional($transaction->creditsPack)->pack_price
                    ? optional($transaction->creditsPack)->pack_price . " " . optional($transaction->creditsPack)->currency
                    : null,
            ],
            'buyer' => $userDetails,
        ];

        // Return the response as JSON
        return response()->json([
            'success' => true,
            'data' => $result,
        ], 200);
    }
}
