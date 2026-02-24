<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravelcm\Subscriptions\Traits\HasPlanSubscriptions;

class Customer extends Model
{
    use HasFactory, HasPlanSubscriptions, Notifiable;

    protected $table = 'customers';

    protected $primaryKey = 'idCustomer';

    protected $fillable = [
        'customerName',
        'nif',
        'stat',
        'assujetti',
        'rcs',
        'description',
        'siteWeb',
        'logo',
        'customerEmail',
        'customerPhone',
        'customer_addr_lot',
        'customer_addr_quartier',
        'customer_addr_rue',
        'customer_addr_code_postal',
        'customer_slogan',
        'idSecteur',
        'idTypeCustomer',
    ];

    public $timestamps = true;

    protected $casts = [
        'idCustomer' => 'integer',
        'assujetti' => 'boolean',
        'customer_addr_code_postal' => 'integer',
        'idSecteur' => 'integer',
        'idTypeCustomer' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function subscriptions()
    {
        return $this->hasMany(config('laravel-subscriptions.models.subscription'));
    }

    public function entreprise()
    {
        return $this->hasOne(Entreprise::class, 'idCustomer', 'idCustomer');
    }

    public function etp_groupeds()
    {
        return $this->hasOne(Etp_groupeds::class, 'idEntreprise', 'idCustomer');
    }

    public static function idCustomer()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idCustomer;
    }

    public static function idCustomerById($userId)
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [$userId]);
        return $customer[0]->idCustomer ?? null;
    }

    public static function customerName($userId)
    {
        $customer = DB::select("SELECT customers.customerName FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [$userId]);
        return $customer[0]->customerName ?? null;
    }

    public static function typeCustomer()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idTypeCustomer;
    }

    public static function getCustomer($idCustomer)
    {
        $customer = DB::table('customers')
            ->select('idCustomer', 'idTypeCustomer', 'customerName AS customer_name', 'customerEmail AS customer_email', 'nif as customer_nif', 'customer_addr_lot')
            ->where('idCustomer', $idCustomer)
            ->first();

        return $customer;
    }
}
