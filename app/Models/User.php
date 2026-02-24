<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Laravelcm\Subscriptions\Traits\HasPlanSubscriptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasPlanSubscriptions, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'matricule',
        'firstName',
        'cin',
        'phone',
        'adresse',
        'photo',
        'dateNais',
        'idVille',
        'user_addr_quartier',
        'user_addr_lot',
        'user_addr_rue',
        'user_addr_code_postal',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function ville()
    {
        return $this->belongsTo(Ville::class, 'idVille');
    }

    /**
     * Gestion de rôles Testing Center
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_users')
            ->wherePivot('hasRole', 1)
            ->wherePivot('isActive', 1);
    }

    public function hasRole($role)
    {
        return $this->roles->contains('roleName', $role);
    }

    public function hasAnyRole($roles)
    {
        return $this->roles->pluck('roleName')->intersect($roles)->isNotEmpty();
    }
    /**
     * Gestion de rôles Testing Center
     */

    // Relation avec le modèle CreditsWallet
    public function creditsWallet()
    {
        return $this->hasOne(CreditsWallet::class, 'idUser', 'id');
    }

    /**
     * Relation avec le modèle TransactionHistory
     * 
     * @param int $id
     * @return HasMany
     */
    public static function getFullNameById($id)
    {
        $user = self::find($id);
        return $user ? $user->firstName . ' ' . $user->name : 'Unknown User';
    }

    public function idEtp()
    {
        $customer = DB::select("SELECT employes.idEmploye, employes.idCustomer, customers.idTypeCustomer FROM employes INNER JOIN customers ON employes.idCustomer = customers.idCustomer WHERE idEmploye = ?", [Auth::user()->id]);
        return $customer[0]->idCustomer;
    }
}
