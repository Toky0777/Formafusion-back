<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DebitCreditsEmp extends Model
{
    use HasFactory;

    protected $table = "emp_debit_credit";
    protected $primaryKey = "idDebitEmpEtp";

    protected $fillable = [
        'idTransaction',
        'idUser',
        'description',
        'montant',
        'typeTransaction',
    ];
}
