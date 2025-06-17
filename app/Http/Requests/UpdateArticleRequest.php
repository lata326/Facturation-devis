<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;


class UpdateArticleRequest extends FormRequest
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
        $articleId = $this->route('article');
        return [

              'entreprise_id' => 'sometimes|integer|min:1',
            'code_article' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('articles', 'code_article')->ignore($articleId, 'article_id')
            ],
            'designation' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:5000',
            'prix_unitaire' => 'sometimes|numeric|min:0|max:99999999.99',
            'taux_tva' => 'sometimes|integer|min:0|max:100',
        ];
        
    }

     public function messages(): array
    {
        return [
            'code_article.unique' => 'Ce code article existe déjà',
            'prix_unitaire.numeric' => 'Le prix doit être un nombre',
            'taux_tva.integer' => 'Le taux de TVA doit être un entier',
            'taux_tva.max' => 'Le taux de TVA ne peut pas dépasser 100%'
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
