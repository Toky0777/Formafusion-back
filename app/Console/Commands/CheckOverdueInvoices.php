<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use Carbon\Carbon;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'invoices:check-overdue';
    protected $description = 'Vérifier les factures échues et mettre à jour leur statut';

    public function handle()
    {
        Invoice::where('invoice_status', '!=', 8)
            ->whereDate('invoice_date_pm', '<', Carbon::now())
            ->update(['invoice_status' => 8]);

        $this->info('Statut des factures mis à jour avec succès.');
    }
}
