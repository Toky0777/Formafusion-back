<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeanceInterne extends Model
{
    use HasFactory;

    protected $table = 'seance_internes';

    protected $fillable = [
        'dateSeance',
        'heureDebut',
        'heureFin',
        'formateur_id',
        'salle_id',
        'projet_interne_id',
        'groupe_interne_id'
    ];
}
