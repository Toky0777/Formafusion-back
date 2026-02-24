<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Projet extends Model
{
    use HasFactory;

    protected $table = 'projets';
    protected $primaryKey = 'idProjet';
    protected $guarded = [];

    public function getIncrementing(): bool
    {
        return false;
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->projectName = 'P-' . ($model::count() + 1);
        });
    }

    public static function getProjects($projectId, $idSession)
    {
        $seances = DB::table('v_seances')
            ->select('projectName', 'idSeance', 'moduleName', 'type', 'ville', 'salle', 'dateSeance', 'heureDebut', 'heureFin', 'nameForm AS name', 'firstNameForm AS firstName')
            ->where('idProjet', '=', $projectId)
            ->where('idSession', '=', $idSession)
            ->get()->toArray();

        return $seances;
    }


    public static function getProjectMonth($string): string
    {
        return explode('-', $string)[1];
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'idModule');
    }

    public function villeCoded()
    {
        return $this->belongsTo(Ville::class, 'idVilleCoded');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'idCustomer');
    }

    public function modalite()
    {
        return $this->belongsTo(Modalite::class, 'idModalite');
    }

    public function typeProjet()
    {
        return $this->belongsTo(TypeProjet::class, 'idTypeProjet');
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'idSalle');
    }

    public static function booted()
    {
        static::deleting(function ($projet) {
            $projet->module()->delete();
            $projet->villeCoded()->delete();
            $projet->customer()->delete();
            $projet->modalite()->delete();
            $projet->typeProjet()->delete();
            $projet->salle()->delete();
        });
    }
}
