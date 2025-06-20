<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DevisRequest extends FormRequest
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
        
            'id' => 'required|exists:clients,id',
            'company_id' => 'required|exists:companies,id',
            'date_expiration' => 'required|date|after:today',
            'date_echeance' => 'required|date|after_or_equal:date_expiration',
            'status' => 'sometimes|in:brouillon,envoye,accepte,refuse,expire',
            'signature' => 'nullable|string',
            'date_signature' => 'nullable|date',
            'note' => 'nullable|string',
            'lignes' => 'required|array|min:1',
            'lignes.*.article_id' => 'required|exists:articles,id',
            'lignes.*.quantite' => 'required|integer|min:1',
            'lignes.*.prix_unitaire' => 'sometimes|numeric|min:0',
        
        ];
    }

    public function message() {
        
        return [
            'id.required' => 'Le client est obligatoire.',
            'id.exists' => 'Le client sélectionné n\'existe pas.',
            'company_id.required' => 'L\'entreprise est obligatoire.',
            'company_id.exists' => 'L\'entreprise sélectionnée n\'existe pas.',
            'date_expiration.required' => 'La date d\'expiration est obligatoire.',
            'date_expiration.after' => 'La date d\'expiration doit être future.',
            'date_echeance.required' => 'La date d\'échéance est obligatoire.',
            'date_echeance.after_or_equal' => 'La date d\'échéance doit être postérieure ou égale à la date d\'expiration.',
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
