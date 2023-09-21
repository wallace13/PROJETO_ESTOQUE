<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogger;

class Estoque extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected static $logName = 'estoques';

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

    protected static function boot()
    {
        parent::boot();
        // Exemplo de registro de atividade no método 'created'
        static::created(function ($estoque) {
            $causador = backpack_auth()->user();
            $eventName = 'created';
            $descricao = "Nova estoque criado por {$causador->name}";
            ActivityLogger::logActivity($estoque, $eventName, $causador, $descricao,static::$logName,$estoque->attributes);
        });

        // Exemplo de registro de atividade no método 'updated'
        static::updated(function ($estoque) {
            $causador = backpack_auth()->user();
            $eventName = 'updated';
            $descricao = "Estoque atualizado por {$causador->name}";
            ActivityLogger::logActivity($estoque, $eventName, $causador, $descricao,static::$logName,$estoque->attributes);
        });

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
