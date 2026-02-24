<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cours extends Model
{
    use HasFactory;

    protected $table = 'cours';

    protected $fillable = [
        'courseName',
        'courseDesc',
        'programme_id'
    ];

    public function programme(){
        return $this->belongsTo(Programme::class);
    }
}
