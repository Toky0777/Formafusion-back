<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    use HasFactory;

    protected $table = 'salles';
    protected $primaryKey = 'idSalle';

    protected $fillable = [
        'salle_name',
        'salle_quartier',
        'salle_rue',
        'idLieu',
        'salle_image'
    ];

    public $timestamps = false;
}
