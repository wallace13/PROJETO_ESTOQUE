<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\ActivityLogger;

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
        // Exemplo de registro de atividade no método 'created'
        static::created(function ($entrada) {
            $causador = backpack_auth()->user();
            $eventName = 'created';
            $descricao = "Nova entrada criado por {$causador->name}";
            ActivityLogger::logActivity($entrada, $eventName, $causador, $descricao,static::$logName,$entrada->attributes);
        });

        // Exemplo de registro de atividade no método 'updated'
        static::updated(function ($entrada) {
            $causador = backpack_auth()->user();
            $eventName = 'updated';
            $descricao = "Entrada atualizado por {$causador->name}";
            ActivityLogger::logActivity($entrada, $eventName, $causador, $descricao,static::$logName,$entrada->attributes);
        });

        // Exemplo de registro de atividade no método 'deleted'
        static::deleted(function ($entrada) {
            $causador = backpack_auth()->user();
            $eventName = 'deleted';
            $descricao = "Entrada excluído por {$causador->name}";
            ActivityLogger::logActivity($entrada, $eventName, $causador, $descricao,static::$logName,$entrada->attributes);
        });

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
