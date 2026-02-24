<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agence extends Model
{
    use HasFactory;

    protected $table = 'agences';
    protected $primaryKey = 'idAgence';

    protected $fillable = [
        'ag_name',
        'idVilleCoded',
        'idCustomer'
    ];

    public $timestamps = false;

    // manome valeur an'ilay "attribut idCustomer"
    public static function boot()
    {
        parent::boot();
        static::creating(
            fn($q) => $q->idCustomer = Customer::idCustomer()
        );
    }
}
