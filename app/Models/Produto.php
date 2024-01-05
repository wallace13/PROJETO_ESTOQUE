<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\ActivityLoggingService;

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

        static::created(fn($produto) => ActivityLoggingService::logActivity($produto, 'created', static::$logName));

        static::updated(fn($produto) => ActivityLoggingService::logActivity($produto, 'updated', static::$logName));

        static::deleted(fn($produto) => ActivityLoggingService::logActivity($produto, 'deleted', static::$logName));
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

    public function fornecedores(){
        return $this->belongsTo(Fornecedor::class,'fornecedor_id','id');
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
