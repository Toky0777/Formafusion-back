<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditWalletHistory extends Model
{
    use HasFactory;

    protected $table = "transaction_history";
    protected $primaryKey = "idTransaction";

    protected $fillable = [
        'idUser',
        'montant',
        'typeTransaction',
        'description'
    ];
}
