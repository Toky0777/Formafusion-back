<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditsPacks extends Model
{
    use HasFactory;

    protected $table = "credits_packs";
    protected $primaryKey = "idPackCredit";

    protected $fillable = [
        'type_pack',
        'description_pack',
        'credits',
        'pack_price',
        'currency',
        'is_active',
    ];
}
