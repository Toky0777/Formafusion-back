<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjetInterne extends Model
{
    use HasFactory;

    protected $table = 'projet_internes';

    protected $fillable = [
        'projectName',
        'etp_id',
        'type_formation_id',
        'statut_id',
        'activite',
    ];

    public function getIncrementing(): bool
    {
        return false;
    }

    public static function boot(){
        parent::boot();

        self::creating(function ($model) {
            $model->projectName = 'Proj-' . ($model::count() + 1);
        });
    }
}
