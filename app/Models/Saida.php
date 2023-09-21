<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\ActivityLogger;

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
        // Exemplo de registro de atividade no método 'created'
        static::created(function ($saida) {
            $causador = backpack_auth()->user();
            $eventName = 'created';
            $descricao = "Nova saida criado por {$causador->name}";
            ActivityLogger::logActivity($saida, $eventName, $causador, $descricao,static::$logName,$saida->attributes);
        });

        // Exemplo de registro de atividade no método 'updated'
        static::updated(function ($saida) {
            $causador = backpack_auth()->user();
            $eventName = 'updated';
            $descricao = "Saida atualizado por {$causador->name}";
            ActivityLogger::logActivity($saida, $eventName, $causador, $descricao,static::$logName,$saida->attributes);
        });

        // Exemplo de registro de atividade no método 'deleted'
        static::deleted(function ($saida) {
            $causador = backpack_auth()->user();
            $eventName = 'deleted';
            $descricao = "Saida excluído por {$causador->name}";
            ActivityLogger::logActivity($saida, $eventName, $causador, $descricao,static::$logName,$saida->attributes);
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
