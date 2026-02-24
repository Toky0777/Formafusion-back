<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vCollaboratioCfpEtps extends Model
{
    use HasFactory;
    protected $table = "v_collaboration_cfp_etps";
    protected $primaryKey = 'idEtp';
    public $timestamps = false;

    protected $fillable = [
        'etp_name',
        'etp_nif',
        'etp_stat',
        'etp_addr_quartier',
        'etp_addr_code_postal',
        'etp_addr_lot',
        'idEtp',
        'idCfp'
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'idEntreprise', 'idEtp');
    }
}
