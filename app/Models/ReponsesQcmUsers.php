<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReponsesQcmUsers extends Model
{
    use HasFactory;

    protected $table = "reponses_utilisateurs";
    protected $primaryKey = "idReponseUtilisateur";

    protected $fillable = [
        'idSession',
        'idQuestion',
        'idReponse',
    ];

    // Relation avec la session
    public function sessionOfQcm()
    {
        return $this->belongsTo(SessionsQcm::class, 'idSession');
    }

    // Relation avec la question
    public function questionOfResponse()
    {
        return $this->belongsTo(QcmQuestions::class, 'idQuestion');
    }

    // Relation avec la réponse
    public function userChoosenReponse()
    {
        return $this->belongsTo(QcmReponses::class, 'idReponse');
    }
}
