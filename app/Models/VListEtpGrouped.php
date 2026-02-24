<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VListEtpGrouped extends Model
{
    use HasFactory;
    protected $table = 'v_list_etp_groupeds';
    protected $primaryKey = 'idEntreprise';
    public $incrementing = false;
    protected $keyType = 'bigint';
    public $timestamps = false;

    protected $fillable = [
        'idEntreprise',
        'idEntrepriseParent',
        'etp_name',
        'etp_nif',
        'etp_stat',
        'etp_rcs',
        'etp_description',
        'etp_site_web',
        'etp_logo',
        'etp_email',
        'etp_phone',
        'etp_slogan',
    ];

    public function subscriptions()
    {
        return $this->belongsTo(config('laravel-subscriptions.models.subscription'), 'idEntreprise', 'subscriber_id');
    }
}
