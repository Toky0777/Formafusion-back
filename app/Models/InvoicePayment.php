<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    use HasFactory;
    protected $table = 'invoice_payments';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['invoice_id', 'amount', 'payment_date', 'payment_method_id', 'payment_bank_id', 'payment_mobilemoney_id', 'payment_description'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function modePaiement()
    {
        return $this->belongsTo(ModePaiement::class, 'payment_method_id', 'idTypePm');
    }

    public function bankacount()
    {
        return $this->belongsTo(BankAcount::class, 'payment_bank_id', 'id');
    }

    public function mobilemoneyacount()
    {
        return $this->belongsTo(MobileMoneyAcount::class, 'payment_mobilemoney_id', 'id');
    }
}
