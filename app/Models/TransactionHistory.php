<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionHistory extends Model
{
    use HasFactory;

    protected $table = "transaction_history";
    protected $primaryKey = "idTransaction";

    protected $fillable = [
        'idUser',
        'transaction_ref',
        'montant',
        'typeTransaction',
        'description',
    ];

    /**
     * Function for getting all transactions related to credits. (v3)
     *
     * @param int|null $id (User ID optional).
     * @param string|null $dateTransaction Date of the transaction in 'Y-m-d' format (optional).
     * @return \Illuminate\Support\Collection Resulting transactions.
     */
    public function getCreditTransaction($id = null, $dateTransaction = null)
    {
        $query = DB::table('v_credit_transactions')
            ->select(
                'transactionId',
                'userId',
                'transaction_ref',
                'transactionAmount',
                'typeTransaction',
                'companyDescription',
                'transactionDate', // Date of the transaction (filtered)
                'created_at'       // Full timestamp for the transaction
            );

        // Filter by user ID if provided
        if ($id !== null) {
            $query->where('userId', $id); // Filter by userId, which corresponds to the ID of the user/company
        }

        // Filter by transaction date if provided
        if ($dateTransaction !== null) {
            $query->where('transactionDate', $dateTransaction); // Filter by date
        }

        $results = $query->get(); // Execute the query and get the results

        if ($results->isEmpty()) {
            return null;
        }

        // Enrich results with buyer names
        foreach ($results as $transaction) {
            $user = User::find($transaction->userId); // Fetch the user model
            if ($user) {
                if ($user->hasRole('Referent')) {
                    // If the user has the "Referent" role, fetch the name from the Customer model
                    $customer = Customer::where('idCustomer', $transaction->userId)->first();
                    $transaction->userName = $customer ? $customer->customerName : 'Unknown Customer';
                } else {
                    // Otherwise, use the User's name and first name
                    $transaction->userName = $user->firstName . ' ' . $user->name;
                }
            } else {
                $transaction->userName = 'Unknown User';
            }
        }

        return $results;
    }

    /**
     * Function for getting all transactions related to debits. (v2)
     * 
     * @param int|null $id User ID (company or employee, optional).
     * @param string|null $dateTransaction Date of the transaction in 'Y-m-d' format (optional).
     * @return \Illuminate\Support\Collection Resulting transactions.
     */
    public function getDebitTransaction($id = null, $dateTransaction = null)
    {
        // Query the v_debit_transactions view
        $query = DB::table('v_debit_transactions')
            ->select(
                'transactionId',
                'userId',
                'transaction_ref',
                'transactionAmount',
                'typeTransaction',
                'companyDescription',
                'transactionDate',
                'created_at',
                'employeeId',
                'employeeDebitAmount',
                'employeeDescription',
                'particularId',
                'particularDebitAmount'
            );

        // Filter by user ID (company or employee transactions)
        if ($id !== null) {
            $query->where(function ($subQuery) use ($id) {
                $subQuery->where('userId', $id) // Company ID
                    ->orWhere('employeeId', $id); // Employee ID
            });
        }

        // Filter by transaction date
        if ($dateTransaction !== null) {
            $query->where('transactionDate', $dateTransaction);
        }

        // Return the resulting collection
        $results = $query->get();

        if ($results->isEmpty()) {
            return null;
        }

        // Enrich results with buyer names
        foreach ($results as $transaction) {
            $user = User::find($transaction->userId); // Fetch the user model
            if ($user) {
                if ($user->hasRole('Referent')) {
                    // If the user has the "Referent" role, fetch the name from the Customer model
                    $customer = Customer::where('idCustomer', $transaction->userId)->first();
                    $transaction->userName = $customer ? $customer->customerName : 'Unknown Customer';
                } else {
                    // Otherwise, use the User's name and first name
                    $transaction->userName = $user->firstName . ' ' . $user->name;
                }
            } else {
                $transaction->userName = 'Unknown User';
            }
        }

        return $results;
    }

    /**
     * Get monthly data grouped by year-month and summarized by credit and debit totals.
     *
     * @param \Illuminate\Database\Eloquent\Collection $transactions
     * @return array
     */
    public function getMonthlyData($transactions)
    {
        return $transactions->groupBy(function ($item) {
            return Carbon::parse($item->created_at)->format('Y-m');
        })->map(function ($group) {
            return [
                'credit' => $group->where('typeTransaction', 'credit')->sum('montant'),
                'debit'  => $group->where('typeTransaction', 'debit')->sum('montant'),
            ];
        })->sortKeys()->toArray();
    }

    /**
     * Get transaction data grouped by type (e.g., credit, debit) or other categories.
     *
     * @param \Illuminate\Database\Eloquent\Collection $transactions
     * @return array
     */
    public function getCategoryData($transactions)
    {
        return $transactions->groupBy('typeTransaction')->map->count()->toArray();
    }

    /**
     * Get filtered transactions based on the provided filters.
     *
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public function getFilteredTransactions(array $filters)
    {
        $query = $this->newQuery();

        // Apply filters
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        if (!empty($filters['type'])) {
            $query->where('typeTransaction', $filters['type']);
        }

        if (!empty($filters['userName'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'LIKE', '%' . $filters['userName'] . '%');
            });
        }

        // Fetch results
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get transactions based on authenticated user roles
     * 
     * @return array
     */
    public function getTransactionsByUserRole()
    {
        $user = Auth::user();
        $userId = $user->id;

        // Get user roles
        $userRoles = DB::table('roles')
            ->join('role_users', 'roles.id', '=', 'role_users.role_id')
            ->where('role_users.user_id', $userId)
            ->where('role_users.isActive', 1)
            ->where('role_users.hasRole', 1)
            ->pluck('roleName')
            ->toArray();

        $creditTransactions = [];
        $debitTransactions = [];

        // Handle credit transactions - only for Referent and Particulier
        if (in_array('Referent', $userRoles) || in_array('Particulier', $userRoles)) {
            $creditTransactions = DB::table('v_credit_transactions')
                ->where('userId', $userId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
        }

        // Handle debit transactions based on user role
        if (!empty($userRoles)) {
            $query = DB::table('v_debit_transactions');

            if (in_array('Referent', $userRoles)) {
                // Referent can see transactions for their employees
                $query->where(function ($q) use ($userId) {
                    $q->where('userId', $userId)
                        ->orWhere('employeeId', $userId);
                });
            } elseif (in_array('Particulier', $userRoles)) {
                // Particulier can see their own transactions where particularId is set
                $query->where('particularId', $userId);
            } elseif (
                in_array('Employe', $userRoles) ||
                in_array('EmployeCfp', $userRoles) ||
                in_array('EmployeEtp', $userRoles)
            ) {
                // Employees can see transactions where they are the employee
                $query->where('employeeId', $userId);
            }

            $debitTransactions = $query->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
        }

        return [
            'creditTransactions' => $creditTransactions,
            'debitTransactions' => $debitTransactions
        ];
    }
}
