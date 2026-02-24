<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etp_groupeds extends Model
{
    use HasFactory;

    protected $table = "etp_groupeds";
    protected $primaryKey = 'idEntreprise';
    public $timestamps = false;

    protected $fillable = ['idEntreprise', 'idEntrepriseParent'];
}
