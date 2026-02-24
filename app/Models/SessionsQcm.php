<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionsQcm extends Model
{
    use HasFactory;

    protected $table = "sessions_test";
    protected $primaryKey = "idSession";

    protected $fillable = [
        'idUtilisateur',
        'idQCM',
        'dateDebut',
        'dateFin',
        'totalPoints',
    ];

    // Relation avec l'utilisateur (ajouter la table des utilisateurs selon votre structure), modèle Users
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'idUtilisateur');
    }

    // Relation avec le QCM
    public function rel_qcm_session()
    {
        return $this->belongsTo(Qcm::class, 'idQCM');
    }

    // Relation avec les réponses choisies par l'utilisateur
    public function reponsesUser()
    {
        return $this->hasMany(ReponsesQcmUsers::class, 'idSession');
    }
}
