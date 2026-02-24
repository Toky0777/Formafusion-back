<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeanceInter extends Model
{
    use HasFactory;

    protected $table = 'seance_inters';

    protected $fillable = [
        'dateSeance',
        'heureDebut',
        'heureFin',
        'formateur_id',
        'sallec_id',
        'projet_inter_id',
    ];
}
