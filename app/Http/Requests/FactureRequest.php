<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FactureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
             
            'clients.nom' => 'required|exists:clients,nom',
            'devis_id' => 'nullable|exists:devis,id',
            'date_emission' => 'sometimes|date',
            'date_echeance' => 'required|date|after:date_emission',
            'status' => 'sometimes|in:brouillon,envoyee,payee,en_retard,annulee',
            'condition_paiement' => 'sometimes|string|max:255',
            'mode_paiement' => 'sometimes|in:Non spécifié,Espèces,Virement bancaire,Chèques,Carte bancaire,PayPal',
            'devise' => 'sometimes|in:FCFA,USD,EUR',
            'signature' => 'nullable|string',
            'date_signature' => 'nullable|date',
            'note' => 'nullable|string',
            'lignes' => 'required|array|min:1',
            'lignes.*.article_id' => 'required|exists:articles,id',
            'lignes.*.quantite' => 'required|integer|min:1',
            'lignes.*.prix_unitaire' => 'sometimes|numeric|min:0',
        
        ];
    }

     public function messages()
    {
        return [
            'clients.nom.required' => 'Le client est obligatoire.',
            'id.exists' => 'Le client sélectionné n\'existe pas.',
            'devis_id.exists' => 'Le devis sélectionné n\'existe pas.',
            'date_echeance.required' => 'La date d\'échéance est obligatoire.',
            'date_echeance.after' => 'La date d\'échéance doit être postérieure à la date d\'émission.',
            'lignes.required' => 'Au moins une ligne est obligatoire.',
            'lignes.min' => 'Au moins une ligne est obligatoire.',
            'lignes.*.article_id.required' => 'L\'article est obligatoire pour chaque ligne.',
            'lignes.*.article_id.exists' => 'L\'article sélectionné n\'existe pas.',
            'lignes.*.quantite.required' => 'La quantité est obligatoire.',
            'lignes.*.quantite.min' => 'La quantité doit être au moins 1.',
            'lignes.*.prix_unitaire.numeric' => 'Le prix unitaire doit être un nombre.',
            'lignes.*.prix_unitaire.min' => 'Le prix unitaire doit être positif.',
        ];
    }

}
