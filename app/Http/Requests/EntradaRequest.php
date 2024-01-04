<?php

namespace App\Http\Requests;

use App\Models\Entrada;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EntradaRequest extends FormRequest
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
        if ($this->id == null) {
            $this->qtdSaidas = 0;
        }
        $estoqueIdRules = [
            Rule::requiredIf($this->id == null),
        ];
    
        return [
            'produto_id' => $estoqueIdRules,
            'quantidade' =>  'required|numeric|min:0.01',
            'validade' =>  'required',
            'qtdSaidas' => Rule::prohibitedIf(function () {
                return $this->qtdSaidas != 0;
            }),
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
            'produto_id'=> 'Produto',
            'quantidade'=> 'Quantidade',
            'validade'=> 'validade',
            'qtdSaidas'=> 'Quantidade de Saidas',
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
            'min' => "O campo :attribute não pode ser menor que 0 e nem ser 0.",
            'numeric' => "O campo :attribute deve ser númerico.",
            'qtdSaidas.prohibited' => 'Edição não permitida pois já foi dada saída do item.',
        ];
    }
}
