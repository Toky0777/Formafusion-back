<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFactureRequest extends FormRequest
{
    public function authorize()
    {
        // Autoriser la requête pour les utilisateurs authentifiés
        return true;
    }

    public function rules()
    {
        return [
            'invoice_number' => 'required|string|max:50|unique:invoices,invoice_number,' . $this->route('idInvoice') . ',idInvoice',
            'invoice_bc' => 'nullable',
            'invoice_date' => 'required',
            'invoice_date_pm' => 'required',
            'invoice_status' => 'required',
            'invoice_condition' => 'nullable',
            'invoice_reduction' => 'nullable',
            'invoice_tva' => 'nullable',
            'invoice_total_amount' => 'nullable',
            'invoice_letter' => 'required',
            'idCustomer' => 'required',
            'idCompany' => 'required',
            'idEntreprise' => 'required',
            'idPaiement' => 'required',
            'idBankAcount' => 'nullable',
            'idPay' => 'required',
            'idTypeFacture' => 'required',

            // Validation des items (détails de la facture)
            'items' => 'nullable',
            'items.*.idItems' => 'nullable',
            'items.*.idProjet' => 'nullable',
            'items.*.item_qty' => 'nullable',
            'items.*.item_description' => 'nullable',
            'items.*.item_unit_price' => 'nullable',
            'items.*.idUnite' => 'nullable',

            // Validation des acomptes
            'acomptes.percent' => 'nullable',

            // Validation des standards
            'standards' => 'nullable',
            'idTypeClient' => 'required',
            'idContact' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'invoice_number.required' => 'Le numéro de facture est obligatoire.',
            'invoice_number.unique' => 'Le numéro de facture existe déjà.',
            'invoice_date.required' => 'La date de la facture est obligatoire.',
            'items.required' => 'Les détails de la facture sont obligatoires.',
            'items.*.idItems.required' => 'L\'identifiant de l\'article est obligatoire.',
            'items.*.item_qty.required' => 'La quantité de l\'article est obligatoire.',
            'items.*.item_unit_price.required' => 'Le prix unitaire est obligatoire.',
            'invoice_letter.required' => 'Vous devrez avoir un montant total.',
            'idTypeClient.required' => 'idTypeClient non retrouver.',
        ];
    }
}
