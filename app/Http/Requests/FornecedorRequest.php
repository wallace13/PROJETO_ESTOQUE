<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class FornecedorRequest extends FormRequest
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
        $cnpjRule = 'required|size:14|unique:fornecedores,cnpj';
    
        // Adicione uma condição para ignorar a unicidade ao editar o registro atual
        if ($this->has('id')) {
            $cnpjRule .= ',' . $this->id;
        }

        if ($this->cnpj != null) {
            $this->merge(['cnpj' => Str::of($this->cnpj)->replace(['.', '/','-','_'], ''),]);
        }
        return [
            'razao_social' => 'required',
            'cnpj' => $cnpjRule,
            'logradouro' =>  'required',
            'numero' =>  'required',
            'complemento' =>  'required',
            'bairro' =>  'required',
            'cidade' =>  'required',
            'estado' =>  'required',
            'cep' =>  'required',
            'telefone' =>  'required',
            'email' =>  'required|email',
            'responsavel_legal' =>  'required|alpha',
            'situacao_cadastral' =>  'required',
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'razao_social' => 'Razão Social',
            'cnpj' =>  'CNPJ',
            'logradouro' =>  'Logradouro',
            'numero' =>  'Número',
            'complemento' =>  'Complemento',
            'bairro' =>  'Bairro',
            'cidade' =>  'Cidade',
            'estado' =>  'Estado',
            'cep' =>  'CEP',
            'telefone' =>  'Telefone',
            'email' =>  'E-mail',
            'responsavel_legal' =>  'Responsável Legal',
            'situacao_cadastral' =>  'Situação Cadastral',
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
            'email' => "O campo deve ser um :attribute valido.",
            'alpha' => ":attribute inválido, informe um nome válido",
            'unique' => 'O :attribute já está cadastrado.',
            'size' => ':attribute inválido'
        ];
    }
}
