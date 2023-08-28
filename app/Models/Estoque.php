<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Estoque extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'estoques';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = ['produto_id', 'qtdTotal'];
    // protected $hidden = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function decodeValidadesJSON($validades){
        return json_decode($validades, true);
    }
    public function encodeValidadesJSON($validades){
        return json_encode($validades);
    }
    public function criaArrayValidades($validadeRequest) {
        return $validadeRequest;
    }
    public function buscaValidadeNoArray($validadeRequest, $validades) {
        return array_search($validadeRequest, $validades);
    }
    public function countValidadeEntrada($request) {
        
        $entradas = Entrada::all();
        $count = 0;
        $idProduto = (intval($request->produto_id) == null) ? $request->estoque->produto_id : intval($request->produto_id);
        foreach ($entradas as $item) {
            if ($item->validade === $request->validade && $idProduto === $item->estoque->produto_id && $item->qtdSaidas != $item->quantidade) {
                $count++;
            }
        }
        return $count;
    }
    public function removeValidade($indice, $validades) {
        unset($validades[$indice]);
        return array_values($validades);
    }
    public function atualizarQuantidadeEntrada($requestQuantidade, $entradaQuantidade){
        $requestQuantidade = intval($requestQuantidade);
        
        if ($requestQuantidade > $entradaQuantidade) {
            $total = $requestQuantidade - $entradaQuantidade;
            $quantidadeNova = $this->qtdTotal += $total;
        } else if($requestQuantidade < $entradaQuantidade){
            $total = $entradaQuantidade - $requestQuantidade;
            $quantidadeNova = $this->qtdTotal -= $total;
        } else if($requestQuantidade == $entradaQuantidade){
            $quantidadeNova = $this->qtdTotal;
        }

        return $quantidadeNova;        
    }
    
    public function atualizarQuantidadeSaida($requestQuantidade, $saidaQuantidade){
        $requestQuantidade = intval($requestQuantidade);
        $quantidadeNova = 0;
        $total = 0;

        if ($requestQuantidade < $saidaQuantidade) {
            $total = $saidaQuantidade - $requestQuantidade;
            $quantidadeNova = $this->qtdTotal += $total;
        }
        

        if ($requestQuantidade > $saidaQuantidade) {
            $subtotal = $requestQuantidade - $saidaQuantidade;
            $total = $subtotal + $saidaQuantidade;
            $quantidadeNova = $this->qtdTotal -= $subtotal;
        }

        if($requestQuantidade == $saidaQuantidade){
            $quantidadeNova = $this->qtdTotal;
        }

        return ["qtdNova" => $quantidadeNova,"total" => $total];        
    }
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function produto(){
        return $this->belongsTo(Produto::class,'produto_id','id');
    }
    public function entradas()
    {
        return $this->hasMany(Entrada::class);
    }
    public function saidas()
    {
        return $this->hasMany(Saida::class);
    }
    
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
