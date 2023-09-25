<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\ActivityLogger;

class Uf extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected static $logName = 'ufs';

    protected $table = 'ufs';
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
        static::created(function ($uf) {
            $causador = backpack_auth()->user();
            $eventName = 'created';
            $descricao = "Novo uf criado por {$causador->name}";
            ActivityLogger::logActivity($uf, $eventName, $causador, $descricao,static::$logName,$uf->attributes);
        });

        // Exemplo de registro de atividade no método 'updated'
        static::updated(function ($uf) {
            $causador = backpack_auth()->user();
            $eventName = 'updated';
            $descricao = "UF atualizado por {$causador->name}";
            ActivityLogger::logActivity($uf, $eventName, $causador, $descricao,static::$logName,$uf->attributes);
        });

        // Exemplo de registro de atividade no método 'deleted'
        static::deleted(function ($uf) {
            $causador = backpack_auth()->user();
            $eventName = 'deleted';
            $descricao = "UF excluído por {$causador->name}";
            ActivityLogger::logActivity($uf, $eventName, $causador, $descricao,static::$logName,$uf->attributes);
        });

    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }

    public function users()
    {
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
