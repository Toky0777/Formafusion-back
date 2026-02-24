<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    use HasFactory;

    protected $table = 'employes';
    protected $primaryKey = 'idEmploye';

    protected $fillable = [
        'idEmploye',
        'idCustomer',
        'idSexe',
        'idNiveau',
        'idFonction',
    ];
}
