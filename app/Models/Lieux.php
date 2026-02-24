<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lieux extends Model
{
    use HasFactory;

    protected $table = 'lieux';
    protected $primaryKey = "idLieu";
    public $timestamps = false;

    protected $fillable = [
        'li_name',
        'idVille',
        'idLieuType',
        'idVilleCoded'
    ];
}
