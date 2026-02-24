<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupeInterne extends Model
{
    use HasFactory;

    protected $table = 'groupe_internes';

    protected $fillable = [
        'dateDebut',
        'dateFin',
        'modalite_id',
        'module_interne_id',
        'projet_interne_id',
        'ville_id'
    ];

    public function getIncrementing(): bool
    {
        return false;
    }

    public static function boot(){
        parent::boot();

        self::creating(function ($model) {
            $model->sessionName = 'Sess-' . ($model::count() + 1);
        });
    }
}
