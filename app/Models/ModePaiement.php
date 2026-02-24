<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModePaiement extends Model
{
    use HasFactory;
    protected $table = 'pm_types';
    protected $primaryKey = 'idTypePm';
    public $timestamps = false;

    protected $fillable = ['idTypePm', 'pm_type_name'];

    public function invoicePayments()
    {
        return $this->hasMany(InvoicePayment::class, 'payment_method_id', 'idTypePm');
    }
}
