<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeEntreprise extends Model
{
    use HasFactory;

    protected $table = "type_entreprises";
    protected $primaryKey = 'idTypeEtp';
    public $timestamps = true;

    protected $fillable = ['idTypeEtp', 'type_etp'];
}
