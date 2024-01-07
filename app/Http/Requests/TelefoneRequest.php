<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TelefoneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'tipo_telefone' =>  'required',
            'ddd' =>  'required',
            'numero_telefone' =>  'required',
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     * 
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'tipo_telefone' =>  'Tipo',
            'ddd' =>  'DDD',
            'numero_telefone' =>  'Telefone',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'required' => "O campo :attribute é obrigatorio.",
        ];
    }
}
