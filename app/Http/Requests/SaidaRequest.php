<?php

namespace App\Http\Requests;

use App\Models\Entrada;
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
        $qtdMax = 0;
        $quantidade = $this->quantidade ?? null;
        if($quantidade != null){            
            $entrada = Entrada::where('id', $this->estoque_id)->get();
            if($this->id != null){
                $saida = Saida::where('id', $this->id)->get();
                if (intval($quantidade) > $saida[0]->quantidade) {
                    $qtdMax = ($entrada[0]->quantidade  - $entrada[0]->qtdSaidas) + $saida[0]->quantidade;
                }else {
                    $subtotal = $saida[0]->quantidade - intval($quantidade);
                    $qtdMax = $entrada[0]->quantidade - $entrada[0]->qtdSaidas + $subtotal;
                }
            }else {
                $qtdMax = $entrada[0]->quantidade - $entrada[0]->qtdSaidas;
            }
        }
        return [
            'estoque_id' =>  'required',
            'quantidade' =>  "required|numeric|min:0.01|max:{$qtdMax}",
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
        $estoque = Entrada::where('id', $this->estoque_id)->get();
        if($this->id != null && $estoque[0]->qtdSaida == $this->qtdSaida){
            $mensagem = "A :attribute de saída não pode ser superior à quantidade da saida disponivel.";
        }else{
            $mensagem = "A :attribute de saída não pode ser superior à quantidade total da entrada no estoque.";
        }
        return [
            'required' => "O campo :attribute é obrigatorio.",
            'min' => "O campo :attribute não pode ser menor que 0 e nem ser 0.",
            'max' => $mensagem,
            'numeric' => "O campo :attribute deve ser númerico.",
        ];
    }
}
