<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\ActivityLoggingService;

class Entrada extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected static $logName = 'entrada';

    protected $table = 'entradas';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::created(fn($entrada) => ActivityLoggingService::logActivity($entrada, 'created', static::$logName));

        static::updated(fn($entrada) => ActivityLoggingService::logActivity($entrada, 'updated', static::$logName));

        static::deleted(fn($entrada) => ActivityLoggingService::logActivity($entrada, 'deleted', static::$logName));
    }
    public function atualizarQuantidadeSaidaNaSaida($requestQuantidade, $saidaQuantidade){
        $requestQuantidade = floatval($requestQuantidade);
        $quantidadeNova = 0;
        $total = 0;
        
        if ($requestQuantidade < $saidaQuantidade) {
            $total = $saidaQuantidade - $requestQuantidade;
            $quantidadeNova = $this->qtdSaidas - $total;
        } else {
            $quantidadeNova = $this->qtdSaidas+($requestQuantidade - $saidaQuantidade);
        }

        return ["qtdNova" => $quantidadeNova,"total" => $total];        
    }

    public function removeQuantidadeEntradaEstoque($quantidade){
        $total = $quantidade - $this->quantidade;
        return $total;
    }

    public function removeQuantidadeEntrada($quantidade){
        $total = $this->qtdSaidas - $quantidade;
        return $total;
    }

    public function removeQuantidadeSaida($quantidade){
        $total = $this->quantidade - $this->qtdSaidas - floatval($quantidade);
        return $total;
    }
    
    public function atualizarQuantidadeSaidaNaEntrada($quantidade)
    {
        $totalEntrada = $this->removeQuantidadeSaida($quantidade);

        $total = ($totalEntrada == 0) ? $this->quantidade : ($this->qtdSaidas + $quantidade);

        return ["quantidade" => $total,"quantidadeSaida" => $totalEntrada];
    }

    public function countValidadeEntrada($validade,$idEstoque) {
        
        $count = Entrada::where('validade', $validade)->where('estoque_id', $idEstoque)->count();
        return $count;
    }

    public function verificaQtdSaida(){
        if ($this->qtdSaidas !== null) {
            return $this->qtdSaidas;
        } 
        return 0;
    }

    public function verificaQtdRestante(){
        $quantidade = $this->verificaQtdSaida();
        $resultado =  $this->quantidade - $quantidade;
        return $resultado;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function estoque(){
        return $this->belongsTo(Estoque::class,'estoque_id','id');
    }
    public function saidas(){
        return $this->hasMany(Saida::class);
    }
    public function users(){
        return $this->belongsTo(User::class,'user_id','id');
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
