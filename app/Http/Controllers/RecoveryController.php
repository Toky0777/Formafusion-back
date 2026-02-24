<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\Customer;

class RecoveryController extends Controller
{
    public function index()
    {
        $customerId = Customer::idCustomer();
        $now = Carbon::now();

        $dates = $this->getDateSlices($now);

        $dueByCustomerByInvoice = $this->getDueAmountsByEntrepriseAndDateRange($customerId, $dates);
        $dueByCustomerByPurchaseOrder = $this->getDuePurchaseOrder();

        $totalDueBySliceByInvoice = (array) $this->getTotalDueBySlice();
        $totalDueBySliceByPurchaseOrder = (array) $this->totalDuePurchaseOrder();

        $dueByCustomer = $this->mergeAndSumObjects([$dueByCustomerByInvoice, $dueByCustomerByPurchaseOrder], 'idEntreprise');

        $totalDueBySlice = $this->mergeAndSumArrays([$totalDueBySliceByInvoice, $totalDueBySliceByPurchaseOrder]);

        return response()->json([
            'due_by_customer' => array_values($dueByCustomer),
            'total_due_by_slice' => $totalDueBySlice
        ], 200);
    }

    private function mergeAndSumObjects(array $collections, string $keyField)
    {
        $result = [];

        foreach ($collections as $collection) {
            $arrayItems = json_decode(json_encode($collection), true);

            foreach ($arrayItems as $item) {
                $id = $item[$keyField] ?? null;
                if (!$id) continue;

                if (!isset($result[$id])) {
                    $result[$id] = $item;
                } else {
                    foreach ($item as $key => $value) {
                        if ($key === $keyField) continue;

                        if (is_numeric($value)) {
                            $result[$id][$key] += $value;
                        }
                    }
                }
            }
        }

        return $result;
    }

    private function mergeAndSumArrays(array $arrays)
    {
        $result = [];
        $allKeys = [];

        foreach ($arrays as $arr) {
            if (!is_array($arr)) continue;
            $allKeys = array_merge($allKeys, array_keys($arr));
        }

        $allKeys = array_unique($allKeys);

        foreach ($allKeys as $key) {
            $sum = 0;
            foreach ($arrays as $arr) {
                $value = $arr[$key] ?? 0;
                $sum += is_numeric($value) ? $value : 0;
            }
            $result[$key] = $sum;
        }

        return $result;
    }

