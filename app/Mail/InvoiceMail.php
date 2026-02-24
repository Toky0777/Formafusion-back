<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $customer;
    public $invoice;
    public $entreprise;
    public $invoiceDetailsOther;
    public $invoiceDetails;
    public $pdf;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($customer, $invoice, $entreprise, $invoiceDetailsOther, $invoiceDetails, $pdf)
    {
        $this->customer = $customer;
        $this->invoice = $invoice;
        $this->entreprise = $entreprise;
        $this->invoiceDetailsOther = $invoiceDetailsOther;
        $this->invoiceDetails = $invoiceDetails;
        $this->pdf = $pdf;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('facture.invoiceEmail')
            ->subject('Votre facture')
            ->attachData($this->pdf, $this->invoice->invoice_number . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
