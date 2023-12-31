<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\ActivityLoggingService;

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
        
        $resultado = ($this->validades !== null)? array_search($validadeRequest, $validades):false;
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
        $requestQuantidade = floatval($requestQuantidade);
        
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
        $requestQuantidade = floatval($requestQuantidade);
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
        $total = $this->qtdTotal - floatval($quantidade);
        return $total;
    }

    protected static function boot()
    {
        parent::boot();

        static::created(fn($estoque) => ActivityLoggingService::logActivity($estoque, 'created', static::$logName));

        static::updated(fn($estoque) => ActivityLoggingService::logActivity($estoque, 'updated', static::$logName));

        static::deleted(fn($estoque) => ActivityLoggingService::logActivity($estoque, 'deleted', static::$logName));
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
