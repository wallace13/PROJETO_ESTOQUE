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
    protected $fillable = ['qtdTotal', 'validades', 'produto_id'];

    protected $casts = [
        'validades' => 'json', 
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function decodeValidadesJSON(){
        // Verifica se $this->validades já é um array
        if (is_array($this->validades)) {
            return $this->validades;
        }
        return json_decode($this->validades, true);
    }
    public function encodeValidadesJSON($validades){
        return json_encode($validades);
    }
    public function criaArrayValidades($validadeRequest) {
        return $validadeRequest;
    }
    public function buscaValidadeNoArray($validadeRequest) {
        $validades = (is_array($this->validades)) ? $this->validades: json_decode($this->validades, true);
        $resultado = ($this->array !== null)? array_search($validadeRequest, $validades):false;

        return $resultado;
    }
    public function adicionaValidadeNoArray($validadeRequest){
        $validades = (is_array($this->validades)) ? $this->validades: json_decode($this->validades, true);
        array_push($validades, $validadeRequest);
        return $validades;
    }
    
    public function removeValidade($indice) {
        $validades = (is_array($this->validades)) ? $this->validades: json_decode($this->validades, true);
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
    public function removeQuantidade($quantidade){
        $total = $this->qtdTotal - intval($quantidade);
        return $total;
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
