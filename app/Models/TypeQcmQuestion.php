<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeQcmQuestion extends Model
{
    use HasFactory;

    protected $table = "type_qcm";

    public function qcm_questions()
    {
        return $this->hasMany(QcmQuestions::class);
    }
}
