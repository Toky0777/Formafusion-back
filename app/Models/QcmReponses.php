<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QcmReponses extends Model
{
    use HasFactory;

    protected $table = "qcm_reponses";
    protected $primaryKey = "idReponse";

    protected $fillable = [
        'idQuestion',
        'categorie_id',
        'texteReponse',
        'explicationReponse', # Modication de la table pour ajouter une explication à la réponse
        'points',
    ];

    // Relation avec le modèle QcmQuestions
    public function reponseToquestion()
    {
        return $this->belongsTo(QcmQuestions::class, 'idQuestion');
    }

    // Relation avec les catégories des réponses
    public function categorie_reponse()
    {
        return $this->belongsTo(CategoriesReponses::class, 'categorie_id', 'idCategorie');
    }
}
