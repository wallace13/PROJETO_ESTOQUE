<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\ActivityLoggingService;

class Saida extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected static $logName = 'saida';

    protected $table = 'saidas';
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

        static::created(fn($saida) => ActivityLoggingService::logActivity($saida, 'created', static::$logName));

        static::updated(fn($saida) => ActivityLoggingService::logActivity($saida, 'updated', static::$logName));

        static::deleted(fn($saida) => ActivityLoggingService::logActivity($saida, 'deleted', static::$logName));
    }

    public function devolveQuantidadeSaida($quantidade){
        $total = $quantidade + $this->quantidade;
        return $total;
    }

    public function verificaQuantidade($entrada, $requestQuantidadeSaida,$saida){
        $requestQuantidade = floatval($requestQuantidadeSaida);
        $quantidadeRestante = $entrada->verificaQtdRestante();

        if ($requestQuantidade > $saida->quantidade) {
            return $quantidadeRestante + $saida->quantidade;
        }

        if($requestQuantidade === $entrada->quantidade){
            return $entrada->quantidade;
        }else{
            $subtotal = $saida->quantidade - $requestQuantidade;
            return $quantidadeRestante + $subtotal;
        }

    }
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function estoque(){
        return $this->belongsTo(Estoque::class,'estoque_id','id');
    }
    public function entrada(){
        return $this->belongsTo(Entrada::class,'entrada_id','id');
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
