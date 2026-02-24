<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Batch extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name', 'customer_id'];

    public $timestamps = false;

    public function batchlearners()
    {
        return $this->hasMany(BatchLearner::class);
    }
}
