<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoicePaymentController extends Controller
{
public function index($id) {
    try {
        // Vérifier si l'ID est valide
        if (!$id || !is_numeric($id)) {
            return response()->json([
                'status' => 400,
                'message' => 'ID invalide'
            ], 400);
        }

        $payements_simple = DB::table('invoice_payments as ip')
            ->leftjoin('pm_types as pt', 'ip.payment_method_id', '=', 'pt.idTypePm')
            ->leftjoin('bankacounts as ba', 'ip.payment_bank_id', '=', 'ba.id')
            ->where('ip.invoice_id', $id)
            ->get();
        $payements_grouped = collect(); // Initialiser avec une collection vide

        $firstLine = DB::table('invoice_payments_grouped')->where('invoice_id', $id)->first();
        
        if ($firstLine) {
            $payements_grouped = DB::table('invoice_payments_grouped as ip')
                ->join('pm_types as pt', 'ip.payment_method_id', '=', 'pt.idTypePm')
                ->join('bankacounts as ba', 'ip.payment_bank_id', '=', 'ba.id')
                ->join('invoices as i', 'ip.invoice_id', '=', 'i.idInvoice')
                ->where('ip.etp_id', $firstLine->etp_id)
                ->get();
        }

        // Vérifier si des données existent
        // if ($payements_simple->isEmpty() && $payements_grouped->isEmpty()) {
        //     return response()->json([
        //         'status' => 404,
        //         'message' => 'Aucun paiement trouvé pour cet ID',
        //         'payments' => [],
        //         'payments_grouped' => []
        //     ], 404);
        // }

        return response()->json([
            'status' => 200,
            'message' => 'Données récupérées avec succès',
            'payments' => $payements_simple,
            'payments_grouped' => $payements_grouped,
        ]);

    } catch (\Exception $e) {
        // Log l'erreur pour le débogage
        // \Log::error('Erreur dans PaymentController@index: ' . $e->getMessage());
        
        return response()->json([
            'status' => 500,
            'message' => 'Erreur serveur lors de la récupération des paiements'
        ], 500);
    }
}

   public function store(Request $request)
{
    $request->validate([
        'invoice_id' => 'required|exists:invoices,idInvoice',
        'payment_date' => 'required|date',
        'amount' => 'required|numeric|min:1',
        'payment_method_id' => 'required|string',
        'payment_bank_id' => 'nullable',
        'payment_mobilemoney_id' => 'nullable',
        'payment_description' => 'nullable|string|max:200',
    ]);

    $invoice = Invoice::findOrFail($request->invoice_id);
    
    InvoicePayment::create([
        'invoice_id' => $request->invoice_id, // Utilisez celui du request
        'amount' => $request->amount,
        'payment_date' => $request->payment_date,
        'payment_method_id' => $request->payment_method_id,
        'payment_bank_id' => $request->payment_bank_id,
        'payment_mobilemoney_id' => $request->payment_mobilemoney_id,
        'payment_description' => $request->payment_description,
    ]);

    // Montant total déjà payé
    $totalPaid = InvoicePayment::where('invoice_id', $invoice->idInvoice)->sum('amount');

    if ($totalPaid >= $invoice->invoice_total_amount) {
        $invoice->update(['invoice_status' => 4]);
    } elseif ($totalPaid < $invoice->invoice_total_amount) {
        $invoice->update(['invoice_status' => 5]);
    }

    return response()->json([
        'status' => 200,
        'message' => "Le paiement a été enregistré avec succès"
    ]);
}

    public function update(Request $request, $paymentId)
    {
        $request->validate([
            // 'payment_date' => 'date|nullable',
            // 'payment_method_id' => 'string|nullable',
            // 'payment_bank_id' => 'nullable',
            // 'payment_mobilemoney_id' => 'nullable',
            // 'payment_description' => 'string|nullable',
            'amount' => 'numeric|nullable',
        ]);

        DB::table('invoice_payments')
            ->where('id', $paymentId)
            ->update(['amount' => $request->amount]);
        DB::table('invoices')
            ->where('idInvoice', $request->invoice_id)
            ->update(['invoice_status' => $request->invoice_status]);
        //$payment = InvoicePayment::findOrFail($paymentId);
        //$payment->update($validatedData);

        return response()->json(['success' => true], 200);
    }

    public function destroy($idFacture, $idPayement)
    {
        $payment = InvoicePayment::findOrFail($idPayement);
        // Montant total déjà payé

        // Supprimer le paiement
        $payment->delete();
        $totalPaid = InvoicePayment::where('invoice_id', $idFacture)->sum('amount');
        // Recalculer la somme des paiements restants
        $invoice = Invoice::findOrFail($idFacture);
        // Montant total déjà payé
        if ($totalPaid >= $invoice->invoice_total_amount) {
            $invoice->update(['invoice_status' => 4]);
        } elseif ($totalPaid < $invoice->invoice_total_amount) {
            $invoice->update(['invoice_status' => 5]);
        }

        $invoice->save();

        return response()->json([
            'success' => true,
            'message' => 'Paiement supprimé avec succès et statut mis à jour.'
        ]);
    }
    public function storeGroupedPayment(Request $request)
    {
        $request->validate([
            'invoices' => 'required|array',
            'invoices.*.idInvoice' => 'required|integer',
            'invoices.*.amount' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric',
            'payment_date' => 'required|date',
            'payment_method_id' => 'required',
            'payment_bank_id' => 'required',
            'payment_description' => 'nullable|string',
        ]);

        $invoices = $request->input('invoices');
        $paymentDate = $request->input('payment_date');
        $paymentMethod = $request->input('payment_method_id');
        $paymentBankId = $request->input('payment_bank_id');
        $paymentDescription = $request->input('payment_description');

        try {

            DB::transaction(function () use ($invoices, $paymentDate, $paymentMethod, $paymentBankId, $paymentDescription) {

                foreach ($invoices as $invoiceData) {

                    $invoiceId = $invoiceData['idInvoice'];
                    $idEntreprise = $invoiceData['idEntreprise'];
                    $amount = $invoiceData['amount'];

                    $invoice = Invoice::findOrFail($invoiceId);
                    $invoice->update(['invoice_status' => 4]);

                    DB::table('invoice_payments_grouped')->insert([
                        'invoice_id' => $invoiceId,
                        'etp_id' => $idEntreprise,
                        'payment_date' => $paymentDate,
                        'payment_method_id' => $paymentMethod,
                        'amount' => $amount,
                        'payment_bank_id' => $paymentBankId,
                        'payment_description' => $paymentDescription,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

            return response()->json([
                'status' => 200,
                'message' => 'Paiement groupé enregistré avec succès',
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l’enregistrement du paiement',
                'details' => $e->getMessage(),
            ], 500);
        }
    }


}
