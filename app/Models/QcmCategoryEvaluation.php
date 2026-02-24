<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QcmCategoryEvaluation extends Model
{
    use HasFactory;

    protected $table = 'qcm_category_evaluations';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'idQCM',
        'idCategorie',
        'min_percentage',
        'max_percentage',
        'id_niveau',
        'description',
        'recommendations'
    ];

    public function qcm()
    {
        return $this->belongsTo(Qcm::class, 'idQCM', 'idQCM');
    }

    public function niveau()
    {
        return $this->belongsTo(NiveauQcm::class, 'id_niveau');
    }

    public function categorie()
    {
        return $this->belongsTo(CategoriesReponses::class, 'idCategorie', 'idCategorie');
    }
}