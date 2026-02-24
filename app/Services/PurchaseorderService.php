<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PurchaseorderService
{
    public function getPurchaseOrderByKey($key, $customerId)
    {
        return DB::table('bon_commandes as bc')
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
                'cu.idCustomer as idEtp',
                'cu.customerName as etp_name',
                'cu.customerEmail as etp_email',
                'c.contact_name',
                'c.contact_mail',
                'c.contact_phone',
                'bc.idCfp'
            ])
            ->join('invoices as i', 'bc.idDevis', '=', 'i.idInvoice')
            ->join('customers as cu', 'i.idEntreprise', '=', 'cu.idCustomer')
            ->join('bc_status as bcs', 'bc.idStatus', '=', 'bcs.idStatus')
            ->leftJoin('bc_contacts as c', 'bc.idContact', '=', 'c.idContact')
            ->where(function ($query) use ($key) {
                $query->where('cu.customerName', 'like', "%$key%")
                    ->orWhere('bc.numero', 'like', "%$key%");
            })
            ->where('bc.idCfp', $customerId)
            ->get();
    }

    public function countPurchaseOrderByKey($key, $customerId)
    {
        $purchaseOrder = DB::table('bon_commandes as bc')
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
                'cu.idCustomer as idEtp',
                'cu.customerName as etp_name',
                'cu.customerEmail as etp_email',
                'c.contact_name',
                'c.contact_mail',
                'c.contact_phone',
                'bc.idCfp'
            ])
            ->join('invoices as i', 'bc.idDevis', '=', 'i.idInvoice')
            ->join('customers as cu', 'i.idEntreprise', '=', 'cu.idCustomer')
            ->join('bc_status as bcs', 'bc.idStatus', '=', 'bcs.idStatus')
            ->leftJoin('bc_contacts as c', 'bc.idContact', '=', 'c.idContact')
            ->where(function ($query) use ($key) {
                $query->where('cu.customerName', 'like', "%$key%")
                    ->orWhere('bc.numero', 'like', "%$key%");
            })
            ->where('bc.idCfp', $customerId)
            ->get();

        return count($purchaseOrder);
    }
}
