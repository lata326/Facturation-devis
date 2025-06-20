<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->route('client') ? $this->route('client')->id : null;

        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'mail' => 'required|email|unique:clients,mail,' . $clientId . ',id',
            'ville' => 'required|string|max:255',
            'pays' => 'required|string|max:255',
            'code_postal' => 'string|max:10',
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

