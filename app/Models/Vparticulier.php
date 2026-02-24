<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vparticulier extends Model
{
    use HasFactory;
    protected $table = "v_list_particuliers";
    protected $primaryKey = 'idParticulier';
    public $timestamps = false;

    protected $fillable = [
        'part_initial_name',
        'part_name',
        'part_firstname',
        'part_email',
        'part_cin',
        'part_matricule',
        'part_phone',
        'part_addr',
        'part_addr_lot',
        'part_addr_quartier',
        'part_photo',
        'part_date_nais'
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'idEntreprise', 'idEtp');
    }
}
