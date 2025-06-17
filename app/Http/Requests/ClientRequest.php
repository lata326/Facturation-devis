<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
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
         $clientId = $this->route('client') ? $this->route('client')->clients_id : null;

        return [
            
             'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'mail' => 'required|email|unique:clients,mail,' . $clientId . ',clients_id',
            'ville' => 'required|string|max:255',
            'pays' => 'required|string|max:255',
            'code_postal' => 'required|string|max:10',
            'telephone' => 'required|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire',
            'prenom.required' => 'Le prénom est obligatoire',
            'mail.required' => 'L\'email est obligatoire',
            'mail.email' => 'L\'email doit être valide',
            'mail.unique' => 'Cet email est déjà utilisé',
            'ville.required' => 'La ville est obligatoire',
            'pays.required' => 'Le pays est obligatoire',
            'code_postal.required' => 'Le code postal est obligatoire',
            'telephone.required' => 'Le téléphone est obligatoire',
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
