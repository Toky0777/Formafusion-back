<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFactureRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'invoice_number' => 'required|string|max:255|unique:invoices,invoice_number',
            'invoice_bc' => 'nullable',
            'invoice_date' => 'required|date',
            'invoice_date_pm' => 'required|date',
            'invoice_status' => 'required',
            'invoice_condition' => 'nullable',
            'idCustomer' => 'required',
            'idCompany' => 'required',
            'idEntreprise' => 'required',
            'idProjet' => 'nullable',
            'idPaiement' => 'required',
            'idBankAcount' => 'nullable',
            'idTypeFacture' => 'required',
            'idItem' => 'nullable',
            'item_qty' => 'nullable',
            'item_description' => 'nullable',
            'item_unit_price' => 'nullable',
            'item_total_price' => 'nullable',
            'idUnite' => 'nullable',
            'acomptes.percent' => 'nullable',
            'standards' => 'nullable',
            'invoice_sub_total' => 'nullable',
            'invoice_reduction' => 'nullable',
            'invoice_tva' => 'nullable',
            'invoice_total_amount' => 'nullable',
            'invoice_letter' => 'required',
            'idModule' => 'nullable',
            'idTypeClient' => 'required',
            'idContact' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'invoice_number.required' => 'Le numéro de facture est requis.',
            'invoice_number.unique' => 'Ce numéro de facture existe déjà.',
            'invoice_date.required' => 'La date de la facture est requise.',
            'invoice_date_pm.required' => 'La date d\'échéance de la facture est requise.',
            'idEntreprise.required' => 'Vous devrez séléctionner une entreprise.',
            'idProjet.required' => 'Vous devrez séléctionner un projet.',
            'idCustomer.required' => 'Ajouter un idCustomer.',
            'idPaiement.required' => 'Vous devrez choisir une méthode de paiement.',
            'invoice_letter.required' => 'Vous devrez avoir un montant total.',
            'idCompany.required' => 'Vous devrez séléctionner une adresse.',
        ];
    }
}
