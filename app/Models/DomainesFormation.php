<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomainesFormation extends Model
{
    use HasFactory;

    protected $table = "domaine_formations";
    protected $primaryKey = "idDomaine";

    // Relation avec le modèle Qcm
    public function qcm()
    {
        return $this->hasMany(Qcm::class, 'idDomaine');
    }
}
