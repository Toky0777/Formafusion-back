<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Invoice extends Model
{
    use HasFactory;
    protected $table = 'invoices';
    protected $primaryKey = 'idInvoice';
    public $timestamps = false;

    protected $fillable = ['invoice_number', 'invoice_bc', 'invoice_date', 'invoice_date_pm', 'invoice_status', 'invoice_condition', 'invoice_sub_total', 'invoice_reduction', 'invoice_tva', 'invoice_total_amount', 'invoice_letter', 'idCustomer ', 'idEntreprise ', 'idPaiement', 'idBankAcount', 'idTypeFacture', 'idTypeClient'];

    protected $appends = ['status_color'];

    public function entreprise()
    {
        return $this->morphTo(null, 'typeCustomer', 'idEntreprise', 'idEntreprise');
    }

    public function entrepriseFromVcollaboration()
    {
        return $this->belongsTo(vCollaboratioCfpEtps::class, 'idEntreprise', 'idEtp');
    }

    public function particulier()
    {
        return $this->belongsTo(Vparticulier::class, 'idEntreprise', 'idParticulier');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'idCustomer', 'idCustomer');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'idCustomer', 'idCustomer');
    }

    public function status()
    {
        return $this->belongsTo(InvoiceStatus::class, 'invoice_status', 'idInvoiceStatus');
    }

    public function items()
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class, 'invoice_id')->with('modePaiement', 'bankacount');
    }

    public function deletedInvoices()
    {
        return $this->hasOne(InvoiceDeleted::class, 'idInvoice', 'idInvoice');
    }

    public function getStatusColorAttribute()
    {
        if (isset($this->status->idInvoiceStatus)) {
            switch ($this->status->idInvoiceStatus) {
                case 1:
                    return 'gray-400';
                case 2:
                    return 'rose-500';
                case 3:
                    return '[#37718e]';
                case 4:
                    return 'teal-600';
                case 5:
                    return 'yellow-600';
                case 6:
                    return 'red-400';
                case 7:
                    return 'green-600';
                case 8:
                    return 'red-600';
                case 9:
                    return 'rose-500';
                case 10:
                    return 'orange-500';
                case 11:
                    return 'amber-700';
                case 12:
                    return 'purple-600';
                case 13:
                    return 'gray-700';
                case 14:
                    return 'blue-900';
                case 15:
                    return 'red-800';
                default:
                    return 'info';
            }
        }
        return 'unknown';
    }


    public function scopeStandard(Builder $builder): Builder
    {
        return $builder->where('idTypeFacture', 1);
    }

    public function scopeProforma(Builder $builder): Builder
    {
        return $builder->where('idTypeFacture', 2);
    }

    public function scopeAcompte(Builder $builder): Builder
    {
        return $builder->where('idTypeFacture', 3);
    }
    public function scopeSolde(Builder $builder): Builder
    {
        return $builder->where('idTypeFacture', 4);
    }

    public function scopeStandardAcompteSolde(Builder $builder): Builder
    {
        return $builder->whereIn('idTypeFacture', [1, 3, 4]);
    }

    public function modePaiement()
    {
        return $this->belongsTo(ModePaiement::class, 'idPaiement');
    }

    public function typeFacture()
    {
        return $this->belongsTo(InvoiceTypes::class, 'idTypeFacture');
    }
}
