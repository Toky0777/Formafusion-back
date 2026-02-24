<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDeleted extends Model
{
    use HasFactory;
    protected $table = 'invoice_deleted';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = ['idInvoice'];
}
