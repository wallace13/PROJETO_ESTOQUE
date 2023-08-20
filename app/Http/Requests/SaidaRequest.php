<?php

namespace App\Http\Requests;

use App\Models\Estoque;
use App\Models\Saida;
use Illuminate\Foundation\Http\FormRequest;

class SaidaRequest extends FormRequest
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
        
        $regras = [];
        $quantidade = $this->quantidade ?? null;
        if($quantidade != null){            
            $estoque = Estoque::where('produto_id', $this->produto_id)->get();
            if($this->id != null && $estoque[0]->qtdTotal == 0){
                $saida = Saida::where('id', $this->id)->get();
                $qtdMax = $saida[0]->quantidade;
            }else{
                $qtdMax = $estoque[0]->qtdTotal;
            }
            $regras = [
                'produto_id' =>  'required',
                'quantidade' =>  "required|numeric|min:0.01|max:{$qtdMax}",
            ];
        }
        
        return $regras;
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
            'quantidade'=> 'Quantidade'
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        $estoque = Estoque::where('produto_id', $this->produto_id)->get();
        if($this->id != null && $estoque[0]->qtdTotal == 0){
            $mensagem = "A :attribute de saída não pode ser superior à quantidade da saida atual.";
        }else{
            $mensagem = "A :attribute de saída não pode ser superior à quantidade total do estoque.";
        }
        return [
            'required' => "O campo :attribute é obrigatorio.",
            'min' => "O campo :attribute não pode ser menor que 0 e nem ser 0.",
            'max' => $mensagem,
            'numeric' => "O campo :attribute deve ser númerico.",
        ];
    }
}
