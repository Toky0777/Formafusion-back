<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;

class InvoiceService
{
    public function countInvoice($key, $idCustomer)
    {
        return Invoice::with([
            'entrepriseFromVcollaboration',
            'particulier',
            'status',
            'payments',
            'company'
        ])
            ->where(function ($query) use ($key) {
                $query->whereHas('entrepriseFromVcollaboration', function ($q) use ($key) {
                    $q->where('etp_name', 'like', "%$key%");
                })
                    ->orWhere('invoice_number', 'like', "%$key%");
            })
            ->where('idCustomer', $idCustomer)
            ->doesntHave('deletedInvoices')
            ->standard()
            ->count();
    }

    public function getInvoice($key)
    {
        return Invoice::with([
            'entrepriseFromVcollaboration',
            'particulier',
            'status',
            'payments',
            'company'
        ])
            ->where(function ($query) use ($key) {
                $query->whereHas('entrepriseFromVcollaboration', function ($q) use ($key) {
                    $q->where('etp_name', 'like', "%$key%");
                })
                    ->orWhere('invoice_number', 'like', "%$key%");
            })
            ->where('idCustomer', Customer::idCustomer())
            ->doesntHave('deletedInvoices')
            ->standard()
            ->orderBy(
                'idInvoice',
                'desc'
            )
            ->paginate(10);
    }

    public function countQuote($key, $idCustomer)
    {
        return Invoice::with([
            'entrepriseFromVcollaboration',
            'particulier',
            'status',
            'payments',
            'company'
        ])
            ->where(function ($query) use ($key) {
                $query->whereHas('entrepriseFromVcollaboration', function ($q) use ($key) {
                    $q->where('etp_name', 'like', "%$key%");
                })
                    ->orWhere('invoice_number', 'like', "%$key%");
            })
            ->where('idCustomer', $idCustomer)
            ->doesntHave('deletedInvoices')
            ->proforma()
            ->count();
    }

    public function getQuote($key)
    {
        return Invoice::with([
            'entrepriseFromVcollaboration',
            'particulier',
            'status',
            'payments',
            'company'
        ])
            ->where(function ($query) use ($key) {
                $query->whereHas('entrepriseFromVcollaboration', function ($q) use ($key) {
                    $q->where('etp_name', 'like', "%$key%");
                })
                    ->orWhere('invoice_number', 'like', "%$key%");
            })
            ->where('idCustomer', Customer::idCustomer())
            ->doesntHave('deletedInvoices')
            ->proforma()
            ->paginate(10);
    }
}
