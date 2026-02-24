<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriesReponses extends Model
{
    use HasFactory;

    protected $table = "categories_reponses";
    protected $primaryKey = "idCategorie";

    protected $fillable = [
        'nomCategorie',
        'descriptionCategorie',
    ];

    // Relation avec les reponses du qcm
    public function qcmReponses()
    {
        return $this->hasMany(QcmReponses::class, 'categorie_id', 'idCategorie');
    }
}
