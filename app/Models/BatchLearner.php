<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchLearner extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['employe_id', 'batch_id'];

    public function employes(){
        return $this->hasMany(Employe::class, 'employe_id');
    }
}
