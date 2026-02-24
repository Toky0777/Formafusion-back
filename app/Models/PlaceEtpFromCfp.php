<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaceEtpFromCfp extends Model
{
    use HasFactory;

    protected $table = "place_etp_from_cfps";
    protected $primaryKey = "idLieu";

    protected $fillable = [
        'idLieu',
        'date_added',
        'idEntreprise',
        'idCfp'
    ];

    public $timestamps = false;
}
