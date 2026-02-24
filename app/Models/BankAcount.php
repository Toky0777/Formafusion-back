<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAcount extends Model
{
    protected $table = 'bankacounts';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['ba_idCustomer', 'ba_account_number', 'ba_name', 'ba_idPostal', 'ba_quartier', 'ba_titulaire'];

    public function invoicePayments()
    {
        return $this->hasMany(InvoicePayment::class, 'payment_bank_id', 'id');
    }
}
