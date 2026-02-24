<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileMoneyAcount extends Model
{
    protected $table = 'mobilemoneyacounts';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['mm_idCustomer', 'mm_phone', 'mm_operateur', 'mm_titulaire'];

    public function invoicePayments()
    {
        return $this->hasMany(InvoicePayment::class, 'payment_mobilemoney_id', 'id');
    }
}
