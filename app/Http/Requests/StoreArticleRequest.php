<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;


class StoreArticleRequest extends FormRequest
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
            'entreprise_id' => 'required|integer|min:1',
            'code_article' => 'required|string|max:50|unique:articles,code_article',
            'designation' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'prix_unitaire' => 'required|numeric|min:0|max:99999999.99',
            'taux_tva' => 'required|integer|min:0|max:100',
           
        ];
    }

    public function messages(): array
    {
        return [
                'entreprise_id.required' => 'L\'ID de l\'entreprise est obligatoire',
                'code_article.required' => 'Le code article est obligatoire',
                'code_article.unique' => 'Ce code article existe déjà',
                'designation.required' => 'La désignation est obligatoire',
                'prix_unitaire.required' => 'Le prix unitaire est obligatoire',
                'prix_unitaire.numeric' => 'Le prix doit être un nombre',
                'taux_tva.integer' => 'Le taux de TVA doit être un entier',
                'taux_tva.max' => 'Le taux de TVA ne peut pas dépasser 100%'
        ];
    }




}
