<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ville extends Model
{
    use HasFactory;

    protected $table = 'ville_codeds';

    protected $fillable = [
        'ville_name',
        'vi_code_postal',
        'idVille'
    ];

    public $timestamps = false;
}
