<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerificationCodeRequest extends FormRequest
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
            'code' => 'required|string|size:6',
            'type' => 'required|in:email_verification,login_verification,password_reset',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Le code de vérification est requis',
            'code.size' => 'Le code de vérification doit contenir 6 caractères',
            'type.required' => 'Le type de vérification est requis',
            'type.in' => 'Type de vérification invalide',
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