    private function getDueAmountsByEntrepriseAndDateRange($customerId, $dates)
    {
        $paymentSubquery = "(SELECT IFNULL(SUM(amount), 0) FROM invoice_payments p WHERE p.invoice_id = invoices.idInvoice)";

        $invoices = Invoice::query()
            ->join('customers', 'invoices.idEntreprise', 'customers.idCustomer')
            ->select(
                'invoices.idEntreprise',
                'customers.customerName',
                DB::raw(" 
                    CASE 
                        WHEN invoice_date_pm < ? THEN 'due_before_60' 
                        WHEN invoice_date_pm BETWEEN ? AND ? THEN 'due_between_60_30' 
                        WHEN invoice_date_pm BETWEEN ? AND ? THEN 'due_between_30_15' 
                        WHEN invoice_date_pm BETWEEN ? AND ? THEN 'due_between_15_0' 
                        WHEN invoice_date_pm BETWEEN ? AND ? THEN 'due_between_0_15' 
                        WHEN invoice_date_pm BETWEEN ? AND ? THEN 'due_between_15_30' 
                        WHEN invoice_date_pm BETWEEN ? AND ? THEN 'due_between_31_60' 
                        WHEN invoice_date_pm > ? THEN 'due_after_60' 
                        ELSE 'other' END as due_category "),
                DB::raw("SUM(invoices.invoice_total_amount - {$paymentSubquery}) as due_amount")
            )
            ->where('invoices.idCustomer', $customerId)
            ->whereRaw("(invoices.invoice_total_amount - {$paymentSubquery}) != 0")
            ->doesntHave('deletedInvoices')
            ->whereNotIn('invoice_status', [1, 4, 9])
            ->standard()
            ->groupBy('invoices.idEntreprise', 'due_category')
            ->setBindings(
                [
                    $dates['minus60'],
                    $dates['minus60'],
                    $dates['minus30'],
                    $dates['minus30'],
                    $dates['minus15'],
                    $dates['minus15'],
                    $dates['now'] ?? Carbon::now()->format('Y-m-d H:i:s'),
                    $dates['now'] ?? Carbon::now()->format('Y-m-d H:i:s'),
                    $dates['plus15'],
                    $dates['plus15'],
                    $dates['plus30'],
                    $dates['plus30'],
                    $dates['plus60'],
                    $dates['plus60'],
                ],
                'select'
            )->get();

        $result = [];
        $entrepriseIds = $invoices->pluck('idEntreprise')->unique();

        foreach ($entrepriseIds as $id) {
            $entrepriseName = $invoices->firstWhere('idEntreprise', $id)->customerName;

            $result[] = [
                'idEntreprise' => $id,
                'entreprise_name' => $entrepriseName,
                'due_before_60' => $this->extractDueAmount($invoices, $id, 'due_before_60'),
                'due_minus60_30' => $this->extractDueAmount($invoices, $id, 'due_between_60_30'),
                'due_minus30_15' => $this->extractDueAmount($invoices, $id, 'due_between_30_15'),
                'due_minus15_0' => $this->extractDueAmount($invoices, $id, 'due_between_15_0'),
                'due_0_15' => $this->extractDueAmount($invoices, $id, 'due_between_0_15'),
                'due_16_30' => $this->extractDueAmount($invoices, $id, 'due_between_15_30'),
                'due_31_60' => $this->extractDueAmount($invoices, $id, 'due_between_31_60'),
                'due_after_60' => $this->extractDueAmount($invoices, $id, 'due_after_60'),
                'total_due' => $this->extractDueAmount($invoices, $id, 'due_before_60')
                    + $this->extractDueAmount($invoices, $id, 'due_between_60_30')
                    + $this->extractDueAmount($invoices, $id, 'due_between_30_15')
                    + $this->extractDueAmount($invoices, $id, 'due_between_15_0'),
                'total_all' => $invoices->where('idEntreprise', $id)->sum('due_amount'),
            ];
        }

        return $result;
    }


    private function extractDueAmount($invoices, $entrepriseId, $category)
    {
        $invoice = $invoices->first(function ($invoice) use ($entrepriseId, $category) {
            return $invoice->idEntreprise == $entrepriseId && $invoice->due_category == $category;
        });

        return $invoice ? (float) $invoice->due_amount : 0;
    }

    private function getTotalDueBySlice()
    {
        $now = Carbon::now();
        $dates = $this->getDateSlices($now);

        $paymentSubquery = "(SELECT IFNULL(SUM(amount), 0) FROM invoice_payments p WHERE p.invoice_id = invoices.idInvoice)";

        return DB::table('invoices')
            ->select(DB::raw($this->buildDueAmountSQL($dates, $now, $paymentSubquery)))
            ->where('invoices.idCustomer', Customer::idCustomer())
            ->where('idTypeFacture', 1)
            ->whereNotIn('invoices.invoice_status', [1, 4, 9])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('invoice_deleted')
                    ->whereColumn('invoice_deleted.idInvoice', 'invoices.idInvoice');
            })
            ->first();
    }


    private function getDuePurchaseOrder()
    {
        $now = Carbon::now();
        $dates = $this->getDateSlices($now);

        return DB::table('bon_commandes as bc')
            ->select(
                DB::raw($this->buildDueRecoveryAmountSQL($dates, $now)),
                'i.idEntreprise',
                'cu.customerName as entreprise_name'
            )
            ->join('invoices as i', 'bc.idDevis', '=', 'i.idInvoice')
            ->join('customers as cu', 'i.idEntreprise', '=', 'cu.idCustomer')
            ->join('bc_status as bcs', 'bc.idStatus', '=', 'bcs.idStatus')
            ->leftJoin('bc_contacts as c', 'bc.idContact', '=', 'c.idContact')
            ->leftJoin('invoices as inv_bc', function ($join) {
                $join->on('inv_bc.invoice_bc', '=', 'bc.numero');
            })
            ->where('bc.idCfp', Customer::idCustomer())
            ->whereIn('bc.idStatus', [1, 2, 3, 4])
            ->whereNull('inv_bc.invoice_bc')
            ->groupBy('cu.idCustomer')
            ->get();
    }

    private function totalDuePurchaseOrder()
    {
        $now = Carbon::now();
        $dates = $this->getDateSlices($now);

        return DB::table('bon_commandes as bc')
            ->select(DB::raw($this->buildDueRecoveryAmountSQL($dates, $now)))
            ->join('invoices as i', 'bc.idDevis', '=', 'i.idInvoice')
            ->join('customers as cu', 'i.idEntreprise', '=', 'cu.idCustomer')
            ->join('bc_status as bcs', 'bc.idStatus', '=', 'bcs.idStatus')
            ->leftJoin('bc_contacts as c', 'bc.idContact', '=', 'c.idContact')
            ->leftJoin('invoices as inv_bc', function ($join) {
                $join->on('inv_bc.invoice_bc', '=', 'bc.numero');
            })
            ->where('bc.idCfp', Customer::idCustomer())
            ->whereNull('inv_bc.invoice_bc')
            ->whereIn('bc.idStatus', [1, 2, 3, 4])
            ->first();
    }

    private function getDateSlices(Carbon $now)
    {
        return [
            'minus60' => $now->copy()->subDays(60)->format('Y-m-d H:i:s'),
            'minus30' => $now->copy()->subDays(30)->format('Y-m-d H:i:s'),
            'minus15' => $now->copy()->subDays(15)->format('Y-m-d H:i:s'),
            'plus15'  => $now->copy()->addDays(15)->format('Y-m-d H:i:s'),
            'plus30'  => $now->copy()->addDays(30)->format('Y-m-d H:i:s'),
            'plus60'  => $now->copy()->addDays(60)->format('Y-m-d H:i:s'),
        ];
    }

    private function buildDueAmountSQL(array $dates, Carbon $now, string $paymentSubquery): string
    {
        $nowStr = $now->format('Y-m-d H:i:s');

        return "
        SUM(CASE WHEN invoice_date_pm < '{$dates['minus60']}' 
            THEN (invoice_total_amount - {$paymentSubquery}) ELSE 0 END) AS due_before_60,
        SUM(CASE WHEN invoice_date_pm BETWEEN '{$dates['minus60']}' AND '{$dates['minus30']}' 
            THEN (invoice_total_amount - {$paymentSubquery}) ELSE 0 END) AS due_minus60_30,
        SUM(CASE WHEN invoice_date_pm BETWEEN '{$dates['minus30']}' AND '{$dates['minus15']}' 
            THEN (invoice_total_amount - {$paymentSubquery}) ELSE 0 END) AS due_minus30_15,
        SUM(CASE WHEN invoice_date_pm BETWEEN '{$dates['minus15']}' AND '{$nowStr}' 
            THEN (invoice_total_amount - {$paymentSubquery}) ELSE 0 END) AS due_minus15_0,
        SUM(CASE WHEN invoice_date_pm BETWEEN '{$nowStr}' AND '{$dates['plus15']}' 
            THEN (invoice_total_amount - {$paymentSubquery}) ELSE 0 END) AS due_0_15,
        SUM(CASE WHEN invoice_date_pm BETWEEN '{$dates['plus15']}' AND '{$dates['plus30']}' 
            THEN (invoice_total_amount - {$paymentSubquery}) ELSE 0 END) AS due_16_30,
        SUM(CASE WHEN invoice_date_pm BETWEEN '{$dates['plus30']}' AND '{$dates['plus60']}' 
            THEN (invoice_total_amount - {$paymentSubquery}) ELSE 0 END) AS due_31_60,
        SUM(CASE WHEN invoice_date_pm > '{$dates['plus60']}' 
            THEN (invoice_total_amount - {$paymentSubquery}) ELSE 0 END) AS due_after_60,
        SUM(CASE WHEN invoice_date_pm < '{$nowStr}' 
            THEN (invoice_total_amount - {$paymentSubquery}) ELSE 0 END) AS total_due,
        SUM(invoice_total_amount - {$paymentSubquery}) AS total_all
        ";
    }

    private function buildDueRecoveryAmountSQL(array $dates, Carbon $now): string
    {
        $nowStr = $now->format('Y-m-d H:i:s');

        return "
        SUM(
            CASE
                WHEN COALESCE(
                    DATE_ADD(bc.date_fin, INTERVAL COALESCE(bc.modalite, 0) DAY),
                    DATE_ADD(i.invoice_date, INTERVAL 60 DAY)
                ) < '{$dates['minus60']}' THEN bc.montant
                ELSE 0
            END
        ) AS due_before_60,

        SUM(
            CASE
                WHEN COALESCE(
                    DATE_ADD(bc.date_fin, INTERVAL COALESCE(bc.modalite, 0) DAY),
                    DATE_ADD(i.invoice_date, INTERVAL 60 DAY)
                ) BETWEEN '{$dates['minus60']}' AND '{$dates['minus30']}' THEN bc.montant
                ELSE 0
            END
        ) AS due_minus60_30,

        SUM(
            CASE
                WHEN COALESCE(
                    DATE_ADD(bc.date_fin, INTERVAL COALESCE(bc.modalite, 0) DAY),
                    DATE_ADD(i.invoice_date, INTERVAL 60 DAY)
                ) BETWEEN '{$dates['minus30']}' AND '{$dates['minus15']}' THEN bc.montant
                ELSE 0
            END
        ) AS due_minus30_15,

        SUM(
            CASE
                WHEN COALESCE(
                    DATE_ADD(bc.date_fin, INTERVAL COALESCE(bc.modalite, 0) DAY),
                    DATE_ADD(i.invoice_date, INTERVAL 60 DAY)
                ) BETWEEN '{$dates['minus15']}' AND '{$nowStr}' THEN bc.montant
                ELSE 0
            END
        ) AS due_minus15_0,

        SUM(
            CASE
                WHEN COALESCE(
                    DATE_ADD(bc.date_fin, INTERVAL COALESCE(bc.modalite, 0) DAY),
                    DATE_ADD(i.invoice_date, INTERVAL 60 DAY)
                ) BETWEEN '{$nowStr}' AND '{$dates['plus15']}' THEN bc.montant
                ELSE 0
            END
        ) AS due_0_15,

        SUM(
            CASE
                WHEN COALESCE(
                    DATE_ADD(bc.date_fin, INTERVAL COALESCE(bc.modalite, 0) DAY),
                    DATE_ADD(i.invoice_date, INTERVAL 60 DAY)
                ) BETWEEN '{$dates['plus15']}' AND '{$dates['plus30']}' THEN bc.montant
                ELSE 0
            END
        ) AS due_16_30,

        SUM(
            CASE
                WHEN COALESCE(
                    DATE_ADD(bc.date_fin, INTERVAL COALESCE(bc.modalite, 0) DAY),
                    DATE_ADD(i.invoice_date, INTERVAL 60 DAY)
                ) BETWEEN '{$dates['plus30']}' AND '{$dates['plus60']}' THEN bc.montant
                ELSE 0
            END
        ) AS due_31_60,

        SUM(
            CASE
                WHEN COALESCE(
                    DATE_ADD(bc.date_fin, INTERVAL COALESCE(bc.modalite, 0) DAY),
                    DATE_ADD(i.invoice_date, INTERVAL 60 DAY)
                ) > '{$dates['plus60']}' THEN bc.montant
                ELSE 0
            END
        ) AS due_after_60,

        SUM(
            CASE
                WHEN COALESCE(
                    DATE_ADD(bc.date_fin, INTERVAL COALESCE(bc.modalite, 0) DAY),
                    DATE_ADD(i.invoice_date, INTERVAL 60 DAY)
                ) < '{$nowStr}' THEN bc.montant
                ELSE 0
            END
        ) AS total_due,

        SUM(bc.montant) AS total_all
    ";
    }



    public function getInvoiceUnpaidByCustomer($idEntreprise)
    {
        $paymentSubquery = "(SELECT IFNULL(SUM(amount), 0) FROM invoice_payments p WHERE p.invoice_id = invoices.idInvoice)";

        $invoices = Invoice::query()
            ->with(['particulier', 'status', 'payments', 'company'])
            ->leftJoin('invoice_contacts as ic', 'invoices.idContact', '=', 'ic.idContact')
            ->select(
                'invoices.idInvoice',
                'invoices.invoice_number',
                'invoices.invoice_date',
                'invoices.invoice_date_pm',
                'invoices.invoice_total_amount',
                'invoices.invoice_status',
                DB::raw("({$paymentSubquery}) AS total_paid"),
                DB::raw("(invoices.invoice_total_amount - {$paymentSubquery}) AS remaining_amount"),
                'ic.contact_name',
                'ic.contact_mail',
                'ic.contact_phone'
            )->where('invoices.idCustomer', Customer::idCustomer())
            ->where('invoices.idEntreprise', $idEntreprise)
            ->whereRaw("(invoices.invoice_total_amount - {$paymentSubquery}) != 0")
            ->doesntHave('deletedInvoices')
            ->whereNotIn('invoice_status', [1, 4, 9])
            ->standard()
            ->orderByDesc('invoices.invoice_date_pm')
            ->get();

        return response()->json([
            'status' => 'success',
            'entreprise_id' => $idEntreprise,
            'count' => $invoices->count(),
            'invoices' => $invoices
        ]);
    }

    public function getPurchaseOrderByCustomer($idEntreprise)
    {
        $purchaseOrders = DB::table('bon_commandes as bc')
            ->select([
                'bc.idBC',
                'bc.idDevis',
                'bcs.idStatus',
                'bcs.status_name',
                'bcs.status_color',
                'i.invoice_number as numero_devis',
                'bc.numero as numero_bc',
                'i.invoice_total_amount as montant_devis',
                'bc.montant as montant_bc',
                'i.invoice_date as date_devis',
                'bc.date as date_bc',
                'cu.idCustomer as idEentreprise',
                'cu.customerName as etp_name',
                'cu.customerEmail as etp_email',
                'c.contact_name',
                'c.contact_mail',
                'c.contact_phone',
                'bc.idCfp',
                DB::raw('CASE
                    WHEN bc.date_fin IS NULL THEN DATE_ADD(i.invoice_date, INTERVAL 60 DAY)
                        ELSE bc.date_fin
                    END as date_paiement')
            ])
            ->join('invoices as i', 'bc.idDevis', '=', 'i.idInvoice')
            ->join('customers as cu', 'i.idEntreprise', '=', 'cu.idCustomer')
            ->join('bc_status as bcs', 'bc.idStatus', '=', 'bcs.idStatus')
            ->leftJoin('bc_contacts as c', 'bc.idContact', '=', 'c.idContact')
            ->leftJoin('invoices as inv_bc', function ($join) {
                $join->on('inv_bc.invoice_bc', '=', 'bc.numero');
            })
            ->where('cu.idCustomer', $idEntreprise)
            ->whereIn('bc.idStatus', [1, 2, 3, 4])
            ->whereNull('inv_bc.invoice_bc')
            ->where('bc.idCfp', Customer::idCustomer())
            ->get();

        return response()->json([
            'status' => 'success',
            'entreprise_id' => $idEntreprise,
            'purchase_orders' => $purchaseOrders,
            'count' => $purchaseOrders->count(),
        ], 200);
    }

    public function getInvoiceUnpaidByCustomerWithDate($idEntreprise, $date)
    {
        $now = Carbon::now();
        $dates = $this->getDateSlices($now);

        $paymentSubquery = "(SELECT IFNULL(SUM(amount), 0) FROM invoice_payments p WHERE p.invoice_id = invoices.idInvoice)";

        $invoices = Invoice::query()
            ->with(['particulier', 'status', 'payments', 'company'])
            ->leftJoin('invoice_contacts as ic', 'invoices.idContact', '=', 'ic.idContact')
            ->select(
                'invoices.idInvoice',
                'invoices.invoice_number',
                'invoices.invoice_date',
                'invoices.invoice_date_pm',
                'invoices.invoice_total_amount',
                'invoices.invoice_status',
                DB::raw("({$paymentSubquery}) AS total_paid"),
                DB::raw("(invoices.invoice_total_amount - {$paymentSubquery}) AS remaining_amount"),
                'ic.contact_name',
                'ic.contact_mail',
                'ic.contact_phone'
            )
            ->where('invoices.idEntreprise', $idEntreprise)
            ->whereNotIn('invoice_status', [1, 4, 9])
            ->doesntHave('deletedInvoices')
            ->standard()
            ->whereRaw("(invoices.invoice_total_amount - {$paymentSubquery}) > 0");

        $this->applyDateFilter($invoices, 'invoices.invoice_date_pm', $date, $dates, $now);

        $invoices = $invoices
            ->orderByDesc('invoices.invoice_date_pm')
            ->get();

        return response()->json([
            'status' => 'success',
            'entreprise_id' => $idEntreprise,
            'count' => $invoices->count(),
            'invoices' => $invoices,
        ]);
    }

    public function getPurchaseOrderByCustomerWithDate($idEntreprise, $date)
    {
        $now = Carbon::now();
        $dates = $this->getDateSlices($now);

        $purchaseOrders = DB::table('bon_commandes as bc')
            ->select([
                'bc.idBC',
                'bc.idDevis',
                'bcs.idStatus',
                'bcs.status_name',
                'bcs.status_color',
                'i.invoice_number as numero_devis',
                'bc.numero as numero_bc',
                'i.invoice_total_amount as montant_devis',
                'bc.montant as montant_bc',
                'i.invoice_date as date_devis',
                'bc.date as date_bc',
                'cu.idCustomer as idEntreprise',
                'cu.customerName as etp_name',
                'cu.customerEmail as etp_email',
                'c.contact_name',
                'c.contact_mail',
                'c.contact_phone',
                'bc.idCfp',
                DB::raw('CASE
                    WHEN bc.date_fin IS NULL THEN DATE_ADD(i.invoice_date, INTERVAL 60 DAY)
                        ELSE DATE_ADD(bc.date_fin, INTERVAL COALESCE(bc.modalite, 0) DAY)
                    END as date_paiement')
            ])
            ->join('invoices as i', 'bc.idDevis', '=', 'i.idInvoice')
            ->join('customers as cu', 'i.idEntreprise', '=', 'cu.idCustomer')
            ->join('bc_status as bcs', 'bc.idStatus', '=', 'bcs.idStatus')
            ->leftJoin('bc_contacts as c', 'bc.idContact', '=', 'c.idContact')
            ->leftJoin('invoices as inv_bc', function ($join) {
                $join->on('inv_bc.invoice_bc', '=', 'bc.numero');
            })
            ->where('i.idEntreprise', $idEntreprise)
            ->whereNull('inv_bc.invoice_bc')
            ->whereIn('bc.idStatus', [1, 2, 3, 4]);

        $this->applyDateFilter($purchaseOrders, 'date_paiement', $date, $dates);

        $purchaseOrders = $purchaseOrders
            ->orderByDesc('bc.date_fin')
            ->get();

        return response()->json([
            'status' => 'success',
            'entreprise_id' => $idEntreprise,
            'count' => $purchaseOrders->count(),
            'purchase_orders' => $purchaseOrders,
        ], 200);
    }


    private function applyDateFilter($query, string $column, string $dateKey, array $dates): void
    {
        if ($column === 'date_paiement') {
            $expr = "CASE
                    WHEN bc.date_fin IS NULL THEN DATE_ADD(i.invoice_date, INTERVAL 60 DAY)
                        ELSE DATE_ADD(bc.date_fin, INTERVAL COALESCE(bc.modalite, 0) DAY)
                    END";
        } else {
            $expr = $column;
        }

        switch ($dateKey) {
            case 'due_before_60':
                $query->whereRaw("$expr < ?", [$dates['minus60']]);
                break;

            case 'due_minus60_30':
                $query->whereRaw("$expr BETWEEN ? AND ?", [$dates['minus60'], $dates['minus30']]);
                break;

            case 'due_minus30_15':
                $query->whereRaw("$expr BETWEEN ? AND ?", [$dates['minus30'], $dates['minus15']]);
                break;

            case 'due_minus15_0':
                $query->whereRaw("$expr BETWEEN ? AND ?", [$dates['minus15'], now()]);
                break;

            case 'due_0_15':
                $query->whereRaw("$expr BETWEEN ? AND ?", [now(), $dates['plus15']]);
                break;

            case 'due_16_30':
                $query->whereRaw("$expr BETWEEN ? AND ?", [$dates['plus15'], $dates['plus30']]);
                break;

            case 'due_31_60':
                $query->whereRaw("$expr BETWEEN ? AND ?", [$dates['plus30'], $dates['plus60']]);
                break;

            case 'due_after_60':
                $query->whereRaw("$expr > ?", [$dates['plus60']]);
                break;
        }
    }
}
