<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravelcm\Subscriptions\Models\Plan;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'due_date',
        'payment_date',
        'payment_method',
        'subscription_name',
        'total_price',
        'user_id',
        'id_order',
    ];

    public function subscription()
    {
        return $this->belongsTo(Plan::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'idCustomer', 'user_id');
    }
}
