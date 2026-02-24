<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vCfpAll extends Model
{
    use HasFactory;
    protected $table = "v_cfp_all";
    protected $primaryKey = 'idCfp';
    public $timestamps = false;

    protected $fillable = [
        'customerName',
        'nif',
        'stat',
        'rcs',
        'customerEmail',
        'customerPhone',
        'customer_addr_quartier',
        'customer_ville',
        'customer_addr_code_postal',
        'customer_addr_lot',
        'idCfp'
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'idEntreprise', 'idCfp');
    }
}
