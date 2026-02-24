<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDetailProfo extends Model
{
    use HasFactory;
    protected $table = 'invoice_details_profo';
    public $timestamps = false;

    protected $fillable = ['idInvoice', 'item_description', 'item_qty', 'item_unit_price', 'item_total_price', 'idUnite', 'idItems', 'idModule'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
