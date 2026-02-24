<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QcmQuestions extends Model
{
    use HasFactory;

    protected $table = "qcm_questions";
    protected $primaryKey = "idQuestion";

    protected $fillable = [
        'idQCM',
        'idImageQ',
        'idTypeQcmQuestion',
        'texteQuestion',
    ];

    // Relation avec le modèle QCM
    public function rel_qcm_question()
    {
        return $this->belongsTo(Qcm::class, 'idQCM');
    }

    // Relation avec le modèle QcmReponses 
    public function reponses_questions()
    {
        return $this->hasMany(QcmReponses::class, 'idQuestion');
    }

    //Relation avec le model TypeQcmQuestion
    public function type_qcm()
    {
        return $this->belongsTo(TypeQcmQuestion::class, 'idTypeQcmQuestion');
    }

    // Relation avec le modèle QcmImages
    public function image()
    {
        return $this->hasOne(QcmImages::class, 'idImageQ', 'idImageQ');
    }
}