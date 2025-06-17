<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CompanyRequest extends FormRequest
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
       $companyId = $this->route('company') ? $this->route('company')->company_id : null;
        
        return [
            'nom_societe' => 'required|string|max:255',
            'forme_juridique_id' => 'required|exists:formes_juridiques,id',
            'numero_tva' => 'nullable|string|max:50',
            'adresse_postale' => 'required|string|max:255',
            'ville' => 'required|string|max:100',
            'pays' => 'required|string|max:100',
            'site_internet' => 'nullable|url|max:255',
            'logo_url' => 'nullable|url|max:255',
            'email_entreprise' => 'required|email|max:255|unique:companies,email_entreprise,' . $companyId . ',company_id',
            'telephone_entreprise' => 'required|string|max:20',
        ];
    }

     public function messages(): array
    {
        return [
            'nom_societe.required' => 'Le nom de la société est requis',
            'forme_juridique_id.required' => 'La forme juridique est requise',
            'forme_juridique_id.exists' => 'La forme juridique sélectionnée n\'existe pas',
            'adresse_postale.required' => 'L\'adresse postale est requise',
            'ville.required' => 'La ville est requise',
            'pays.required' => 'Le pays est requis',
            'email_entreprise.required' => 'L\'email de l\'entreprise est requis',
            'email_entreprise.email' => 'L\'email de l\'entreprise doit être valide',
            'email_entreprise.unique' => 'Cet email d\'entreprise est déjà utilisé',
            'telephone_entreprise.required' => 'Le téléphone de l\'entreprise est requis',
            'site_internet.url' => 'Le site internet doit être une URL valide',
            'logo_url.url' => 'L\'URL du logo doit être une URL valide',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Erreurs de validation',
            'errors' => $validator->errors()
        ], 422));
    }
}
