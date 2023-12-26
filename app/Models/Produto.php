<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\ActivityLogger;

class Produto extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected static $logName = 'produtos';

    protected $table = 'produtos';
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
    public function getFormattedNameAttribute()
    {
        $uf = \App\Models\Uf::find($this->uf_id);
        return $this->nome . ' - ' . $uf->uf;
    }

    protected static function boot()
    {
        parent::boot();
        // Exemplo de registro de atividade no método 'created'
        static::created(function ($produto) {
            $causador = backpack_auth()->user();
            $eventName = 'created';
            $descricao = "Novo produto criado por {$causador->name}";
            ActivityLogger::logActivity($produto, $eventName, $causador, $descricao,static::$logName,$produto->attributes);
        });

        // Exemplo de registro de atividade no método 'updated'
        static::updated(function ($produto) {
            $causador = backpack_auth()->user();
            $eventName = 'updated';
            $descricao = "Produto atualizado por {$causador->name}";
            ActivityLogger::logActivity($produto, $eventName, $causador, $descricao,static::$logName,$produto->attributes);
        });

        // Exemplo de registro de atividade no método 'deleted'
        static::deleted(function ($produto) {
            $causador = backpack_auth()->user();
            $eventName = 'deleted';
            $descricao = "Produto excluído por {$causador->name}";
            ActivityLogger::logActivity($produto, $eventName, $causador, $descricao,static::$logName,$produto->attributes);
        });

    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function ufs(){
        return $this->belongsTo(Uf::class,'uf_id','id');
    }

    public function categorias(){
        return $this->belongsTo(Categoria::class,'categoria_id','id');
    }

    public function users(){
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function estoques(){
        return $this->hasMany(Estoque::class);
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
