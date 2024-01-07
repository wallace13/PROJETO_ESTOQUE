<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Telefone extends Model
{
    use CrudTrait;
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'telefones';
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
    public function getTipoTelefoneFormattedAttribute()
    {
        switch ($this->attributes['tipo_telefone']) {
            case '0':
                return "Telefone";
            case '1':
                return "Comercial";
            case '2':
                return "Celular";
            default:
                return "Não Encontrado";
        }
    }
    public function getNumeroFormattedAttribute()
    {
        return "(".$this->attributes['ddd'].") ".$this->attributes['numero_telefone'];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function users(){
        return $this->belongsTo(User::class,'user_id','id');
    }
    
    public function fornecedores(){
        return $this->belongsTo(Fornecedor::class,'fornecedor_id','id');
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
