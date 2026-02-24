<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CommissionsReceived extends Model
{
    use HasFactory;

    protected $table = 'commissions_received';
    protected $primaryKey = 'idCommissionReceived';

    protected $fillable = [
        'credit_payment_id',
        'commission_rate',
        'total_commission',
        'currency',
        'receiver_id',
    ];

    /**
     * Relation avec le modèle `CreditsPayment`.
     * Un enregistrement dans `commissions_received` appartient à un paiement spécifique.
     */
    public function creditPayment()
    {
        return $this->belongsTo(CreditsPayment::class, 'credit_payment_id', 'idCreditPayment');
    }

    /**
     * Relation avec le modèle `User` (si un utilisateur reçoit la commission).
     * Un enregistrement dans `commissions_received` peut avoir un bénéficiaire.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }

    /**
     * Create a new commission received record.
     *
     * @param array $data
     * @return CommissionsReceived
     */
    public static function createCommission(array $data): CommissionsReceived
    {
        return self::create($data);
    }

    /**
     * Retrieve all commissions and return as a JSON response with formatted date (v2) not used
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCommissions()
    {
        $commissions = self::with(['creditPayment', 'receiver'])->get();

        if ($commissions->isEmpty()) {
            return null;
        }

        $formattedCommissions = $commissions->map(function ($commission) {
            return [
                'idCommissionReceived' => $commission->idCommissionReceived,
                'credit_payment_id' => $commission->credit_payment_id,
                'commission_rate' => $commission->commission_rate,
                'total_commission' => $commission->total_commission,
                'currency' => $commission->currency,
                'receiver_id' => $commission->receiver_id,
                'commission_date' => $commission->created_at ? $commission->created_at->format('Y-m-d') : null,
                'receiver_name' => optional($commission->receiver)->name,
                'credit_payment_details' => $commission->creditPayment
            ];
        });

        return response()->json($formattedCommissions);
    }

    /**
     * Get a specific commission received record by ID.
     *
     * @param int $id (id de la commission)
     * @return CommissionsReceived
     * @throws ModelNotFoundException
     */
    public function getCommissionById(int $id)
    {
        $commission = self::with(['creditPayment', 'receiver'])
            ->where('idCommissionReceived', $id)
            ->get();

        if ($commission->isEmpty()) {
            return null;
        }

        $formattedCommission = $commission->map(function ($commission) {
            return [
                'idCommissionReceived' => $commission->idCommissionReceived,
                'credit_payment_id' => $commission->credit_payment_id,
                'commission_rate' => $commission->commission_rate,
                'total_commission' => $commission->total_commission,
                'currency' => $commission->currency,
                'receiver_id' => $commission->receiver_id,
                'commission_date' => $commission->created_at ? $commission->created_at->format('Y-m-d') : null,
                'receiver_name' => optional($commission->receiver)->name,
                'credit_payment_details' => $commission->creditPayment
            ];
        });

        return response()->json($formattedCommission);
    }

    /**
     * Update a specific commission received record by ID.
     *
     * @param int $id
     * @param array $data
     * @return CommissionsReceived
     * @throws ModelNotFoundException
     */
    public static function updateCommission(int $id, array $data): CommissionsReceived
    {
        $commission = self::findOrFail($id);
        $commission->update($data);
        return $commission;
    }

    /**
     * Delete a specific commission received record by ID.
     *
     * @param int $id
     * @return bool
     * @throws ModelNotFoundException
     */
    public static function deleteCommission(int $id): bool
    {
        $commission = self::findOrFail($id);
        return $commission->delete();
    }

    /**
     * Calculate and save commission for a credit payment
     * 
     * @param CreditsPayment $payment
     * @param User|null $referrer
     * @return CommissionsReceived|null
     */
    public static function calculateAndSaveCommission(CreditsPayment $payment, $referrer = null)
    {
        // Find the commission setting based on payment type and currency
        $commissionSetting = CommissionSettings::where('payment_type', $payment->payment_type)
            ->where('currency', $payment->currency)
            ->first();

        // If no commission setting found, return null
        if (!$commissionSetting) {
            return null;
        }

        // Calculate total commission
        $commissionRate = $commissionSetting->commission_rate;
        $totalCommission = $payment->amount_paid * ($commissionRate / 100);

        // Determine the receiver (could be a referrer or system default)
        $receiverId = $referrer ? $referrer->id : null;

        // Create commission record
        return self::create([
            'credit_payment_id' => $payment->idCreditPayment,
            'commission_rate' => $commissionRate,
            'total_commission' => $totalCommission,
            'currency' => $payment->currency,
            'receiver_id' => $receiverId
        ]);
    }

    /**
     * Scope to filter commissions by month and year
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $month
     * @param int|null $year
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByMonthYear($query, $month = null, $year = null)
    {
        if ($month !== null) {
            $query->whereMonth('created_at', $month);
        }

        if ($year !== null) {
            $query->whereYear('created_at', $year);
        }

        return $query;
    }

    /**
     * Get distinct years with commissions
     * 
     * @return array
     */
    public function getDistinctCommissionYears()
    {
        return self::select(DB::raw('DISTINCT YEAR(created_at) as year'))
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
    }

    /**
     * Get commission dashboard data
     * 
     * @param int|null $month
     * @param int|null $year
     * @return array
     */
    public function getCommissionDashboardData($month = null, $year = null)
    {
        $query = self::with(['creditPayment', 'receiver'])
            ->filterByMonthYear($month, $year);

        $totalCommissions = $query->sum('total_commission');
        $totalCommissionsCount = $query->count();

        // Group commissions by currency
        $commissionsByCurrency = $query->get()
            ->groupBy('currency')
            ->map(function ($currencyCommissions) {
                return [
                    'total_amount' => $currencyCommissions->sum('total_commission'),
                    'count' => $currencyCommissions->count()
                ];
            });

        return [
            'total_commissions' => $totalCommissions,
            'total_commissions_count' => $totalCommissionsCount,
            'commissions_by_currency' => $commissionsByCurrency
        ];
    }

    /**
     * Get monthly commissions for chart
     * 
     * @param int $year
     * @return array
     */
    public function getMonthlyCommissionsForChart($year)
    {
        $monthlyCommissions = self::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total_commission) as total_commission'),
            DB::raw('COUNT(*) as commission_count')
        )
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();

        // Prepare data for Chart.js
        $labels = [];
        $commissionsData = [];
        $countsData = [];

        for ($month = 1; $month <= 12; $month++) {
            $labels[] = date('F', mktime(0, 0, 0, $month, 1));

            $monthData = $monthlyCommissions->firstWhere('month', $month);
            $commissionsData[] = $monthData ? floatval($monthData->total_commission) : 0;
            $countsData[] = $monthData ? intval($monthData->commission_count) : 0;
        }

        return [
            'labels' => $labels,
            'commissionsData' => $commissionsData,
            'countsData' => $countsData
        ];
    }
}
