<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\ActivityLogger;

class Categoria extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected static $logName = 'categorias';

    protected $table = 'categorias';
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
        static::created(function ($categorias) {
            $causador = backpack_auth()->user();
            $eventName = 'created';
            $descricao = "Novo categoria criado por {$causador->name}";
            ActivityLogger::logActivity($categorias, $eventName, $causador, $descricao,static::$logName,$categorias->attributes);
        });

        // Exemplo de registro de atividade no método 'updated'
        static::updated(function ($categorias) {
            $causador = backpack_auth()->user();
            $eventName = 'updated';
            $descricao = "Categoria atualizado por {$causador->name}";
            ActivityLogger::logActivity($categorias, $eventName, $causador, $descricao,static::$logName,$categorias->attributes);
        });

        // Exemplo de registro de atividade no método 'deleted'
        static::deleted(function ($categorias) {
            $causador = backpack_auth()->user();
            $eventName = 'deleted';
            $descricao = "Categoria excluído por {$causador->name}";
            ActivityLogger::logActivity($categorias, $eventName, $causador, $descricao,static::$logName,$categorias->attributes);
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
