<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceTypes extends Model
{
    use HasFactory;
    protected $table = 'type_factures';
    protected $primaryKey = 'idTypeFacture';
    public $timestamps = false;

    protected $fillable = ['idTypeFacture','typeFacture'];
}
